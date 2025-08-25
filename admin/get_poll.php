<?php
require_once 'db.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No ID']);
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM polls WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $poll = $res->fetch_assoc();
    echo json_encode(['success' => true, 'poll' => $poll]);
} else {
    echo json_encode(['success' => false, 'message' => 'Poll not found']);
}
