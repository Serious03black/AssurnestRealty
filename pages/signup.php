<?php
session_start();
include '../includes/db.php';

// Redirect if logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? './admin/admin_dashboard.php' : './user/user_dashboard.php'));
    exit;
}

$message = '';
$type = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role         = $_POST['role'] ?? '';
    $name         = trim($_POST['name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $mobile       = trim($_POST['mobile'] ?? '');
    $password     = $_POST['password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (!in_array($role, ['employee', 'driver'])) {
        $message = "Please select a valid role.";
    } elseif (empty($name) || empty($email) || empty($mobile) || empty($password)) {
        $message = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_pass) {
        $message = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (!preg_match("/^[6-9]\d{9}$/", $mobile)) {
        $message = "Invalid 10-digit mobile number.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $date = date('Y-m-d');

        try {
            if ($role === 'employee') {
                $stmt = $pdo->prepare("
                    INSERT INTO employees (emp_name, email, mobile_no, password, enrollment_date, status)
                    VALUES (?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([$name, $email, $mobile, $hash, $date]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO cab_drivers (driver_name, email, mobile_no, password, enrollment_date, status)
                    VALUES (?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([$name, $email, $mobile, $hash, $date]);
            }

            $message = "Registration successful! Your account is pending admin approval.";
            $type = 'success';
        } catch (PDOException $e) {
            $message = ($e->getCode() == 23000) ? "Email or mobile already registered." : "Registration failed.";
            $type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Assurnest Realty</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Reuse same beautiful CSS as login page */
        /* ... paste your full CSS here ... */
        .message.success { background: rgba(40,167,69,0.15); color: #28a745; }
        .message.error   { background: rgba(220,53,69,0.15); color: #dc3545; }
    </style>
</head>
<body>
<div class="signup-container">
    <!-- Left panel same as login -->
    <div class="left-panel">
        <!-- ... logo, welcome title, features ... -->
    </div>

    <div class="right-panel">
        <h2 class="form-title">Create Account</h2>
        <p style="text-align:center; color:#555; margin-bottom:30px;">Join as Employee or Cab Driver</p>

        <?php if ($message): ?>
            <div class="message <?= $type ?>">
                <i class="fas fa-<?= $type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Register As</label>
                <select name="role" class="form-control" required>
                    <option value="">Select role</option>
                    <option value="employee">Real Estate Employee / Agent</option>
                    <option value="driver">Cab Driver</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="Enter your full name">
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required placeholder="Your email address">
            </div>

            <div class="form-group">
                <label class="form-label">Mobile Number</label>
                <input type="tel" name="mobile" class="form-control" required pattern="[6-9][0-9]{9}" placeholder="10-digit number">
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required minlength="6" placeholder="Minimum 6 characters">
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required placeholder="Re-enter password">
            </div>

            <button type="submit" class="btn-login">Sign Up</button>

            <div class="signup-link" style="margin-top:25px;">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>