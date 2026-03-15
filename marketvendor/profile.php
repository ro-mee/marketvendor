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
        // Check which form was submitted
        $form_type = $_POST['form_type'] ?? '';
        
        if ($form_type === 'personal_info') {
            // Handle personal information update
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            
            if ($phone || $address) {
                $stmt = $db->prepare("UPDATE users SET phone = ?, address = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$phone, $address, $user_id]);
                $success_message = "Personal information updated successfully!";
            }
        } elseif ($form_type === 'security_settings') {
            // Handle security settings update
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
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
                        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/ ?]/', $new_password)) {
                            $password_errors[] = "Password must contain at least one special character.";
                        }
                        
                        if (empty($password_errors)) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                            $stmt->execute([$hashed_password, $user_id]);
                            $success_message = "Password updated successfully!";
                        } else {
                            $error_message = implode(" ", $password_errors);
                        }
                    } else {
                        $error_message = "New passwords do not match.";
                    }
                } else {
                    $error_message = "Current password is incorrect.";
                }
            } else {
                $error_message = "Please fill in all password fields.";
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

        .validation-message {
            display: none;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            border: 1px solid;
            font-size: 0.875rem;
            align-items: center;
            gap: 8px;
        }

        .validation-message.show {
            display: flex;
        }

        .validation-message.error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .validation-message.success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border-color: rgba(34, 197, 94, 0.3);
        }

        .validation-message.info {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border-color: rgba(59, 130, 246, 0.3);
        }

        .field-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        .field-success {
            border-color: #22c55e !important;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1) !important;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .validation-message.show {
            animation: slideDown 0.3s ease-out;
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .loading-spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .btn-primary.loading .loading-spinner {
            display: inline-block;
        }

        .btn-primary.loading .btn-text {
            display: none;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                <div class="profile-layout">
                    <!-- Personal Information Form -->
                    <form method="POST" id="personalInfoForm">
                        <input type="hidden" name="form_type" value="personal_info">
                        
                        <div class="validation-message" id="personalInfoValidation">
                            <i class="fas fa-exclamation-circle"></i>
                            <span class="message-text"></span>
                        </div>
                        
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
                            
                            <button type="submit" class="btn-primary" id="personalInfoSubmit">
                                <span class="btn-text"><i class="fas fa-save"></i> Save Personal Information</span>
                                <div class="loading-spinner"></div>
                            </button>
                            <button type="button" class="btn-secondary" onclick="resetPersonalInfoForm()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </form>

                    <!-- Security Settings Form -->
                    <form method="POST" id="securityForm">
                        <input type="hidden" name="form_type" value="security_settings">
                        
                        <div class="validation-message" id="securityValidation">
                            <i class="fas fa-exclamation-circle"></i>
                            <span class="message-text"></span>
                        </div>
                        
                        <div class="form-section">
                            <h3><i class="fas fa-lock"></i> Security Settings</h3>
                            
                            <div class="password-requirements">
                                <h4>Password Requirements:</h4>
                                <div class="requirement">
                                    <i class="fas fa-times-circle"></i>
                                    <i class="fas fa-check-circle"></i>
                                    At least 8 characters long
                                </div>
                                <div class="requirement">
                                    <i class="fas fa-times-circle"></i>
                                    <i class="fas fa-check-circle"></i>
                                    Contains uppercase letter (A-Z)
                                </div>
                                <div class="requirement">
                                    <i class="fas fa-times-circle"></i>
                                    <i class="fas fa-check-circle"></i>
                                    Contains lowercase letter (a-z)
                                </div>
                                <div class="requirement">
                                    <i class="fas fa-times-circle"></i>
                                    <i class="fas fa-check-circle"></i>
                                    Contains number (0-9)
                                </div>
                                <div class="requirement">
                                    <i class="fas fa-times-circle"></i>
                                    <i class="fas fa-check-circle"></i>
                                    Contains special character (!@#$%^&*...)
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" placeholder="Enter your current password">
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" placeholder="Enter your new password">
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password">
                            </div>
                            
                            <button type="submit" class="btn-primary" id="securitySubmit">
                                <span class="btn-text"><i class="fas fa-shield-alt"></i> Update Security Settings</span>
                                <div class="loading-spinner"></div>
                            </button>
                            <button type="button" class="btn-secondary" onclick="resetSecurityForm()">
                                <i class="fas fa-undo"></i> Clear
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            </main>
        </div>
    </div>
    <script src="responsive-script.js"></script>

    <script>
        // Validation helper functions
        function showValidationMessage(elementId, message, type = 'error') {
            const validationElement = document.getElementById(elementId);
            const messageText = validationElement.querySelector('.message-text');
            const icon = validationElement.querySelector('i');
            
            // Remove all classes
            validationElement.classList.remove('error', 'success', 'info');
            
            // Add new classes
            validationElement.classList.add(type, 'show');
            messageText.textContent = message;
            
            // Update icon
            icon.className = type === 'success' ? 'fas fa-check-circle' : 
                            type === 'info' ? 'fas fa-info-circle' : 
                            'fas fa-exclamation-circle';
            
            // Auto hide after 5 seconds for success messages
            if (type === 'success') {
                setTimeout(() => {
                    validationElement.classList.remove('show');
                }, 5000);
            }
        }
        
        function hideValidationMessage(elementId) {
            const validationElement = document.getElementById(elementId);
            validationElement.classList.remove('show');
        }
        
        function setFieldError(fieldId, isError = true) {
            const field = document.getElementById(fieldId);
            if (isError) {
                field.classList.add('field-error');
                field.classList.remove('field-success');
            } else {
                field.classList.add('field-success');
                field.classList.remove('field-error');
            }
        }
        
        function clearFieldErrors(...fieldIds) {
            fieldIds.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                field.classList.remove('field-error', 'field-success');
            });
        }
        
        function setButtonLoading(buttonId, loading = true) {
            const button = document.getElementById(buttonId);
            if (loading) {
                button.classList.add('loading');
                button.disabled = true;
            } else {
                button.classList.remove('loading');
                button.disabled = false;
            }
        }

        // Personal Information Form Validation
        document.getElementById('personalInfoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const phone = document.getElementById('phone').value.trim();
            const address = document.getElementById('address').value.trim();
            
            // Clear previous validation
            clearFieldErrors('phone', 'address');
            hideValidationMessage('personalInfoValidation');
            
            // Validate at least one field is filled
            if (!phone && !address) {
                showValidationMessage('personalInfoValidation', 'Please enter either a phone number or address to update.', 'error');
                return;
            }
            
            // Validate phone format if provided
            if (phone && !/^[\d\s\-\+\(\)]+$/.test(phone)) {
                setFieldError('phone', true);
                showValidationMessage('personalInfoValidation', 'Please enter a valid phone number.', 'error');
                return;
            }
            
            // Show loading state
            setButtonLoading('personalInfoSubmit', true);
            
            // Submit form
            this.submit();
        });
        
        // Security Form Validation
        document.getElementById('securityForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Clear previous validation
            clearFieldErrors('current_password', 'new_password', 'confirm_password');
            hideValidationMessage('securityValidation');
            
            // Validate all password fields are filled
            let hasError = false;
            let errorMessage = '';
            
            if (!currentPassword) {
                setFieldError('current_password', true);
                errorMessage = 'Please enter your current password.';
                hasError = true;
            }
            
            if (!newPassword) {
                setFieldError('new_password', true);
                errorMessage = errorMessage ? 'Please fill in all password fields.' : 'Please enter a new password.';
                hasError = true;
            }
            
            if (!confirmPassword) {
                setFieldError('confirm_password', true);
                errorMessage = errorMessage ? 'Please fill in all password fields.' : 'Please confirm your new password.';
                hasError = true;
            }
            
            if (hasError) {
                showValidationMessage('securityValidation', errorMessage, 'error');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                setFieldError('confirm_password', true);
                showValidationMessage('securityValidation', 'New passwords do not match.', 'error');
                return;
            }
            
            // Enhanced password validation
            const passwordErrors = [];
            
            if (newPassword.length < 8) {
                passwordErrors.push("At least 8 characters long");
            }
            
            if (!/[A-Z]/.test(newPassword)) {
                passwordErrors.push("Contains uppercase letter (A-Z)");
            }
            
            if (!/[a-z]/.test(newPassword)) {
                passwordErrors.push("Contains lowercase letter (a-z)");
            }
            
            if (!/[0-9]/.test(newPassword)) {
                passwordErrors.push("Contains number (0-9)");
            }
            
            if (!/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/ ?]/.test(newPassword)) {
                passwordErrors.push("Contains special character");
            }
            
            if (passwordErrors.length > 0) {
                setFieldError('new_password', true);
                showValidationMessage('securityValidation', 'Password must meet all requirements: ' + passwordErrors.join(', ') + '.', 'error');
                return;
            }
            
            // Show loading state
            setButtonLoading('securitySubmit', true);
            
            // Submit form
            this.submit();
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
                special: /[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/ ?]/.test(password)
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
            
            // Clear validation message when user starts typing
            if (this.value.length > 0) {
                hideValidationMessage('securityValidation');
                clearFieldErrors('new_password');
            }
        });
        
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value && this.value !== newPasswordInput.value) {
                this.setCustomValidity('Passwords do not match');
                setFieldError('confirm_password', true);
            } else {
                this.setCustomValidity('');
                clearFieldErrors('confirm_password');
            }
        });
        
        // Reset form functions
        function resetPersonalInfoForm() {
            document.getElementById('phone').value = '<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>';
            document.getElementById('address').value = '<?php echo htmlspecialchars($user_data['address'] ?? ''); ?>';
            clearFieldErrors('phone', 'address');
            hideValidationMessage('personalInfoValidation');
        }
        
        function resetSecurityForm() {
            document.getElementById('current_password').value = '';
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
            
            // Reset password requirements
            const requirementElements = document.querySelectorAll('.requirement');
            requirementElements.forEach(element => {
                element.classList.remove('valid', 'invalid');
            });
            
            clearFieldErrors('current_password', 'new_password', 'confirm_password');
            hideValidationMessage('securityValidation');
        }

        // Clear error styling on input
        document.querySelectorAll('input').forEach(field => {
            field.addEventListener('input', function() {
                this.classList.remove('field-error', 'field-success');
            });
        });
        
        // Clear validation messages when user starts typing in any field
        document.getElementById('phone').addEventListener('input', function() {
            if (this.value.trim()) {
                hideValidationMessage('personalInfoValidation');
                clearFieldErrors('phone');
            }
        });
        
        document.getElementById('address').addEventListener('input', function() {
            if (this.value.trim()) {
                hideValidationMessage('personalInfoValidation');
                clearFieldErrors('address');
            }
        });
        
        document.getElementById('current_password').addEventListener('input', function() {
            if (this.value) {
                hideValidationMessage('securityValidation');
                clearFieldErrors('current_password');
            }
        });
    </script>
                                                                                                                                                                                        
