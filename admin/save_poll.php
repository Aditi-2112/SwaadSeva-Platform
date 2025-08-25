<?php
require_once 'db.php';

$question = $_POST['question'] ?? '';
$option1 = $_POST['option1'] ?? '';
$option2 = $_POST['option2'] ?? '';
$option3 = $_POST['option3'] ?? '';
$option4 = $_POST['option4'] ?? '';
$poll_id = $_POST['poll_id'] ?? null;

if ($poll_id) {
  $stmt = $conn->prepare("UPDATE polls SET question=?, option1=?, option2=?, option3=?, option4=? WHERE id=?");
  $stmt->bind_param("sssssi", $question, $option1, $option2, $option3, $option4, $poll_id);
} else {
  $stmt = $conn->prepare("INSERT INTO polls (question, option1, option2, option3, option4, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
  $stmt->bind_param("sssss", $question, $option1, $option2, $option3, $option4);
}

if ($stmt->execute()) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'message' => $conn->error]);
}
