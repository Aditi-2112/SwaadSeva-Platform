<?php
include 'db_connect.php';

// Fetch menu from database
$sql = "SELECT day_of_week, meal_type, items FROM menu ORDER BY 
        FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
        FIELD(meal_type, 'Breakfast', 'Lunch', 'Dinner')";

$result = $conn->query($sql);

// Organize results into array
$menuData = [];
while ($row = $result->fetch_assoc()) {
    $menuData[$row['day_of_week']][$row['meal_type']] = $row['items'];
}

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SwaadSeva | Weekly Menu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css"> <!-- Make sure this path is correct -->
</head>

<body>
    <!-- Navbar -->
    <nav>
        <div class="nav-wrapper">
            <div class="nav-left">
                <a href="index.php" class="nav-logo">SwaadSeva</a>
            </div>
            <div class="nav-toggle" id="navToggle">&#9776;</div>
            <div class="nav-menu" id="navMenu">
                <div class="nav-center">
                    <a href="index.php">Home</a>
                    <a href="about.php">About Us</a>
                    <a href="menu.php" class="active-nav">Menu</a>
                    <a href="plans.php">Plans</a>
                    <a href="custom-orders.php">Custom Orders</a>
                    <a href="products.php">Products</a>
                    <a href="contact.php">Contact</a>
                </div>
                <div class="nav-right">
                    <a href="login.php" class="btn-nav">Login</a>
                    <a href="signup.php" class="btn-nav btn-signup">Sign Up</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header  -->
    <header>
        <div class="header-container">
            <img src="assets/images/logo5.png" alt="SwaadSeva Logo" class="header-logo">
            <div class="header-text">
                <h1>Swaad<span style="color:rgb(22, 148, 13);">Seva</span></h1>
                <p>Wholesome, Pure Veg Tiffin Service</p>
            </div>
        </div>
    </header>

    <!-- Menu Section -->
    <section class="menu-hero">
        <h1>This Week's Menu</h1>
        <p>Pure Veg. Homely Taste. Freshly Cooked.</p>
    </section>

    <section class="weekly-menu">
        <div class="menu-table">
            <table>
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Breakfast</th>
                        <th>Lunch</th>
                        <th>Dinner</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($days as $day): ?>
                        <tr>
                            <td><?= $day ?></td>
                            <td><?= $menuData[$day]['Breakfast'] ?? '-' ?></td>
                            <td><?= $menuData[$day]['Lunch'] ?? '-' ?></td>
                            <td><?= $menuData[$day]['Dinner'] ?? '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="menu-note">
        <p><strong>Note:</strong> Menu is subject to seasonal changes to ensure freshness and taste.</p>
        <a href="plans.php" class="menu-cta">View Subscription Plans</a>
    </section>

    <footer>
        <p>&copy; <?= date("Y") ?> SwaadSeva. All rights reserved.</p>
    </footer>
    <script src="js/common.js"></script>

</body>

</html>