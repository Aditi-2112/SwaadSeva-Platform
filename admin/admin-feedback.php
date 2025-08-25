<?php
session_start();
if ($_SESSION['user_role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}
include 'db.php';

// Get filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

// Build query
$sql = "SELECT feedback.id, users.name AS username, feedback.rating, feedback.message, feedback.admin_reply
        FROM feedback
        INNER JOIN users ON feedback.user_id = users.id
        WHERE users.name LIKE '%$search%' OR feedback.message LIKE '%$search%'";

if ($sort === 'high') {
  $sql .= " ORDER BY feedback.rating DESC";
} elseif ($sort === 'low') {
  $sql .= " ORDER BY feedback.rating ASC";
}

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Feedback - Admin Panel</title>

  <link rel="stylesheet" href="css/admin-style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    .modal-button-group {
      margin-top: 15px;
      display: flex;
      gap: 10px;
      justify-content: flex-end;
    }

    .modal-action-btn {
      padding: 8px 16px;
      background-color: #007bff;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }

    .modal-action-btn:hover {
      background-color: #0056b3;
    }

    .modal-action-btn.danger {
      background-color: #dc3545;
    }

    .modal-action-btn.danger:hover {
      background-color: #a9001f;
    }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <aside class="sidebar">
    <img src="assets/logo/logo5.png" alt="Logo Image">
    <div class="brand">SwaadSeva Admin</div>
    <ul class="sidebar-menu">
      <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
      <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
      <li><a href="orders.php"><i class="fas fa-box"></i> Orders</a></li>
      <li><a href="admin-event-orders.php"><i class="fas fa-calendar-alt"></i> Event Orders</a></li>
      <li><a href="menu.php"><i class="fas fa-utensils"></i> Menu</a></li>
      <li><a href="plans.php"><i class="fas fa-clipboard-list"></i> Plans</a></li>
      <li><a href="admin-feedback.php" class="active"><i class="fas fa-comment-dots"></i> Feedback</a></li>
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
      <h1>Customer Feedback</h1>
      <p>View and manage feedback from users.</p>
    </div>

    <!-- Filter Bar -->
    <form class="filter-bar" method="GET">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or message">
      <select name="sort">
        <option value="">Sort by Rating</option>
        <option value="high" <?= $sort === 'high' ? 'selected' : '' ?>>Highest First</option>
        <option value="low" <?= $sort === 'low' ? 'selected' : '' ?>>Lowest First</option>
      </select>
      <button type="submit">Apply</button>
    </form>



    <!-- Feedback Table -->
    <div class="feedback-wrapper">
      <div class="responsive-table-container">
        <table class="responsive-table">
          <thead>
            <tr>
              <th>Sr No</th>
              <th>User</th>
              <th>Rating</th>
              <th>Message</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sr = 1;
            while ($row = mysqli_fetch_assoc($result)) {
              $replyGiven = !empty($row['admin_reply']);
              $escapedReply = htmlspecialchars($row['admin_reply'], ENT_QUOTES);

              echo "<tr>
            <td>{$sr}</td>
            <td>{$row['username']}</td>
            <td><span class='rating-stars'>" . str_repeat("<i class='fas fa-star'></i>", $row['rating']) . "</span></td>
            <td>{$row['message']}</td>
            <td class='actions-cell'>";

              if ($replyGiven) {
                echo "<a href='#' 
              class='action-btn replied' 
              data-username=\"" . htmlspecialchars($row['username'], ENT_QUOTES) . "\"
              data-reply=\"{$escapedReply}\" 
              data-id='{$row['id']}' 
              onclick='handleViewReply(this)'>View Reply</a>";
              } else {
                echo "<a href='#' 
              class='action-btn reply' 
              onclick=\"openReply('{$row['username']}', {$row['id']})\">Reply</a>";
              }

              echo "<a href='#' 
              class='action-btn delete' 
              onclick=\"openDelete('{$row['username']}', {$row['id']})\">Delete</a>";

              echo "</td></tr>";

              $sr++;
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>



  </main>

  <!-- Modal -->
  <div class="modal-overlay" id="modalOverlay">
    <div class="modal-box">
      <span class="close-modal" onclick="closeModal()">&times;</span>
      <div id="modalContent">
        <!-- Injected from JS -->
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    function openReply(name, feedbackId) {
      document.getElementById('modalContent').innerHTML = `
      <h3>Reply to ${name}</h3>
      <form method="POST" action="send_reply.php">
      <input type="hidden" name="feedback_id" value="${feedbackId}">
      <textarea name="admin_reply" rows="5" style="width: 100%; padding: 10px;" placeholder="Type your reply..."></textarea>
      <div class="modal-button-group">
      <button type="submit" class="modal-action-btn">Send Reply</button>
          <button type="button" class="modal-action-btn danger" onclick="closeModal()">Cancel</button>
          </div>
          </form>
          `;
      document.getElementById('modalOverlay').style.display = 'flex';
    }

    function handleViewReply(element) {
      const name = element.getAttribute('data-username');
      const reply = element.getAttribute('data-reply');
      const feedbackId = element.getAttribute('data-id');

      document.getElementById('modalContent').innerHTML = `
          <h3>Reply to ${name}</h3>
          <form method="POST" action="send_reply.php">
          <input type="hidden" name="feedback_id" value="${feedbackId}">
          <textarea name="admin_reply" rows="5" style="width: 100%; padding: 10px;">${reply}</textarea>
          <div class="modal-button-group">
          <button type="submit" class="modal-action-btn">Update Reply</button>
          <button type="button" class="modal-action-btn danger" onclick="closeModal()">Close</button>
          </div>
          </form>
          `;
      document.getElementById('modalOverlay').style.display = 'flex';
    }


    function openDelete(name, feedbackId) {
      document.getElementById('modalContent').innerHTML = `
          <h3>Delete Feedback</h3>
          <p>Are you sure you want to delete feedback from <strong>${name}</strong>?</p>
          <form method="POST" action="delete_feedback.php">
          <input type="hidden" name="feedback_id" value="${feedbackId}">
        <div class="modal-button-group">
        <button type="submit" class="modal-action-btn danger">Yes, Delete</button>
        <button type="button" class="modal-action-btn" onclick="closeModal()">Cancel</button>
        </div>
        </form>
        `;
      document.getElementById('modalOverlay').style.display = 'flex';
    }

    function closeModal() {
      document.getElementById('modalOverlay').style.display = 'none';
    }
  </script>

  <script src="js/admin.js"></script>
</body>

</html>