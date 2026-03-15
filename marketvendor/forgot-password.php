<?php
require_once 'config/database.php';

$error_message = '';
$success_message = '';

// Handle forgot password form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    
    // Validate email
    if (empty($email)) {
        $error_message = "Please enter your email address.";
    } elseif (!is_valid_email($email)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Check if email exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                // Generate secure reset token
                $token = generate_secure_token();
                $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                // Store token in database
                $stmt = $db->prepare("INSERT INTO password_resets (email, reset_token, token_expiry) VALUES (:email, :token, :expiry)");
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':token', $token);
                $stmt->bindParam(':expiry', $expiry);
                $stmt->execute();
                
                // Send email (in production, use actual email service)
                $reset_link = "http://yourdomain.com/reset-password.php?token=" . $token;
                
                // For demo purposes, show the link
                $success_message = "Password reset link has been sent to your email. (Demo: <a href='$reset_link'>$reset_link</a>)";
                
            } else {
                // Don't reveal if email exists or not for security
                $success_message = "If your email exists in our system, you will receive a password reset link shortly.";
            }
        } catch(PDOException $exception) {
            $error_message = "Request failed. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Market Vendor Loan</title>
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

        .forgot-container {
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
        }

        .success-message a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .forgot-container {
                margin: 20px;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="logo-section">
            <img src="images/loo.png" alt="Logo" class="logo">
            <div class="brand-text">
                <h1>Market Vendor Loan</h1>
                <p>Reset Password</p>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="forgot-password.php" id="forgotForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Enter your registered email"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    required
                >
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i> Send Reset Link
            </button>
        </form>

        <div class="back-link">
            <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('forgotForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            
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
            
            return true;
        });
    </script>
</body>
</html>
