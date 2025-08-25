<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delivery_id'])) {
    $deliveryId = intval($_POST['delivery_id']);

    // Verify delivery belongs to this user and is Out for Delivery
    $checkStmt = $conn->prepare("SELECT * FROM delivery WHERE id = ? AND user_id = ? AND status = 'Out for Delivery'");
    $checkStmt->bind_param("ii", $deliveryId, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        // Update status to Delivered
        $updateStmt = $conn->prepare("UPDATE delivery SET status = 'Delivered' WHERE id = ?");
        $updateStmt->bind_param("i", $deliveryId);
        $updateStmt->execute();
        $updateStmt->close();
    }

    $checkStmt->close();
}

header("Location: user-orders.php");
exit;
?>