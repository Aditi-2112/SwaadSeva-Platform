<?php
session_start();
require_once "db_connect.php"; // Make sure this connects and sets $conn

// Check for session values
if (!isset($_SESSION['signup_otp']) || !isset($_SESSION['signup_data'])) {
    echo "<script>alert('Session expired. Please sign up again.'); window.location.href='signup.php';</script>";
    exit;
}

// OPTIONAL: Check if OTP has expired (e.g. 5 minutes = 300 seconds)
if (isset($_SESSION['otp_created_at']) && (time() - $_SESSION['otp_created_at'] > 300)) {
    unset($_SESSION['signup_otp'], $_SESSION['signup_data'], $_SESSION['otp_created_at']);
    echo "<script>alert('OTP expired. Please sign up again.'); window.location.href='signup.php';</script>";
    exit;
}

// Handle OTP form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp']);
    $original_otp = $_SESSION['signup_otp'];

    if ($entered_otp == $original_otp) {
        // OTP is correct – insert user data
        $data = $_SESSION['signup_data'];
        $name = $data['name'];
        $email = $data['email'];
        $phone = $data['phone'];
        $hashed_password = $data['password'];

        // Insert new user
        $insert_sql = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);

        if ($insert_stmt->execute()) {
            // Clean up session
            unset($_SESSION['signup_otp'], $_SESSION['signup_data'], $_SESSION['otp_created_at']);

            echo "<script>alert('Signup successful! You can now login.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Error: Could not create account. Please try again later.');</script>";
        }

    } else {
        echo "<script>alert('Invalid OTP. Please try again.');</script>";
    }
}
?>

<!-- HTML for OTP form -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Verify OTP | SwaadSeva</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <section class="signup-container">
        <div class="signup-card">
            <h2>Verify OTP 🔐</h2>
            <p>An OTP was sent to your email/phone. Please enter it below to complete registration.</p>

            <form method="POST">
                <input type="number" name="otp" placeholder="Enter 6-digit OTP" required pattern="\d{6}" maxlength="6"
                    min="100000" max="999999" title="Enter a valid 6-digit OTP">
                <button type="submit">Verify</button>
            </form>

            <div class="signup-footer">
                <p>Didn't receive it? <a href="signup.php">Try again</a></p>
            </div>
        </div>
    </section>

    <!-- 🧪 Dev-only: Show OTP in browser console -->
    <script>
        console.log("Your OTP is: <?php echo htmlspecialchars($_SESSION['signup_otp']); ?>");
    </script>
</body>

</html>