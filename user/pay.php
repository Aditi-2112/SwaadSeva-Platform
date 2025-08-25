<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$planId = $_POST['plan_id'] ?? null;
$payAmount = floatval($_POST['pay_amount'] ?? 0);

if (!$planId || $payAmount <= 0) {
    die("Invalid input.");
}

// Fetch current payment info
$stmt = $conn->prepare("SELECT amount_paid, p.total_price 
                        FROM user_plans up 
                        JOIN plans p ON up.plan_id = p.id 
                        WHERE up.user_id = ? AND up.plan_id = ?");
$stmt->bind_param("ii", $userId, $planId);
$stmt->execute();
$stmt->bind_result($amountPaid, $totalPrice);
$stmt->fetch();
$stmt->close();

$newPaid = $amountPaid + $payAmount;
$status = ($newPaid >= $totalPrice) ? 'Done' : 'Partial';

$stmt = $conn->prepare("UPDATE user_plans SET amount_paid = ?, payment_status = ? WHERE user_id = ? AND plan_id = ?");
$stmt->bind_param("dsii", $newPaid, $status, $userId, $planId);
$stmt->execute();

header("Location: plans.php?payment=success");
