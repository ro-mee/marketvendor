<?php
session_start();
require_once 'config/database.php';
require_once 'includes/late_fee_functions.php';

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$lateFeeManager = new LateFeeManager();

$user_id = $_SESSION['user_id'];

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_payment') {
    try {
        $loan_id = $_POST['loan_id'] ?? '';
        $payment_amount = floatval($_POST['payment_amount']);
        $payment_method = $_POST['payment_method'] ?? '';
        $reference_number = $_POST['reference_number'] ?? '';
        $payment_notes = $_POST['payment_notes'] ?? '';
        
        // Handle file upload
        $screenshot_path = '';
        if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] == 0) {
            $file = $_FILES['payment_screenshot'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file_ext, $allowed_types)) {
                $payment_error = "Invalid file type. Only JPG, JPEG, PNG, and PDF files are allowed.";
            } elseif ($file['size'] > $max_size) {
                $payment_error = "File size too large. Maximum size is 5MB.";
            } else {
                // Create uploads directory if it doesn't exist
                $upload_dir = 'uploads/payment_proofs/' . date('Y/m/');
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate unique filename
                $filename = $loan_id . '_' . time() . '.' . $file_ext;
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $screenshot_path = $filepath;
                } else {
                    $payment_error = "Failed to upload screenshot. Please try again.";
                }
            }
        } else {
            $payment_error = "Payment screenshot is required for verification.";
        }
        
        if (empty($loan_id)) {
            $payment_error = "Loan ID is required.";
        } elseif ($payment_amount <= 0) {
            $payment_error = "Payment amount must be greater than 0.";
        } elseif (empty($reference_number)) {
            $payment_error = "Reference number is required for verification.";
        } elseif (empty($screenshot_path)) {
            $payment_error = "Payment screenshot is required for verification.";
        } else {
            // Get loan details
            $loan_sql = "SELECT * FROM loans WHERE loan_id = ? AND user_id = ? AND status IN ('approved', 'active')";
            $loan_stmt = $db->prepare($loan_sql);
            $loan_stmt->execute([$loan_id, $user_id]);
            $loan = $loan_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$loan) {
                $payment_error = "Invalid loan or loan not eligible for payment.";
            } else {
                // Calculate remaining balance and include pending late fees
                $paid_sql = "SELECT SUM(amount_paid) as total_paid FROM payment_history WHERE loan_id = ? AND status = 'paid'";
                $paid_stmt = $db->prepare($paid_sql);
                $paid_stmt->execute([$loan_id]);
                $paid_result = $paid_stmt->fetch(PDO::FETCH_ASSOC);
                $paid_amount = $paid_result['total_paid'] ?? 0;
                $remaining_balance = $loan['loan_amount'] - $paid_amount;
                
                // Get pending late fees for this loan (with fallback)
                $pending_late_fees = $lateFeeManager->getPendingFeesByLoan($loan_id);
                $total_pending_fees = empty($pending_late_fees) ? 0 : array_sum(array_column($pending_late_fees, 'fee_amount'));
                
                // Maximum payment allowed = remaining balance + pending late fees
                $max_payment = $remaining_balance + $total_pending_fees;
                
                if ($payment_amount > $max_payment) {
                    $payment_error = "Payment amount cannot exceed remaining balance of ₱" . number_format($max_payment, 2);
                } else {
                    // Generate payment ID
                    $payment_id = 'PAY' . date('Y') . str_pad($user_id, 4, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                    
                    // Insert payment record with 'pending' status (ensure positive amounts)
                    $insert_sql = "INSERT INTO payment_history (payment_id, loan_id, user_id, borrower_name, payment_date, amount_paid, principal_paid, interest_paid, payment_method, status, reference_number, payment_notes, screenshot_path, verification_status) VALUES (?, ?, ?, ?, CURRENT_DATE, ?, ?, ?, ?, 'pending', ?, ?, ?, 'pending_verification')";
                    $insert_stmt = $db->prepare($insert_sql);
                    
                    // Calculate principal and interest (80% principal, 20% interest of payment amount)
                    $principal_paid = abs($payment_amount) * 0.8;
                    $interest_paid = abs($payment_amount) * 0.2;
                    
                    $insert_result = $insert_stmt->execute([
                        $payment_id,
                        $loan_id,
                        $_SESSION['user_id'],
                        $loan['full_name'],
                        abs($payment_amount), // Ensure positive amount
                        $principal_paid,
                        $interest_paid,
                        $payment_method,
                        $reference_number,
                        $payment_notes,
                        $screenshot_path,
                        'pending_verification'
                    ]);
                    if ($insert_result) {
                        $payment_success = "Payment submitted successfully! Your payment (ID: $payment_id) is now under verification and will be reflected in your balance within 3-4 hours.";
                        
                        // Apply any pending late fees for this payment
                        $late_fees = $lateFeeManager->getLateFeesByLoan($loan_id);
                        foreach ($late_fees as $fee) {
                            if ($fee['status'] === 'pending') {
                                $lateFeeManager->applyLateFee($fee['id'], $payment_id);
                            }
                        }
                        
                        // Schedule verification (in a real system, this would be a cron job)
                        $verification_time = date('Y-m-d H:i:s', strtotime('+3 hours'));
                        $schedule_sql = "INSERT INTO payment_verification_queue (payment_id, scheduled_verification_time) VALUES (?, ?)";
                        $schedule_stmt = $db->prepare($schedule_sql);
                        $schedule_stmt->execute([$payment_id, $verification_time]);
                    } else {
                        $payment_error = "Failed to submit payment. Please try again.";
                    }
                }
            }
        }
    } catch (Exception $e) {
        $payment_error = "Error processing payment: " . $e->getMessage();
    }
}

// Get user's active loans for payment form
$stmt = $db->prepare("SELECT loan_id, full_name, loan_amount, status, created_at FROM loans WHERE user_id = ? AND status IN ('approved', 'active') ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$active_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending payments (for display)
$stmt = $db->prepare("SELECT * FROM payment_history WHERE user_id = ? AND status = 'pending' AND verification_status = 'pending_verification' ORDER BY payment_date DESC LIMIT 5");
$stmt->execute([$user_id]);
$pending_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user info for header
$stmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Process - Market Vendor Loan Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="enhanced-styles.css">
    <style>
        :root {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
            --border: #334155;
            --accent: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
        }

        body {
            background: linear-gradient(135deg, var(--bg-primary) 0%, #1a1f3a 100%);
            color: var(--text-primary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .payment-process-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .process-header {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .process-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), #2563eb, #1d4ed8);
        }

        .process-header h2 {
            color: var(--text-primary);
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .process-header p {
            color: var(--text-secondary);
            font-size: 1.2rem;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
        }

        .verification-notice {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.1));
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.1);
        }

        .verification-notice i {
            font-size: 2.5rem;
            color: var(--warning);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .verification-notice-content h4 {
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 1.2rem;
        }

        .verification-notice-content p {
            color: var(--text-secondary);
            margin: 0;
            line-height: 1.5;
        }

        .payment-form-section {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, #1a2332 100%);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .form-section-title {
            color: var(--text-primary);
            font-size: 1.5rem;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 16px;
            font-weight: 600;
        }

        .form-section-title i {
            color: var(--accent);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 14px 18px;
            background: rgba(15, 23, 42, 0.6);
            border: 2px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            transform: translateY(-2px);
        }

        .form-group small {
            color: var(--text-tertiary);
            font-size: 0.85rem;
            margin-top: 6px;
        }

        .payment-instructions {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.6), rgba(30, 41, 59, 0.6));
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-top: 10px;
        }

        .payment-instructions p {
            color: var(--text-primary);
            margin-bottom: 12px;
        }

        .payment-instructions ul {
            color: var(--text-secondary);
            margin: 12px 0;
            padding-left: 20px;
        }

        .payment-instructions li {
            margin-bottom: 8px;
        }

        .payment-instructions em {
            color: var(--text-tertiary);
            font-size: 0.9rem;
        }

        .payment-methods-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .method-card {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8), rgba(30, 41, 59, 0.8));
            border: 2px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .method-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .method-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.5);
        }

        .method-card.selected {
            border-color: var(--success);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
        }

        .method-card.selected::before {
            opacity: 1;
        }

        .method-card i {
            font-size: 2.5rem;
            margin-bottom: 16px;
            color: var(--success);
            position: relative;
            z-index: 1;
        }

        .method-card h4 {
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
        }

        .method-card p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .file-upload-container {
            position: relative;
            margin-top: 10px;
        }

        .file-upload-container input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            z-index: 2;
        }

        .file-upload-info {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.6), rgba(30, 41, 59, 0.6));
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-info:hover {
            border-color: var(--accent);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8), rgba(30, 41, 59, 0.8));
        }

        .file-upload-info i {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 16px;
            display: block;
        }

        .file-upload-info p {
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .file-upload-info small {
            color: var(--text-tertiary);
            font-size: 0.9rem;
        }

        .file-preview {
            position: relative;
            margin-top: 16px;
            border-radius: 12px;
            overflow: hidden;
            background: rgba(15, 23, 42, 0.8);
            border: 2px solid var(--border);
        }

        .file-preview img {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            display: block;
        }

        .remove-file {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 3;
        }

        .remove-file:hover {
            background: rgba(220, 38, 38, 0.9);
            transform: scale(1.1);
        }

        .form-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #2563eb);
            color: white;
            border: none;
            padding: 16px 36px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-secondary);
            border: 2px solid var(--border);
            padding: 16px 36px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .btn-secondary:hover {
            background: rgba(107, 114, 128, 0.1);
            color: var(--text-primary);
            border-color: rgba(107, 114, 128, 0.5);
            transform: translateY(-2px);
        }

        .pending-payments {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, #1a2332 100%);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .pending-payments h3 {
            color: var(--text-primary);
            font-size: 1.5rem;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            font-weight: 600;
        }

        .pending-payments h3 i {
            color: var(--warning);
        }

        .payment-item {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8), rgba(30, 41, 59, 0.8));
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .payment-item:hover {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.9));
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .payment-info h4 {
            color: var(--text-primary);
            margin-bottom: 12px;
            font-size: 1.1rem;
        }

        .payment-info p {
            color: var(--text-secondary);
            margin: 6px 0;
            font-size: 0.95rem;
        }

        .verification-status {
            text-align: right;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .status-pending {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.2));
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.3);
            animation: pulse 2s infinite;
        }

        .alert {
            padding: 20px 24px;
            border-radius: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 1rem;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--success);
        }

        .alert-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2));
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--error);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
            color: var(--text-tertiary);
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 12px;
            color: var(--text-primary);
        }

        @media (max-width: 768px) {
            .payment-process-container {
                padding: 10px;
            }
            
            .process-header {
                padding: 30px 20px;
            }
            
            .process-header h2 {
                font-size: 2rem;
            }
            
            .payment-form-section {
                padding: 30px 20px;
            }
            
            .pending-payments {
                padding: 30px 20px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .payment-type-grid {
                grid-template-columns: 1fr;
            }
            
            .payment-methods-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .payment-item {
                flex-direction: column;
                gap: 20px;
                text-align: left;
            }
            
            .verification-status {
                text-align: left;
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
            <a class="nav-item" href="my-loans.php">My Loans</a>
            <a class="nav-item" href="apply-loan.php">Apply Loan</a>
            <a class="nav-item active" href="payment-process.php">Payment Process</a>
            <a class="nav-item" href="payments.php">Payment Schedule</a>
            <a class="nav-item" href="client-payment-history.php">Payment History</a>
            <a class="nav-item" href="profile.php">Profile</a>
        </div>

        <!-- Main Content -->
        <div class="content-wrap">
            <div class="dashboard-header">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user_info['name'], 0, 2)); ?>
                    </div>
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($user_info['name']); ?></h3>
                        <p>Vendor</p>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <header class="header">
                <div>
                    <h2>Payment Process</h2>
                    <p>Submit your payment for verification and processing</p>
                </div>
            </header>

            <main class="main-content">
                <div class="payment-process-container">
                    <div class="process-header">
                        <h2><i class="fas fa-credit-card"></i> Payment Processing Center</h2>
                        <p>Secure payment submission with automated verification. All payments are verified before reflecting in your account balance.</p>
                    </div>

                    <div class="verification-notice">
                        <i class="fas fa-clock"></i>
                        <div class="verification-notice-content">
                            <h4>Verification Process</h4>
                            <p>All submitted payments undergo a 3-4 hour verification process to ensure security and prevent fraud. Your payment will be reflected in your balance once verification is complete.</p>
                        </div>
                    </div>

                    <?php if (isset($payment_success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($payment_success); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($payment_error)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($payment_error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (count($active_loans) > 0): ?>
                    <div class="payment-form-section">
                        <h3 class="form-section-title"><i class="fas fa-edit"></i> Submit Payment</h3>
                        
                        <form method="POST" id="paymentForm" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="process_payment">
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="loan_id">Select Loan</label>
                                    <select id="loan_id" name="loan_id" required onchange="updateLoanInfo()">
                                        <option value="">Select loan to pay</option>
                                        <?php foreach ($active_loans as $loan): ?>
                                            <?php
                                            // Get remaining balance for this loan
                                            $paid_sql = "SELECT SUM(amount_paid) as total_paid FROM payment_history WHERE loan_id = ? AND status = 'paid'";
                                            $paid_stmt = $db->prepare($paid_sql);
                                            $paid_stmt->execute([$loan['loan_id']]);
                                            $paid_result = $paid_stmt->fetch(PDO::FETCH_ASSOC);
                                            $loan_paid = $paid_result['total_paid'] ?? 0;
                                            $loan_remaining = $loan['loan_amount'] - $loan_paid;
                                            ?>
                                            <option value="<?php echo $loan['loan_id']; ?>" 
                                                    data-max="<?php echo $loan_remaining; ?>"
                                                    data-amount="<?php echo $loan['loan_amount']; ?>"
                                                    data-paid="<?php echo $loan_paid; ?>">
                                                <?php echo htmlspecialchars($loan['loan_id']); ?> - 
                                                ₱<?php echo number_format($loan_remaining, 2); ?> remaining
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="payment_amount">Payment Amount</label>
                                    <input type="number" id="payment_amount" name="payment_amount" 
                                           step="0.01" min="1" placeholder="Enter amount" required>
                                    <small id="maxAmountInfo">Select a loan to see maximum amount</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Payment Method</label>
                                <div class="payment-methods-grid">
                                    <div class="method-card" data-method="gcash">
                                        <i class="fas fa-mobile-alt"></i>
                                        <h4>GCash</h4>
                                        <p>0912-345-6789</p>
                                    </div>
                                    <div class="method-card" data-method="maya">
                                        <i class="fas fa-wallet"></i>
                                        <h4>Maya</h4>
                                        <p>0912-345-6789</p>
                                    </div>
                                    <div class="method-card" data-method="bank_transfer">
                                        <i class="fas fa-university"></i>
                                        <h4>Bank Transfer</h4>
                                        <p>BPI Account 1234-5678-90</p>
                                    </div>
                                    <div class="method-card" data-method="cash">
                                        <i class="fas fa-money-bill"></i>
                                        <h4>Cash</h4>
                                        <p>Visit our office</p>
                                    </div>
                                </div>
                                <input type="hidden" id="payment_method" name="payment_method">
                            </div>

                            <div class="form-group">
                                <label for="payment_screenshot">Payment Screenshot *</label>
                                <div class="file-upload-container">
                                    <input type="file" id="payment_screenshot" name="payment_screenshot" 
                                           accept="image/jpeg,image/jpg,image/png,application/pdf" required>
                                    <div class="file-upload-info">
                                        <i class="fas fa-camera"></i>
                                        <p>Upload payment screenshot or receipt</p>
                                        <small>Accepted formats: JPG, JPEG, PNG, PDF (Max 5MB)</small>
                                    </div>
                                </div>
                                <div id="filePreview" class="file-preview" style="display: none;">
                                    <img id="previewImage" src="" alt="Payment screenshot">
                                    <button type="button" class="remove-file" onclick="removeFile()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="reference_number">Reference Number *</label>
                                    <input type="text" id="reference_number" name="reference_number" 
                                           placeholder="Transaction ID, Receipt #, etc." required>
                                    <small>Required for payment verification</small>
                                </div>
                                <div class="form-group">
                                    <label for="payment_notes">Notes (Optional)</label>
                                    <textarea id="payment_notes" name="payment_notes" rows="2" 
                                              placeholder="Add any additional information..."></textarea>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-paper-plane"></i> Submit Payment
                                </button>
                                <button type="button" class="btn-secondary" onclick="location.reload()">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-exclamation-circle"></i>
                            <h3>No Active Loans</h3>
                            <p>You don't have any active loans that require payment.</p>
                            <a href="my-loans.php" class="btn-primary">
                                <i class="fas fa-list"></i> View My Loans
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (count($pending_payments) > 0): ?>
                    <div class="pending-payments">
                        <h3><i class="fas fa-hourglass-half"></i> Pending Verifications</h3>
                        <?php foreach ($pending_payments as $payment): ?>
                            <div class="payment-item">
                                <div class="payment-info">
                                    <h4>Payment ID: <?php echo htmlspecialchars($payment['payment_id']); ?></h4>
                                    <p>Amount: ₱<?php echo number_format($payment['amount_paid'], 2); ?></p>
                                    <p>Method: <?php echo htmlspecialchars(ucfirst($payment['payment_method'])); ?></p>
                                    <p>Reference: <?php echo htmlspecialchars($payment['reference_number']); ?></p>
                                    <p>Submitted: <?php echo date('M d, Y h:i A', strtotime($payment['payment_date'])); ?></p>
                                </div>
                                <div class="verification-status">
                                    <span class="status-badge status-pending">Pending Verification</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        function updateLoanInfo() {
            const select = document.getElementById('loan_id');
            const amountInput = document.getElementById('payment_amount');
            const maxInfo = document.getElementById('maxAmountInfo');
            
            if (select.value) {
                const selectedOption = select.options[select.selectedIndex];
                const maxAmount = parseFloat(selectedOption.dataset.max);
                const loanAmount = parseFloat(selectedOption.dataset.amount);
                const paidAmount = parseFloat(selectedOption.dataset.paid);
                
                amountInput.max = maxAmount;
                maxInfo.textContent = `Maximum: ₱${maxAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) (Loan: ₱${loanAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) - Paid: ₱${paidAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})})`;
            } else {
                amountInput.max = '';
                maxInfo.textContent = 'Select a loan to see maximum amount';
            }
        }

        // Payment method selection
        document.querySelectorAll('.method-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.method-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('payment_method').value = this.dataset.method;
            });
        });

        // File upload functionality
        document.getElementById('payment_screenshot').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type. Only JPG, JPEG, PNG, and PDF files are allowed.');
                    e.target.value = '';
                    return;
                }
                
                if (file.size > maxSize) {
                    alert('File size too large. Maximum size is 5MB.');
                    e.target.value = '';
                    return;
                }
                
                // Show preview
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('previewImage').src = e.target.result;
                        document.getElementById('filePreview').style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    // For PDF files, show a placeholder
                    document.getElementById('previewImage').src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTE0IDJINkM0Ljg5NTQzIDIgNCAyLjg5NTQzIDQgNFYyMEM0IDIxLjEwNDYgNC44OTU0MyAyMiA2IDIySDE0QzE1LjEwNDYgMjIgMTYgMjEuMTA0NiAxNiAyMFY0QzE2IDIuODk1NDMgMTUuMTA0NiAyIDE0IDJaIiBzdHJva2U9IiM5Q0EzQUYiIHN0cm9rZS13aWR0aD0iMiIvPgo8cGF0aCBkPSJNOCAxNkgxNk04IDE2WiIgZmlsbD0iIzlDQTNBRiIvPgo8L3N2Zz4K';
                    document.getElementById('filePreview').style.display = 'block';
                }
                
                // Update upload info
                const uploadInfo = document.querySelector('.file-upload-info p');
                uploadInfo.textContent = file.name;
                const uploadSmall = document.querySelector('.file-upload-info small');
                uploadSmall.textContent = `Size: ${(file.size / 1024 / 1024).toFixed(2)} MB`;
            }
        });

        function removeFile() {
            document.getElementById('payment_screenshot').value = '';
            document.getElementById('filePreview').style.display = 'none';
            document.getElementById('previewImage').src = '';
            
            // Reset upload info
            const uploadInfo = document.querySelector('.file-upload-info p');
            uploadInfo.textContent = 'Upload payment screenshot or receipt';
            const uploadSmall = document.querySelector('.file-upload-info small');
            uploadSmall.textContent = 'Accepted formats: JPG, JPEG, PNG, PDF (Max 5MB)';
        }

        // Form validation
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const loanId = document.getElementById('loan_id').value;
            const paymentAmount = document.getElementById('payment_amount').value;
            const paymentMethod = document.getElementById('payment_method').value;
            const referenceNumber = document.getElementById('reference_number').value;
            const screenshotFile = document.getElementById('payment_screenshot').files[0];
            
            if (!loanId) {
                e.preventDefault();
                alert('Please select a loan to pay.');
                return;
            }
            
            if (!paymentAmount || paymentAmount <= 0) {
                e.preventDefault();
                alert('Please enter a valid payment amount.');
                return;
            }
            
            if (!paymentMethod) {
                e.preventDefault();
                alert('Please select a payment method.');
                return;
            }
            
            if (!referenceNumber) {
                e.preventDefault();
                alert('Reference number is required for payment verification.');
                return;
            }
            
            if (!screenshotFile) {
                e.preventDefault();
                alert('Payment screenshot is required for verification.');
                return;
            }
        });
    </script>
</body>
</html>
