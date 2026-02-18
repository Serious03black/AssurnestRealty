<?php
session_start();
include '../includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? '';
    if ($role === 'admin') {
        header('Location: ./admin/admin_dashboard.php');
    } elseif ($role === 'employee' || $role === 'driver') {
        header('Location: ./user/user_dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? ''); // email or username
    $password   = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = "Please enter your email/username and password.";
    } else {
        try {
            $user = null;

            // 1. Check admins (using 'user' field)
            $stmt = $pdo->prepare("
                SELECT admin_id AS id, admin_name AS name, user AS identifier, password, 'admin' AS role, 'approved' AS status
                FROM admins 
                WHERE user = ?
            ");
            $stmt->execute([$identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. If not admin → check employees
            if (!$user) {
                $stmt = $pdo->prepare("
                    SELECT emp_id AS id, emp_name AS name, email AS identifier, password, 'employee' AS role, status
                    FROM employees 
                    WHERE email = ?
                ");
                $stmt->execute([$identifier]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // 3. If still not found → check cab drivers
            if (!$user) {
                $stmt = $pdo->prepare("
                    SELECT driver_id AS id, driver_name AS name, email AS identifier, password, 'driver' AS role, status
                    FROM cab_drivers 
                    WHERE email = ?
                ");
                $stmt->execute([$identifier]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Verify credentials and status
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'approved') {
                    $error = "Your account is not yet approved. Please wait for admin approval.";
                } else {
                    // Login success
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['name']      = $user['name'];
                    $_SESSION['role']      = $user['role'];
                    $_SESSION['identifier']= $user['identifier'];

                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header('Location: ./admin/admin_dashboard.php');
                    } elseif ($user['role'] === 'employee' || $user['role'] === 'driver') {
                        header('Location: ./user/user_dashboard.php');
                    }
                    exit;
                }
            } else {
                $error = "Invalid credentials.";
            }
        } catch (Exception $e) {
            $error = "System error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Assurnest Realty</title>
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
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(42,91,215,0.15);
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #777;
            cursor: pointer;
            font-size: 18px;
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

        .error-box {
            background: rgba(220,53,69,0.1);
            color: #dc3545;
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 15px;
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            color: #777;
            font-size: 14px;
            position: relative;
        }

        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 42%;
            height: 1px;
            background: #ddd;
        }

        .divider::before { left: 0; }
        .divider::after { right: 0; }

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
                <div class="logo-tagline">PREMIUM PROPERTIES</div>
            </div>
        </div>

        <h1 class="welcome-title">Welcome Back</h1>
        <p class="welcome-text">
            Sign in to manage properties, track sales, commissions, and grow your real estate business.
        </p>

        <ul class="features">
            <li><i class="fas fa-check-circle"></i> Manage premium listings</li>
            <li><i class="fas fa-check-circle"></i> Track sales & earnings</li>
            <li><i class="fas fa-check-circle"></i> Client & team management</li>
            <li><i class="fas fa-check-circle"></i> Real-time performance stats</li>
        </ul>
    </div>

    <!-- Right - Login Form -->
    <div class="right-panel">
        <div class="login-header">
            <h2 class="form-title">Sign In</h2>
            <p class="form-subtitle">Access your Assurnest Realty dashboard</p>
        </div>

        <?php if ($error): ?>
            <div class="error-box"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Email / Username</label>
                <input type="text" class="form-control" name="identifier" required autofocus placeholder="Enter email or username">
            </div>

            <div class="form-group" style="position:relative;">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required placeholder="Enter your password">
                <button type="button" class="password-toggle" id="togglePass"><i class="fas fa-eye"></i></button>
            </div>

            <button type="submit" class="btn-login">Login</button>

            <div class="divider">OR</div>

            <div class="signup-link">
                Don't have an account? <a href="signup.php">Sign up here</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('togglePass')?.addEventListener('click', function() {
        const input = this.previousElementSibling;
        const type = input.type === 'password' ? 'text' : 'password';
        input.type = type;
        this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });
</script>

</body>
</html>