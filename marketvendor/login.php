<?php
session_start();
require_once 'config/database.php';
require_once 'includes/audit_helper.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header("Location: " . ($role == 'admin' ? 'admin-dashboard.php' : 'my-loans.php'));
    exit();
}

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $error_message = "Please fill in all fields.";
    } elseif (!is_valid_email($email)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Prepare statement to prevent SQL injection
            $stmt = $db->prepare("SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch();
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Password correct, create session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    
                    // Log successful login
                    logLogin($user['id'], $user['name'], true);
                    
                    // Set remember me cookie if checked
                    if ($remember) {
                        $token = generate_secure_token();
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
                        
                        // Store token in database (implementation needed)
                    }
                    
                    // Redirect based on role
                    $redirect = $user['role'] == 'admin' ? 'admin-dashboard.php' : 'my-loans.php';
                    header("Location: $redirect");
                    exit();
                } else {
                    $error_message = "Invalid email or password.";
                    // Log failed login attempt
                    logFailedAttempt('login', "Email: {$email}");
                }
            } else {
                $error_message = "Invalid email or password.";
                // Log failed login attempt
                logFailedAttempt('login', "Email: {$email} - User not found");
            }
        } catch(PDOException $exception) {
            $error_message = "Login failed. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Sign Up - Market Vendor Loan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('images/bg.png') center/cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e2e8f0;
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.85);
            z-index: -1;
        }

        .auth-header {
            background: rgba(30, 41, 59, 0.95);
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1px 1px;
        }

        .header-logo {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            margin-right: 15px;
        }

        .logo-section {
            display: flex;
            align-items: center;
        }

        .brand-text h1 {
            font-size: 20px;
            font-weight: 600;
            color: #3b82f6;
            margin: 0;
        }

        .brand-text p {
            font-size: 12px;
            color: #94a3b8;
            margin: 0;
        }

        .auth-container {
            display: flex;
            gap: 20px;
            max-width: 500px;
            width: 100%;
            padding: 20px;
            flex-direction: column;
        }

        .auth-card {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        }

        .auth-card h2 {
            text-align: center;
            color: #3b82f6;
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: 600;
        }

        .auth-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .auth-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.4);
        }

        .auth-toggle {
            text-align: center;
            margin-top: 20px;
        }

        .auth-toggle a {
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .auth-toggle a:hover {
            color: #60a5fa;
            text-decoration: underline;
        }

        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password a {
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #60a5fa;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
                padding: 10px;
            }
        }

        .login-container {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .logo-section .logo {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }
        
        .logo-section .brand-text {
            text-align: left;
        }

        .logo-section h1 {
            font-size: 24px;
            font-weight: 600;
            color: #3b82f6;
            margin-bottom: 4px;
        }

        .logo-section p {
            color: #94a3b8;
            font-size: 14px;
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #cbd5e1;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: 8px;
            background: rgba(15, 23, 42, 0.6);
            color: #e2e8f0;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 8px;
            width: 16px;
            height: 16px;
        }

        .checkbox-group label {
            color: #94a3b8;
            font-size: 14px;
            cursor: pointer;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password a {
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #60a5fa;
            text-decoration: underline;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .success-message {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #4ade80;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .password-strength {
            margin-top: 8px;
            font-size: 12px;
            height: 4px;
            border-radius: 2px;
            background: #374151;
            transition: all 0.3s ease;
        }

        .password-strength.weak { background: #ef4444; }
        .password-strength.medium { background: #f59e0b; }
        .password-strength.strong { background: #10b981; }

        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <!-- Login Form (Top) -->

        <div class="auth-card" id="loginCard">
               <!-- Header -->
         <div class="header-container">
            <div class="logo-section">
                <img src="images/loo.png" alt="Market Vendor Loan Logo" class="header-logo">
                <div class="brand-text">
                    <h1>Market Vendor Loan</h1>
                    <p>Loan Management System</p>
                </div>
            </div>
        </div>

  
            <h2>Sign In</h2>
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" id="loginForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="auth-toggle">
                <a href="register.php">Don't have an account? Sign Up</a>
            </div>

            <div class="forgot-password">
                <a href="forgot-password.php">Forgot Password?</a>
            </div>
        </div>

        <!-- Registration Form (Bottom) -->
        <div class="auth-card" id="signupCard" style="display: none;">
                    <div class="header-container">
            <div class="logo-section">
                <img src="images/loo.png" alt="Market Vendor Loan Logo" class="header-logo">
                <div class="brand-text">
                    <h1>Market Vendor Loan</h1>
                    <p>Loan Management System</p>
                </div>
            </div>
        </div>
            <h2>Sign Up</h2>
            <form method="POST" action="register.php" id="registerForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        placeholder="Enter your full name"
                        value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email address"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Create a strong password"
                        required
                    >
                    <div class="password-strength" id="passwordStrength"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Confirm your password"
                        required
                    >
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-toggle">
                <a href="#" onclick="switchToLogin(event)">Already have an account? Sign In</a>
            </div>
        </div>
    </div>

    <script>
        // Switch to Sign Up form
        function switchToSignup(event) {
            event.preventDefault();
            document.getElementById('loginCard').style.display = 'none';
            document.getElementById('signupCard').style.display = 'block';
        }

        // Switch to Login form
        function switchToLogin(event) {
            event.preventDefault();
            document.getElementById('signupCard').style.display = 'none';
            document.getElementById('loginCard').style.display = 'block';
        }

        // Real-time password validation
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            let strength = 0;
            let feedback = '';
            
            // Length check
            if (password.length >= 8) strength++;
            
            // Uppercase check
            if (/[A-Z]/.test(password)) strength++;
            
            // Lowercase check
            if (/[a-z]/.test(password)) strength++;
            
            // Number check
            if (/[0-9]/.test(password)) strength++;
            
            // Special character check
            if (/[!@#$%^&*]/.test(password)) strength++;
            
            // Update strength bar
            if (password.length === 0) {
                strengthBar.className = 'password-strength';
                strengthBar.style.width = '0%';
            } else if (strength <= 2) {
                strengthBar.className = 'password-strength weak';
                strengthBar.style.width = '33%';
            } else if (strength <= 3) {
                strengthBar.className = 'password-strength medium';
                strengthBar.style.width = '66%';
            } else {
                strengthBar.className = 'password-strength strong';
                strengthBar.style.width = '100%';
            }
        });

        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Email validation
            if (!email) {
                alert('Please enter your email address.');
                e.preventDefault();
                return false;
            }
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                alert('Please enter a valid email address.');
                e.preventDefault();
                return false;
            }
            
            // Password validation
            if (!password) {
                alert('Please enter your password.');
                e.preventDefault();
                return false;
            }
            
            if (password.length < 8) {
                alert('Password must be at least 8 characters long.');
                e.preventDefault();
                return false;
            }
            
            // Password policy check
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*]/.test(password);
            
            if (!hasUpper || !hasLower || !hasNumber || !hasSpecial) {
                alert('Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character (!@#$%^&*).');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>
