<?php
include 'db.php';

session_start();
$_SESSION['message'] = "Plan deleted successfully ✅";
header("Location: plans.php");


if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM plans WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: plans.php");
    } else {
        echo "Error deleting plan.";
    }

    $stmt->close();
    $conn->close();
}
?>