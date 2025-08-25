<?php
require '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $planId = $_POST['plan_id'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $paid = $_POST['amount_paid'];

    // Validate and insert
    $stmt = $conn->prepare("INSERT INTO user_plans (user_id, plan_id, start_date, end_date, amount_paid, payment_status) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $paymentStatus = ($paid > 0) ? 'Pending' : 'Pending';
    $stmt->bind_param("iissds", $userId, $planId, $start, $end, $paid, $paymentStatus);

    if ($stmt->execute()) {
        echo "User plan added successfully.";
    } else {
        echo "Error adding plan.";
    }

    $stmt->close();
}
?>