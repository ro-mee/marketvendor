<?php
require_once 'config/database.php';

$error_message = '';
$success_message = '';
$token = sanitize_input($_GET['token'] ?? '');

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = sanitize_input($_POST['password']);
    $confirm_password = sanitize_input($_POST['confirm_password']);
    $token = sanitize_input($_POST['token']);
    
    // Validate inputs
    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])/', $new_password)) {
        $error_message = "Password must contain at least 1 uppercase, 1 lowercase, 1 number, and 1 special character.";
    } else {
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Validate token and check expiry
            $stmt = $db->prepare("SELECT email FROM password_resets WHERE reset_token = :token AND token_expiry > NOW() LIMIT 1");
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $reset_data = $stmt->fetch();
                $email = $reset_data['email'];
                
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update user password
                $stmt = $db->prepare("UPDATE users SET password = :password, updated_at = NOW() WHERE email = :email");
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                // Invalidate the token
                $stmt = $db->prepare("DELETE FROM password_resets WHERE reset_token = :token");
                $stmt->bindParam(':token', $token);
                $stmt->execute();
                
                $success_message = "Password has been reset successfully. You can now login with your new password.";
                
            } else {
                $error_message = "Invalid or expired reset token. Please request a new password reset.";
            }
        } catch(PDOException $exception) {
            $error_message = "Password reset failed. Please try again later.";
        }
    }
}

// Validate token on page load
elseif (!empty($token)) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $stmt = $db->prepare("SELECT email FROM password_resets WHERE reset_token = :token AND token_expiry > NOW() LIMIT 1");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        if ($stmt->rowCount() != 1) {
            $error_message = "Invalid or expired reset token. Please request a new password reset.";
            $token = ''; // Invalidate token
        }
    } catch(PDOException $exception) {
        $error_message = "Token validation failed. Please try again later.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - BlueLedger Finance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e2e8f0;
        }

        .reset-container {
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
        }

        .logo-section h1 {
            font-size: 24px;
            font-weight: 600;
            color: #3b82f6;
            margin-bottom: 8px;
        }

        .logo-section p {
            color: #94a3b8;
            font-size: 14px;
        }

        .back-link {
            text-align: center;
            margin-bottom: 30px;
        }

        .back-link a {
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #60a5fa;
            text-decoration: underline;
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

        .password-requirements {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 8px;
            line-height: 1.4;
        }

        .password-requirements ul {
            list-style: none;
            padding-left: 0;
        }

        .password-requirements li {
            margin-bottom: 4px;
            padding-left: 16px;
            position: relative;
        }

        .password-requirements li::before {
            content: '•';
            position: absolute;
            left: 0;
            color: #64748b;
        }

        .password-requirements li.valid {
            color: #4ade80;
        }

        .password-requirements li.valid::before {
            content: '✓';
            color: #4ade80;
        }

        .submit-btn {
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

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
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

        .success-message a {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 600;
        }

        .success-message a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .reset-container {
                margin: 20px;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="logo-section">
            <h1>BlueLedger Finance</h1>
            <p>Reset Password</p>
        </div>

        <div class="back-link">
            <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <?php echo $success_message; ?><br>
                <a href="login.php">Proceed to Login</a>
            </div>
        <?php endif; ?>

        <?php if (empty($success_message) && !empty($token)): ?>
            <form method="POST" action="reset-password.php" id="resetForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter new password"
                        required
                    >
                    <div class="password-requirements">
                        <ul id="passwordRequirements">
                            <li id="length">At least 8 characters</li>
                            <li id="uppercase">At least 1 uppercase letter</li>
                            <li id="lowercase">At least 1 lowercase letter</li>
                            <li id="number">At least 1 number</li>
                            <li id="special">At least 1 special character (!@#$%^&*)</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Confirm new password"
                        required
                    >
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-lock"></i> Reset Password
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($success_message) && !empty($token)): ?>
    <script>
        // Real-time password validation
        const password = document.getElementById('password');
        const confirm = document.getElementById('confirm_password');
        const requirements = {
            length: document.getElementById('length'),
            uppercase: document.getElementById('uppercase'),
            lowercase: document.getElementById('lowercase'),
            number: document.getElementById('number'),
            special: document.getElementById('special')
        };

        function validatePassword() {
            const pwd = password.value;
            
            // Length check
            requirements.length.className = pwd.length >= 8 ? 'valid' : '';
            
            // Uppercase check
            requirements.uppercase.className = /[A-Z]/.test(pwd) ? 'valid' : '';
            
            // Lowercase check
            requirements.lowercase.className = /[a-z]/.test(pwd) ? 'valid' : '';
            
            // Number check
            requirements.number.className = /[0-9]/.test(pwd) ? 'valid' : '';
            
            // Special character check
            requirements.special.className = /[!@#$%^&*]/.test(pwd) ? 'valid' : '';
        }

        password.addEventListener('input', validatePassword);
        confirm.addEventListener('input', validatePassword);

        // Form validation
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const pwd = password.value;
            const conf = confirm.value;
            
            if (!pwd || !conf) {
                alert('Please fill in all fields.');
                e.preventDefault();
                return false;
            }
            
            if (pwd !== conf) {
                alert('Passwords do not match.');
                e.preventDefault();
                return false;
            }
            
            if (pwd.length < 8) {
                alert('Password must be at least 8 characters long.');
                e.preventDefault();
                return false;
            }
            
            if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])/.test(pwd)) {
                alert('Password must contain at least 1 uppercase, 1 lowercase, 1 number, and 1 special character.');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    </script>
    <?php endif; ?>
</body>
</html>
