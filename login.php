<?php
session_start();
include 'db_connect.php'; // DB connection

$login_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, name, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row['password'])) {
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["user_name"] = $row["name"];
                $_SESSION["user_email"] = $email;
                $_SESSION["user_role"] = $row["role"];

                date_default_timezone_set("Asia/Kolkata");
                $login_time = date('Y-m-d H:i:s');

                $insert_log = $conn->prepare("INSERT INTO user_logs (user_id, login_time) VALUES (?, ?)");
                $insert_log->bind_param("is", $row["id"], $login_time);
                $insert_log->execute();
                $_SESSION["log_id"] = $insert_log->insert_id;

                if ($row['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: user/index.php");
                }
                exit;
            } else {
                $login_error = "Invalid password.";
            }
        } else {
            $login_error = "No user found with that email.";
        }
    } else {
        $login_error = "Database error.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SwaadSeva | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>

    <section class="login-container">
        <div class="login-card">
            <h2>Welcome Back 👋</h2>
            <p>Login to continue to <strong>SwaadSeva</strong></p>

            <form action="login.php" method="POST">
                <?php if (!empty($login_error)): ?>
                    <div class="error-msg"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <input type="email" name="email" placeholder="Email address" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Log In</button>
            </form>

            <div class="login-footer">
                <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
            </div>
        </div>
    </section>

</body>

</html>