<?php
require_once "../db_connect.php"; // Update path if needed

session_start();
$userName = $_SESSION['user_name'] ?? 'User';

// Define days
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

// Check if filter is applied
$selectedDay = isset($_GET['day']) ? $_GET['day'] : 'All';

$sql = "SELECT day_of_week, meal_type, items FROM menu ";
if ($selectedDay !== 'All') {
    $sql .= "WHERE day_of_week = ? ";
}
$sql .= "ORDER BY FIELD(day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), 
                FIELD(meal_type, 'Breakfast','Lunch','Dinner')";

$stmt = $conn->prepare($sql);
if ($selectedDay !== 'All') {
    $stmt->bind_param("s", $selectedDay);
}
$stmt->execute();
$result = $stmt->get_result();

$menu = [];
while ($row = $result->fetch_assoc()) {
    $menu[$row['day_of_week']][$row['meal_type']] = $row['items'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Menu | SwaadSeva</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/user-style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="js/user.js"></script>
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <img src="assets/logo/logo5.png" alt="Logo" />
        <div class="brand">SwaadSeva</div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="menu.php" class="active"><i class="fas fa-utensils"></i> Menu</a></li>
            <li><a href="plans.php"><i class="fas fa-clipboard-list"></i> Plans</a></li>
            <li><a href="user-orders.php"><i class="fas fa-box"></i> My Orders</a></li>
            <li><a href="event-orders.php"><i class="fas fa-calendar-alt"></i> Event Orders</a></li>
            <li><a href="user-feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a></li>
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
        <div class="page-header">
            <h1>This Week's Menu</h1>
            <p>Check what you'll be enjoying this week!</p>
        </div>

        <div class="menu-filter">
            <form method="GET">
                <label for="day">Select Day: </label>
                <select name="day" id="day" onchange="this.form.submit()">
                    <option value="All" <?= $selectedDay === 'All' ? 'selected' : '' ?>>All Days</option>
                    <?php foreach ($days as $day): ?>
                        <option value="<?= $day ?>" <?= $selectedDay === $day ? 'selected' : '' ?>><?= $day ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="responsive-table-container">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Breakfast</th>
                        <th>Lunch</th>
                        <th>Dinner</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $daysToDisplay = $selectedDay === 'All' ? $days : [$selectedDay];
                    foreach ($daysToDisplay as $day) {
                        $breakfast = isset($menu[$day]['Breakfast']) ? $menu[$day]['Breakfast'] : '-';
                        $lunch = isset($menu[$day]['Lunch']) ? $menu[$day]['Lunch'] : '-';
                        $dinner = isset($menu[$day]['Dinner']) ? $menu[$day]['Dinner'] : '-';
                        echo "<tr>
                        <td>$day</td>
                        <td>$breakfast</td>
                        <td>$lunch</td>
                        <td>$dinner</td>
                      </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </main>

</body>

</html>