<?php
include 'db.php';


session_start();
$_SESSION['message'] = "Plan updated successfully ✅";
header("Location: plans.php");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $description = $_POST['description'];
    $meal_type = $_POST['meal_type'];
    $duration_days = intval($_POST['duration_days']);
    $base_price = floatval($_POST['base_price']);
    $delivery_charge = floatval($_POST['delivery_charge']);
    $includes_delivery = $_POST['includes_delivery'];

    $total_price = $base_price + $delivery_charge;

    $stmt = $conn->prepare("UPDATE plans SET 
        name = ?, 
        description = ?, 
        meal_type = ?, 
        duration_days = ?, 
        base_price = ?, 
        delivery_charge = ?, 
        includes_delivery = ?, 
        total_price = ?
        WHERE id = ?");

    $stmt->bind_param(
        "sssiddsdi",
        $name,
        $description,
        $meal_type,
        $duration_days,
        $base_price,
        $delivery_charge,
        $includes_delivery,
        $total_price,
        $id
    );

    if ($stmt->execute()) {
        header("Location: plans.php");
    } else {
        echo "Error updating plan.";
    }

    $stmt->close();
    $conn->close();
}
?>