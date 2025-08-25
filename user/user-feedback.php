<?php
session_start();
$userName = $_SESSION['user_name'] ?? 'User';

include 'db.php';

// Get current user ID from session (adjust as needed)
$user_id = $_SESSION['user_id'] ?? 0;

// Handle feedback fetch
$sql = "SELECT * FROM feedback WHERE user_id = $user_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Feedback - SwaadSeva</title>
    <link rel="stylesheet" href="css/user-style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="js/user.js"></script>
    <style>
        /* Page container */
        .user-main {
            padding: 40px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Heading + Button row */
        .feedback-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .feedback-header-row h2 {
            margin: 0;
            font-size: 26px;
            color: #222;
        }

        /* Button styled to stick to the right */
        .add-feedback-toggle {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 24px;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .add-feedback-toggle:hover {
            background-color: #0056b3;
        }

        /* Feedback form box */
        .feedback-form-container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            display: none;
        }

        /* Previous feedbacks block */
        .previous-feedbacks {
            background: #fff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.04);
        }

        /* Each feedback card */
        .feedback-card {
            background-color: #fdfdfd;
            border-left: 4px solid #00b894;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .feedback-card .rating-stars {
            color: #f1c40f;
            margin-bottom: 8px;
        }

        .feedback-card .admin-reply {
            background-color: #eef9f1;
            padding: 10px 15px;
            border-left: 4px solid #2ecc71;
            margin-top: 10px;
            border-radius: 6px;
            color: #2d6a4f;
        }

        .feedback-card .timestamp {
            text-align: right;
            font-size: 0.85rem;
            color: #999;
            margin-top: 12px;
        }

        @media (max-width: 768px) {
            .feedback-header-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .add-feedback-toggle {
                align-self: flex-end;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <img src="assets/logo/logo5.png" alt="Logo" />
        <div class="brand">SwaadSeva</div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="menu.php"><i class="fas fa-utensils"></i> Menu</a></li>
            <li><a href="plans.php"><i class="fas fa-clipboard-list"></i> Plans</a></li>
            <li><a href="user-orders.php"><i class="fas fa-box"></i> My Orders</a></li>
            <li><a href="event-orders.php"><i class="fas fa-calendar-alt"></i> Event Orders</a></li>
            <li><a href="user-feedback.php" class="active"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
        </ul>
    </aside>

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <header class="topbar">
        <button class="menu-btn" id="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>

        <div class="topbar-right">
            <span class="admin-name">Hello, <?= htmlspecialchars($userName) ?></span>
            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="user-main">

        <div class="feedback-header-row">
            <h2>Your Feedback</h2>
            <button class="add-feedback-toggle" onclick="toggleForm()">+ Add Feedback</button>
        </div>


        <!-- Feedback Form -->
        <div class="feedback-form-container" id="feedbackFormContainer" style="display: none;">
            <form action="submit_feedback.php" method="POST" id="feedbackForm">
                <div class="form-group">
                    <label for="rating">Rating</label>
                    <select id="rating" name="rating" required>
                        <option value="">-- Select Rating --</option>
                        <option value="5">★★★★★ - Excellent</option>
                        <option value="4">★★★★☆ - Very Good</option>
                        <option value="3">★★★☆☆ - Good</option>
                        <option value="2">★★☆☆☆ - Fair</option>
                        <option value="1">★☆☆☆☆ - Poor</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" placeholder="Write your feedback..." rows="4"
                        required></textarea>
                </div>

                <button type="submit" class="submit-feedback-btn">Submit Feedback</button>
            </form>
        </div>

        <!-- Previous Feedbacks -->
        <div class="previous-feedbacks">
            <h3>Your Previous Feedback</h3>

            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='feedback-card'>";
                    echo "<div class='rating-stars'>" . str_repeat("<i class='fas fa-star'></i>", $row['rating']) . "</div>";
                    echo "<p>{$row['message']}</p>";

                    if (!empty($row['admin_reply'])) {
                        echo "<div class='admin-reply'><strong>SwaadSeva Reply:</strong> {$row['admin_reply']}</div>";
                    }

                    $formattedDate = date('d M Y, h:i A', strtotime($row['created_at']));
                    echo "<div class='timestamp'>Submitted on {$formattedDate}</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>You haven't submitted any feedback yet.</p>";
            }
            ?>
        </div>
    </main>

    <script>
        function toggleForm() {
            const form = document.getElementById('feedbackFormContainer');
            form.style.display = (form.style.display === 'none') ? 'block' : 'none';
        }
    </script>

</body>

</html>