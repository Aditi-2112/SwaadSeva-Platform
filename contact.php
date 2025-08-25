<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contact Us | SwaadSeva</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
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
                    <a href="products.php">Products</a>
                    <a href="contact.php" class="active-nav">Contact</a>
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
                <h1>Swaad<div style="color:rgb(22, 148, 13); display:inline;">Seva</div>
                </h1>
                <p>Wholesome, Pure Veg Tiffin Service</p>
            </div>
        </div>
    </header>



    <section class="contact-hero">
        <h1>Contact <span>Us</span></h1>
        <p>We’d love to hear from you — questions, feedback, or just a hello!</p>
    </section>

    <section class="contact-container">
        <div class="contact-form">
            <h2>Send a Message</h2>
            <form action="contact_process.php" method="POST">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <input type="tel" name="phone" placeholder="Your Phone Number" required>
                <textarea name="message" rows="5" placeholder="Your Message..." required></textarea>
                <button type="submit">Send Message</button>
            </form>
        </div>

        <div class="contact-info">
            <h2>Reach Us</h2>
            <p><strong>📍 Address:</strong> SwaadSeva Kitchen, Ahmednagar, Maharashtra</p>
            <p><strong>📞 Phone:</strong> +91 98765 43210</p>
            <p><strong>✉️ Email:</strong> support@swaadseva.com</p>
            <div class="social-links">
                <a href="#"><img src="assets/icons/instagram.webp" alt="Instagram"></a>
                <a href="#"><img src="assets/icons/facebook.png" alt="Facebook"></a>
                <a href="#"><img src="assets/icons/whatsapp.webp" alt="WhatsApp"></a>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> SwaadSeva. All rights reserved.</p>
    </footer>
    <script src="js/common.js"></script>

</body>

</html>