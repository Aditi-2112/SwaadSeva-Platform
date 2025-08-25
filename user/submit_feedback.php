<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'];
$rating = $_POST['rating'];
$message = $_POST['message'];

$sql = "INSERT INTO feedback (user_id, rating, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $user_id, $rating, $message);
$stmt->execute();

header("Location: user-feedback.php");
exit;
