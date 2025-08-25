<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $poll_id = $_POST['poll_id'] ?? null;
    $question = $_POST['question'] ?? '';
    $options = [];
    for ($i = 1; $i <= 4; $i++) {
        $options[$i] = $_POST["option$i"] ?? '';
    }

    if ($action === 'save') {
        if ($poll_id) {
            // Update existing poll
            $stmt = $conn->prepare("UPDATE polls SET question = ?, option1 = ?, option2 = ?, option3 = ?, option4 = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $question, $options[1], $options[2], $options[3], $options[4], $poll_id);
        } else {
            // Insert new poll
            $stmt = $conn->prepare("INSERT INTO polls (question, option1, option2, option3, option4, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssss", $question, $options[1], $options[2], $options[3], $options[4]);
        }
        $stmt->execute();
    } elseif ($action === 'delete' && $poll_id) {
        $stmt = $conn->prepare("DELETE FROM polls WHERE id = ?");
        $stmt->bind_param("i", $poll_id);
        $stmt->execute();

        // Also delete votes
        $stmt2 = $conn->prepare("DELETE FROM poll_votes WHERE poll_id = ?");
        $stmt2->bind_param("i", $poll_id);
        $stmt2->execute();
    }

    header("Location: dashboard.php");
    exit;
}
?>