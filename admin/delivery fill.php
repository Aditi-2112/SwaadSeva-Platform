<?php
require_once 'db.php';

$startDate = date('Y-m-d'); // change to fixed date if needed
$endDate = date('Y-m-d', strtotime('+30 days')); // fill for next 30 days

$sql = "SELECT up.user_id, up.plan_id, up.start_date, up.end_date, p.meal_type
        FROM user_plans up
        JOIN plans p ON up.plan_id = p.id
        WHERE up.end_date >= ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $startDate);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $start = new DateTime(max($row['start_date'], $startDate));
    $end = new DateTime(min($row['end_date'], $endDate));
    $meals = explode(',', str_replace([' + ', '+'], ',', $row['meal_type']));

    while ($start <= $end) {
        $date = $start->format('Y-m-d');
        foreach ($meals as $meal) {
            $meal = trim($meal);
            $insert = $conn->prepare("INSERT IGNORE INTO delivery (user_id, plan_id, meal_type, delivery_date) VALUES (?, ?, ?, ?)");
            $insert->bind_param("iiss", $row['user_id'], $row['plan_id'], $meal, $date);
            $insert->execute();
        }
        $start->modify('+1 day');
    }
}
echo "Delivery data filled.";
?>
