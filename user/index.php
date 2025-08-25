<?php
session_start();
require_once 'db.php';

if ($_SESSION['user_role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

// if (!isset($_SESSION['user_id'])) {
//     header("Location: ../login.php");
//     exit;
// }

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';

$today = date('Y-m-d');
$dayName = strtolower(date('l'));

// Fetch active user plans
$mealCounts = ['Breakfast' => 0, 'Lunch' => 0, 'Dinner' => 0];
$plans = [];
$menuToday = ['Breakfast' => '-', 'Lunch' => '-', 'Dinner' => '-'];

$stmt = $conn->prepare("SELECT up.*, p.name AS plan_name, p.meal_type, p.total_price FROM user_plans up JOIN plans p ON up.plan_id = p.id WHERE up.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $plans[] = $row;
    if ($today >= $row['start_date'] && $today <= $row['end_date']) {
        $meals = explode(',', $row['meal_type']);
        foreach ($meals as $meal) {
            $mealType = ucfirst(trim(strtolower($meal)));
            if (isset($mealCounts[$mealType])) {
                $mealCounts[$mealType]++;
            }
        }
    }
}
$stmt->close();

// Today's Menu
$menuStmt = $conn->prepare("SELECT meal_type, items FROM menu WHERE LOWER(day_of_week) = ?");
$menuStmt->bind_param("s", $dayName);
$menuStmt->execute();
$menuRes = $menuStmt->get_result();
while ($menu = $menuRes->fetch_assoc()) {
    $mealType = ucfirst($menu['meal_type']);
    $menuToday[$mealType] = $menu['items'];
}
$menuStmt->close();

// Normal Orders
$normalPaid = $normalPending = $normalTotal = 0;
foreach ($plans as $plan) {
    $paid = floatval($plan['amount_paid']);
    $total = floatval($plan['total_price']);
    $pending = $total - $paid;
    $normalPaid += $paid;
    $normalPending += $pending;
    $normalTotal += $total;
}

// Special Orders
$specialOrders = [];
$eventPaid = $eventPending = $eventTotal = 0;
$specialQuery = $conn->prepare("SELECT * FROM special_orders WHERE user_id = ? ORDER BY order_date DESC");
$specialQuery->bind_param("i", $userId);
$specialQuery->execute();
$specialResult = $specialQuery->get_result();
while ($row = $specialResult->fetch_assoc()) {
    $specialOrders[] = $row;
    $paid = floatval($row['paid_amount']);
    $total = floatval($row['order_price']);
    $pending = $total - $paid;
    $eventPaid += $paid;
    $eventPending += $pending;
    $eventTotal += $total;
}
$specialQuery->close();

// Combined
$combinedPaid = $normalPaid + $eventPaid;
$combinedPending = $normalPending + $eventPending;
$combinedTotal = $normalTotal + $eventTotal;





$polls = [];
$user_id = $_SESSION['user_id'];

$pollQuery = $conn->query("SELECT * FROM polls ORDER BY created_at DESC");
while ($poll = $pollQuery->fetch_assoc()) {
    $poll_id = $poll['id'];

    // Check if user has voted
    $voteCheck = $conn->prepare("SELECT * FROM poll_votes WHERE user_id = ? AND poll_id = ?");
    $voteCheck->bind_param("ii", $user_id, $poll_id);
    $voteCheck->execute();
    $voteRes = $voteCheck->get_result();
    $hasVoted = $voteRes->num_rows > 0;

    $voteData = [0, 0, 0, 0];
    if ($hasVoted) {
        $resultQuery = $conn->prepare("SELECT option_selected, COUNT(*) AS count FROM poll_votes WHERE poll_id = ? GROUP BY option_selected");
        $resultQuery->bind_param("i", $poll_id);
        $resultQuery->execute();
        $resultRes = $resultQuery->get_result();
        while ($r = $resultRes->fetch_assoc()) {
            $index = (int) $r['option_selected'] - 1;
            $voteData[$index] = $r['count'];
        }
    }

    $polls[] = [
        'data' => $poll,
        'hasVoted' => $hasVoted,
        'voteData' => $voteData
    ];
}

$hasVoted = false;
$voteData = null;

if ($poll) {
    $user_id = $_SESSION['user_id'];
    $voteCheck = $conn->prepare("SELECT * FROM poll_votes WHERE user_id = ? AND poll_id = ?");
    $voteCheck->bind_param("ii", $user_id, $poll['id']);
    $voteCheck->execute();
    $voteRes = $voteCheck->get_result();
    $hasVoted = $voteRes->num_rows > 0;

    if ($hasVoted) {
        $resultQuery = $conn->prepare("SELECT option_selected, COUNT(*) AS count FROM poll_votes WHERE poll_id = ? GROUP BY option_selected");
        $resultQuery->bind_param("i", $poll['id']);
        $resultQuery->execute();
        $resultRes = $resultQuery->get_result();

        $voteData = [0, 0, 0, 0];
        while ($r = $resultRes->fetch_assoc()) {
            $index = (int) $r['option_selected'] - 1;
            $voteData[$index] = $r['count'];
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - SwaadSeva</title>
    <link rel="stylesheet" href="css/user-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1"></script>
    <script src="js/user.js"></script>
</head>

<body>
    <aside class="sidebar">
        <img src="assets/logo/logo5.png" alt="Logo">
        <div class="brand">SwaadSeva</div>
        <ul class="sidebar-menu">
            <li><a href="index.php" class="active"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="menu.php"><i class="fas fa-utensils"></i> Menu</a></li>
            <li><a href="plans.php"><i class="fas fa-clipboard-list"></i> Plans</a></li>
            <li><a href="user-orders.php"><i class="fas fa-box"></i> My Orders</a></li>
            <li><a href="event-orders.php"><i class="fas fa-calendar-alt"></i> Event Orders</a></li>
            <li><a href="user-feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
        </ul>
    </aside>

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <header class="topbar">
        <button class="menu-btn" id="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>

        <div class="topbar-right">
            <span class="admin-name">Hello, <?= htmlspecialchars($userName) ?></span>
            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>


    <main class="user-main">
        <div class="notification" id="notification">
            Welcome back, <?= htmlspecialchars($userName) ?>!
            <span class="close" onclick="document.getElementById('notification').style.display='none'">&times;</span>
        </div>
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?php echo $userName; ?>. Here’s your daily snapshot.</p>
        </div>
        <div class="dashboard-grid">
            <div class="card">
                <h2>Today's Meals</h2>
                <div class="card-content">
                    <div class="meal-card">
                        <div class="meal-tile lunch-tile">
                            <div class="tile-icon">😋</div>
                            <div class="tile-info">
                                <div class="tile-title">Lunch</div>
                                <div class="tile-count"><?= $mealCounts['Lunch'] ?></div>
                            </div>
                        </div>
                        <div class="meal-tile dinner-tile">
                            <div class="tile-icon">🍚</div>
                            <div class="tile-info">
                                <div class="tile-title">Dinner</div>
                                <div class="tile-count"><?= $mealCounts['Dinner'] ?></div>
                            </div>
                        </div>
                        <br>
                        <div class="meal-tile breakfast-tile">
                            <div class="tile-icon">🍽️</div>
                            <div class="tile-info">
                                <div class="tile-title">Breakfast</div>
                                <div class="tile-count"><?= $mealCounts['Breakfast'] ?></div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>

            <div class="card">
                <h2>Today's Menu</h2>
                <div class="card-content">
                    <p class="breakfast-tile"><strong>Breakfast:</strong>
                        <?= htmlspecialchars($menuToday['Breakfast']) ?></p>
                    <hr style="margin:8px 0;border-top:1px solid #eee;">
                    <p class="lunch-tile"><strong>Lunch:</strong> <?= htmlspecialchars($menuToday['Lunch']) ?></p>
                    <hr style="margin:8px 0;border-top:1px solid #eee;">
                    <p class="dinner-tile"><strong>Dinner:</strong> <?= htmlspecialchars($menuToday['Dinner']) ?></p>
                </div>
                <a href="menu.php" class="btn">View Full Week's Menu</a>
            </div>

            <div class="card">
                <h2>Payment Summary</h2>
                <div class="card-content">
                    <div class="chart-container">
                        <canvas id="paymentChart"></canvas>
                    </div>
                    <h4>Normal Orders:</h4>
                    <p><strong class="pending">Pending:</strong> ₹<?= number_format($normalPending, 2) ?></p>
                    <p><strong class="paid">Paid:</strong> ₹<?= number_format($normalPaid, 2) ?></p>
                    <p><strong>Total:</strong> ₹<?= number_format($normalTotal, 2) ?></p>

                    <hr style="margin:8px 0;border-top:1px solid #eee;">

                    <h4>Event Orders:</h4>
                    <p><strong class="pending">Pending:</strong> ₹<?= number_format($eventPending, 2) ?></p>
                    <p><strong class="paid">Paid:</strong> ₹<?= number_format($eventPaid, 2) ?></p>
                    <p><strong>Total:</strong> ₹<?= number_format($eventTotal, 2) ?></p>

                    <hr style="margin:8px 0;border-top:1px solid #eee;">

                    <h4>Combined:</h4>
                    <p><strong class="pending">Pending:</strong> ₹<?= number_format($combinedPending, 2) ?></p>
                    <p><strong class="paid">Paid:</strong> ₹<?= number_format($combinedPaid, 2) ?></p>
                    <p><strong>Total:</strong> ₹<?= number_format($combinedTotal, 2) ?></p>
                </div>
                <a href="payments.php" class="btn">Pay Now</a>
            </div>

            <div class="card">
                <h2>Your Plans</h2>
                <div class="card-content">
                    <?php if (count($plans) > 0): ?>
                        <ul>
                            <?php foreach ($plans as $plan): ?>
                                <li><strong><?= htmlspecialchars($plan['plan_name']) ?></strong>
                                    (<?= date('d-m-Y', strtotime($plan['start_date'])) ?> to
                                    <?= date('d-m-Y', strtotime($plan['end_date'])) ?>)
                                    <hr style="border: 0; border-top: 1px solid #eee; margin: 9px 0;">
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No active plans.</p>
                    <?php endif; ?>
                </div>
                <a href="plans.php" class="btn">Add New Plan</a>
            </div>




            <div class="card">
                <h2>Special Orders</h2>
                <div class="card-content">
                    <?php if (count($specialOrders) > 0): ?>
                        <?php foreach ($specialOrders as $order): ?>
                            <div class="special-order-item">
                                <p><strong>Menu:</strong> <?= htmlspecialchars($order['menu']) ?></p>
                                <p><strong>Date:</strong> <?= date('d-m-Y', strtotime($order['order_date'])) ?></p>
                                <p><strong>Status:</strong> <?= htmlspecialchars($order['order_status']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No special orders found.</p>
                    <?php endif; ?>
                </div>
                <a href="event-orders.php" class="btn">View Orders</a>
            </div>





            <div class="card">
                <h2>Poll</h2>
                <div class="card-content" style="max-height: 300px; overflow-y: auto;">
                    <?php foreach ($polls as $pollSet): ?>
                        <?php $poll = $pollSet['data']; ?>
                        <?php $hasVoted = $pollSet['hasVoted']; ?>
                        <?php $voteData = $pollSet['voteData']; ?>
                        <?php $pollId = $poll['id']; ?>

                        <div style="margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                            <p><strong><?= htmlspecialchars($poll['question']) ?></strong></p>

                            <?php if (!$hasVoted): ?>
                                <form class="pollForm" data-id="<?= $pollId ?>">
                                    <input type="hidden" name="poll_id" value="<?= $pollId ?>">
                                    <?php for ($i = 1; $i <= 4; $i++): ?>
                                        <?php if (!empty($poll["option$i"])): ?>
                                            <div class="poll-option">
                                                <label>
                                                    <input type="radio" name="option" value="<?= $i ?>" required>
                                                    <?= htmlspecialchars($poll["option$i"]) ?>
                                                </label>
                                            </div>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    <button type="submit" class="btn vote-btn">Vote</button>
                                    <div class="poll-message"></div>
                                </form>
                            <?php else: ?>
                                <canvas id="pollChart_<?= $pollId ?>" width="250" height="150"
                                    style="max-width: 100%;"></canvas>
                                <script>
                                    document.addEventListener("DOMContentLoaded", function () {
                                        new Chart(document.getElementById("pollChart_<?= $pollId ?>"), {
                                            type: "bar",
                                            data: {
                                                labels: <?= json_encode(array_filter([
                                                    $poll['option1'],
                                                    $poll['option2'],
                                                    $poll['option3'],
                                                    $poll['option4']
                                                ])) ?>,
                                                datasets: [{
                                                    label: 'Votes',
                                                    data: <?= json_encode(array_slice($voteData, 0, 4)) ?>,
                                                    backgroundColor: ['#4CAF50', '#FF9800', '#2196F3', '#E91E63']
                                                }]
                                            },
                                            options: {
                                                responsive: false,
                                                maintainAspectRatio: false,
                                                plugins: {
                                                    legend: { display: false }
                                                },
                                                scales: {
                                                    y: { beginAtZero: true }
                                                }
                                            }
                                        });
                                    });

                                </script>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('paymentChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Normal Pending', 'Normal Paid', 'Special Pending', 'Special Paid'],
                    datasets: [{
                        data: [
                            <?= $normalPending ?>,
                            <?= $normalPaid ?>,
                            <?= $eventPending ?>,
                            <?= $eventPaid ?>
                        ],
                        backgroundColor: [
                            '#FF6B6B', // Normal Pending
                            '#4CAF50', // Normal Paid
                            '#FFA07A', // Special Pending
                            '#81C784'  // Special Paid
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            align: 'center',
                            labels: {
                                boxWidth: 12,
                                padding: 10,
                                font: {
                                    size: 11
                                },
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return `${context.label}: ₹${context.parsed.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true
                    }
                }
            });
        });
    </script>

    <script>
        document.querySelectorAll('.pollForm').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                const pollId = formData.get('poll_id');
                const option = formData.get('option');

                fetch('submit_vote.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        const msg = this.querySelector('.poll-message');
                        if (data.success) {
                            msg.innerHTML = 'Thank you for voting!';
                            msg.style.color = 'green';
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            msg.innerHTML = data.message || 'Error submitting vote.';
                            msg.style.color = 'red';
                        }
                    })
                    .catch(err => {
                        this.querySelector('.poll-message').innerHTML = 'Something went wrong.';
                    });
            });
        });
    </script>

</body>

</html>