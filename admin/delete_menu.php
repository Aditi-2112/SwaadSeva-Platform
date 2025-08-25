<?php
require_once "../db_connect.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM menu WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Menu item deleted successfully.";
    } else {
        echo "Error deleting menu item.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
?>