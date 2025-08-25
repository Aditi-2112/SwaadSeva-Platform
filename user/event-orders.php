<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';

// Handle new order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $quantity = (int) $_POST['quantity'];
    $menu = $_POST['sample_menu'] === 'custom' ? trim($_POST['custom_menu']) : $_POST['sample_menu'];
    $order_date = $_POST['order_date'];
    $order_time = $_POST['order_time'];

    // Date validation: at least tomorrow
    $today = new DateTime('today');
    $selectedDate = new DateTime($order_date);

    if ($selectedDate <= $today) {
        echo "<script>alert('Orders must be placed at least 1 day in advance.'); window.history.back();</script>";
        exit;
    }

    // Example defaults (adjust as per your business logic)
    $order_price = 0;
    $paid_amount = 0;
    $payment_status = 'Pending';

    $stmt = $conn->prepare("INSERT INTO special_orders 
        (user_id, name, contact, address, quantity, order_price, paid_amount, payment_status, menu, order_date, order_time, order_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending Approval')");
    $stmt->bind_param("isssiddssss", $userId, $name, $contact, $address, $quantity, $order_price, $paid_amount, $payment_status, $menu, $order_date, $order_time);
    $stmt->execute();

    header("Location: event-orders.php?success=1");
    exit;
}



// Fetch previous orders
$stmt = $conn->prepare("SELECT * FROM special_orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Orders | SwaadSeva</title>
    <link rel="stylesheet" href="css/user-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/user.js"></script>

    <style>
        .event-form {
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            background: #f9f9f9;
            border-radius: 10px;
            display: none;
        }

        .event-form input,
        .event-form select,
        .event-form textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .create-order-btn {
            background-color: #28a745;
            color: #fff;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 15px;
        }

        .create-order-btn:hover {
            background-color: #218838;
        }

        .submit-order-btn {
            background-color: #007bff;
            color: #fff;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-order-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <img src="assets/logo/logo5.png" alt="Logo">
        <div class="brand">SwaadSeva</div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="menu.php"><i class="fas fa-utensils"></i> Menu</a></li>
            <li><a href="plans.php"><i class="fas fa-clipboard-list"></i> Plans</a></li>
            <li><a href="user-orders.php"><i class="fas fa-box"></i> My Orders</a></li>
            <li><a href="event-orders.php" class="active"><i class="fas fa-calendar-alt"></i> Event Orders</a></li>
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

    <!-- Main Content -->
    <main class="user-main">
        <div class="page-header">
            <h1>Event Orders</h1>
            <?php if (!empty($successMessage)): ?>
                <div class="success-msg"><?= $successMessage ?></div>
            <?php endif; ?>
            <button class="create-order-btn" onclick="toggleOrderForm()">+ Create New Order</button>
        </div>

        <!-- Order Form -->
        <div id="orderForm" class="event-form">
            <form method="POST">
                <label>Name:</label>
                <input type="text" name="name" required>

                <label>Contact:</label>
                <input type="text" name="contact" required>

                <label>Address:</label>
                <textarea name="address" required></textarea>

                <label>Quantity (e.g., No. of People):</label>
                <input type="number" name="quantity" min="1" required>

                <label>Select Sample Menu:</label>
                <select name="sample_menu" onchange="toggleCustomMenu(this.value)" required>
                    <option value="">-- Select --</option>
                    <option value="North Indian Special">North Indian Special</option>
                    <option value="South Indian Feast">South Indian Feast</option>
                    <option value="Gujarati Thali">Gujarati Thali</option>
                    <option value="custom">Custom</option>
                </select>

                <div id="customMenuBox" style="display:none;">
                    <label>Custom Menu:</label>
                    <textarea name="custom_menu" placeholder="List your menu items here..."></textarea>
                </div>

                <label>Preferred Date:</label>
                <input type="date" name="order_date" id="order_date" required>

                <label>Preferred Time:</label>
                <input type="time" name="order_time" required>
                <script>
                    // Set minimum date to tomorrow
                    const dateInput = document.getElementById('order_date');
                    const today = new Date();
                    today.setDate(today.getDate() + 1); // tomorrow
                    const minDate = today.toISOString().split('T')[0];
                    dateInput.setAttribute('min', minDate);
                </script>
                <button type="submit" class="submit-order-btn">Submit Order</button>
            </form>
        </div>

        <!-- Orders Table -->
        <h2 style="margin-top: 40px;">Your Previous Event Orders</h2>
        <div class="responsive-table-container">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Qty</th>
                        <th>Menu</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['name']) ?></td>
                                <td><?= htmlspecialchars($order['contact']) ?></td>
                                <td><?= htmlspecialchars($order['address']) ?></td>
                                <td><?= $order['quantity'] ?></td>
                                <td><?= htmlspecialchars($order['menu']) ?></td>
                                <td><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                                <td><?= htmlspecialchars($order['order_time']) ?></td>
                                <td><strong><?= htmlspecialchars($order['order_status']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No event orders yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <script>
        function toggleOrderForm() {
            const form = document.getElementById('orderForm');
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        }

        function toggleCustomMenu(value) {
            const box = document.getElementById('customMenuBox');
            box.style.display = value === 'custom' ? 'block' : 'none';
        }
    </script>
</body>

</html>