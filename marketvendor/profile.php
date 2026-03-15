<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Update basic info
        if ($phone || $address) {
            $stmt = $db->prepare("UPDATE users SET phone = ?, address = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$phone, $address, $user_id]);
            $success_message = "Profile updated successfully!";
        }
        
        // Update password
        if ($current_password && $new_password) {
            // Verify current password
            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($current_password, $user['password'])) {
                if ($new_password === $confirm_password) {
                    // Enhanced password validation
                    $password_errors = [];
                    
                    // Check minimum length
                    if (strlen($new_password) < 8) {
                        $password_errors[] = "Password must be at least 8 characters long.";
                    }
                    
                    // Check for uppercase letter
                    if (!preg_match('/[A-Z]/', $new_password)) {
                        $password_errors[] = "Password must contain at least one uppercase letter.";
                    }
                    
                    // Check for lowercase letter
                    if (!preg_match('/[a-z]/', $new_password)) {
                        $password_errors[] = "Password must contain at least one lowercase letter.";
                    }
                    
                    // Check for at least one number
                    if (!preg_match('/[0-9]/', $new_password)) {
                        $password_errors[] = "Password must contain at least one number.";
                    }
                    
                    // Check for at least one special character
                    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $new_password)) {
                        $password_errors[] = "Password must contain at least one special character.";
                    }
                    
                    if (empty($password_errors)) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                        $stmt->execute([$hashed_password, $user_id]);
                        $success_message .= " Password updated successfully!";
                    } else {
                        $error_message = implode(" ", $password_errors);
                    }
                } else {
                    $error_message = "New passwords do not match.";
                }
            } else {
                $error_message = "Current password is incorrect.";
            }
        }
        
        // Refresh user data
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $error_message = "Error updating profile: " . $e->getMessage();
    }
} else {
    // Get current user data
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - BlueLedger Finance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="enhanced-styles.css">
    <link rel="stylesheet" href="responsive-styles-fixed.css">
    <style>
        .form-container {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid var(--line);
            border-radius: var(--card-radius);
            padding: 32px;
            margin-bottom: 24px;
        }

        .profile-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .form-section {
            background: rgba(15, 39, 75, 0.6);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 0;
        }

        .form-section h3 {
            color: var(--text-100);
            font-size: 1.125rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
            
            .profile-layout {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .form-section {
                padding: 20px;
            }
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--line);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-100);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-200);
            font-size: 0.875rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            background: rgba(15, 39, 75, 0.5);
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--text-100);
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--success);
            box-shadow: 0 0 0 3px rgba(46, 122, 214, 0.1);
        }

        .required {
            color: #ef4444;
        }

        .btn-primary {
            background: var(--success);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-300);
            border: 1px solid var(--line);
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: rgba(107, 114, 128, 0.1);
            color: var(--text-100);
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border-color: rgba(34, 197, 94, 0.3);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(30, 41, 59, 0.95);
            border-radius: 12px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-details h3 {
            font-size: 16px;
            margin: 0;
            color: #e2e8f0;
        }

        .user-details p {
            font-size: 12px;
            color: #94a3b8;
            margin: 0;
        }

        .logout-btn {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: translateY(-1px);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(24, 58, 109, 0.35);
            margin-bottom: 4px;
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            border-radius: 8px;
        }

        .brand-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand-content h1 {
            font-size: 1rem;
            margin-bottom: 4px;
        }

        .brand-content p {
            color: var(--text-300);
            font-size: .8rem;
        }

        .nav-section-title {
            color: var(--text-200);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
            padding: 0 12px;
            border-bottom: 1px solid var(--line);
        }

        .nav-item {
            display: flex !important;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-200) !important;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 500;
            border: 1px solid transparent;
            background: transparent;
        }

        .nav-item:hover {
            background: rgba(59, 130, 246, 0.1) !important;
            border-color: var(--primary) !important;
            color: var(--text-100) !important;
            transform: translateX(4px);
        }

        .nav-item.active {
            background: var(--primary) !important;
            border-color: var(--primary) !important;
            color: white !important;
        }

        .nav-item i {
            font-size: 1rem !important;
            width: 20px;
            text-align: center;
        }

        .password-requirements {
            background: rgba(15, 39, 75, 0.3);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
        }

        .password-requirements h4 {
            color: var(--text-100);
            font-size: 0.875rem;
            margin-bottom: 12px;
        }

        .password-requirements ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .password-requirements li {
            color: var(--text-300);
            font-size: 0.8rem;
            margin-bottom: 8px;
            padding-left: 24px;
            position: relative;
            transition: all 0.3s ease;
        }

        .password-requirements li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--text-400);
            font-weight: bold;
        }

        .password-requirements .requirement {
            display: flex;
            align-items: center;
            gap: 8px;
            padding-left: 0;
            margin-bottom: 6px;
            transition: all 0.3s ease;
        }

        .password-requirements .requirement i {
            font-size: 0.9rem;
            min-width: 16px;
        }

        .password-requirements .requirement .fa-check-circle {
            color: var(--success);
            display: none;
        }

        .password-requirements .requirement .fa-times-circle {
            color: var(--danger);
            display: inline;
        }

        .password-requirements .requirement.valid .fa-check-circle {
            display: inline;
        }

        .password-requirements .requirement.valid .fa-times-circle {
            display: none;
        }

        .password-requirements .requirement.valid {
            color: var(--success);
        }

        .password-requirements .requirement.invalid {
            color: var(--danger);
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="brand">
                <img src="images/loo.png" alt="Market Vendor Loan Logo" class="brand-logo">
                <div class="brand-content">
                    <h1>Market Vendor Loan</h1>
                    <p>Client Portal</p>
                </div>
            </div>

            <p class="nav-section-title">Main Menu</p>
            <a class="nav-item" href="my-loans.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a class="nav-item" href="apply-loan.php">
                <i class="fas fa-plus-circle"></i> Apply for Loan
            </a>
            <a class="nav-item" href="payments.php">
                <i class="fas fa-calendar-alt"></i> Payment Schedule
            </a>
            <a class="nav-item" href="make-payment.php">
                <i class="fas fa-credit-card"></i> Make Payment
            </a>
            <a class="nav-item" href="client-payment-history.php">
                <i class="fas fa-history"></i> Payment History
            </a>
            <a class="nav-item active" href="profile.php">
                <i class="fas fa-user"></i> Profile
            </a>

        </div>

        <!-- Main Content -->
        <div class="content-wrap">
            <div class="dashboard-header">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user_data['name'], 0, 2)); ?>
                    </div>
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($user_data['name']); ?></h3>
                        <p>Vendor</p>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <header class="header">
                <div>
                    <h2>Profile Settings</h2>
                    <p>Manage your account information and security settings</p>
                </div>
            </header>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST">
                    <div class="profile-layout">
                        <!-- Personal Information -->
                        <div class="form-section">
                            <h3><i class="fas fa-user"></i> Personal Information</h3>
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" placeholder="Enter your phone number">
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" rows="3" placeholder="Enter your address"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Security Settings -->
                        <div class="form-section">
                            <h3><i class="fas fa-lock"></i> Security Settings</h3>
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" placeholder="Enter your current password">
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" placeholder="Enter your new password">
                                <small>Minimum 8 characters with at least one uppercase, lowercase, number, and special character</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password">
                            </div>
                        </div>

                    <!-- Submit Buttons -->
                    <div class="form-section" style="background: transparent; border: none; padding: 0;">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <button type="button" class="btn-secondary" onclick="window.location.href='dashboard.php'">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
            </main>
        </div>
    </div>
    <script src="responsive-script.js"></script>

    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // If password fields are filled, validate them
            if (currentPassword || newPassword || confirmPassword) {
                if (!currentPassword) {
                    alert('Please enter your current password.');
                    e.preventDefault();
                    return;
                }
                
                if (!newPassword) {
                    alert('Please enter a new password.');
                    e.preventDefault();
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    alert('New passwords do not match.');
                    e.preventDefault();
                    return;
                }
                
                // Enhanced password validation
                const passwordErrors = [];
                
                // Check minimum length
                if (newPassword.length < 8) {
                    passwordErrors.push("Password must be at least 8 characters long.");
                }
                
                // Check for uppercase letter
                if (!/[A-Z]/.test(newPassword)) {
                    passwordErrors.push("Password must contain at least one uppercase letter.");
                }
                
                // Check for lowercase letter
                if (!/[a-z]/.test(newPassword)) {
                    passwordErrors.push("Password must contain at least one lowercase letter.");
                }
                
                // Check for at least one number
                if (!/[0-9]/.test(newPassword)) {
                    passwordErrors.push("Password must contain at least one number.");
                }
                
                // Check for at least one special character
                if (!/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(newPassword)) {
                    passwordErrors.push("Password must contain at least one special character.");
                }
                
                if (passwordErrors.length > 0) {
                    alert(passwordErrors.join("\n"));
                    e.preventDefault();
                    return;
                }
            }
        });
        
        // Real-time password validation feedback
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        function validatePasswordStrength(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(password)
            };
            
            return requirements;
        }
        
        function updatePasswordRequirements(password) {
            const requirements = validatePasswordStrength(password);
            const requirementElements = document.querySelectorAll('.requirement');
            
            requirementElements.forEach((element, index) => {
                const requirementTypes = ['length', 'uppercase', 'lowercase', 'number', 'special'];
                const isValid = requirements[requirementTypes[index]];
                
                if (isValid) {
                    element.classList.add('valid');
                    element.classList.remove('invalid');
                } else {
                    element.classList.add('invalid');
                    element.classList.remove('valid');
                }
            });
        }
        
        newPasswordInput.addEventListener('input', function() {
            updatePasswordRequirements(this.value);
        });
        
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value && this.value !== newPasswordInput.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
                }
            }
        });

        // Clear error styling on input
        document.querySelectorAll('input').forEach(field => {
            field.addEventListener('input', function() {
                this.style.borderColor = 'var(--line)';
            });
        });
                                                                                                                                                                                        
