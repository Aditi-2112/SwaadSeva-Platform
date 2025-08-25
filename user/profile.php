<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';

$success = '';
$error = '';

// Fetch user data
$stmt = $conn->prepare("SELECT name, email, phone, address, profile_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    $photoPath = $user['profile_photo'];

    // Handle profile photo upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/profile_photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = time() . '_' . basename($_FILES['profile_photo']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetPath)) {
            $photoPath = $targetPath;
        } else {
            $error = "Failed to upload profile photo.";
        }
    }

    if (!$error) {
        if (!empty($newPassword)) {
            if ($newPassword === $confirmPassword) {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, address=?, profile_photo=?, password=? WHERE id=?");
                $stmt->bind_param("ssssssi", $name, $email, $phone, $address, $photoPath, $hashed, $userId);
            } else {
                $error = "Passwords do not match.";
            }
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, address=?, profile_photo=? WHERE id=?");
            $stmt->bind_param("sssssi", $name, $email, $phone, $address, $photoPath, $userId);
        }

        if (!$error && $stmt->execute()) {
            $_SESSION['user_name'] = $name;
            header("Location: profile.php?success=1");
            exit;
        } elseif (!$error) {
            $error = "Failed to update profile.";
        }
        $stmt->close();
    }
}

if (isset($_GET['success'])) {
    $success = "Profile updated successfully.";
}

// Re-fetch user
$stmt = $conn->prepare("SELECT name, email, phone, address, profile_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Profile - SwaadSeva</title>
    <link rel="stylesheet" href="css/user-style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/user.js"></script>
    <style>
        .form-group {
            margin-bottom: 15px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
        }

        .profile-card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            max-width: 500px;
            margin: auto;
        }

        .success-msg {
            background-color: #d4edda;
            color: #155724;
            padding: 10px 15px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
            border-radius: 6px;
            font-weight: bold;
        }

        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px 15px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            border-radius: 6px;
            font-weight: bold;
        }

        .profile-photo {
            display: block;
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            margin: 0 auto 15px;
            border: 3px solid #ffa500;
        }

        .edit-btn {
            background-color: #ffc107;
            color: #000;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <img src="assets/logo/logo5.png" alt="Logo" />
        <div class="brand">SwaadSeva</div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="menu.php"><i class="fas fa-utensils"></i> Menu</a></li>
            <li><a href="plans.php"><i class="fas fa-clipboard-list"></i> Plans</a></li>
            <li><a href="user-orders.php"><i class="fas fa-box"></i> My Orders</a></li>
            <li><a href="event-orders.php"><i class="fas fa-calendar-alt"></i> Event Orders</a></li>
            <li><a href="user-feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a></li>
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

    <main class="user-main">
        <div class="page-header">
            <h1>My Profile</h1>
            <p>View and edit your profile information.</p>
        </div>

        <div class="profile-card">
            <?php if ($success): ?>
                <div class="success-msg">✅ <?= $success ?></div>
            <?php elseif ($error): ?>
                <div class="error-msg">❌ <?= $error ?></div>
            <?php endif; ?>

            <form id="profileForm" method="POST" enctype="multipart/form-data">
                <?php if (!empty($user['profile_photo']) && file_exists($user['profile_photo'])): ?>
                    <img src="<?= $user['profile_photo'] ?>" class="profile-photo" alt="Profile Photo">
                <?php else: ?>
                    <img src="assets/default-user.png" class="profile-photo" alt="Default Photo">
                <?php endif; ?>

                <div class="form-group">
                    <label for="profile_photo">Change Profile Photo</label>
                    <input type="file" name="profile_photo" accept="image/*" disabled>
                </div>

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required readonly>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required readonly>
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required readonly>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea name="address" rows="3" readonly><?= htmlspecialchars($user['address']) ?></textarea>
                </div>

                <hr>

                <div class="form-group">
                    <label for="password">New Password (leave blank if unchanged)</label>
                    <input type="password" name="password" readonly>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" name="confirm_password" readonly>
                </div>

                <button type="button" class="edit-btn" onclick="enableEditing()">Edit</button>
                <button type="submit" class="save-btn"
                    onclick="return confirm('Are you sure you want to save changes?')" style="display:none;">Save
                    Changes</button>
            </form>
        </div>
    </main>

    <script>
        function enableEditing() {
            const inputs = document.querySelectorAll("#profileForm input, #profileForm textarea");
            inputs.forEach(input => input.removeAttribute("readonly"));
            inputs.forEach(input => input.removeAttribute("disabled"));

            document.querySelector(".edit-btn").style.display = 'none';
            document.querySelector(".save-btn").style.display = 'inline-block';
        }
    </script>
</body>

</html>