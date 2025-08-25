<?php
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['feedback_id'];
    $reply = $_POST['admin_reply'];

    $sql = "UPDATE feedback SET admin_reply=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $reply, $id);
    $stmt->execute();
}
header("Location: admin-feedback.php");
exit();
