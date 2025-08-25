<?php
session_start();
require_once 'db.php';

// Ensure only admin access
// if (!isset($_SESSION['admin_logged_in'])) {
//     header("Location: ../index.php");
//     exit;
// }

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate required fields
    $required_fields = ['id', 'name', 'contact', 'address', 'quantity', 'menu', 'order_date', 'order_time', 'order_status', 'order_price'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            $_SESSION['error_msg'] = "Missing required field: $field";
            header("Location: admin-event-orders.php");
            exit;
        }
    }

    // Sanitize and assign values
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $quantity = intval($_POST['quantity']);
    $menu = trim($_POST['menu']);
    $order_date = $_POST['order_date'];
    $order_time = $_POST['order_time'];
    $order_status = $_POST['order_status'];
    $order_price = is_numeric($_POST['order_price']) ? floatval($_POST['order_price']) : 0.0;

    // Prepare and execute update query
    $stmt = $conn->prepare("
        UPDATE special_orders 
        SET name = ?, contact = ?, address = ?, quantity = ?, menu = ?, order_date = ?, order_time = ?, order_status = ?, order_price = ?
        WHERE id = ?
    ");

    if ($stmt === false) {
        $_SESSION['error_msg'] = "Prepare failed: " . $conn->error;
        header("Location: admin-event-orders.php");
        exit;
    }

    $stmt->bind_param("sssissssdi", $name, $contact, $address, $quantity, $menu, $order_date, $order_time, $order_status, $order_price, $id);

    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Order updated successfully.";
    } else {
        $_SESSION['error_msg'] = "Failed to update order. Error: " . $stmt->error;
    }

    $stmt->close();
    header("Location: admin-event-orders.php");
    exit;
} else {
    // Redirect for invalid access
    header("Location: admin-event-orders.php");
    exit;
}
