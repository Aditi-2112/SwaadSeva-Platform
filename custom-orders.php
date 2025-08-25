<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Custom Orders | SwaadSeva</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

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
            display: flex;
            justify-content: center;
            align-items: center;
            width: 45px;
            height: 45px;
            border-radius: 0.25rem;
            background-color: #ff4800ff;
            color: white;
            font-size: 18px;
            font-weight: 700;
        }

        .card_price span {
            font-size: 12px;
            margin-top: -2px;
        }

        .note {
            position: absolute;
            top: 0px;
            right: 0px;
            padding: 4px 8px;
            border-radius: 0.25rem;
            color: white;
            /* border: 1px solid white; */
            color: white;
            background-color: #ff4800ff;
            font-size: 14px;
            font-weight: 700;
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

        .card_content {
            position: relative;
            padding: 16px 12px 32px 24px;
            margin: 16px 8px 8px 0;
            max-height: 290px;
            overflow-y: scroll;
        }

        .card_content h2 {
            color: #ff5100ff;
        }

        .card_content::-webkit-scrollbar {
            width: 8px;
        }

        .card_content::-webkit-scrollbar-track {
            box-shadow: 0;
            border-radius: 0;
        }

        .card_content::-webkit-scrollbar-thumb {
            background: #FF6F00;
            border-radius: 15px;
        }

        .card_title {
            position: relative;
            margin: 0 0 24px;
            padding-bottom: 10px;
            text-align: center;
            font-size: 20px;
            font-weight: 700;
        }

        .card_title::after {
            position: absolute;
            display: block;
            width: 50px;
            height: 2px;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            background-color: #FF6F00;
            content: "";
        }

        hr {
            margin: 24px auto;
            width: 50px;
            border-top: 2px solid #FF6F00;
        }

        .card_text p {
            margin: 0 0 24px;
            font-size: 14px;
            line-height: 1.5;
        }

        .card_text p:last-child {
            margin: 0;
        }




        /* --- CTA SECTION --- */
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
            display: inline-block;
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
                    <a href="custom-orders.php" class="active-nav">Custom Orders</a>
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
                <h1>Swaad<div style="color:rgb(22, 148, 13); display:inline;">Seva</div>
                </h1>
                <p>Wholesome, Pure Veg Tiffin Service</p>
            </div>
        </div>
    </header>

    <!-- Custom Orders Hero Section -->
    <section class="contact-hero">
        <h1>Custom <span>Orders</span></h1>
        <p>Food made with love — perfect for every special occasion!</p>
    </section>


    <h2 style="margin: 42px 0px 20px 0px; color: #ff6f00;" align="center">Have a look at our orders completed in the
        past!</h2>

    <!-- Past Orders Cards  -->
    <div class="main">
        <ul class="cards">
            <li class="cards_item">
                <div class="card">
                    <div class="card_image">
                        <img src="assets/images/o1.webp" alt="card1" />
                        <span class="note">Best Seller</span>
                        <span class="card_price">₹180</span>
                    </div>
                    <div class="card_content">
                        <h2 class="card_title">Farmstand Salad</h2>
                        <div class="card_text">
                            <p>Dig into the freshest veggies of the season! This salad-in-a-jar features a mixture of
                                leafy greens and seasonal vegetables, fresh from the farmer's market.
                            </p>
                        </div>
                    </div>
                </div>
            </li>

            <li class="cards_item">
                <div class="card">
                    <div class="card_image">
                        <img src="assets/images/o2.jpg" alt="card2" />
                        <!-- <span class="note">Seasonal</span> -->
                        <!-- <span class="card_price"><span>$</span>18</span> -->
                    </div>
                    <div class="card_content">
                        <h2 class="card_title">Ultimate Reuben</h2>
                        <div class="card_text">
                            <p>All great meals take time, but this one takes it to the next level! More than 650 hours
                                of fermenting, brining, aging, and curing goes into each and every one of our legendary
                                Reuben sandwiches.
                            </p>
                        </div>
                    </div>
                </div>
            </li>

            <li class="cards_item">
                <div class="card">
                    <div class="card_image">
                        <img src="assets/images/o3.jpg" alt="card3" />
                        <!-- <span class="note">Seasonal</span> -->
                        <!-- <span class="card_price"><span>$</span>16</span> -->
                    </div>
                    <div class="card_content">
                        <h2 class="card_title">Fig &amp; Berry Plate</h2>
                        <div class="card_text">
                            <p>A succulent sextet of fresh figs join with a selection of bodacious seasonal berries in
                                this refreshing, shareable dessert.
                            </p>
                        </div>
                    </div>
                </div>
            </li>
            <li class="cards_item">
                <div class="card">
                    <div class="card_image">
                        <!-- <span class="note">Seasonal</span> -->
                        <img src="assets/images/o4.jpg" alt="card4" />
                        <!-- <span class="card_price"><span>$</span>16</span> -->
                    </div>
                    <div class="card_content">
                        <h2 class="card_title">Fig &amp; Berry Plate</h2>
                        <div class="card_text">
                            <p>A succulent sextet of fresh figs join with a selection of bodacious seasonal berries in
                                this refreshing, shareable dessert.
                            </p>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>



    <!-- Call to Action -->
    <section class="custom-order-cta">
        <h2>🎉 Make Your Moments Memorable with SwaadSeva!</h2>
        <p>Planning a special day? Leave the food to us!</p>
        <a href="signup.php" class="btn-cta">SignUp & Book Your Event Order Now</a>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> SwaadSeva. All rights reserved.</p>
    </footer>

    <script src="js/common.js"></script>

</body>

</html>