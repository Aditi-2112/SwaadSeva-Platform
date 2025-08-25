<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Products | SwaadSeva</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .main {
            max-width: 1200px;
            margin: 0 auto;
        }

        .cards {
            display: flex;
            flex-wrap: wrap;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .cards_item {
            display: flex;
            padding: 1rem;
        }

        @media (min-width: 40rem) {
            .cards_item {
                width: 50%;
            }
        }

        @media (min-width: 56rem) {
            .cards_item {
                width: 33.3333%;
            }
        }

        .card {
            background-color: #fff8f3;
            border-radius: 0.25rem;
            box-shadow: 0 20px 40px -14px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .card_image {
            position: relative;
            max-height: 250px;
        }

        .card_image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card_price {
            position: absolute;
            bottom: 8px;
            right: 8px;
            background-color: #FF6F00;
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
        }

        .note {
            position: absolute;
            top: 0px;
            right: 0px;
            padding: 4px 8px;
            border-radius: 0.25rem;
            background-color: #ff4800ff;
            color: white;
            font-size: 14px;
            font-weight: 700;
        }

        .card_content {
            padding: 16px 12px 32px 24px;
            margin: 16px 8px 8px 0;
            max-height: 290px;
            overflow-y: auto;
        }

        .card_content::-webkit-scrollbar {
            width: 6px;
        }

        .card_content::-webkit-scrollbar-thumb {
            background: #FF6F00;
            border-radius: 10px;
        }

        .card_title {
            position: relative;
            margin: 0 0 16px;
            padding-bottom: 10px;
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            color: #FF6F00;
        }

        .card_title::after {
            content: "";
            display: block;
            width: 40px;
            height: 2px;
            background-color: #FF6F00;
            margin: 8px auto 0;
        }

        .card_text p {
            margin: 0 0 16px;
            font-size: 14px;
            line-height: 1.5;
        }

        /* CTA Section */
        .custom-order-cta {
            background: #fff2e6;
            padding: 50px 20px;
            text-align: center;
        }

        .custom-order-cta h2 {
            font-size: 2em;
            color: #FF6F00;
            margin-bottom: 10px;
        }

        .custom-order-cta p {
            margin-bottom: 25px;
            font-size: 1.1em;
            color: #555;
        }

        .btn-cta {
            background: #FF6F00;
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 1rem;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .btn-cta:hover {
            background: #e65c00;
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
                    <a href="plans.php">Plans</a>
                    <a href="custom-orders.php">Custom Orders</a>
                    <a href="products.php" class="active-nav">Products</a>
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
                <h1>Swaad<div style="color:rgb(22, 148, 13); display:inline;">Seva</div>
                </h1>
                <p>Wholesome, Pure Veg Tiffin Service</p>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="contact-hero">
        <h1>Our <span>Products</span></h1>
        <p>Homemade goodness packed with taste and care</p>
    </section>

    <!-- Products Card List -->
    <div class="main">
        <ul class="cards">
            <li class="cards_item">
                <div class="card">
                    <div class="card_image">
                        <img src="assets/images/p1.jpg" alt="Pickle" />
                        <span class="note">Popular</span>
                        <span class="card_price">₹80</span>
                    </div>
                    <div class="card_content">
                        <h2 class="card_title">Anarse</h2>
                        <div class="card_text">
                            <p>Made with juicy lemons and traditional masala. A must-have sidekick for your meals!</p>
                        </div>
                    </div>
                </div>
            </li>

            <li class="cards_item">
                <div class="card">
                    <div class="card_image">
                        <img src="assets/images/p2.jpg" alt="Ladoo" />
                        <span class="card_price">₹120</span>
                    </div>
                    <div class="card_content">
                        <h2 class="card_title">Kurdai</h2>
                        <div class="card_text">
                            <p>Sweet, soft, and made with pure ghee – our besan ladoos are a festive favorite!</p>
                        </div>
                    </div>
                </div>
            </li>

            <li class="cards_item">
                <div class="card">
                    <div class="card_image">
                        <img src="assets/images/p3.jpeg" alt="Snacks" />
                        <span class="card_price">₹60</span>
                    </div>
                    <div class="card_content">
                        <h2 class="card_title">Chips</h2>
                        <div class="card_text">
                            <p>Crispy, crunchy, and full of flavor — perfect with evening chai or for gifting!</p>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>

    <!-- CTA -->
    <section class="custom-order-cta">
        <h2>🛍️ Order Now and Add Flavour to Your Life!</h2>
        <p>Our homemade products are available in limited batches. Grab yours today.</p>
        <a href="signup.php" class="btn-cta">SignUp & Place Your Order</a>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> SwaadSeva. All rights reserved.</p>
    </footer>
    
    <script src="js/common.js"></script>

</body>

</html>