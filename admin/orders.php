<?php
session_start();
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once 'db.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delivery_id']) && isset($_POST['new_status'])) {
    $deliveryId = (int) $_POST['delivery_id'];
    $newStatus = $_POST['new_status'];
    $allowedStatuses = ['Pending', 'Out for Delivery', 'Delivered', 'Canceled', 'Missed'];

    if (in_array($newStatus, $allowedStatuses)) {
        $stmt = $conn->prepare("UPDATE delivery SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $deliveryId);
        $stmt->execute();
    }

    // Preserve filters using query string
    $queryString = http_build_query($_GET);
    header("Location: orders.php" . (!empty($queryString) ? "?$queryString" : ""));
    exit;
}


// Filters
$filter_type = $_GET['type'] ?? 'today';
$filter_meal = $_GET['meal'] ?? '';
$filter_date = $_GET['date'] ?? date('Y-m-d');
$custom_from = $_GET['from'] ?? '';
$custom_to = $_GET['to'] ?? '';
$search_name = $_GET['search'] ?? '';

$startDate = $endDate = $filter_date;
if ($filter_type === 'month') {
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-t');
} elseif ($filter_type === 'week') {
    $startDate = date('Y-m-d', strtotime('monday this week'));
    $endDate = date('Y-m-d', strtotime('sunday this week'));
} elseif ($filter_type === 'tomorrow') {
    $startDate = $endDate = date('Y-m-d', strtotime('+1 day'));
} elseif ($filter_type === 'custom') {
    $startDate = $custom_from;
    $endDate = $custom_to;
}

$orders = [];
$count = ['Breakfast' => 0, 'Lunch' => 0, 'Dinner' => 0];

$sql = "SELECT d.id, d.delivery_date, d.meal_type, d.status, u.name AS user_name
        FROM delivery d
        JOIN users u ON d.user_id = u.id
        WHERE d.delivery_date BETWEEN ? AND ?";

$params = [$startDate, $endDate];
$types = "ss";

if (!empty($filter_meal)) {
    $sql .= " AND d.meal_type = ?";
    $params[] = $filter_meal;
    $types .= "s";
}
if (!empty($search_name)) {
    $sql .= " AND u.name LIKE ?";
    $params[] = "%$search_name%";
    $types .= "s";
}

$filter_status = $_GET['status'] ?? '';
if (!empty($filter_status)) {
    $sql .= " AND d.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
    $count[$row['meal_type']] = ($count[$row['meal_type']] ?? 0) + 1;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orders | SwaadSeva Admin</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Filter Form Styles */
        form {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
            padding: 10px 15px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        form select,
        form input[type="date"],
        form input[type="text"] {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            min-width: 140px;
        }

        form button {
            padding: 8px 14px;
            border-radius: 5px;
            background-color: #007bff;
            border: none;
            color: white;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        form button:hover {
            background-color: #0056b3;
        }

        /* Delivery Status Styling */
        .status {
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
            min-width: 110px;
            text-align: center;
        }

        .status.Delivered {
            background-color: #d4edda;
            color: #155724;
        }

        .status.Pending {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .status['Out for Delivery'],
        .status.Out\ for\ Delivery {
            background-color: #fff3cd;
            color: #856404;
        }

        .status.Canceled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status.Missed {
            background-color: #f5c6cb;
            color: #721c24;
        }

        /* Counts Styling */
        .counts {
            margin-bottom: 15px;
            font-weight: bold;
            background: #f1f1f1;
            padding: 10px;
            border-radius: 5px;
        }

        .counts span {
            margin-right: 20px;
        }

        /* Mark Delivered / Dropdown Styling */
        .mark-btn,
        .status-form select,
        .status-form button {
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 13px;
            margin-left: 5px;
            border: 1px solid #ccc;
        }

        .mark-btn {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }

        .mark-btn:hover {
            background-color: #218838;
        }

        .status-form {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Responsive Fixes */
        @media (max-width: 768px) {
            form {
                flex-direction: column;
                align-items: stretch;
            }

            form select,
            form input[type="date"],
            form input[type="text"],
            form button {
                width: 100%;
            }

            .status {
                min-width: unset;
                width: 100%;
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
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="orders.php" class="active"><i class="fas fa-box"></i> Orders</a></li>
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


    <main class="admin-main">
        <div class="page-header">
            <h1>Normal Orders</h1>
            <div class="counts">
                <span>Breakfast: <?= $count['Breakfast'] ?></span>
                <span>Lunch: <?= $count['Lunch'] ?></span>
                <span>Dinner: <?= $count['Dinner'] ?></span>
            </div>
            <form method="GET">
                <select name="type" onchange="toggleCustomDates()">
                    <option value="today" <?= $filter_type === 'today' ? 'selected' : '' ?>>Today</option>
                    <option value="tomorrow" <?= $filter_type === 'tomorrow' ? 'selected' : '' ?>>Tomorrow</option>
                    <option value="week" <?= $filter_type === 'week' ? 'selected' : '' ?>>This Week</option>
                    <option value="month" <?= $filter_type === 'month' ? 'selected' : '' ?>>This Month</option>
                    <option value="custom" <?= $filter_type === 'custom' ? 'selected' : '' ?>>Custom</option>
                </select>
                <div id="customRange"
                    style="display: <?= $filter_type === 'custom' ? 'inline-flex' : 'none' ?>; gap: 10px;">
                    <input type="date" name="from" value="<?= $custom_from ?>">
                    <input type="date" name="to" value="<?= $custom_to ?>">
                </div>
                <select name="meal">
                    <option value="">All Meals</option>
                    <option value="Breakfast" <?= $filter_meal === 'Breakfast' ? 'selected' : '' ?>>Breakfast</option>
                    <option value="Lunch" <?= $filter_meal === 'Lunch' ? 'selected' : '' ?>>Lunch</option>
                    <option value="Dinner" <?= $filter_meal === 'Dinner' ? 'selected' : '' ?>>Dinner</option>
                </select>

                <select name="status">
                    <option value="">All</option>
                    <?php
                    $statusOptions = ['Pending', 'Out for Delivery', 'Delivered', 'Canceled', 'Missed'];
                    foreach ($statusOptions as $status) {
                        $selected = ($filter_status === $status) ? 'selected' : '';
                        echo "<option value=\"$status\" $selected>$status</option>";
                    }
                    ?>
                </select>

                <input type="text" name="search" placeholder="Search by name"
                    value="<?= htmlspecialchars($search_name) ?>">
                <button type="submit">Apply</button>
            </form>
        </div>

        <div class="responsive-table-container">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Sr No</th>
                        <th>Date</th>
                        <th>User</th>
                        <th>Meal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)):
                        $sr = 1; ?>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><?= $sr++ ?></td>
                                <td><?= htmlspecialchars($o['delivery_date']) ?></td>
                                <td><?= htmlspecialchars($o['user_name']) ?></td>
                                <td><?= htmlspecialchars($o['meal_type']) ?></td>
                                <td>
                                    <?php if ($o['status'] === 'Out for Delivery'): ?>
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="delivery_id" value="<?= $o['id'] ?>">
                                            <input type="hidden" name="new_status" value="Delivered">
                                            <button type="submit">Mark Delivered</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="delivery_id" value="<?= $o['id'] ?>">
                                            <select name="new_status" onchange="this.form.submit()">
                                                <?php
                                                $statuses = ['Pending', 'Out for Delivery', 'Delivered', 'Canceled', 'Missed'];
                                                foreach ($statuses as $status): ?>
                                                    <option value="<?= $status ?>" <?= $o['status'] === $status ? 'selected' : '' ?>>
                                                        <?= $status ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function toggleCustomDates() {
            const type = document.querySelector('[name="type"]').value;
            document.getElementById('customRange').style.display = (type === 'custom') ? 'inline-flex' : 'none';
        }
        document.addEventListener('DOMContentLoaded', toggleCustomDates);
    </script>
    <script src="js/admin.js"></script>
</body>

</html>