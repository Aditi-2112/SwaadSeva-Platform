<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    echo "Invalid request.";
    exit;
}

$orderId = intval($_GET['order_id']);
$userId = $_SESSION['user_id'];

// Fetch order
$stmt = $conn->prepare("SELECT * FROM special_orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "Order not found.";
    exit;
}

// Fetch total paid so far
$txnQuery = $conn->prepare("SELECT SUM(amount) AS total_paid FROM special_order_transactions WHERE special_order_id = ?");
$txnQuery->bind_param("i", $orderId);
$txnQuery->execute();
$txnResult = $txnQuery->get_result()->fetch_assoc();
$txnQuery->close();

$paid = floatval($txnResult['total_paid'] ?? 0);
$total = floatval($order['order_price']);
$pending = $total - $paid;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $method = trim($_POST['payment_method']);
    $notes = trim($_POST['notes']);

    if ($amount <= 0 || $amount > $pending) {
        $error = "Invalid amount.";
    } else {
        // 1. Insert into transaction table
        $insert = $conn->prepare("INSERT INTO special_order_transactions (special_order_id, amount, txn_date, payment_method, notes) VALUES (?, ?, NOW(), ?, ?)");
        $insert->bind_param("idss", $orderId, $amount, $method, $notes);

        if ($insert->execute()) {
            // 2. Calculate new paid amount
            $newPaidAmount = $paid + $amount;
            $newStatus = ($newPaidAmount >= $total) ? 'Done' : 'Pending';

            // 3. Update special_orders table
            $update = $conn->prepare("UPDATE special_orders SET paid_amount = ?, payment_status = ? WHERE id = ?");
            $update->bind_param("dsi", $newPaidAmount, $newStatus, $orderId);
            $update->execute();
            $update->close();

            // 4. Redirect
            header("Location: payments.php");
            exit;
        } else {
            $error = "Failed to record payment.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Special Order - SwaadSeva</title>
    <link rel="stylesheet" href="css/user-style.css">
</head>

<body>
    <div class="container">
        <h2>Pay for Special Order #<?= $orderId ?></h2>
        <p><strong>Total Price:</strong> ₹<?= number_format($total, 2) ?></p>
        <p><strong>Amount Paid:</strong> ₹<?= number_format($paid, 2) ?></p>
        <p><strong>Pending:</strong> ₹<?= number_format($pending, 2) ?></p>

        <?php if (isset($error)): ?>
            <p style="color: red; font-weight: bold;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Amount (Max ₹<?= number_format($pending, 2) ?>):</label><br>
            <input type="number" name="amount" step="0.01" max="<?= $pending ?>" required><br><br>

            <label>Payment Method:</label><br>
            <select name="payment_method" required>
                <option value="">Select Method</option>
                <option value="Cash">Cash</option>
                <option value="UPI">UPI</option>
                <option value="Card">Card</option>
                <option value="Online">Online</option>
            </select><br><br>

            <label>Notes (optional):</label><br>
            <textarea name="notes" rows="3"></textarea><br><br>

            <button type="submit">Submit Payment</button>
        </form>
    </div>
</body>

</html>