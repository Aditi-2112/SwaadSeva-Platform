<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$planId = isset($_GET['plan_id']) ? intval($_GET['plan_id']) : 0;

// Fetch latest pending subscription for this user and plan
$stmt = $conn->prepare("SELECT up.*, p.name, p.total_price FROM user_plans up JOIN plans p ON up.plan_id = p.id WHERE up.user_id = ? AND up.plan_id = ? AND up.payment_status = 'Pending' ORDER BY up.id DESC LIMIT 1");
$stmt->bind_param("ii", $userId, $planId);
$stmt->execute();
$result = $stmt->get_result();
$subscription = $result->fetch_assoc();
$stmt->close();

if (!$subscription) {
    $_SESSION['error'] = "No pending subscription found.";
    header("Location: plans.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amountPaid = floatval($_POST['amount_paid']);
    $total = floatval($subscription['total_price']);
    $newPaid = $subscription['amount_paid'] + $amountPaid;
    $status = $newPaid >= $total ? 'Done' : 'Pending';

    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Update user_plans
        $update = $conn->prepare("UPDATE user_plans SET amount_paid = ?, payment_status = ? WHERE id = ?");
        $update->bind_param("dsi", $newPaid, $status, $subscription['id']);
        $update->execute();
        $update->close();

        // 2. Insert into transactions
        $txn = $conn->prepare("INSERT INTO transactions (user_plan_id, user_id, amount, paid_at, payment_method, notes)
                               VALUES (?, ?, ?, NOW(), ?, ?)");
        $paymentMethod = 'Online'; // Since user is doing self-payment
        $note = 'Self-payment by user';
        $txn->bind_param("iisss", $subscription['id'], $userId, $amountPaid, $paymentMethod, $note);
        $txn->execute();
        $txn->close();

        $conn->commit();

        $_SESSION['success'] = "Payment updated successfully.";
        header("Location: plans.php");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Payment failed. Try again.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pay Now | SwaadSeva</title>
    <link rel="stylesheet" href="css/user-style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 40px;
        }

        .payment-box {
            max-width: 500px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .payment-box h2 {
            margin-top: 0;
            color: #4CAF50;
        }

        label {
            font-weight: bold;
            display: block;
            margin: 15px 0 5px;
        }

        input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .error {
            color: red;
        }
    </style>
</head>

<body>
    <div class="payment-box">
        <h2>Pay for <?= htmlspecialchars($subscription['name']) ?></h2>
        <p><strong>Total:</strong> ₹<?= number_format($subscription['total_price'], 2) ?></p>
        <p><strong>Paid:</strong> ₹<?= number_format($subscription['amount_paid'], 2) ?></p>
        <p><strong>Remaining:</strong>
            ₹<?= number_format($subscription['total_price'] - $subscription['amount_paid'], 2) ?></p>

        <form method="POST">
            <label for="amount_paid">Enter Amount to Pay:</label>
            <input type="number" name="amount_paid" id="amount_paid" step="0.01" min="1"
                max="<?= $subscription['total_price'] - $subscription['amount_paid'] ?>" required>
            <button type="submit">Make Payment</button>
        </form>
        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
    </div>
</body>

</html>