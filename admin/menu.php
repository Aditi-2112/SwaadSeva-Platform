<?php
require_once "../db_connect.php";
session_start();
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


$selectedDay = $_GET['day'] ?? '';
$selectedMeal = $_GET['meal'] ?? 'All';

$whereClauses = [];


if ($selectedDay === '') {
    $selectedDay = date('l'); // Get today's day (e.g., Monday)
}

if ($selectedDay !== 'All') {
    if ($selectedDay === 'Others') {
        $whereClauses[] = "day_of_week NOT IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
    } else {
        $whereClauses[] = "day_of_week = '$selectedDay'";
    }
}

if ($selectedMeal !== 'All') {
    if ($selectedMeal === 'Others') {
        $whereClauses[] = "meal_type NOT IN ('Breakfast', 'Lunch', 'Dinner')";
    } else {
        $whereClauses[] = "meal_type = '$selectedMeal'";
    }
}

$whereSQL = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : "";

$menu_query = "SELECT * FROM menu $whereSQL ORDER BY id ";
$menu_result = $conn->query($menu_query);
$menu_data = [];
while ($row = $menu_result->fetch_assoc()) {
    $menu_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin | Weekly Menu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filter-form,
        .add-form {
            margin: 20px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .filter-form select,
        .add-form input {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .filter-form button,
        .add-form button {
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .menu-table .edit-btn,
        .menu-table .save-btn,
        .menu-table .delete-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin: 0 4px;
        }

        .menu-table .edit-btn i {
            color: #28a745;
        }

        .menu-table .save-btn i {
            color: #17a2b8;
        }

        .menu-table .delete-btn i {
            color: #dc3545;
        }
    </style>
    <script>
        function enableEdit(rowId) {
            const row = document.getElementById(rowId);
            const tdMenu = row.querySelector(".menu-text");
            const currentText = tdMenu.innerText;
            tdMenu.innerHTML = `<input type='text' value='${currentText}' class='edit-input'>`;
            row.querySelector(".edit-btn").style.display = "none";
            row.querySelector(".save-btn").style.display = "inline-block";
        }

        function saveEdit(rowId, id) {
            const row = document.getElementById(rowId);
            const newValue = row.querySelector("input.edit-input").value;

            fetch("update_menu.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${id}&items=${encodeURIComponent(newValue)}`
            })
                .then(response => response.text())
                .then(data => {
                    row.querySelector(".menu-text").innerText = newValue;
                    row.querySelector(".edit-btn").style.display = "inline-block";
                    row.querySelector(".save-btn").style.display = "none";
                    alert("Menu updated successfully");
                });
        }

        function deleteMenu(id) {
            if (confirm("Are you sure you want to delete this menu item?")) {
                fetch("delete_menu.php?id=" + id)
                    .then(res => res.text())
                    .then(data => {
                        alert("Menu item deleted");
                        location.reload();
                    });
            }
        }
    </script>
</head>

<body>
    <aside class="sidebar">
        <img src="assets/logo/logo5.png" alt="Logo Image">
        <div class="brand">SwaadSeva Admin</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="orders.php"><i class="fas fa-box"></i> Orders</a></li>
            <li><a href="admin-event-orders.php"><i class="fas fa-calendar-alt"></i> Event Orders</a></li>
            <li><a href="menu.php" class="active"><i class="fas fa-utensils"></i> Menu</a></li>
            <li><a href="plans.php"><i class="fas fa-clipboard-list"></i> Plans</a></li>
            <li><a href="admin-feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
        </ul>
    </aside>

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
            <h1>Weekly Tiffin Menu</h1>
        </div>


        <form method="GET" class="filter-form">
            <select name="day">
                <option value="All" <?= $selectedDay === 'All' ? 'selected' : '' ?>>All Days</option>
                <option value="Monday" <?= $selectedDay === 'Monday' ? 'selected' : '' ?>>Monday</option>
                <option value="Tuesday" <?= $selectedDay === 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
                <option value="Wednesday" <?= $selectedDay === 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
                <option value="Thursday" <?= $selectedDay === 'Thursday' ? 'selected' : '' ?>>Thursday</option>
                <option value="Friday" <?= $selectedDay === 'Friday' ? 'selected' : '' ?>>Friday</option>
                <option value="Saturday" <?= $selectedDay === 'Saturday' ? 'selected' : '' ?>>Saturday</option>
                <option value="Sunday" <?= $selectedDay === 'Sunday' ? 'selected' : '' ?>>Sunday</option>
                <option value="Others" <?= $selectedDay === 'Others' ? 'selected' : '' ?>>Others</option>
            </select>

            <select name="meal">
                <option value="All" <?= $selectedMeal === 'All' ? 'selected' : '' ?>>All Meals</option>
                <option value="Breakfast" <?= $selectedMeal === 'Breakfast' ? 'selected' : '' ?>>Breakfast</option>
                <option value="Lunch" <?= $selectedMeal === 'Lunch' ? 'selected' : '' ?>>Lunch</option>
                <option value="Dinner" <?= $selectedMeal === 'Dinner' ? 'selected' : '' ?>>Dinner</option>
                <option value="Others" <?= $selectedMeal === 'Others' ? 'selected' : '' ?>>Others</option>
            </select>

            <button type="submit">Apply Filters</button>
        </form>


        <form action="add_menu.php" method="POST" class="add-form">
            <input type="text" name="day_of_week" placeholder="Enter Day (e.g., Monday)" required>
            <input type="text" name="meal_type" placeholder="Enter Meal (e.g., Lunch)" required>
            <input type="text" name="items" placeholder="Menu Items" required>
            <button type="submit">Add Menu</button>
        </form>

        <div class="responsive-table-container">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Meal</th>
                        <th>Menu Items</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    foreach ($menu_data as $menu) {
                        $rowId = "row$i";
                        echo "<tr id='$rowId'>
                        <td>" . htmlspecialchars($menu['day_of_week']) . "</td>
                        <td>" . htmlspecialchars($menu['meal_type']) . "</td>
                        <td class='menu-text'>" . htmlspecialchars($menu['items']) . "</td>
                        <td>
                        <button class='edit-btn' onclick='enableEdit(\"$rowId\")'><i class='fas fa-edit'></i></button>
                        <button class='save-btn' onclick='saveEdit(\"$rowId\", \"{$menu['id']}\")' style='display:none;'><i class='fas fa-save'></i></button>
                        <button class='delete-btn' onclick='deleteMenu({$menu['id']})'><i class='fas fa-trash-alt'></i></button>
                        </td>
                        </tr>";
                        $i++;
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </main>
    <script src="js/admin.js"></script>
</body>

</html>