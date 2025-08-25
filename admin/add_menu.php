<?php
require_once "../db_connect.php";

// Insert new menu row on POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $day = trim($_POST['day_of_week']);
    $meal = trim($_POST['meal_type']);
    $items = trim($_POST['items']);

    if (!empty($day) && !empty($meal) && !empty($items)) {
        $insert_sql = "INSERT INTO menu (day_of_week, meal_type, items) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("sss", $day, $meal, $items);

        if ($stmt->execute()) {
            // Success
            header("Location: menu.php?success=1");
            exit();
        } else {
            echo "Error inserting menu: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Please fill in all fields.";
    }
} else {
    echo "Invalid request.";
}
?>