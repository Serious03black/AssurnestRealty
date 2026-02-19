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
    $ref_code_input = trim($_POST['referral_code'] ?? '');

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
                    INSERT INTO employees (emp_name, email, mobile_no, password, enrollment_date, status, commission, referral_bonus)
                    VALUES (?, ?, ?, ?, ?, 'pending', 0.00, 0)
                ");
                $stmt->execute([$name, $email, $mobile, $hash, $date]);
            } else {
                // Generare Referral Code 
                // Format: First 3 letters of name + Random 4 digits (e.g., JOH1234)
                $prefix_name = strtoupper(substr(str_replace(' ', '', $name), 0, 3));
                $unique_code = $prefix_name . rand(1000, 9999);
                
                $referrer_id = null;
                if (!empty($ref_code_input)) {
                    $ref_stmt = $pdo->prepare("SELECT driver_id FROM cab_drivers WHERE referral_code = ?");
                    $ref_stmt->execute([$ref_code_input]);
                    $referrer = $ref_stmt->fetch(PDO::FETCH_ASSOC);
                    if ($referrer) {
                        $referrer_id = $referrer['driver_id'];
                    }
                }

                $stmt = $pdo->prepare("
                    INSERT INTO cab_drivers (driver_name, email, mobile_no, password, enrollment_date, status, referral_code, referral_id, commission, referral_bonus, total_properties_sold)
                    VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, 0.00, 0.00, 0)
                ");
                $stmt->execute([$name, $email, $mobile, $hash, $date, $unique_code, $referrer_id]);
            }

            $message = "Registration successful! Your account is pending admin approval.";
            $type = 'success';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                 $message = "Email, mobile, or referral code already registered.";
            } else {
                 $message = "Registration failed: " . $e->getMessage();
            }
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
        :root {
            --primary: #2a5bd7;
            --primary-dark: #1e4bb9;
            --secondary: #ff7e5f;
            --gradient: linear-gradient(135deg, #1a2980 0%, #26d0ce 100%);
            --dark-bg: #0f1217;
            --card-bg: #161b22;
            --text: #e2e8f0;
            --muted: #94a3b8;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }

        body {
            background: var(--gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #fff;
        }

        .login-container {
            width: 100%;
            max-width: 1100px;
            display: flex;
            min-height: 650px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }

        .left-panel {
            flex: 1;
            background: var(--dark-bg);
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 40px;
        }

        .logo i { font-size: 42px; color: var(--secondary); }

        .logo-text { font-size: 32px; font-weight: 700; }
        .logo-tagline { font-size: 14px; color: var(--muted); letter-spacing: 2px; text-transform: uppercase; margin-top: 5px; }

        .welcome-title {
            font-size: 38px;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .welcome-text {
            font-size: 17px;
            opacity: 0.9;
            margin-bottom: 40px;
        }

        .features li {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            font-size: 16px;
        }

        .features i { color: var(--secondary); }

        .right-panel {
            flex: 1;
            background: white;
            color: #333;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-title {
            font-size: 32px;
            color: var(--primary);
            margin-bottom: 10px;
            font-weight: 700;
            text-align: center;
        }

        .form-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 35px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(42,91,215,0.15);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(42,91,215,0.35);
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        
        .message.success { background: rgba(40,167,69,0.1); color: #28a745; border: 1px solid rgba(40,167,69,0.2); }
        .message.error   { background: rgba(220,53,69,0.1); color: #dc3545; border: 1px solid rgba(220,53,69,0.2); }

        .signup-link {
            text-align: center;
            margin-top: 25px;
            font-size: 15px;
        }

        .signup-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        @media (max-width: 992px) {
            .login-container { flex-direction: column; max-width: 480px; min-height: auto; }
            .left-panel, .right-panel { padding: 50px 30px; }
            .welcome-title { font-size: 30px; }
        }
    </style>
</head>
<body>

<div class="login-container">
    <!-- Left - Branding -->
    <div class="left-panel">
        <div class="logo">
            <i class="fas fa-building"></i>
            <div>
                <div class="logo-text">Assurnest Realty</div>
                <div class="logo-tagline">PARTNER PROGRAM</div>
            </div>
        </div>

        <h1 class="welcome-title">Join Our Network</h1>
        <p class="welcome-text">
            Become a part of the fastest growing real estate network. Join as an Employee or a Cab Partner to start earning.
        </p>

        <ul class="features">
            <li><i class="fas fa-check-circle"></i> High commission rates</li>
            <li><i class="fas fa-check-circle"></i> <strong>Cab Drivers:</strong> Earn extra with zero investment</li>
            <li><i class="fas fa-check-circle"></i> Real-time earnings tracking</li>
            <li><i class="fas fa-check-circle"></i> Weekly payouts & rewards</li>
        </ul>
    </div>

    <!-- Right - Signup Form -->
    <div class="right-panel">
        <h2 class="form-title">Create Account</h2>
        <p class="form-subtitle">Register as Real Estate Agent or Cab Partner</p>

        <?php if ($message): ?>
            <div class="message <?= $type ?>">
                <i class="fas fa-<?= $type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Register As</label>
                <select name="role" id="roleSelect" class="form-control" required>
                    <option value="">Select role</option>
                    <option value="employee">Real Estate Employee / Agent</option>
                    <option value="driver">Cab Driver</option>
                </select>
            </div>

            <div class="form-group" id="referralField" style="display:none;">
                <label class="form-label">Referral Code (Optional)</label>
                <input type="text" name="referral_code" class="form-control" placeholder="Enter referrer's code if any">
            </div>

            <script>
                document.getElementById('roleSelect').addEventListener('change', function() {
                    const refField = document.getElementById('referralField');
                    if (this.value === 'driver') {
                        refField.style.display = 'block';
                    } else {
                        refField.style.display = 'none';
                        document.querySelector('[name="referral_code"]').value = '';
                    }
                });
            </script>

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
                <input type="tel" name="mobile" class="form-control" required pattern="[6-9][0-9]{9}" placeholder="10-digit number" title="Please enter a valid 10-digit mobile number">
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

            <div class="signup-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>