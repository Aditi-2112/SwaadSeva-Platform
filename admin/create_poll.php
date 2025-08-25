<?php
require_once 'db.php';

$question = $_POST['question'] ?? '';
$option1 = $_POST['option1'] ?? '';
$option2 = $_POST['option2'] ?? '';
$option3 = $_POST['option3'] ?? '';
$option4 = $_POST['option4'] ?? '';

if ($question && $option1 && $option2) {
  $stmt = $conn->prepare("INSERT INTO polls (question, option1, option2, option3, option4) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $question, $option1, $option2, $option3, $option4);
  if ($stmt->execute()) {
    header("Location: dashboard.php");
    exit;
  } else {
    echo "Error: " . $conn->error;
  }
} else {
  echo "Please enter at least 2 options and a question.";
}
?>
