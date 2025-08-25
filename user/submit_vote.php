<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$poll_id = $_POST['poll_id'] ?? 0;
$option = $_POST['option'] ?? 0;

// Check if already voted
$stmt = $conn->prepare("SELECT id FROM poll_votes WHERE user_id = ? AND poll_id = ?");
$stmt->bind_param("ii", $user_id, $poll_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already voted.']);
    exit;
}

// Save vote
$vote = $conn->prepare("INSERT INTO poll_votes (poll_id, user_id, option_selected) VALUES (?, ?, ?)");
$vote->bind_param("iii", $poll_id, $user_id, $option);

if ($vote->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Vote not recorded.']);
}


