<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['plan_id'])) {
    $_SESSION['error'] = "No plan selected.";
    header("Location: plans.php");
    exit;
}

$planId = intval($_GET['plan_id']);
$userId = $_SESSION['user_id'];

// Fetch plan details
$stmt = $conn->prepare("SELECT * FROM plans WHERE id = ?");
$stmt->bind_param("i", $planId);
$stmt->execute();
$result = $stmt->get_result();
$plan = $result->fetch_assoc();
$stmt->close();

if (!$plan) {
    $_SESSION['error'] = "Plan not found.";
    header("Location: plans.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_date'])) {
    $startDate = $_POST['start_date'];
    $endDate = date('Y-m-d', strtotime($startDate . ' + ' . $plan['duration_days'] . ' days'));

    $insert = $conn->prepare("INSERT INTO user_plans (user_id, plan_id, start_date, end_date, payment_status, amount_paid) VALUES (?, ?, ?, ?, 'Pending', 0.00)");
    $insert->bind_param("iiss", $userId, $planId, $startDate, $endDate);

    if ($insert->execute()) {
        $subscriptionId = $conn->insert_id;

        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $interval = new DateInterval('P1D');
        $mealTypes = explode(',', $plan['meal_type']);

        for ($date = clone $start; $date <= $end; $date->add($interval)) {
            foreach ($mealTypes as $meal) {
                $meal = trim($meal);
                $stmt = $conn->prepare("INSERT INTO delivery (user_id, plan_id, delivery_date, meal_type, status) VALUES (?, ?, ?, ?, 'Pending')");
                $stmt->bind_param("iiss", $userId, $planId, $deliveryDate, $meal);
                $deliveryDate = $date->format('Y-m-d');
                $stmt->execute();
                $stmt->close();
            }
        }

        $_SESSION['success'] = "Subscribed to plan successfully.";
        header("Location: pay-now.php?plan_id=$planId");
        exit;
    } else {
        $error = "Failed to subscribe. Please try again.";
    }
    $insert->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Subscribe Plan | SwaadSeva</title>
    <link rel="stylesheet" href="css/user-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        .subscribe-container {
            max-width: 600px;
            margin: 60px auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .subscribe-container h1 {
            margin-top: 0;
            color: #4CAF50;
        }

        .subscribe-container p {
            color: #555;
        }

        .plan-details {
            margin-top: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin: 15px 0 5px;
        }

        input[type="date"] {
            padding: 8px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .subscribe-btn {
            margin-top: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .subscribe-btn:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        .date-info {
            margin-top: 10px;
            font-style: italic;
            color: #333;
        }

        .mascot-container {
            display: flex;
            align-items: center;
            margin-top: 30px;
        }

        .mascot-container img {
            width: 100px;
            margin-right: 20px;
        }

        .mascot-text {
            font-style: italic;
            font-size: 1rem;
            color: #4CAF50;
        }
    </style>
    <script>
        function updateEndDate() {
            const startInput = document.getElementById('start_date');
            const endSpan = document.getElementById('end_date');
            const duration = <?= (int) $plan['duration_days'] ?>;
            if (startInput.value) {
                const start = new Date(startInput.value);
                start.setDate(start.getDate() + duration);
                const endDate = start.toISOString().split('T')[0];
                endSpan.textContent = endDate;
            } else {
                endSpan.textContent = '-';
            }
        }
    </script>
</head>

<body>
    <div class="subscribe-container">
        <div class="mascot-container">
            <img src="assets/images/mascot6.png" alt="SwaadSeva Mascot">
            <!-- <div class="mascot-text">Bohot accha khana khilaungi! ❤️<br> Ab har din ghar jaisa swaad!</div> -->
        </div>
        <h1>Subscribe to <i class="fas fa-utensils"></i> <?= htmlspecialchars($plan['name']) ?></h1>
        <p><?= htmlspecialchars($plan['description']) ?></p>

        <div class="plan-details">
            <p><strong>Meal Type:</strong> <?= htmlspecialchars($plan['meal_type']) ?></p>
            <p><strong>Duration:</strong> <?= $plan['duration_days'] ?> days</p>
            <p><strong>Total Price:</strong> ₹<?= number_format($plan['total_price'], 2) ?></p>
        </div>

        <form method="POST">
            <label for="start_date">Choose Start Date:</label>
            <input type="date" name="start_date" id="start_date" min="<?= date('Y-m-d') ?>" required
                onchange="updateEndDate()">
            <p class="date-info">End Date: <span id="end_date">-</span></p>

            <button type="submit" class="subscribe-btn">Subscribe & Proceed to Payment</button>
        </form>

        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

    </div>
</body>

</html>