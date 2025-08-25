<?php
session_start();
include 'db_connect.php'; // Ensure this is correctly linked to your DB

date_default_timezone_set('Asia/Kolkata');

// Log logout time if log_id is set
if (isset($_SESSION['log_id']) && is_numeric($_SESSION['log_id'])) {
    $log_id = (int) $_SESSION['log_id'];
    $logout_time = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("UPDATE user_logs SET logout_time = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $logout_time, $log_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Clear session data
$_SESSION = [];
session_unset();
session_destroy();

// Redirect to home/login page
header("Location: index.php");
exit;
?>