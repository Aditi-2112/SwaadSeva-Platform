<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>About Us | SwaadSeva</title>
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
                    <a href="about.php" class="active-nav">About Us</a>
                    <a href="menu.php">Menu</a>
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
                <h1>Swaad<div style="color:rgb(22, 148, 13); display:inline;">Seva</div>
                </h1>
                <p>Wholesome, Pure Veg Tiffin Service</p>
            </div>
        </div>
    </header>


    <!-- About Section  -->
    <section class="about-hero">
        <div class="about-hero-content">
            <h1>About <span>SwaadSeva</span></h1>
            <p>Bringing the warmth of home-cooked vegetarian food to every student, professional, and soul away from
                home.</p>
        </div>
    </section>

    <section class="about-section">
        <div class="about-container">
            <div class="about-text">
                <h2>Our Story</h2>
                <p>SwaadSeva was born with a simple idea: to serve wholesome, pure vegetarian meals to people staying
                    away from their families. Whether you're a student, a paying guest, or a busy professional — we know
                    how hard it is to find clean, tasty, and balanced food every day.</p>
                <p>We blend traditional Indian flavors with modern kitchen hygiene and timely delivery to give you the
                    feeling of “Ghar ka Khana” — served with care.</p>
            </div>
            <div class="about-image">
                <img src="assets/images/tiffin5.jpg" alt="Our Tiffin Service">
            </div>
        </div>
    </section>

    <section class="mission-section">
        <div class="mission-container">
            <h2>Our Mission</h2>
            <p>To bring affordable, homely, and hygienic food to everyone who misses the taste and warmth of home.</p>
        </div>
    </section>

    <section class="team-section">
        <h2>Meet Our Team</h2>
        <div class="team-container">
            <div class="team-card">
                <img src="assets/images/mascot6.png" alt="Mom Chef">
                <h3>Chef Aai</h3>
                <p>Our recipe master — expert in simple, soulful food that reminds you of your childhood.</p>
            </div>
            <div class="team-card">
                <img src="assets/images/profile.jpg" alt="Founder">
                <h3>Geet Gugale</h3>
                <p>Founder & Developer — loves food, tech, and solving real problems with great design.</p>
            </div>
            <div class="team-card">
                <img src="assets/images/profile1.jpg" alt="Founder">
                <h3>Aditi Gawali</h3>
                <p>Founder & Developer — loves food, tech, and solving real problems with great design.</p>
            </div>
            <div class="team-card">
                <img src="assets/images/profile2.jpg" alt="Founder">
                <h3>Poonam Choure</h3>
                <p>Founder & Developer — loves food, tech, and solving real problems with great design.</p>
            </div>
        </div>
    </section>

    <!-- Optional Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> SwaadSeva. All rights reserved.</p>
    </footer>
    <script src="js/common.js"></script>

</body>

</html>