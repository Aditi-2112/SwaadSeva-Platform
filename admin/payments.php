<?php
session_start();
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once 'db.php';

// Filters
$filter_type = $_GET['type'] ?? 'month';
$filter_from = $_GET['from'] ?? '';
$filter_to = $_GET['to'] ?? '';
$filter_status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$order_type = $_GET['order_type'] ?? 'normal';

// Build date range
$startDate = $endDate = date('Y-m-d');
if ($filter_type === 'today') {
    $startDate = $endDate = date('Y-m-d');
} elseif ($filter_type === 'week') {
    $startDate = date('Y-m-d', strtotime('monday this week'));
    $endDate = date('Y-m-d', strtotime('sunday this week'));
} elseif ($filter_type === 'month') {
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-t');
} elseif ($filter_type === 'year') {
    $startDate = date('Y-01-01');
    $endDate = date('Y-12-31');
} elseif ($filter_type === 'custom') {
    $startDate = $filter_from;
    $endDate = $filter_to;
}

$payments = [];
$totalPaid = 0;
$totalPending = 0;
$totalRevenue = 0;

if ($order_type === 'normal') {
    $sql = "SELECT up.id, u.id AS user_id, u.name, p.name AS plan_name, p.total_price AS plan_total, up.amount_paid, up.payment_status, up.start_date, up.end_date
            FROM user_plans up
            JOIN users u ON up.user_id = u.id
            JOIN plans p ON up.plan_id = p.id
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
        $row['remaining'] = $row['plan_total'] - $row['amount_paid'];
        $row['type'] = 'normal';
        $payments[] = $row;
        $totalRevenue += $row['plan_total'];
        $totalPaid += $row['amount_paid'];
        if (strtolower($row['payment_status']) !== 'paid') {
            $totalPending += $row['plan_total'] - $row['amount_paid'];
        }
    }
} elseif ($order_type === 'special') {
    $sql = "SELECT id, user_id, name, contact, address, quantity, order_price, paid_amount, payment_status, menu, order_date, order_time FROM special_orders
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
        $row['plan_name'] = $row['menu'];
        $row['plan_total'] = $row['order_price'];
        $row['amount_paid'] = $row['paid_amount'];
        $row['remaining'] = $row['order_price'] - $row['paid_amount'];
        $row['subscribed_at'] = $row['order_date'];
        $row['type'] = 'special';
        $payments[] = $row;
        $totalRevenue += $row['order_price'];
        $totalPaid += $row['paid_amount'];
        if (strtolower($row['payment_status']) !== 'paid') {
            $totalPending += $row['order_price'] - $row['paid_amount'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Admin Panel</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ================================
     PAYMENTS PAGE STYLING  
  ========================== */
        /* Filter Form */
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-form input,
        .filter-form select,
        .filter-form button {
            padding: 8px;
            font-size: 14px;
            border-radius: 4px;
        }

        /* Summary Section   */
        .summary {
            font-weight: bold;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .summary span {
            margin-right: 20px;
        }

        .summary-value {
            padding: 4px 8px;
            border-radius: 5px;
            font-weight: bold;
        }

        .paid-highlight {
            background-color: #d4edda;
            color: #155724;
        }

        .pending-highlight {
            background-color: #f8d7da;
            color: #721c24;
        }


        /* Status Labels (Inline Badge Style) */
        .status-label {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-label.done {
            background-color: #d4edda;
            color: #155724;
        }

        .status-label.pending {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Status Pill Styling */
        td .done,
        td .pending {
            display: inline-block;
            padding: 4px 10px;
            font-size: 13px;
            border-radius: 12px;
            font-weight: 600;
            text-align: center;
        }

        td .done {
            background-color: #d4edda;
            color: #155724;
        }

        td .pending {
            background-color: #f8d7da;
            color: #721c24;
        }




        /* Modal Overlay */
        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        /* Modal Box */
        .modal {
            background: white;
            padding: 20px 25px;
            border-radius: 8px;
            width: 400px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }

        .modal .close-btn {
            position: absolute;
            top: 10px;
            right: 12px;
            font-size: 20px;
            color: #555;
            cursor: pointer;
        }

        /* Modal Form Fields */
        .modal form label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }

        .modal input,
        .modal textarea,
        .modal select {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* Modal Button */
        .modal button {
            background-color: #28a745;
            color: #fff;
            padding: 8px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
        }

        .modal button:hover {
            background-color: #218838;
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
            <li><a href="admin-feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="payments.php" class="active"><i class="fas fa-credit-card"></i> Payments</a></li>
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
            <h1>Payments</h1>
            <form method="GET" class="filter-form">
                <select name="order_type">
                    <option value="normal" <?= $order_type === 'normal' ? 'selected' : '' ?>>Normal Orders</option>
                    <option value="special" <?= $order_type === 'special' ? 'selected' : '' ?>>Special Orders</option>
                </select>
                <select name="type">
                    <option value="today" <?= $filter_type === 'today' ? 'selected' : '' ?>>Today</option>
                    <option value="week" <?= $filter_type === 'week' ? 'selected' : '' ?>>This Week</option>
                    <option value="month" <?= $filter_type === 'month' ? 'selected' : '' ?>>This Month</option>
                    <option value="year" <?= $filter_type === 'year' ? 'selected' : '' ?>>This Year</option>
                    <option value="custom" <?= $filter_type === 'custom' ? 'selected' : '' ?>>Custom</option>
                </select>
                <input type="date" name="from" value="<?= $filter_from ?>">
                <input type="date" name="to" value="<?= $filter_to ?>">
                <select name="status">
                    <option value="">All</option>
                    <option value="paid" <?= $filter_status === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
                <input type="text" name="search" placeholder="Search by name or amount"
                    value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Apply</button>
            </form>

            <div class="summary">
                <span>Total Paid: <span
                        class="summary-value paid-highlight">₹<?= number_format($totalPaid, 2) ?></span></span>
                <span>Total Pending: <span
                        class="summary-value pending-highlight">₹<?= number_format($totalPending, 2) ?></span></span>
                <span>Total Revenue: ₹<?= number_format($totalRevenue, 2) ?></span>
            </div>
        </div>

        <table class="responsive-table">
            <thead>
                <tr>
                    <th>Sr No</th>
                    <th>User Name</th>
                    <?php if ($order_type === 'normal'): ?>
                        <th>Plan</th>
                    <?php else: ?>
                        <th>Menu</th>
                    <?php endif; ?>
                    <th>Total Amount</th>
                    <th>Amount Paid</th>
                    <th>Remaining</th>
                    <th>Status</th>
                    <?php if ($order_type === 'normal'): ?>
                        <th>Start Date</th>
                        <th>End Date</th>
                    <?php else: ?>
                        <th>Order Date</th>
                    <?php endif; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)):
                    $sr = 1;
                    foreach ($payments as $row): ?>
                        <tr>
                            <td><?= $sr++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($order_type === 'normal' ? $row['plan_name'] : $row['menu']) ?></td>
                            <td>₹<?= number_format($row['plan_total'], 2) ?></td>
                            <td>₹<?= number_format($row['amount_paid'], 2) ?></td>
                            <td>₹<?= number_format($row['remaining'], 2) ?></td>
                            <td>
                                <span
                                    class="status-label <?= strtolower($row['payment_status']) === 'paid' ? 'done' : 'pending' ?>">
                                    <?= ucfirst($row['payment_status']) ?>
                                </span>
                            </td>
                            <?php if ($order_type === 'normal'): ?>
                                <td><?= date('d M Y', strtotime($row['start_date'])) ?></td>
                                <td><?= date('d M Y', strtotime($row['end_date'])) ?></td>
                            <?php else: ?>
                                <td><?= date('d M Y', strtotime($row['order_date'])) ?></td>
                            <?php endif; ?>
                            <td>
                                <button class="view-btn"
                                    onclick="showTransactionHistory(<?= $row['id'] ?>, '<?= $order_type ?>')">View</button>
                                <button class="add-btn"
                                    onclick="openAddTransactionModal(<?= $row['id'] ?>, <?= $row['user_id'] ?>, '<?= $order_type ?>')">Add</button>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="10">No <?= $order_type === 'normal' ? 'normal' : 'special' ?> orders found in selected
                            range.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>


        <!-- Transaction Modal -->
        <div class="modal-overlay" id="transactionModal">
            <div class="modal">
                <span class="close-btn"
                    onclick="document.getElementById('transactionModal').style.display='none'">&times;</span>
                <h3>Transaction History</h3>
                <div id="transactionContent">Loading...</div>
            </div>
        </div>

        <!-- Add Transaction Modal -->
        <div class="modal-overlay" id="addTransactionModal">
            <div class="modal">
                <span class="close-btn"
                    onclick="document.getElementById('addTransactionModal').style.display='none'">&times;</span>
                <h3>Add Transaction</h3>
                <form id="addTransactionForm">
                    <input type="hidden" name="user_id" id="add_user_id">
                    <input type="hidden" name="plan_id" id="add_plan_id">
                    <input type="hidden" name="order_type" id="order_type_hidden">


                    <label>Amount:</label>
                    <input type="number" name="amount" required>

                    <label>Transaction Date:</label>
                    <input type="date" name="txn_date" required>

                    <label>Payment Method:</label>
                    <select name="payment_method">
                        <option value="Cash">Cash</option>
                        <option value="UPI">UPI</option>
                        <option value="Online">Online</option>
                        <option value="Card">Card</option>
                    </select>

                    <label>Notes:</label>
                    <textarea name="notes" placeholder="Optional note..."></textarea>

                    <button type="submit">Add</button>
                </form>
            </div>
        </div>
    </main>


    <script>
        function showTransactionHistory(id, type) {
            fetch(`fetch_transactions.php?id=${id}&type=${type}`)
                .then(res => res.text())
                .then(data => {
                    document.getElementById('transactionContent').innerHTML = data;
                    document.getElementById('transactionModal').style.display = 'flex';
                });
        }

        function openAddTransactionModal(planId, userId, type = 'normal') {
            document.getElementById('add_plan_id').value = planId;
            document.getElementById('add_user_id').value = userId;
            document.getElementById('order_type_hidden').value = type;
            document.getElementById('addTransactionModal').style.display = 'flex';
        }



        document.getElementById('addTransactionForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            fetch('add_transaction.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.text())
                .then(result => {
                    if (result.trim() === 'success') {
                        alert('Transaction added successfully');
                        form.reset();
                        document.getElementById('addTransactionModal').style.display = 'none';
                        location.reload();
                    } else {
                        alert('Error: ' + result);
                    }
                });
        });
    </script>

    <script src="js/admin.js"></script>
</body>

</html>