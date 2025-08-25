<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';

$filter = $_GET['filter'] ?? 'today';
$mealFilter = $_GET['meal_type'] ?? '';
$customDate = $_GET['custom_date'] ?? null;

$today = date('Y-m-d');

if ($filter === 'month') {
    $start = date('Y-m-01');
    $end = date('Y-m-t');
} elseif ($filter === 'week') {
    $start = date('Y-m-d', strtotime('monday this week'));
    $end = date('Y-m-d', strtotime('sunday this week'));
} elseif ($filter === 'custom' && $customDate) {
    $start = $end = $customDate;
} else {
    $start = $end = $today;
}

$dateRange = [];
$current = strtotime($start);
$endTime = strtotime($end);
while ($current <= $endTime) {
    $dateRange[] = date('Y-m-d', $current);
    $current = strtotime('+1 day', $current);
}

$meals = [];
$planStmt = $conn->prepare("SELECT up.start_date, up.end_date, p.meal_type FROM user_plans up JOIN plans p ON up.plan_id = p.id WHERE up.user_id = ?");
$planStmt->bind_param("i", $userId);
$planStmt->execute();
$planResult = $planStmt->get_result();

while ($plan = $planResult->fetch_assoc()) {
    $startDate = $plan['start_date'];
    $endDate = $plan['end_date'];
    $mealTypes = array_map('trim', explode(',', $plan['meal_type']));

    foreach ($dateRange as $date) {
        if ($date >= $startDate && $date <= $endDate) {
            $day = strtolower(date('l', strtotime($date)));
            foreach ($mealTypes as $meal) {
                $mealLower = strtolower($meal);
                if ($mealFilter && $mealLower !== strtolower($mealFilter))
                    continue;

                $menuStmt = $conn->prepare("SELECT items FROM menu WHERE LOWER(day_of_week) = ? AND LOWER(meal_type) = ?");
                $menuStmt->bind_param("ss", $day, $mealLower);
                $menuStmt->execute();
                $menuResult = $menuStmt->get_result();

                $deliveryStmt = $conn->prepare("SELECT id, status FROM delivery WHERE user_id = ? AND meal_type = ? AND delivery_date = ?");
                $deliveryStmt->bind_param("iss", $userId, $meal, $date);
                $deliveryStmt->execute();
                $deliveryResult = $deliveryStmt->get_result();
                $deliveryRow = $deliveryResult->fetch_assoc();

                if ($menuRow = $menuResult->fetch_assoc()) {
                    $meals[] = [
                        'date' => $date,
                        'day' => ucfirst($day),
                        'meal_type' => ucfirst($meal),
                        'items' => $menuRow['items'],
                        'delivery_id' => $deliveryRow['id'] ?? null,
                        'status' => $deliveryRow['status'] ?? 'Pending'
                    ];
                }
                $menuStmt->close();
                $deliveryStmt->close();
            }
        }
    }
}
$planStmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | SwaadSeva</title>
    <link rel="stylesheet" href="css/user-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/user.js"></script>
    <style>
        .status.delivered {
            background-color: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }

        .status.pending,
        .status.out-for-delivery {
            background-color: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }


        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-form label {
            font-weight: bold;
            margin-right: 5px;
        }

        .filter-form select,
        .filter-form input[type="date"] {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .date-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .date-input {
            position: relative;
        }

        .date-input i.fa-calendar {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #666;
        }

        .date-input input[type="date"] {
            padding-left: 30px;
        }

        .mark-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <img src="assets/logo/logo5.png" alt="Logo">
        <div class="brand">SwaadSeva</div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="menu.php"><i class="fas fa-utensils"></i> Menu</a></li>
            <li><a href="plans.php"><i class="fas fa-clipboard-list"></i> Plans</a></li>
            <li><a href="user-orders.php" class="active"><i class="fas fa-box"></i> My Orders</a></li>
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
        <div class="page-header">
            <h1>My Orders</h1>
            <form method="GET" class="filter-form">
                <label for="filter">View:</label>
                <select name="filter" id="filter" onchange="toggleDatePicker(); this.form.submit()">
                    <option value="today" <?= $filter === 'today' ? 'selected' : '' ?>>Today</option>
                    <option value="week" <?= $filter === 'week' ? 'selected' : '' ?>>This Week</option>
                    <option value="month" <?= $filter === 'month' ? 'selected' : '' ?>>This Month</option>
                    <option value="custom" <?= $filter === 'custom' ? 'selected' : '' ?>>Custom Date</option>
                </select>

                <label for="meal_type">Meal:</label>
                <select name="meal_type" id="meal_type" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="breakfast" <?= $mealFilter === 'breakfast' ? 'selected' : '' ?>>Breakfast</option>
                    <option value="lunch" <?= $mealFilter === 'lunch' ? 'selected' : '' ?>>Lunch</option>
                    <option value="dinner" <?= $mealFilter === 'dinner' ? 'selected' : '' ?>>Dinner</option>
                </select>

                <div class="date-wrapper" id="dateWrapper" style="<?= $filter === 'custom' ? '' : 'display: none;' ?>">
                    <label for="custom_date">Choose Date:</label>
                    <div class="date-input">
                        <i class="fa fa-calendar"></i>
                        <input type="date" name="custom_date" id="custom_date"
                            value="<?= htmlspecialchars($customDate ?? '') ?>" onchange="this.form.submit()" />
                    </div>
                </div>
            </form>
        </div>

        <div class="responsive-table-container">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Meal Type</th>
                        <th>Items</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($meals)): ?>
                        <?php foreach ($meals as $meal): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($meal['date'])) ?></td>
                                <td><?= $meal['day'] ?></td>
                                <td><?= $meal['meal_type'] ?></td>
                                <td><?= htmlspecialchars($meal['items']) ?></td>
                                <td>
                                    <?php if ($meal['status'] === 'Out for Delivery'): ?>
                                        <form method="POST" action="mark-received.php" style="display:inline-block;">
                                            <input type="hidden" name="delivery_id" value="<?= $meal['delivery_id'] ?>">
                                            <button type="submit" class="mark-btn">Mark as Received</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="status <?= strtolower(str_replace(' ', '-', $meal['status'])) ?>">
                                            <?= $meal['status'] ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">No meals found for selected filter.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <script>
        function toggleDatePicker() {
            const filter = document.getElementById('filter').value;
            const wrapper = document.getElementById('dateWrapper');
            wrapper.style.display = filter === 'custom' ? 'flex' : 'none';
        }
    </script>
</body>

</html>