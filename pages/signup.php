<?php
session_start();
include '../includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: user_dashboard.php');
    }
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($username) || empty($password)) {
        $message = "Please fill in all fields.";
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $messageType = 'error';
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $messageType = 'error';
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, approved) VALUES (?, ?, 'user', 0)");
            $stmt->execute([$username, $password_hash]);
            $message = "Registration successful! Your account is pending admin approval. You'll be notified once approved.";
            $messageType = 'success';
            
            // Clear form
            $_POST = [];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $message = "Username already taken. Please choose another.";
            } else {
                $message = "Registration failed. Please try again.";
            }
            $messageType = 'error';
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

        .signup-container {
            width: 100%;
            max-width: 1200px;
            display: flex;
            min-height: 700px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
        }

        .signup-left {
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

        .signup-left::before {
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

        .signup-right {
            flex: 1;
            background: white;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .signup-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .signup-title {
            font-size: 32px;
            color: var(--primary);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .signup-subtitle {
            color: var(--gray);
            font-size: 16px;
        }

        .signup-form {
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

        .password-strength {
            margin-top: 8px;
            height: 5px;
            background: var(--light-gray);
            border-radius: 3px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s;
            border-radius: 3px;
        }

        .password-strength-text {
            font-size: 12px;
            margin-top: 5px;
            color: var(--gray);
        }

        .requirements {
            margin-top: 5px;
            font-size: 13px;
            color: var(--gray);
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 3px;
        }

        .requirement i {
            font-size: 12px;
        }

        .requirement.met {
            color: var(--success);
        }

        .requirement.unmet {
            color: var(--gray);
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

        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message.error {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger);
        }

        .message.success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }

        .message i {
            font-size: 18px;
        }

        .login-link {
            text-align: center;
            margin-top: 30px;
            color: var(--gray);
            font-size: 15px;
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
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

        .info-box {
            background: rgba(42, 91, 215, 0.05);
            border-left: 4px solid var(--primary);
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 14px;
            color: var(--dark);
        }

        @media (max-width: 992px) {
            .signup-container {
                flex-direction: column;
                max-width: 500px;
            }
            
            .signup-left, .signup-right {
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
            
            .signup-left, .signup-right {
                padding: 30px 20px;
            }
            
            .logo-text {
                font-size: 24px;
            }
            
            .welcome-title {
                font-size: 24px;
            }
            
            .signup-title {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <!-- Left Side - Branding & Info -->
        <div class="signup-left">
            <div class="logo">
                <i class="fas fa-building"></i>
                <div>
                    <div class="logo-text">Assurnest Realty</div>
                    <div class="logo-tagline">PREMIUM PROPERTIES</div>
                </div>
            </div>
            
            <h1 class="welcome-title">Join Assurnest Realty Today</h1>
            <p class="welcome-text">
                Create your agent account and start managing premium properties, connecting with clients, 
                and growing your real estate business with our powerful platform.
            </p>
            
            <ul class="features">
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Access premium property listings</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Track your sales and commissions</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Manage client relationships</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Get market insights and analytics</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>24/7 support from our team</span>
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

        <!-- Right Side - Signup Form -->
        <div class="signup-right">
            <div class="signup-header">
                <h2 class="signup-title">Create Account</h2>
                <p class="signup-subtitle">Join our community of real estate professionals</p>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <i class="fas fa-info-circle" style="color: var(--primary); margin-right: 8px;"></i>
                <strong>Note:</strong> All new accounts require admin approval. You'll be notified via email once your account is approved.
            </div>

            <form method="POST" class="signup-form" id="signupForm">
                <div class="form-group">
                    <label class="form-label" for="username">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" required 
                           placeholder="Choose a username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           minlength="3" maxlength="50">
                    <div class="requirements">
                        <div class="requirement" id="usernameLength">
                            <i class="fas fa-circle" id="usernameLengthIcon"></i>
                            <span>3-50 characters</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-container">
                        <input type="password" class="form-control" id="password" name="password" required 
                               placeholder="Create a strong password" minlength="6">
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <div class="password-strength-text" id="passwordStrengthText">Password strength: None</div>
                    <div class="requirements">
                        <div class="requirement" id="passwordLength">
                            <i class="fas fa-circle" id="passwordLengthIcon"></i>
                            <span>At least 6 characters</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">
                        <i class="fas fa-lock"></i> Confirm Password
                    </label>
                    <div class="password-container">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required 
                               placeholder="Re-enter your password">
                        <button type="button" class="toggle-password" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="requirements">
                        <div class="requirement" id="passwordMatch">
                            <i class="fas fa-circle" id="passwordMatchIcon"></i>
                            <span>Passwords must match</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>

                <div class="login-link">
                    Already have an account? <a href="login.php">Sign in here</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            const strengthBar = document.getElementById('passwordStrengthBar');
            const strengthText = document.getElementById('passwordStrengthText');
            
            // Length check
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            
            // Character variety checks
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Update strength bar and text
            const width = (strength / 6) * 100;
            strengthBar.style.width = width + '%';
            
            // Set color and text based on strength
            if (strength <= 2) {
                strengthBar.style.backgroundColor = '#dc3545';
                strengthText.textContent = 'Password strength: Weak';
                strengthText.style.color = '#dc3545';
            } else if (strength <= 4) {
                strengthBar.style.backgroundColor = '#ffc107';
                strengthText.textContent = 'Password strength: Fair';
                strengthText.style.color = '#ffc107';
            } else {
                strengthBar.style.backgroundColor = '#28a745';
                strengthText.textContent = 'Password strength: Strong';
                strengthText.style.color = '#28a745';
            }
        }
        
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
        
        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
        
        // Real-time validation
        document.getElementById('username').addEventListener('input', function() {
            const username = this.value.trim();
            const usernameLength = document.getElementById('usernameLength');
            const usernameLengthIcon = document.getElementById('usernameLengthIcon');
            
            if (username.length >= 3 && username.length <= 50) {
                usernameLength.classList.add('met');
                usernameLength.classList.remove('unmet');
                usernameLengthIcon.className = 'fas fa-check-circle';
                usernameLengthIcon.style.color = '#28a745';
            } else {
                usernameLength.classList.add('unmet');
                usernameLength.classList.remove('met');
                usernameLengthIcon.className = 'fas fa-circle';
                usernameLengthIcon.style.color = '#6c757d';
            }
        });
        
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const passwordLength = document.getElementById('passwordLength');
            const passwordLengthIcon = document.getElementById('passwordLengthIcon');
            
            // Check password length
            if (password.length >= 6) {
                passwordLength.classList.add('met');
                passwordLength.classList.remove('unmet');
                passwordLengthIcon.className = 'fas fa-check-circle';
                passwordLengthIcon.style.color = '#28a745';
            } else {
                passwordLength.classList.add('unmet');
                passwordLength.classList.remove('met');
                passwordLengthIcon.className = 'fas fa-circle';
                passwordLengthIcon.style.color = '#6c757d';
            }
            
            // Check password strength
            checkPasswordStrength(password);
            
            // Check password match
            checkPasswordMatch();
        });
        
        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const passwordMatch = document.getElementById('passwordMatch');
            const passwordMatchIcon = document.getElementById('passwordMatchIcon');
            
            if (confirmPassword && password === confirmPassword) {
                passwordMatch.classList.add('met');
                passwordMatch.classList.remove('unmet');
                passwordMatchIcon.className = 'fas fa-check-circle';
                passwordMatchIcon.style.color = '#28a745';
            } else if (confirmPassword) {
                passwordMatch.classList.add('unmet');
                passwordMatch.classList.remove('met');
                passwordMatchIcon.className = 'fas fa-times-circle';
                passwordMatchIcon.style.color = '#dc3545';
            } else {
                passwordMatch.classList.add('unmet');
                passwordMatch.classList.remove('met');
                passwordMatchIcon.className = 'fas fa-circle';
                passwordMatchIcon.style.color = '#6c757d';
            }
        }
        
        // Form submission validation
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            let isValid = true;
            let errorMessage = '';
            
            // Username validation
            if (username.length < 3 || username.length > 50) {
                isValid = false;
                errorMessage = 'Username must be between 3 and 50 characters.';
            }
            
            // Password validation
            else if (password.length < 6) {
                isValid = false;
                errorMessage = 'Password must be at least 6 characters long.';
            }
            
            // Password match validation
            else if (password !== confirmPassword) {
                isValid = false;
                errorMessage = 'Passwords do not match.';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
            }
        });
        
        // Auto-focus username field
        document.getElementById('username').focus();
        
        // Initialize validation
        document.getElementById('username').dispatchEvent(new Event('input'));
        document.getElementById('password').dispatchEvent(new Event('input'));
    </script>
</body>
</html>