<?php
session_start();
require_once 'config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header("Location: " . ($role == 'admin' ? 'admin-dashboard.php' : 'client-dashboard.php'));
    exit();
}

$error_message = '';
$success_message = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);
    $confirm_password = sanitize_input($_POST['confirm_password']);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Please fill in all fields.";
    } elseif (strlen($name) < 3) {
        $error_message = "Name must be at least 3 characters long.";
    } elseif (!is_valid_email($email)) {
        $error_message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error_message = "Password must contain at least 1 uppercase letter.";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error_message = "Password must contain at least 1 lowercase letter.";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error_message = "Password must contain at least 1 number.";
    } elseif (!preg_match('/[!@#$%^&*]/', $password)) {
        $error_message = "Password must contain at least 1 special character (!@#$%^&*).";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strpos($password, ' ') !== false) {
        $error_message = "Password cannot contain spaces.";
    } else {
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Check if email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $error_message = "Email address already exists. Please use a different email or login.";
            } else {
                // Hash password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user (default role = vendor)
                $stmt = $db->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (:name, :email, :password, 'vendor', NOW())");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->execute();
                
                $success_message = "Registration successful! You can now login with your credentials.";
            }
        } catch(PDOException $exception) {
            $error_message = "Registration failed. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Market Vendor Loan</title>
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
            padding: 15px 20px;
            text-align: center;
        }
        .header-logo {
            width: 60px;
            height: 60px;
            border-radius: 12px;
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
            box-shadow: 0 0 3px rgba(59, 130, 246, 0.1);
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

        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Registration Form Only -->
        <div class="auth-card" id="signupCard">
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
                <a href="login.php" >Already have an account? Sign In</a>
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

        // Real-time password validation for registration
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            let strength = 0;
            
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

        // Form validation for registration
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Name validation
            if (!name) {
                alert('Please enter your full name.');
                e.preventDefault();
                return false;
            }
            
            if (name.length < 3) {
                alert('Name must be at least 3 characters long.');
                e.preventDefault();
                return false;
            }
            
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
                alert('Please enter a password.');
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
            
            if (password !== confirmPassword) {
                alert('Passwords do not match.');
                e.preventDefault();
                return false;
            }
            
            if (password.indexOf(' ') !== -1) {
                alert('Password cannot contain spaces.');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>
