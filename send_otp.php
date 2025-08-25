<?php
session_start();
require_once "db_connect.php"; // Make sure this connects and sets $conn

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    $errors = [];

    // Basic validations
    if (!preg_match("/^[A-Za-z0-9 ]{3,}$/", $name)) {
        $errors[] = "Name must be at least 3 characters and contain only letters, numbers, and spaces.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }
    if (!preg_match("/^[6-9]\d{9}$/", $phone)) {
        $errors[] = "Invalid mobile number. It should be 10 digits starting with 6-9.";
    }
    if (strlen($password) < 4) {
        $errors[] = "Password must be at least 4 characters long.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    // 🚨 Check if email or phone already exists in DB
    if (empty($errors)) {
        $check_sql = "SELECT id FROM users WHERE email = ? OR phone = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $email, $phone);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $errors[] = "User already exists. Please login instead.";
        }
    }

    // If any errors, show alert and stop
    if (!empty($errors)) {
        $msg = implode("\\n", $errors);
        echo "<script>alert('$msg'); window.history.back();</script>";
        exit;
    }

    // ✅ If all good: generate OTP
    $otp = rand(100000, 999999);
    $_SESSION['signup_otp'] = $otp;
    $_SESSION['otp_created_at'] = time();
    $_SESSION['signup_data'] = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'password' => password_hash($password, PASSWORD_DEFAULT)
    ];

    header("Location: verify_otp.php");
    exit;
}
echo "Invalid access.";
