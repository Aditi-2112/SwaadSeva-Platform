<?php
require_once 'db.php';

$poll_id = $_POST['poll_id'] ?? 0;
$question = $_POST['question'] ?? '';
$option1 = $_POST['option1'] ?? '';
$option2 = $_POST['option2'] ?? '';
$option3 = $_POST['option3'] ?? '';
$option4 = $_POST['option4'] ?? '';

if ($poll_id > 0 && $question && $option1 && $option2) {
  $stmt = $conn->prepare("UPDATE polls SET question=?, option1=?, option2=?, option3=?, option4=? WHERE id=?");
  $stmt->bind_param("sssssi", $question, $option1, $option2, $option3, $option4, $poll_id);
  if ($stmt->execute()) {
    header("Location: dashboard.php");
    exit;
  } else {
    echo "Error: " . $conn->error;
  }
} else {
  echo "Please fill required fields.";
}
?>
