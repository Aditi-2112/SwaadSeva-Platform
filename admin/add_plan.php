<?php
include 'db.php';


session_start();
$_SESSION['message'] = "Plan added successfully ✅";
header("Location: plans.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $meal_type = $_POST['meal_type'];
    $duration_days = intval($_POST['duration_days']);
    $base_price = floatval($_POST['base_price']);
    $delivery_charge = floatval($_POST['delivery_charge']);
    $includes_delivery = $_POST['includes_delivery'];

    $total_price = $base_price + $delivery_charge;

    $stmt = $conn->prepare("INSERT INTO plans 
        (name, description, meal_type, duration_days, base_price, delivery_charge, includes_delivery, total_price)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssiddsd",
        $name,
        $description,
        $meal_type,
        $duration_days,
        $base_price,
        $delivery_charge,
        $includes_delivery,
        $total_price
    );

    if ($stmt->execute()) {
        header("Location: plans.php");
    } else {
        echo "Error adding new plan.";
    }

    $stmt->close();
    $conn->close();
}
?>