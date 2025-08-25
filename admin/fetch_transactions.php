<?php
require_once 'db.php';

$id = $_GET['id'] ?? 0;
$type = $_GET['type'] ?? 'normal'; // 'normal' or 'special'

if ($type === 'special') {
    // For special orders
    $sql = "SELECT 
                t.amount, 
                t.txn_date, 
                t.payment_method, 
                t.notes,
                u.name AS customer_name
            FROM special_order_transactions t
            LEFT JOIN special_orders so ON t.special_order_id = so.id
            LEFT JOIN users u ON so.user_id = u.id
            WHERE t.special_order_id = ?
            ORDER BY t.txn_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

} else {
    // For normal plans
    $sql = "SELECT 
                t.amount, 
                t.paid_at, 
                t.payment_method, 
                t.notes, 
                u.name AS customer_name
            FROM transactions t
            LEFT JOIN user_plans up ON t.user_plan_id = up.id
            LEFT JOIN users u ON up.user_id = u.id
            WHERE t.user_plan_id = ?
            ORDER BY t.paid_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
}

$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    echo "<ul>";
    while ($row = $res->fetch_assoc()) {
        $amount = "₹" . number_format($row['amount'], 2);
        $date = $type === 'special' ? date('d M Y', strtotime($row['txn_date'])) : date('d M Y h:i A', strtotime($row['paid_at']));
        $payment = htmlspecialchars($row['payment_method']);
        $name = htmlspecialchars($row['customer_name'] ?? '');
        $notes = htmlspecialchars($row['notes'] ?? '');

        echo "<li>$amount on $date via $payment";
        if ($name)
            echo " by <strong>$name</strong>";
        if ($notes)
            echo " - Note: $notes";
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No transactions found for this order.</p>";
}
?>