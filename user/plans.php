<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';

// Get all plans
$plansQuery = $conn->query("SELECT * FROM plans");

// Get user subscriptions (multiple subscriptions allowed)
$subscribedPlans = [];
$subQuery = $conn->prepare("SELECT up.*, p.name, p.meal_type, p.total_price 
                            FROM user_plans up 
                            JOIN plans p ON up.plan_id = p.id 
                            WHERE up.user_id = ?");
$subQuery->bind_param("i", $userId);
$subQuery->execute();
$subResult = $subQuery->get_result();
while ($row = $subResult->fetch_assoc()) {
    $subscribedPlans[] = $row;
}
$subQuery->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Plans | SwaadSeva</title>
    <link rel="stylesheet" href="css/user-style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="js/user.js"></script>
    <style>
        .slider-wrapper {
            display: flex;
            align-items: center;
            position: relative;
            margin-bottom: 40px;
        }

        .slider-container {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            /* allow scrolling on small screens */
            scroll-behavior: smooth;
            padding-bottom: 10px;
        }

        .slider-container::-webkit-scrollbar {
            display: none;
            /* hide scrollbar for cleaner look */
        }

        .plan-card {
            flex: 0 0 calc(33.333% - 20px);
            /* 3 per row on desktop */
            min-width: 250px;
            background: #f8f8f8;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .slider-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }



        .plan-card h3 {
            margin: 0;
        }

        .select-btn {
            text-decoration: none;
            margin-top: 10px;
            padding: 8px 12px;
            border: none;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }

        a.select-btn {
            padding: 3px 12px;
        }

        .select-btn,
        .select-btn:visited {
            display: inline-block;
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            text-align: center;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .select-btn:hover {
            background-color: #45a049;
        }

        .select-btn.subscribed {
            background-color: #aaa;
            cursor: not-allowed;
            pointer-events: none;
        }

        .section-title {
            margin: 20px 0 10px 0;
            font-size: 1.5rem;
            color: #333;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <img src="assets/logo/logo5.png" alt="Logo" />
        <div class="brand">SwaadSeva</div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="menu.php"><i class="fas fa-utensils"></i> Menu</a></li>
            <li><a href="plans.php" class="active"><i class="fas fa-clipboard-list"></i> Plans</a></li>
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

    <!-- Main -->
    <main class="user-main">
        <div class="page-header">
            <h1>Choose Your Plan</h1>
            <p>Select the plan that fits your schedule and hunger!</p>
        </div>

        <!-- Subscribed Plans -->
        <h2 class="section-title">📦 Your Subscribed Plans</h2>
        <div class="slider-wrapper">
            <button class="slider-btn prev-btn" onclick="slide('prev', 'subscribedSlider')">&larr;</button>
            <div class="slider-container" id="subscribedSlider">
                <?php if (!empty($subscribedPlans)): ?>
                    <?php foreach ($subscribedPlans as $plan):
                        $start = new DateTime($plan['start_date']);
                        $end = new DateTime($plan['end_date']);
                        $today = new DateTime();
                        $daysLeft = $today > $end ? 0 : $today->diff($end)->days;

                        $total = $plan['total_price'];
                        $paid = $plan['amount_paid'];
                        $remaining = $total - $paid;
                        ?>
                        <div class="plan-card">
                            <h3><?= htmlspecialchars($plan['name']) ?></h3>
                            <p><?= htmlspecialchars($plan['meal_type']) ?></p>
                            <p><strong>Start:</strong> <?= date('d-m-Y', strtotime($plan['start_date'])) ?></p>
                            <p><strong>End:</strong> <?= date('d-m-Y', strtotime($plan['end_date'])) ?></p>
                            <p><strong>Days Left:</strong> <?= $daysLeft ?></p>

                            <p><strong>Total:</strong> ₹<?= number_format($total, 2) ?></p>
                            <p><strong>Paid:</strong> ₹<?= number_format($paid, 2) ?></p>
                            <p><strong>Remaining:</strong> ₹<?= number_format($remaining, 2) ?></p>

                            <p><strong>Status:</strong>
                                <span style="color: <?= $plan['payment_status'] === 'Done' ? 'green' : 'red' ?>;">
                                    <?= htmlspecialchars($plan['payment_status']) ?>
                                </span>
                            </p>

                            <?php if ($remaining > 0): ?>
                                <form action="pay-now.php" method="GET">
                                    <input type="hidden" name="plan_id" value="<?= $plan['plan_id'] ?>">
                                    <button type="submit" class="select-btn">💳 Pay Now</button>
                                </form>
                            <?php else: ?>
                                <button class="select-btn subscribed" disabled>✔ Paid in Full</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="margin-left: 20px;">You have not subscribed to any plans yet.</p>
                <?php endif; ?>
            </div>
            <button class="slider-btn next-btn" onclick="slide('next', 'subscribedSlider')">&rarr;</button>
        </div>

        <!-- Available Plans -->
        <h2 class="section-title">📋 Available Plans</h2>
        <div class="slider-wrapper">
            <button class="slider-btn prev-btn" onclick="slide('prev', 'availableSlider')">&larr;</button>
            <div class="slider-container" id="availableSlider">
                <?php if ($plansQuery->num_rows > 0): ?>
                    <?php while ($row = $plansQuery->fetch_assoc()): ?>
                        <div class="plan-card <?= $row['id'] == 2 ? 'popular' : '' ?>">
                            <h3><?= htmlspecialchars($row['name']) ?></h3>
                            <p><?= htmlspecialchars($row['meal_type']) ?></p>
                            <p class="price">₹<?= number_format($row['total_price'], 2) ?></p>

                            <a href="subscribe.php?plan_id=<?= $row['id'] ?>" class="select-btn">Subscribe</a>

                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="margin-left: 20px;">No plans available right now.</p>
                <?php endif; ?>
            </div>
            <button class="slider-btn next-btn" onclick="slide('next', 'availableSlider')">&rarr;</button>
        </div>
    </main>

    <script>
        function slide(direction, containerId) {
            const container = document.getElementById(containerId);
            const scrollAmount = 300;
            container.scrollBy({ left: direction === 'next' ? scrollAmount : -scrollAmount, behavior: 'smooth' });
        }
    </script>
</body>

</html>