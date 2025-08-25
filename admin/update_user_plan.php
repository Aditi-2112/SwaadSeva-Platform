<?php
require '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['user_plan_id'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $price = $_POST['total_price'];

    // Update user_plans
    $stmt = $conn->prepare("UPDATE user_plans SET start_date = ?, end_date = ?, amount_paid = ? WHERE id = ?");
    $stmt->bind_param("ssdi", $start, $end, $price, $id);

    if ($stmt->execute()) {
        echo "Plan updated successfully.";
    } else {
        echo "Error updating plan.";
    }

    $stmt->close();
}
?>