<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SwaadSeva | Sign Up</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i>Back</a>
    <section class="signup-container">
        <div class="signup-card">
            <h2>Create an Account 📝</h2>
            <p>Join <strong>SwaadSeva</strong> and enjoy daily homely meals.</p>

            <form action="send_otp.php" method="POST" onsubmit="return validateForm()">
                <input type="text" name="name" placeholder="Username" required pattern="^[A-Za-z0-9 ]{3,}$" title="Enter at least 3 alphabetic characters.">

                <input type="email" name="email" placeholder="Email Address" required>

                <input type="tel" name="phone" placeholder="Mobile Number" required pattern="^[6-9]\d{9}$"
                    title="Enter a valid 10-digit Indian mobile number.">

                <input type="password" id="password" name="password" placeholder="Create Password" required
                    minlength="4">

                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password"
                    required minlength="4">

                <button type="submit">Sign Up</button>
            </form>


            <div class="signup-footer">
                <p>Already have an account? <a href="login.php">Log In</a></p>
            </div>
        </div>
    </section>
    <script>
        function validateForm() {
            const password = document.getElementById("password").value.trim();
            const confirmPassword = document.getElementById("confirm_password").value.trim();

            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                return false;
            }
            return true;
        }
    </script>


</body>

</html>