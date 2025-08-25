<?php
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['feedback_id'];
    $sql = "DELETE FROM feedback WHERE id=$id";
    mysqli_query($conn, $sql);
}
header("Location: admin-feedback.php");
exit();
