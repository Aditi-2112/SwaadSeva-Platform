<?php
session_start();
if ($_SESSION['user_role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}
require_once 'db.php';

// --- Calculate Today's Meal Counts ---
$today = date('Y-m-d');
$mealCount = ['Breakfast' => 0, 'Lunch' => 0, 'Dinner' => 0];
$specialCount = 0;

$sql = "SELECT p.meal_type FROM user_plans up
        JOIN plans p ON up.plan_id = p.id
        WHERE up.start_date <= ? AND up.end_date >= ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $today, $today);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
  $meals = explode(',', str_replace([' + ', '+'], ',', $row['meal_type']));
  foreach ($meals as $meal) {
    $meal = trim($meal);
    if (isset($mealCount[$meal]))
      $mealCount[$meal]++;
  }
}

$sql2 = "SELECT COUNT(*) AS total FROM special_orders WHERE order_date = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("s", $today);
$stmt2->execute();
$result2 = $stmt2->get_result();
$specialCount = $result2->fetch_assoc()['total'];

// --- Payment Summary ---
$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');

function getPaymentSummary($conn, $startDate, $endDate, $order_type = 'normal', $filter_status = '', $search = '')
{
  $totalPaid = 0;
  $totalPending = 0;
  $totalRevenue = 0;

  if ($order_type === 'normal') {
    $sql = "SELECT p.total_price AS plan_total, up.amount_paid, up.payment_status
                FROM user_plans up
                JOIN plans p ON up.plan_id = p.id
                JOIN users u ON up.user_id = u.id
                WHERE DATE(up.start_date) BETWEEN ? AND ?";

    if ($filter_status === 'paid') {
      $sql .= " AND up.payment_status = 'Paid'";
    } elseif ($filter_status === 'pending') {
      $sql .= " AND up.payment_status != 'Paid'";
    }

    if (!empty($search)) {
      $sql .= " AND (u.name LIKE ? OR p.name LIKE ?)";
    }

    $stmt = $conn->prepare($sql);
    if (!empty($search)) {
      $searchParam = "%$search%";
      $stmt->bind_param("ssss", $startDate, $endDate, $searchParam, $searchParam);
    } else {
      $stmt->bind_param("ss", $startDate, $endDate);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
      $planTotal = floatval($row['plan_total']);
      $amountPaid = floatval($row['amount_paid']);
      $totalRevenue += $planTotal;
      $totalPaid += $amountPaid;

      if (strtolower($row['payment_status']) !== 'paid') {
        $totalPending += ($planTotal - $amountPaid);
      }
    }
  } else { // Special Orders
    $sql = "SELECT order_price, paid_amount, payment_status
                FROM special_orders
                WHERE order_date BETWEEN ? AND ?";

    if ($filter_status === 'paid') {
      $sql .= " AND payment_status = 'Paid'";
    } elseif ($filter_status === 'pending') {
      $sql .= " AND payment_status != 'Paid'";
    }

    if (!empty($search)) {
      $sql .= " AND (name LIKE ? OR menu LIKE ?)";
    }

    $stmt = $conn->prepare($sql);
    if (!empty($search)) {
      $searchParam = "%$search%";
      $stmt->bind_param("ssss", $startDate, $endDate, $searchParam, $searchParam);
    } else {
      $stmt->bind_param("ss", $startDate, $endDate);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
      $orderTotal = floatval($row['order_price']);
      $paidAmount = floatval($row['paid_amount']);
      $totalRevenue += $orderTotal;
      $totalPaid += $paidAmount;

      if (strtolower($row['payment_status']) !== 'paid') {
        $totalPending += ($orderTotal - $paidAmount);
      }
    }
  }

  return [$totalPaid, $totalPending, $totalRevenue];
}




list($paidN, $pendingN, $revN) = getPaymentSummary($conn, $monthStart, $monthEnd, 'normal');
list($paidS, $pendingS, $revS) = getPaymentSummary($conn, $monthStart, $monthEnd, 'special');

$totalPaid = $paidN + $paidS;
$totalPending = $pendingN + $pendingS;
$totalRevenue = $revN + $revS;

// Menu for today
$todayDay = date('l');
$menuQuery = $conn->prepare("SELECT meal_type, items FROM menu WHERE day_of_week = ?");
$menuQuery->bind_param("s", $todayDay);
$menuQuery->execute();
$menuRes = $menuQuery->get_result();
$menuToday = [];
while ($row = $menuRes->fetch_assoc()) {
  $menuToday[$row['meal_type']] = $row['items'];
}






// --- Poll System ---
$pollDataList = [];

$pollsQuery = $conn->query("SELECT * FROM polls ORDER BY created_at DESC");

if ($pollsQuery && $pollsQuery->num_rows > 0) {
  while ($poll = $pollsQuery->fetch_assoc()) {
    $voteCounts = [0, 0, 0, 0]; // For 4 options

    // Get vote counts for each option
    $voteStmt = $conn->prepare("
      SELECT option_selected, COUNT(*) AS total 
      FROM poll_votes 
      WHERE poll_id = ? 
      GROUP BY option_selected
    ");
    $voteStmt->bind_param("i", $poll['id']);
    $voteStmt->execute();
    $voteRes = $voteStmt->get_result();

    while ($row = $voteRes->fetch_assoc()) {
      $index = intval($row['option_selected']) - 1;
      if ($index >= 0 && $index < 4) {
        $voteCounts[$index] = intval($row['total']);
      }
    }

    // Store the poll and its votes
    $pollDataList[] = [
      'poll' => $poll,
      'votes' => $voteCounts
    ];
  }
}




?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | SwaadSeva</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/admin-style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    #mealChart {
      max-height: 190px;
      /* Reduce height */
      max-width: 100%;
      width: 100%;
      height: auto;
      display: block;
      margin: 0 auto;
    }



    /* Poll Card Buttons */
    .poll-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 15px;
    }

    .poll-actions button {
      padding: 8px 14px;
      font-size: 14px;
      border: none;
      border-radius: 5px;
      background-color: #007bff;
      color: #fff;
      transition: background-color 0.2s;
    }

    .poll-actions button:hover {
      background-color: #0056b3;
    }

    /* Poll Modal (Add/Edit) */
    .poll-modal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100vw;
      height: 100vh;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
    }

    .poll-modal.show {
      display: flex;
    }

    .poll-modal-content {
      background-color: #fff;
      padding: 25px 30px;
      border-radius: 10px;
      max-width: 500px;
      width: 90%;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      position: relative;
      animation: fadeIn 0.3s ease-in-out;
    }

    .poll-modal-content h2 {
      font-size: 20px;
      margin-bottom: 18px;
      text-align: center;
    }

    .poll-modal-content input[type="text"],
    .poll-modal-content textarea {
      display: block;
      width: 100%;
      padding: 10px 12px;
      margin-bottom: 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
      box-sizing: border-box;
    }

    .poll-modal-content button[type="submit"] {
      background-color: #28a745;
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
      font-size: 15px;
      margin-top: 8px;
    }

    .poll-modal-content button[type="submit"]:hover {
      background-color: #218838;
    }

    .poll-modal-close {
      position: absolute;
      top: 12px;
      right: 16px;
      font-size: 22px;
      font-weight: bold;
      color: #888;
      cursor: pointer;
    }

    .poll-modal-close:hover {
      color: #000;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: scale(0.95);
      }

      to {
        opacity: 1;
        transform: scale(1);
      }
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <aside class="sidebar">
    <img src="assets/logo/logo5.png" alt="Logo Image">
    <div class="brand">SwaadSeva Admin</div>
    <ul class="sidebar-menu">
      <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
      <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
      <li><a href="orders.php"><i class="fas fa-box"></i> Orders</a></li>
      <li><a href="admin-event-orders.php"><i class="fas fa-calendar-alt"></i> Event Orders</a></li>
      <li><a href="menu.php"><i class="fas fa-utensils"></i> Menu</a></li>
      <li><a href="plans.php"><i class="fas fa-clipboard-list"></i> Plans</a></li>
      <li><a href="admin-feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a></li>
      <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
      <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
    </ul>
  </aside>

  <!-- Topbar -->
  <header class="topbar">
    <div class="topbar-left">
      <button class="menu-toggle"><i class="fas fa-bars"></i></button>
    </div>
    <div class="topbar-right">
      <span class="admin-name">Hello, Admin</span>
      <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </header>


  <!-- Main Content -->
  <main class="admin-main">
    <div class="page-header">
      <h1>Dashboard</h1>
      <p>Welcome back, Admin. Here’s your daily snapshot.</p>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-clock"></i>
          <h3>Today's Meal Orders</h3>
        </div>
        <p>Breakfast: <?= $mealCount['Breakfast'] ?> | Lunch: <?= $mealCount['Lunch'] ?> | Dinner:
          <?= $mealCount['Dinner'] ?> | Special: <?= $specialCount ?>
        </p><br>
        <canvas id="mealChart"></canvas>
      </div>

      <div class="card">
        <div class="card-header">
          <i class="fas fa-rupee-sign"></i>
          <h3>Monthly Revenue Summary</h3>
        </div>
        <p>Normal - Paid: ₹<?= $paidN ?>, Pending: ₹<?= $pendingN ?><br>
          Special - Paid: ₹<?= $paidS ?>, Pending: ₹<?= $pendingS ?><br>
          Total - Paid: ₹<?= $totalPaid ?>, Pending: ₹<?= $totalPending ?>, Revenue: ₹<?= $totalRevenue ?></p>
        <canvas id="paymentChart"></canvas>
      </div>

      <div class="card">
        <div class="card-header">
          <i class="fas fa-utensils"></i>
          <h3>Today's Menu (<?= $todayDay ?>)</h3>
        </div>
        <?php foreach ($menuToday as $meal => $items): ?>
          <p><strong><?= $meal ?>:</strong> <?= htmlspecialchars($items) ?></p>
        <?php endforeach; ?>
      </div>

      <div class="card" id="notesCard">
        <div class="card-header">
          <i class="fas fa-sticky-note"></i>
          <h3>Important Notes</h3>
        </div>
        <div id="notesContainer"></div>
        <form id="noteForm">
          <input type="text" id="noteSubject" placeholder="Subject" required>
          <textarea id="noteText" placeholder="Write note..." required></textarea>
          <button type="submit">Add Note</button>
        </form>
      </div>






      <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <i class="fas fa-poll"></i>
            <h3 style="display: inline-block; margin-left: 10px;">Polls</h3>
          </div>
        </div>

        <div class="card-content poll-container">
          <?php
          $polls = $conn->query("SELECT * FROM polls ORDER BY created_at DESC");
          if ($polls->num_rows > 0):
            while ($poll = $polls->fetch_assoc()):
              $pid = $poll['id'];

              // Get vote counts
              $votes = [0, 0, 0, 0];
              $voteStmt = $conn->prepare("SELECT option_selected, COUNT(*) AS total FROM poll_votes WHERE poll_id = ? GROUP BY option_selected");
              $voteStmt->bind_param("i", $pid);
              $voteStmt->execute();
              $voteRes = $voteStmt->get_result();
              while ($row = $voteRes->fetch_assoc()) {
                $opt = intval($row['option_selected']) - 1;
                if ($opt >= 0 && $opt < 4)
                  $votes[$opt] = intval($row['total']);
              }

              $labels = [];
              $options = [];
              for ($i = 1; $i <= 4; $i++) {
                if (!empty($poll["option$i"])) {
                  $labels[] = htmlspecialchars($poll["option$i"]);
                  $options[] = $votes[$i - 1];
                }
              }

              $chartId = "pollChart_" . $pid;
              ?>
              <div class="poll-item">
                <h4><?= htmlspecialchars($poll['question']) ?></h4>
                <!-- <div class="poll-options">
                  <?php foreach ($labels as $i => $opt): ?>
                    <div><?= $i + 1 ?>. <?= $opt ?> — <strong><?= $options[$i] ?> votes</strong></div>
                  <?php endforeach; ?>
                </div> -->

                <canvas id="<?= $chartId ?>" height="75"></canvas>

                <div class="poll-actions">
                  <button onclick="deletePoll(<?= $pid ?>)" style="background-color:red;">Delete</button>
                  <button onclick="editPoll(<?= $pid ?>)">Edit</button>
                </div>
              </div>

              <script>
                document.addEventListener('DOMContentLoaded', function () {
                  new Chart(document.getElementById("<?= $chartId ?>"), {
                    type: 'bar',
                    data: {
                      labels: <?= json_encode($labels) ?>,
                      datasets: [{
                        label: 'Votes',
                        data: <?= json_encode($options) ?>,
                        backgroundColor: [
                          '#4CAF50',  // green
                          '#FF9800',  // orange
                          '#2196F3',  // blue
                          '#E91E63'   // pink
                        ]
                      }]
                    },
                    options: {
                      plugins: { legend: { display: false } },
                      scales: {
                        y: { beginAtZero: true }
                      }
                    }
                  });
                });

              </script>
            <?php endwhile;
          else:
            echo "<p>No polls created yet.</p>";
          endif;
          ?>
        </div>
      </div>



    </div>




    <div class="quick-actions">
      <h3>Quick Actions</h3>
      <div class="actions-wrapper">
        <a href="menu.php" class="btn">Update Menu</a>
        <a href="orders.php" class="btn">View Orders</a>
        <a href="#" class="btn">Send Notification</a>
        <button class="btn" onclick="document.getElementById('addPollModal').classList.add('show');">
          <i class="fas fa-plus"></i> Add Poll
        </button>
      </div>
    </div>



    <!-- Add Poll Modal -->
    <div id="addPollModal" class="poll-modal">
      <div class="poll-modal-content">
        <span class="poll-modal-close" onclick="closeAddPollModal()">&times;</span>
        <h2>Add New Poll</h2>
        <form action="create_poll.php" method="POST">
          <input type="text" name="question" placeholder="Poll Question" required>
          <input type="text" name="option1" placeholder="Option 1" required>
          <input type="text" name="option2" placeholder="Option 2" required>
          <input type="text" name="option3" placeholder="Option 3 (optional)">
          <input type="text" name="option4" placeholder="Option 4 (optional)">
          <button type="submit">Create Poll</button>
        </form>
      </div>
    </div>

    <!-- Edit Poll Modal -->
    <div id="editPollModal" class="poll-modal">
      <div class="poll-modal-content">
        <span class="poll-modal-close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Poll</h2>
        <form id="editPollForm" action="edit_poll.php" method="POST">
          <input type="hidden" name="poll_id" id="editPollId">
          <input type="text" name="question" id="editQuestion" placeholder="Poll Question" required>
          <input type="text" name="option1" id="editOption1" placeholder="Option 1" required>
          <input type="text" name="option2" id="editOption2" placeholder="Option 2" required>
          <input type="text" name="option3" id="editOption3" placeholder="Option 3 (optional)">
          <input type="text" name="option4" id="editOption4" placeholder="Option 4 (optional)">
          <button type="submit">Save Changes</button>
        </form>
      </div>
    </div>






  </main>


  <script>
    const form = document.getElementById('noteForm');
    const container = document.getElementById('notesContainer');

    function loadNotes() {
      const notes = JSON.parse(localStorage.getItem('admin_notes') || '[]');
      container.innerHTML = '';
      notes.forEach((note, i) => {
        const div = document.createElement('div');
        div.className = 'note';
        div.innerHTML = `<strong>${note.subject}</strong><br>${note.text}<br><small style="color:gray">${note.time}</small><br><button onclick="deleteNote(${i})">Delete</button>`;
        container.appendChild(div);
      });
    }

    function deleteNote(index) {
      const notes = JSON.parse(localStorage.getItem('admin_notes') || '[]');
      notes.splice(index, 1);
      localStorage.setItem('admin_notes', JSON.stringify(notes));
      loadNotes();
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const subject = document.getElementById('noteSubject').value;
      const text = document.getElementById('noteText').value;
      const notes = JSON.parse(localStorage.getItem('admin_notes') || '[]');
      const timestamp = new Date().toLocaleString();
      notes.push({ subject, text, time: timestamp });
      localStorage.setItem('admin_notes', JSON.stringify(notes));
      loadNotes();
      form.reset();
    });



    document.addEventListener('DOMContentLoaded', () => {
      loadNotes();

      new Chart(document.getElementById('mealChart'), {
        type: 'doughnut',
        data: {
          labels: ['Breakfast', 'Lunch', 'Dinner', 'Special'],
          datasets: [{
            data: [<?= $mealCount['Breakfast'] ?>, <?= $mealCount['Lunch'] ?>, <?= $mealCount['Dinner'] ?>, <?= $specialCount ?>],
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#66BB6A']
          }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
      });

      new Chart(document.getElementById('paymentChart'), {
        type: 'bar',
        data: {
          labels: ['Normal Paid', 'Normal Pending', 'Special Paid', 'Special Pending'],
          datasets: [{
            label: 'Amount (₹)',
            data: [<?= $paidN ?>, <?= $pendingN ?>, <?= $paidS ?>, <?= $pendingS ?>],
            backgroundColor: ['#4CAF50', '#FF9800', '#2196F3', '#f44336']
          }]
        },
        options: {
          indexAxis: 'y',  // 💡 Makes the bars horizontal
          scales: {
            x: { beginAtZero: true }
          }
        }
      });

    });
  </script>







  <script>
    function editPoll(id) {
      fetch('get_poll.php?id=' + id)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('editPollId').value = data.poll.id;
            document.getElementById('editQuestion').value = data.poll.question;
            document.getElementById('editOption1').value = data.poll.option1;
            document.getElementById('editOption2').value = data.poll.option2;
            document.getElementById('editOption3').value = data.poll.option3;
            document.getElementById('editOption4').value = data.poll.option4;
            document.getElementById('editPollModal').classList.add('show');
          } else {
            alert('Failed to load poll data.');
          }
        })
        .catch(err => {
          alert('Error: ' + err);
        });
    }

    function closeEditModal() {
      document.getElementById('editPollModal').classList.remove('show');
    }
    function closeAddPollModal() {
      document.getElementById('addPollModal').classList.remove('show');
    }


    function deletePoll(id) {
      if (confirm("Are you sure you want to delete this poll?")) {
        window.location.href = "delete_poll.php?id=" + id;
      }
    }
  </script>


  <script src="js/admin.js"></script>



</body>

</html>