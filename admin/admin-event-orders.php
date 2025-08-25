<?php
session_start();
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once 'db.php';

// Filter values
$status = $_GET['status'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = "WHERE 1=1";
$params = [];
$types = "";

if ($status) {
    $where .= " AND order_status = ?";
    $params[] = $status;
    $types .= "s";
}
if ($from && $to) {
    $where .= " AND order_date BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
    $types .= "ss";
}
if ($search) {
    $where .= " AND (name LIKE ? OR contact LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

$sql = "SELECT * FROM special_orders $where ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Event Orders | SwaadSeva</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        form.filters {
            display: flex;
            gap: 10px;
            margin: 15px 0;
            flex-wrap: wrap;
        }

        form.filters input,
        form.filters select,
        form.filters button {
            padding: 6px 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .edit-btn {
            background: #007bff;
            color: #fff;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .event-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }

        .event-modal.active {
            display: block;
        }

        .event-modal-content {
            background: #fff;
            width: 100%;
            max-width: 520px;
            margin: 80px auto;
            padding: 30px;
            border-radius: 10px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: fadeInModal 0.3s ease-in-out;
        }

        @keyframes fadeInModal {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .event-modal-content h3 {
            color: #FF6F00;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .event-modal-content label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #333;
        }

        .event-modal-content input,
        .event-modal-content select,
        .event-modal-content textarea {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            box-sizing: border-box;
        }

        .event-modal-content button {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            margin-right: 10px;
        }

        .event-modal-content .save-btn {
            background-color: #28a745;
            color: white;
        }

        .event-modal-content .cancel-btn {
            background-color: #6c757d;
            color: white;
        }

        .event-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .event-modal-content .save-btn,
        .event-modal-content .cancel-btn {
            margin: 0;
            min-width: 110px;
        }

        .alert {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            font-size: 14px;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
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
            <li><a href="admin-event-orders.php" class="active"><i class="fas fa-calendar-alt"></i> Event Orders</a>
            </li>
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

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert success"><?= $_SESSION['success_msg'];
            unset($_SESSION['success_msg']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert error"><?= $_SESSION['error_msg'];
            unset($_SESSION['error_msg']); ?></div>
        <?php endif; ?>

        <div class="page-header">
            <h1>Manage Event Orders</h1>
        </div>

        <form method="GET" class="filters">
            <select name="status">
                <option value="">All Status</option>
                <option value="Pending Approval" <?= $status === 'Pending Approval' ? 'selected' : '' ?>>Pending Approval
                </option>
                <option value="Approved" <?= $status === 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Rejected" <?= $status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed</option>
            </select>
            <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
            <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
            <input type="text" name="search" placeholder="Search name/contact" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Apply</button>
        </form>

        <div class="responsive-table-container">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Quantity</th>
                        <th>Menu</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['contact']) ?></td>
                            <td><?= htmlspecialchars($row['address']) ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td><?= htmlspecialchars($row['menu']) ?></td>
                            <td><?= $row['order_date'] ?></td>
                            <td><?= $row['order_time'] ?></td>
                            <td><?= $row['order_status'] ?></td>
                            <td>₹<?= $row['order_price'] ?></td>
                            <td><button class="edit-btn" onclick='openEditModal(<?= json_encode($row) ?>)'>Edit</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>


        <div class="event-modal" id="eventEditModal">
            <div class="event-modal-content">
                <h3>Edit Event Order</h3>
                <form method="POST" action="update-special-order.php" onsubmit="return validateOrderForm()">
                    <input type="hidden" name="id" id="orderId">

                    <label>Name</label>
                    <input type="text" name="name" id="name" required>

                    <label>Contact</label>
                    <input type="text" name="contact" id="contact" required>

                    <label>Address</label>
                    <textarea name="address" id="address" required></textarea>

                    <label>Quantity</label>
                    <input type="number" name="quantity" id="quantity" required>

                    <label>Menu</label>
                    <textarea name="menu" id="menu" required></textarea>

                    <label>Order Date</label>
                    <input type="date" name="order_date" id="order_date" required>

                    <label>Order Time</label>
                    <input type="time" name="order_time" id="order_time" required>

                    <label>Status</label>
                    <select name="order_status" id="order_status" onchange="togglePriceField()" required>
                        <option value="Pending Approval">Pending Approval</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Completed">Completed</option>
                    </select>

                    <label>Order Price (₹)</label>
                    <input type="number" name="order_price" id="order_price" min="0">

                    <div class="event-modal-actions">
                        <button type="submit" class="save-btn">Save Changes</button>
                        <button type="button" class="cancel-btn" onclick="closeEventModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

    </main>
    <script>
        function togglePriceField() {
            const status = document.getElementById("order_status").value;
            const priceField = document.getElementById("order_price");

            if (status === "Rejected") {
                priceField.value = "";        // clear value
                priceField.disabled = true;   // disable field
            } else {
                priceField.disabled = false;  // enable again
            }
        }

        function validateOrderForm() {
            const status = document.getElementById("order_status").value;
            const price = document.getElementById("order_price").value.trim();

            // Rule 1: If Approved → price must be set
            if (status === "Approved" && price === "") {
                alert("Order Price is required when status is Approved.");
                return false;
            }

            // Rule 2: If Completed → price must be set
            if (status === "Completed" && price === "") {
                alert("Order Price is required when status is Completed.");
                return false;
            }

            // Rule 3: If Pending Approval or Rejected → price must not be set
            if ((status === "Pending Approval" || status === "Rejected") && price !== "") {
                alert("Order Price cannot be set when status is Pending Approval or Rejected.");
                return false;
            }

            return true;
        }
    </script>
    <script>
        function openEditModal(order) {
            const modal = document.getElementById('eventEditModal');
            modal.classList.add('active');

            document.getElementById('orderId').value = order.id;
            document.getElementById('name').value = order.name;
            document.getElementById('contact').value = order.contact;
            document.getElementById('address').value = order.address;
            document.getElementById('quantity').value = order.quantity;
            document.getElementById('menu').value = order.menu;
            document.getElementById('order_date').value = order.order_date;
            document.getElementById('order_time').value = order.order_time;
            document.getElementById('order_status').value = order.order_status;
            document.getElementById('order_price').value = order.order_price;
        }

        function closeEventModal() {
            document.getElementById('eventEditModal').classList.remove('active');
        }
    </script>
    <script src="js/admin.js"></script>

</body>

</html>