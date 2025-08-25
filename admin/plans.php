<?php
session_start();

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include 'db.php'; // adjust path as needed

// Fetch plans from the database
$sql = "SELECT * FROM plans";
$result = $conn->query($sql);
?>
<?php

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Plans</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
            <li><a href="plans.php" class="active"><i class="fas fa-clipboard-list"></i> Plans</a></li>
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


    <!-- Main -->
    <main class="admin-main">
        <div class="page-header">
            <h1>Plans</h1>
            <p>Manage all available subscription plans here.</p>
        </div>


        <div class="plans-wrapper">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Plan Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td>
                                <button class="view-btn" onclick="openEditModalplans(
              <?= $row['id'] ?>,
              '<?= addslashes($row['name']) ?>',
              '<?= addslashes($row['description']) ?>',
              '<?= addslashes($row['meal_type']) ?>',
              <?= $row['duration_days'] ?>,
              <?= $row['base_price'] ?>,
              <?= $row['delivery_charge'] ?>,
              '<?= addslashes($row['includes_delivery']) ?>'
              )">Edit</button>
                                <button class="delete-btn" onclick="confirmDelete(<?= $row['id'] ?>)"><i
                                        class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>



        <button class="add-btn" onclick="openModalplans('addModalplans')">+ Add New Plan</button>

    </main>

    <!-- Edit Modalplans -->
    <div class="modalplans" id="editModalplans">
        <div class="modalplans-content">
            <span class="close" onclick="closeModalplans('editModalplans')">&times;</span>
            <h2>Edit Plan</h2>
            <form id="editForm" method="POST" action="update_plan.php">
                <input type="hidden" name="id" id="edit-id">

                <label for="edit-name">Name:</label>
                <input type="text" name="name" id="edit-name" placeholder="Plan Name" required>

                <label for="edit-description">Description:</label>
                <textarea name="description" id="edit-description" placeholder="Description"></textarea>

                <label for="edit-meal">Meal Type:</label>
                <input type="text" name="meal_type" id="edit-meal" placeholder="Lunch / Dinner / Both">

                <label for="edit-duration">Duration (Days):</label>
                <input type="number" name="duration_days" id="edit-duration" placeholder="Duration in Days">

                <label for="edit-base">Base Price (₹):</label>
                <input type="number" name="base_price" id="edit-base" step="0.01" placeholder="Base Price">

                <label for="edit-delivery">Delivery Charge (₹):</label>
                <input type="number" name="delivery_charge" id="edit-delivery" step="0.01"
                    placeholder="Delivery Charge">

                <label for="edit-includes">Includes Delivery:</label>
                <input type="text" name="includes_delivery" id="edit-includes" placeholder="Yes / No">

                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>



    <!-- Add Modalplans -->
    <div class="modalplans" id="addModalplans">
        <div class="modalplans-content">
            <span class="close" onclick="closeModalplans('addModalplans')">&times;</span>
            <h2>Add New Plan</h2>
            <form method="POST" action="add_plan.php">

                <label for="add-name">Name:</label>
                <input type="text" name="name" id="add-name" placeholder="Plan Name" required>

                <label for="add-description">Description:</label>
                <textarea name="description" id="add-description" placeholder="Description"></textarea>

                <label for="add-meal">Meal Type:</label>
                <input type="text" name="meal_type" id="add-meal" placeholder="Lunch / Dinner / Both">

                <label for="add-duration">Duration (Days):</label>
                <input type="number" name="duration_days" id="add-duration" placeholder="Duration in Days">

                <label for="add-base">Base Price (₹):</label>
                <input type="number" name="base_price" id="add-base" step="0.01" placeholder="Base Price">

                <label for="add-delivery">Delivery Charge (₹):</label>
                <input type="number" name="delivery_charge" id="add-delivery" step="0.01" placeholder="Delivery Charge">

                <label for="add-includes">Includes Delivery:</label>
                <input type="text" name="includes_delivery" id="add-includes" placeholder="Yes / No">

                <button type="submit">Add Plan</button>
            </form>
        </div>
    </div>


    <script>
        function openModalplans(id) {
            document.getElementById(id).classList.add('show');
            document.body.classList.add('modalplans-open');
        }

        function closeModalplans(id) {
            document.getElementById(id).classList.remove('show');
            document.body.classList.remove('modalplans-open');
        }

        function openEditModalplans(id, name, description, meal_type, duration, base_price, delivery_charge, includes_delivery) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-description').value = description;
            document.getElementById('edit-meal').value = meal_type;
            document.getElementById('edit-duration').value = duration;
            document.getElementById('edit-base').value = base_price;
            document.getElementById('edit-delivery').value = delivery_charge;
            document.getElementById('edit-includes').value = includes_delivery;
            openModalplans('editModalplans');
        }

        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this plan?")) {
                window.location.href = "delete_plan.php?id=" + id;
            }
        }
    </script>

    <?php if (!empty($message)): ?>
        <script>
            alert("<?= $message ?>");
        </script>
    <?php endif; ?>


    <script src="js/admin.js"></script>
</body>

</html>