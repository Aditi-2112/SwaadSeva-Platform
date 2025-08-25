<?php
include 'db_connect.php'; // Make sure this file correctly connects to your DB

// Fetch plans from database
$sql = "SELECT * FROM plans ORDER BY id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SwaadSeva | Subscription Plans</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .plan-card.popular {
            border: 2px solid #FF9800;
            background: #fff8e1;
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.4);
            transform: scale(1.05);
        }

        .popular .price {
            color: #28a745;
            font-weight: bold;
        }

        .plan-card .btn-plan {
            margin-top: 15px;
            background-color: #ff6f00;
            color: #fff;
            padding: 10px 18px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
        }

        .plan-card .btn-plan:hover {
            background-color: #e55d00;
        }
    </style>
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
                    <a href="menu.php">Menu</a>
                    <a href="plans.php" class="active-nav">Plans</a>
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

    <!-- Header -->
    <header>
        <div class="header-container">
            <img src="assets/images/logo5.png" alt="SwaadSeva Logo" class="header-logo">
            <div class="header-text">
                <h1>Swaad<span style="color:rgb(22, 148, 13);">Seva</span></h1>
                <p>Wholesome, Pure Veg Tiffin Service</p>
            </div>
        </div>
    </header>

    <!-- Plans Page HTML -->
    <section class="plans-hero">
        <h1>Choose Your Tiffin Plan</h1>
        <p>Affordable and homely meals, delivered daily.</p>
    </section>

    <section class="plans-section">
        <?php
        if ($result && $result->num_rows > 0) {
            $count = 1;
            while ($row = $result->fetch_assoc()) {
                $planId = $row['id'];
                $name = $row['name'];
                $price = number_format($row['base_price'], 2);
                $desc = $row['description'];
                $meals = $row['meal_type'];
                $duration = $row['duration_days'];
                $total_price = $row['total_price'];

                // Delivery info
                if ($row['includes_delivery'] == 'Yes') {
                    $delivery = "✔ Delivery Included";
                } else if ($row['includes_delivery'] == 'No') {
                    $delivery = "❌ Pickup Only";
                } else {
                    $delivery = "N/A";
                }

                // Highlight most preferred (1rd) plan
                $highlightClass = ($count == 1) ? "plan-card popular" : "plan-card";

                echo "
        <div class='$highlightClass'>
            <h2>$name</h2>
            <p class='price'>₹$total_price / {$duration} day(s)</p>
            <ul>
                <li>Meals: $meals</li>
                <li>$desc</li>
                <li>$delivery</li>
            </ul>
            <a href='subscribe.php?plan_id=$planId' class='btn-plan'>Subscribe</a>
        </div>";
                $count++;
            }
        } else {
            echo "<p style='text-align:center;'>No plans available at the moment. Please check back later.</p>";
        }
        ?>

    </section>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> SwaadSeva. All rights reserved.</p>
    </footer>
    <script src="js/common.js"></script>

</body>

</html>