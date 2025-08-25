<?php
include 'db_connect.php'; // Make sure the path is correct

$today = date('l'); // e.g., Monday, Tuesday
$meals = ['Breakfast', 'Lunch', 'Dinner'];
$today_menu = [];

foreach ($meals as $meal) {
    $stmt = $conn->prepare("SELECT items FROM menu WHERE day_of_week = ? AND meal_type = ?");
    $stmt->bind_param("ss", $today, $meal);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $today_menu[$meal] = $row ? $row['items'] : 'Not Available';
}
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SwaadSeva | Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
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
                    <a href="index.php" class="active-nav">Home</a>
                    <a href="about.php">About Us</a>
                    <a href="menu.php">Menu</a>
                    <a href="plans.php">Plans</a>
                    <a href="custom-orders.php">Custom Orders</a>
                    <a href="products.php">Products</a>
                    <a href="contact.php">Contact</a>
                </div>

                <div class="nav-right">
                    <a href="login.php" class="btn-nav btn-login">Login</a>
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




    <!-- Carousel  -->
    <div class="carousel" id="carousel">
        <div class="carousel-inner" id="carouselInner">
            <img src="assets/images/tif1.webp" alt="Tiffin 1">
            <img src="assets/images/o2.jpg" alt="Tiffin 2">
            <img src="assets/images/a1.webp" alt="Tiffin 3">
            <img src="assets/images/a2.jpg" alt="Tiffin 4">
            <img src="assets/images/a4.png" alt="Tiffin 5">
            <img src="assets/images/a6.jpg" alt="Tiffin 6">
        </div>
        <button class="carousel-arrow left" onclick="prevSlide()">&#10094;</button>
        <button class="carousel-arrow right" onclick="nextSlide()">&#10095;</button>

        <div class="carousel-bars" id="carouselBars"></div>
    </div>




    <!-- hero section  -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-left">
                <img src="assets/images/mascot3.png" alt="SwaadSeva Mascot" class="hero-mascot">
            </div>
            <div class="hero-right">
                <h1>Ghar Ka Khana aur Swaad, Har Din</h1>
                <p>Welcome to <strong>SwaadSeva</strong> – your go-to pure veg mess service for homely, hygienic, and
                    heartwarming meals. Loved by students and professionals alike!</p>
                <a href="signup.php" class="hero-btn">Get Started</a>
            </div>
        </div>
    </section>

    <!-- Why to choose SwaadSeva  -->
    <section class="why-choose">
        <h2>Why Choose SwaadSeva?</h2>
        <div class="why-grid">
            <div class="why-card">
                <img src="assets/images/pureveg1.png" alt="Pure Veg">
                <h3>Pure Veg & Hygienic</h3>
                <p>Only clean, hygienic vegetarian food – made with care and love.</p>
            </div>
            <div class="why-card">
                <img src="assets/images/timelydelivery.png" alt="Timely Delivery">
                <h3>On-Time Delivery</h3>
                <p>Hot meals delivered daily right to your doorstep.</p>
            </div>
            <div class="why-card">
                <img src="assets/images/mascot3.png" alt="Home Taste">
                <h3>Ghar Jaisa Swaad</h3>
                <p>Taste that reminds you of mom's kitchen.</p>
            </div>
            <div class="why-card">
                <img src="assets/images/affordable.png" alt="Affordable">
                <h3>Affordable Plans</h3>
                <p>Daily, weekly, monthly – pick what suits your pocket.</p>
            </div>
        </div>
    </section>





    <!-- YouTube Intro Video -->
    <section class="video-section">
        <h2>🎥 Watch Our Introduction</h2>
        <p>See how SwaadSeva brings homely vegetarian meals to your doorstep!</p>
        <div class="video-container">
            <iframe src="https://www.youtube.com/embed/YOUR_VIDEO_ID" frameborder="0" allowfullscreen></iframe>
        </div>
    </section>






    


    <!-- Subscription Plans -->
    <section class="index-plans-section">
        <div class="plans-header">
            <h2>📦 Choose Your Plan</h2>
        </div>

        <!-- Cards Section -->
        <div class="plans-container">
            <?php
            $query = "SELECT * FROM plans WHERE id IN (1, 3, 2)";
            $result = $conn->query($query);
            $highlighted_id = 1;

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $id = $row['id'];
                    $name = $row['name'];
                    $price = number_format($row['total_price'], 2);
                    $desc = $row['description'];
                    $meals = $row['meal_type'];
                    $delivery = ($row['includes_delivery'] === 'Yes') ? "✔ Delivery Included" : "❌ Pickup Only";
                    $duration = $row['duration_days'];

                    $highlightClass = ($id == $highlighted_id) ? "plan-card highlight" : "plan-card";

                    echo "<div class='$highlightClass'>";

                    if ($id == $highlighted_id) {
                        echo "<div class='badge'>🔥 Preferred</div>";
                    }

                    echo "
                    <h3>$name</h3>
                    <p class='price'>₹$price / {$duration} days</p>
                    <ul>
                        <li>Meals: $meals</li>
                        <li>$desc</li>
                        <li>$delivery</li>
                    </ul>
                    <a href='subscribe.php?plan_id=$id' class='plan-btn'>Book Now</a>
                </div>";
                }
            } else {
                echo "<p style='text-align:center;'>No plans available.</p>";
            }
            ?>
        </div>

        <!-- Button at the Bottom -->
        <div class="plans-footer">
            <a href="plans.php" class="btn-plan">See All Plans</a>
        </div>
    </section>







    <!-- Today's Menu -->
    <section class="todays-menu">
        <h2>🍽 <?php echo $today; ?>'s Special Menu - </h2>
        <div class="menu-grid">
            <?php foreach ($today_menu as $meal => $items): ?>
                <div class="menu-box">
                    <h3><?php echo $meal; ?></h3>
                    <p><?php echo $items; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="menu-link">
            <a href="menu.php" class="btn-big">View Full Weekly Menu</a>
        </div>
    </section>




    <!-- How it Works? -->
    <section class="how-it-works">
        <h2>How It Works</h2>
        <div class="how-steps">
            <div class="step">
                <span>1</span>
                <h3>Choose a Plan</h3>
                <p>Select daily, weekly, or monthly plans based on your needs.</p>
            </div>
            <div class="step">
                <span>2</span>
                <h3>Place Your Order</h3>
                <p>Sign up and confirm your delivery details easily.</p>
            </div>
            <div class="step">
                <span>3</span>
                <h3>Get Your Tiffin</h3>
                <p>Enjoy hot, homemade food delivered to your doorstep every day!</p>
            </div>
        </div>
    </section>



    <!-- Call to Action  -->
    <section class="cta-section">
        <h2>Ready to Taste Ghar Ka Khana?</h2>
        <p>Join hundreds of happy students and professionals who rely on SwaadSeva for pure veg, home-style meals
            delivered daily.</p>
        <a href="signup.php" class="cta-btn">Get Started Now</a>
    </section>

    <section class="testimonials">
        <h2>What Our Customers Say</h2>
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <p>"Tastes just like home! I never thought I'd enjoy hostel food again."</p>
                <h4>- Pratik sheth, Engineering Student</h4>
            </div>
            <div class="testimonial-card">
                <p>"Very punctual and hygienic. I’ve subscribed for 3 months straight."</p>
                <h4>- Anjali, IT Professional</h4>
            </div>
            <div class="testimonial-card">
                <p>"Affordable and filling. Their Sunday special is my favorite!"</p>
                <h4>- Karan, MBA Student</h4>
            </div>
        </div>
    </section>



    <!-- Footer  -->
    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-about">
                <h3>SwaadSeva</h3>
                <p>Your trusted mess partner for pure veg, home-style food delivered fresh every day.</p>
            </div>
            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="plans.php">Plans</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Contact Us</h4>
                <p>Email: support@swaadseva.com</p>
                <p>Phone: +91-9876543210</p>
                <p>Location: Pune, India</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2025 SwaadSeva. All rights reserved.
        </div>
    </footer>


    <script src="js/common.js"></script>

    <script>
        // Carousel
        let currentSlide = 0;
        const carouselInner = document.getElementById("carouselInner");
        const barsContainer = document.getElementById("carouselBars");
        const totalSlides = carouselInner.children.length;

        // Create bar indicators
        for (let i = 0; i < totalSlides; i++) {
            const bar = document.createElement("div");
            bar.classList.add("bar");
            bar.addEventListener("click", () => {
                currentSlide = i;
                updateSlide();
            });
            barsContainer.appendChild(bar);
        }

        function updateBars() {
            const bars = barsContainer.children;
            for (let i = 0; i < bars.length; i++) {
                bars[i].classList.toggle("active", i === currentSlide);
            }
        }

        function updateSlide() {
            const slideWidth = document.querySelector(".carousel").offsetWidth;
            carouselInner.style.transform = `translateX(-${currentSlide * slideWidth}px)`;
            updateBars();
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlide();
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateSlide();
        }

        // Auto-slide
        setInterval(nextSlide, 4000);

        // Responsive
        window.addEventListener('resize', updateSlide);
        window.addEventListener('load', updateSlide);
    </script>






</body>

</html>