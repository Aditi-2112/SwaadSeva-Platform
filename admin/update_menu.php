<?php
require_once "../db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['items'])) {
    $id = intval($_POST['id']);
    $items = trim($_POST['items']);

    $stmt = $conn->prepare("UPDATE menu SET items = ? WHERE id = ?");
    $stmt->bind_param("si", $items, $id);

    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Failed to update";
    }
} else {
    echo "Invalid request";
}
?>