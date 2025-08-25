<?php
require_once 'db.php';
$id = $_GET['id'] ?? 0;

if ($id > 0) {
  $conn->query("DELETE FROM poll_votes WHERE poll_id = $id");
  $conn->query("DELETE FROM polls WHERE id = $id");
}

header("Location: dashboard.php");
exit;
?>
