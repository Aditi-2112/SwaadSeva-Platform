<?php
include '../db_connect.php';

$id = $_POST['id'] ?? null;
$fields = ['name', 'email', 'phone', 'address', 'plan', 'meal_pref', 'delivery_time', 'start_date', 'end_date', 'payment_status'];

if (!$id) {
    http_response_code(400);
    echo "Invalid User ID.";
    exit;
}

$updates = [];
$params = [];
$types = "";

foreach ($fields as $field) {
    if (isset($_POST[$field])) {
        $updates[] = "$field = ?";
        $params[] = $_POST[$field];
        $types .= "s";
    }
}

if (count($updates) === 0) {
    echo "No fields to update.";
    exit;
}

$params[] = $id;
$types .= "i";

$sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo "User updated successfully.";
} else {
    http_response_code(500);
    echo "Error updating user.";
}
?>