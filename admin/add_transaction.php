<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_type = $_POST['order_type'] ?? 'normal'; // 'normal' or 'special'
    $id = $_POST['plan_id']; // plan_id or special_order_id
    $amount = $_POST['amount'];
    $txn_date = $_POST['txn_date'];
    $method = $_POST['payment_method'] ?? 'Manual';
    $notes = $_POST['notes'] ?? '';

    if ($order_type === 'special') {
        // Special Orders

        // Get user_id
        $stmt = $conn->prepare("SELECT user_id FROM special_orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();

        if (!$user_id) {
            echo "Special order not found.";
            exit;
        }

        // Insert into special_order_transactions
        $stmt = $conn->prepare("INSERT INTO special_order_transactions (special_order_id, amount, txn_date, payment_method, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idsss", $id, $amount, $txn_date, $method, $notes);

        if ($stmt->execute()) {
            // Recalculate total paid amount
            $stmt = $conn->prepare("SELECT SUM(amount) FROM special_order_transactions WHERE special_order_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($total_paid);
            $stmt->fetch();
            $stmt->close();

            // Get order_price
            $stmt = $conn->prepare("SELECT order_price FROM special_orders WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($order_price);
            $stmt->fetch();
            $stmt->close();

            $status = ($total_paid >= $order_price) ? 'Paid' : 'Pending';

            // Update special_orders
            $stmt = $conn->prepare("UPDATE special_orders SET paid_amount = ?, payment_status = ? WHERE id = ?");
            $stmt->bind_param("dsi", $total_paid, $status, $id);
            $stmt->execute();
            $stmt->close();

            echo "success";
        } else {
            echo "Failed to insert special order transaction.";
        }

    } else {
        // Normal Plans

        // Get user_id and plan_id
        $stmt = $conn->prepare("SELECT user_id, plan_id FROM user_plans WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($user_id, $plan_ref);
        $stmt->fetch();
        $stmt->close();

        if (!$user_id) {
            echo "User not found for this plan.";
            exit;
        }

        // Insert into transactions
        $stmt = $conn->prepare("INSERT INTO transactions (user_plan_id, user_id, amount, paid_at, payment_method, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iidsss", $id, $user_id, $amount, $txn_date, $method, $notes);

        if ($stmt->execute()) {
            // Recalculate total amount_paid
            $stmt = $conn->prepare("SELECT SUM(amount) FROM transactions WHERE user_plan_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($total_paid);
            $stmt->fetch();
            $stmt->close();

            // Get total_price
            $stmt = $conn->prepare("SELECT total_price FROM plans WHERE id = ?");
            $stmt->bind_param("i", $plan_ref);
            $stmt->execute();
            $stmt->bind_result($total_price);
            $stmt->fetch();
            $stmt->close();

            $status = ($total_paid >= $total_price) ? 'Paid' : 'Pending';

            // Update user_plans
            $stmt = $conn->prepare("UPDATE user_plans SET amount_paid = ?, payment_status = ? WHERE id = ?");
            $stmt->bind_param("dsi", $total_paid, $status, $id);
            $stmt->execute();
            $stmt->close();

            echo "success";
        } else {
            echo "Failed to insert transaction.";
        }
    }
}
?>