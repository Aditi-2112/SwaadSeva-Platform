<?php
require '../db_connect.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT up.id, up.start_date, up.end_date, p.total_price 
                        FROM user_plans up 
                        JOIN plans p ON up.plan_id = p.id 
                        WHERE up.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode($data);
?>