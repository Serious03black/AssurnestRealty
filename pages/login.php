<?php
session_start();
include '../includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ./admin/admin_dashboard.php');
    } else {
        header('Location: ./user/user_dashboard.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND approved = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] === 'admin') {
            header('Location: ./admin/admin_dashboard.php');
        } else {
            header('Location: ./user/user_dashboard.php');
        }
        exit;
    } else {
        $error = "Invalid credentials or account not approved.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Assurnest Realty</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #2a5bd7;
            --primary-dark: #1e4bb9;
            --secondary: #ff7e5f;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --gray: #6c757d;
            --light-gray: #e9ecef;
        }

        body {
            background: linear-gradient(135deg, #1a2980 0%, #26d0ce 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--dark);
        }

        .login-container {
            width: 100%;
            max-width: 1200px;
            display: flex;
            min-height: 700px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none" opacity="0.05"><path d="M0,0 L100,0 L100,100 Z" fill="white"/></svg>');
            background-size: cover;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 40px;
            z-index: 1;
        }

        .logo i {
            font-size: 40px;
            color: var(--secondary);
        }

        .logo-text {
            font-size: 28px;
            font-weight: 700;
        }

        .logo-tagline {
            font-size: 14px;
            opacity: 0.8;
            margin-top: 5px;
            letter-spacing: 2px;
        }

        .welcome-title {
            font-size: 36px;
            margin-bottom: 20px;
            line-height: 1.2;
            z-index: 1;
        }

        .welcome-text {
            font-size: 16px;
            line-height: 1.6;
            opacity: 0.9;
            margin-bottom: 40px;
            z-index: 1;
        }

        .features {
            list-style: none;
            margin-top: 40px;
            z-index: 1;
        }

        .features li {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .features i {
            color: var(--secondary);
            font-size: 18px;
        }

        .login-right {
            flex: 1;
            background: white;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-title {
            font-size: 32px;
            color: var(--primary);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-subtitle {
            color: var(--gray);
            font-size: 16px;
        }

        .login-form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 15px;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 1px solid var(--light-gray);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            background-color: #fdfdfd;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(42, 91, 215, 0.1);
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 16px;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--gray);
        }

        .remember-me input {
            width: 16px;
            height: 16px;
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(42, 91, 215, 0.3);
        }

        .btn-secondary {
            background: var(--light);
            color: var(--gray);
            border: 1px solid var(--light-gray);
            margin-top: 15px;
        }

        .btn-secondary:hover {
            background: var(--light-gray);
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
            color: var(--gray);
            font-size: 14px;
        }

        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background: var(--light-gray);
        }

        .divider::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background: var(--light-gray);
        }

        .error-message {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger);
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message i {
            font-size: 18px;
        }

        .success-message {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .signup-link {
            text-align: center;
            margin-top: 30px;
            color: var(--gray);
            font-size: 15px;
        }

        .signup-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .property-badge {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin: 5px;
        }

        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
                max-width: 500px;
            }
            
            .login-left, .login-right {
                padding: 40px 30px;
            }
            
            .welcome-title {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .login-left, .login-right {
                padding: 30px 20px;
            }
            
            .logo-text {
                font-size: 24px;
            }
            
            .welcome-title {
                font-size: 24px;
            }
            
            .login-title {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Branding & Info -->
        <div class="login-left">
            <div class="logo">
                <i class="fas fa-building"></i>
                <div>
                    <div class="logo-text">Assurnest Realty</div>
                    <div class="logo-tagline">PREMIUM PROPERTIES</div>
                </div>
            </div>
            
            <h1 class="welcome-title">Welcome Back to Assurnest Realty</h1>
            <p class="welcome-text">
                Sign in to access your dashboard and manage properties, sales, and client relationships. 
                Join thousands of real estate professionals who trust Assurnest Realty for premium property management.
            </p>
            
            <ul class="features">
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Manage premium property listings</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Track sales and commissions</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Access client management tools</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Real-time market analytics</span>
                </li>
            </ul>
            
            <div style="margin-top: 40px; z-index: 1;">
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <span class="property-badge"><i class="fas fa-home"></i> 500+ Properties</span>
                    <span class="property-badge"><i class="fas fa-users"></i> 250+ Agents</span>
                    <span class="property-badge"><i class="fas fa-handshake"></i> $50M+ Sales</span>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-right">
            <div class="login-header">
                <h2 class="login-title">Sign In</h2>
                <p class="login-subtitle">Enter your credentials to access your account</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span>Registration successful! Please wait for admin approval.</span>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label class="form-label" for="username">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" required 
                           placeholder="Enter your username" autofocus>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-container">
                        <input type="password" class="form-control" id="password" name="password" required 
                               placeholder="Enter your password">
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>

                <div class="divider">
                    OR
                </div>

                <a href="signup.php" class="btn btn-secondary">
                    <i class="fas fa-user-plus"></i> Create New Account
                </a>

                <div class="signup-link">
                    Don't have an account? <a href="signup.php">Sign up here</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });

        // Form validation
        document.querySelector('.login-form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });

        // Auto-focus username field
        document.getElementById('username').focus();
    </script>
</body>
</html>