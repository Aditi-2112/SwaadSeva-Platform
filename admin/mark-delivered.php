<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delivery_id'])) {
    $id = intval($_POST['delivery_id']);
    $stmt = $conn->prepare("UPDATE delivery SET status = 'Delivered' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: orders.php"); // redirect back
exit;
?>