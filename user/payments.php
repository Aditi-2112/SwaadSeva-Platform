<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';

// Fetch normal plan payments
$query = $conn->prepare("SELECT up.*, p.name AS plan_name, p.total_price FROM user_plans up JOIN plans p ON up.plan_id = p.id WHERE up.user_id = ? ORDER BY up.start_date DESC");
$query->bind_param("i", $userId);
$query->execute();
$result = $query->get_result();
$payments = $result->fetch_all(MYSQLI_ASSOC);
$query->close();

// Fetch special orders with pending payment
$specialQuery = $conn->prepare("SELECT * FROM special_orders WHERE user_id = ? ORDER BY order_date DESC");
$specialQuery->bind_param("i", $userId);
$specialQuery->execute();
$specialOrdersResult = $specialQuery->get_result();
$specialOrders = $specialOrdersResult->fetch_all(MYSQLI_ASSOC);
$specialQuery->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payments - SwaadSeva</title>
    <link rel="stylesheet" href="css/user-style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="js/user.js"></script>
    <style>
        .paid {
            color: green;
            font-weight: bold;
        }

        .pending {
            color: red;
            font-weight: bold;
        }

        .pay-btn,
        .view-btn {
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 5px;
        }

        .view-btn {
            background-color: #007BFF;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        .modal {
            background: white;
            padding: 20px;
            border-radius: 6px;
            width: 450px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 22px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <img src="assets/logo/logo5.png" alt="Logo" class="logo" />
        <div class="brand">SwaadSeva</div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="menu.php"><i class="fas fa-utensils"></i> Menu</a></li>
            <li><a href="plans.php"><i class="fas fa-clipboard-list"></i> Plans</a></li>
            <li><a href="user-orders.php"><i class="fas fa-box"></i> My Orders</a></li>
            <li><a href="event-orders.php"><i class="fas fa-calendar-alt"></i> Event Orders</a></li>
            <li><a href="user-feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="payments.php" class="active"><i class="fas fa-credit-card"></i> Payments</a></li>
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
            <h1>Payments</h1>
            <p>View and manage your payment records.</p>
        </div>

        <h2>Plan Payments</h2>
        <div class="responsive-table-container">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Pending</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $payment): ?>
                            <?php
                            $paidAmt = floatval($payment['amount_paid']);
                            $totalAmt = floatval($payment['total_price']);
                            $pendingAmt = $totalAmt - $paidAmt;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($payment['plan_name']) ?></td>
                                <td><?= !empty($payment['start_date']) ? date('d-m-Y', strtotime($payment['start_date'])) : '' ?>
                                </td>
                                <td><?= !empty($payment['end_date']) ? date('d-m-Y', strtotime($payment['end_date'])) : '' ?>
                                </td>
                                <td>₹<?= number_format($totalAmt, 2) ?></td>
                                <td>₹<?= number_format($paidAmt, 2) ?></td>
                                <td>₹<?= number_format($pendingAmt, 2) ?></td>
                                <td>
                                    <span class="<?= $payment['payment_status'] === 'Done' ? 'paid' : 'pending' ?>">
                                        <?= htmlspecialchars($payment['payment_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="pay-now.php" method="GET" style="display:inline-block;">
                                        <input type="hidden" name="plan_id" value="<?= $payment['plan_id'] ?>">
                                        <?php if ($payment['payment_status'] !== 'Done'): ?>
                                            <button class="pay-btn" type="submit">Pay Now</button>
                                        <?php endif; ?>
                                    </form>
                                    <button class="view-btn"
                                        onclick="showTransactionHistory(<?= $payment['id'] ?>, 'plan')">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No plan payment records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>




        <h2 style="margin-top: 40px;">Special Orders</h2>
        <div class="responsive-table-container">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Event Date</th>
                        <th>Price</th>
                        <th>Paid</th>
                        <th>Pending</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($specialOrders)): ?>
                        <?php foreach ($specialOrders as $order): ?>
                            <?php
                            $total = floatval($order['order_price']);
                            $paid = floatval($order['paid_amount']);
                            $pending = $total - $paid;
                            $status = htmlspecialchars($order['payment_status']);
                            ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= date('d-m-Y', strtotime($order['order_date'])) ?></td>
                                <td>₹<?= number_format($total, 2) ?></td>
                                <td>₹<?= number_format($paid, 2) ?></td>
                                <td>₹<?= number_format($pending, 2) ?></td>
                                <td>
                                    <?php if ($pending > 0): ?>
                                        <a class="pay-btn" href="pay_special_order.php?order_id=<?= $order['id'] ?>">Pay Now</a>
                                    <?php endif; ?>
                                    <button class="view-btn"
                                        onclick="showTransactionHistory(<?= $order['id'] ?>, 'special')">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No special orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <div class="modal-overlay" id="transactionModal">
        <div class="modal">
            <span class="close-btn"
                onclick="document.getElementById('transactionModal').style.display='none'">&times;</span>
            <h3>Transaction History</h3>
            <div id="transactionContent">Loading...</div>
        </div>
    </div>

    <script>
        function showTransactionHistory(id, type) {
            fetch(`../admin/fetch_transactions.php?id=${id}&type=${type}`)
                .then(res => res.text())
                .then(data => {
                    document.getElementById('transactionContent').innerHTML = data;
                    document.getElementById('transactionModal').style.display = 'flex';
                });
        }
    </script>

</body>

</html>