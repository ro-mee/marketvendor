<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';
require_once 'includes/loan_functions.php';
require_once 'includes/late_fee_functions.php';

$loanManager = new LoanManager();
$lateFeeManager = new LateFeeManager();
$loan_id = $_GET['loan_id'] ?? '';
$payment_id = $_GET['payment_id'] ?? '';
$database = new Database();
$db = $database->getConnection();

// Get user's active loans with pending payments
$stmt = $db->prepare("SELECT l.*, ps.payment_id, ps.total_amount, ps.due_date, ps.status as payment_status 
    FROM loans l 
    LEFT JOIN payment_schedules ps ON l.loan_id = ps.loan_id AND ps.status = 'pending'
    WHERE l.user_id = ? AND l.status = 'active'
    ORDER BY ps.due_date ASC");
$stmt->execute([$_SESSION['user_id']]);
$loans_with_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by loan and get pending payments
$available_payments = [];
foreach ($loans_with_payments as $row) {
    if (!isset($available_payments[$row['loan_id']])) {
        $available_payments[$row['loan_id']] = [
            'loan_id' => $row['loan_id'],
            'full_name' => $row['full_name'],
            'loan_amount' => $row['loan_amount'],
            'remaining_balance' => $row['remaining_balance'],
            'status' => $row['status'],
            'pending_payments' => []
        ];
    }
    
    if ($row['payment_id']) {
        $available_payments[$row['loan_id']]['pending_payments'][] = [
            'payment_id' => $row['payment_id'],
            'total_amount' => $row['total_amount'],
            'due_date' => $row['due_date'],
            'payment_status' => $row['payment_status']
        ];
    }
}

// Pagination for payment schedule
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Count total pending payments for pagination
$total_pending_payments = 0;
foreach ($available_payments as $loan) {
    $total_pending_payments += count($loan['pending_payments']);
}
$total_pages = ceil($total_pending_payments / $per_page);

// If specific loan_id and payment_id provided, get payment details
$payment = null;
if ($loan_id && $payment_id) {
    $stmt = $db->prepare("SELECT ps.*, l.full_name, l.loan_amount, l.remaining_balance 
        FROM payment_schedules ps 
        JOIN loans l ON ps.loan_id = l.loan_id 
        WHERE ps.payment_id = ? AND ps.loan_id = ? AND ps.user_id = ? AND ps.status = 'pending'");
    $stmt->execute([$payment_id, $loan_id, $_SESSION['user_id']]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        $_SESSION['error_message'] = "Payment not found or already paid";
        header("Location: make-payment.php");
        exit();
    }
}

// Process payment form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    $payment_amount = floatval($_POST['payment_amount'] ?? 0);
    $selected_loan_id = $_POST['loan_id'] ?? '';
    $selected_payment_id = $_POST['payment_id'] ?? '';
    
    if ($payment_amount <= 0) {
        $error = "Payment amount must be greater than 0";
    } elseif (!$selected_loan_id || !$selected_payment_id) {
        $error = "Please select a loan and payment";
    } else {
        // Get payment details for validation
        $stmt = $db->prepare("SELECT total_amount, due_date FROM payment_schedules WHERE payment_id = ? AND loan_id = ? AND user_id = ? AND status = 'pending'");
        $stmt->execute([$selected_payment_id, $selected_loan_id, $_SESSION['user_id']]);
        $payment_details = $stmt->fetch();
        
        if (!$payment_details) {
            $error = "Invalid payment selection";
        } else {
            // Get loan info for receipt
            $loan_stmt = $db->prepare("SELECT l.*, u.name as client_name, u.email FROM loans l LEFT JOIN users u ON l.user_id = u.id WHERE l.loan_id = ? AND l.user_id = ?");
            $loan_stmt->execute([$selected_loan_id, $_SESSION['user_id']]);
            $loan_info = $loan_stmt->fetch(PDO::FETCH_ASSOC);
            
            $allowed_amount = $payment_details['total_amount'];
            
            // Calculate late fee if overdue
            $due_date = new DateTime($payment_details['due_date']);
            $today = new DateTime();
            
            if ($due_date < $today) {
                $days_overdue = $today->diff($due_date)->days;
                
                $late_fee_calculation = $lateFeeManager->calculateLateFee(
                    $payment_details['total_amount'],
                    $days_overdue,
                    $selected_payment_id
                );
                
                if (is_array($late_fee_calculation) && $late_fee_calculation['fee_amount'] > 0) {
                    $allowed_amount += $late_fee_calculation['fee_amount'];
                }
            }
            
            if ($payment_amount > $allowed_amount) {
                $error = "Payment amount cannot exceed total due (including late fees)";
            } else {
                $result = $loanManager->processPayment($selected_loan_id, $selected_payment_id, $payment_amount, $payment_method);
                
                if ($result['success']) {
                    // Generate receipt number
                    $receipt_number = 'RCP' . date('YmdHis') . rand(1000, 9999);
                    $payment_id = 'PAY' . date('YmdHis') . rand(1000, 9999);
                    
                    // Calculate principal and interest (5% annual rate)
                    $payment_amount = $payment_amount;
                    $annualRate = 0.05;
                    $dailyRate = $annualRate / 365;
                    $interest = round($payment_amount * ($dailyRate / (1 + $dailyRate)) * 100) / 100;
                    $principal = round(($payment_amount - $interest) * 100) / 100;
                    
                    // Get late fees for this payment
                    $late_fees = 0;
                    try {
                        $late_fee_stmt = $db->prepare("
                            SELECT SUM(fee_amount) as total_late_fees
                            FROM late_fees 
                            WHERE loan_id = ? AND status = 'pending'
                        ");
                        $late_fee_stmt->execute([$selected_loan_id]);
                        $late_fee_result = $late_fee_stmt->fetch(PDO::FETCH_ASSOC);
                        $late_fees = $late_fee_result['total_late_fees'] ?? 0;
                    } catch (Exception $e) {
                        $late_fees = 0;
                    }
                    
                    // Store receipt data for display
                    $_SESSION['receipt_data'] = [
                        'receipt_number' => $receipt_number,
                        'payment_id' => $payment_id,
                        'loan_id' => $selected_loan_id,
                        'client_name' => $loan_info['client_name'],
                        'client_email' => $loan_info['email'],
                        'payment_amount' => $payment_amount,
                        'principal_paid' => $principal,
                        'interest_paid' => $interest,
                        'late_fees' => $late_fees,
                        'payment_method' => $payment_method,
                        'payment_date' => date('Y-m-d H:i:s'),
                        'notes' => 'Payment processed successfully',
                        'processed_by' => $_SESSION['user_name'] ?? 'Client'
                    ];
                
                $_SESSION['success_message'] = "✅ Payment successful! Transaction ID: " . $result['transaction_id'] . " | Amount: ₱" . number_format($payment_amount, 2);
                header("Location: make-payment.php");
                exit();
            } else {
                $error = $result['error'] ?? "Payment processing failed";
            }
        }
    }
}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment - Market Vendor Loan</title>
    <link rel="stylesheet" href="enhanced-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="responsive-styles-fixed.css">
    <style>
        /* Force logo size update */
        .brand-logo {
            width: 50px !important;
            height: 50px !important;
            border-radius: 8px !important;
            object-fit: cover !important;
        }
        
        /* Ensure brand layout is side by side */
        .brand {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
        }
        
        .brand-content {
            flex: 1 !important;
        }
        
        /* Brand text styling */
        .brand-content h1 {
            font-size: 1rem;
            margin-bottom: 4px;
            font-weight: 600;
            color: var(--text-100);
        }
        
        .brand-content p {
            color: var(--text-300);
            font-size: .8rem;
            margin: 0;
        }

        /* Better viewport handling */
        html, body {
            height: 100%;
            overflow-x: hidden;
        }
        
        body {
            min-height: 100vh;
        }
        
        .layout {
            min-height: 100vh;
        }
        
        .content-wrap {
            padding-bottom: 20px;
            overflow-x: hidden;
        }
        
        .main-content {
            padding: 20px;
            max-width: 100%;
            box-sizing: border-box;
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
        
        /* Header Styles - Match payments.php exactly */
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
        
        .header {
            position: sticky;
            top: 0;
            z-index: 999;
            padding: 16px 26px;
            border-bottom: 1px solid var(--line);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        
        .header h2 { 
            font-size: 1.15rem; 
            margin: 0;
        }
        
        .header p { 
            color: var(--text-300); 
            font-size: .85rem; 
            margin-top: 2px; 
            margin-bottom: 0;
        }
        
        .payment-container {
            width: 100%;
            margin: 0;
            padding: 0;
            max-width: 100%;
        }
        
        .payment-card {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid var(--line);
            border-radius: var(--card-radius);
            padding: 24px;
            margin-bottom: 30px;
            max-width: 100%;
            box-sizing: border-box;
        }
        
        .payment-header {
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--line);
        }
        
        .payment-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-100);
            margin-bottom: 8px;
        }
        
        .payment-header p {
            color: var(--text-300);
            font-size: 0.9rem;
        }
        
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .payment-details {
            margin-bottom: 25px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--line);
        }
        
        .detail-row:last-child {
            border-bottom: none;
            padding-top: 20px;
            margin-top: 10px;
        }
        
        .detail-label {
            color: var(--text-300);
            font-size: 1rem;
        }
        
        .detail-value {
            color: var(--text-100);
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .amount-due {
            color: var(--success);
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: var(--text-100);
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            background: rgba(15, 39, 75, 0.8);
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--text-100);
            font-size: 1rem;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .method-option {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .method-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .method-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background: linear-gradient(145deg, rgba(15, 39, 75, 0.9), rgba(30, 41, 59, 0.8));
            border: 2px solid var(--line);
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--text-300);
            font-weight: 600;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
            min-height: 120px;
        }
        
        .method-option label::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(145deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.05));
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 0;
        }
        
        .method-option label::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.3) 0%, transparent 70%);
            opacity: 0;
            transition: all 0.6s ease;
            transform: scale(0);
            z-index: 0;
        }
        
        .method-option input[type="radio"]:checked + label {
            background: linear-gradient(145deg, rgba(59, 130, 246, 0.25), rgba(37, 99, 235, 0.15));
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(59, 130, 246, 0.3), 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        
        .method-option input[type="radio"]:checked + label::before {
            opacity: 1;
        }
        
        .method-option input[type="radio"]:checked + label::after {
            opacity: 1;
            transform: scale(1);
        }
        
        .method-option label:hover {
            background: linear-gradient(145deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.08));
            border-color: rgba(59, 130, 246, 0.5);
            color: var(--text-100);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        
        .method-option label:hover::before {
            opacity: 0.7;
        }
        
        .method-option i {
            display: block;
            font-size: 2.5rem;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .method-option input[type="radio"]:checked + label i {
            transform: scale(1.1);
            filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.5));
        }
        
        .method-option label:hover i {
            transform: scale(1.05);
        }
        
        .method-option .method-name {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 4px;
            position: relative;
            z-index: 1;
        }
        
        .method-option .method-description {
            font-size: 0.75rem;
            opacity: 0.7;
            position: relative;
            z-index: 1;
        }
        
        .method-option input[type="radio"]:checked + label .method-description {
            opacity: 0.9;
        }
        
        /* Payment method specific colors */
        .method-option:nth-child(1) input[type="radio"]:checked + label {
            border-color: #3b82f6;
            color: #3b82f6;
        }
        
        .method-option:nth-child(2) input[type="radio"]:checked + label {
            border-color: #10b981;
            color: #10b981;
        }
        
        .method-option:nth-child(3) input[type="radio"]:checked + label {
            border-color: #f59e0b;
            color: #f59e0b;
        }
        
        /* Enhanced selection indicator */
        .method-option input[type="radio"]:checked + label::before {
            content: '✓';
            position: absolute;
            top: 8px;
            right: 8px;
            width: 24px;
            height: 24px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 2;
            opacity: 1;
            transform: scale(1);
            animation: checkmarkPop 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        @keyframes checkmarkPop {
            0% {
                transform: scale(0) rotate(-180deg);
                opacity: 0;
            }
            50% {
                transform: scale(1.2) rotate(10deg);
            }
            100% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }
        
        /* Loading state for payment methods */
        .method-option.loading label {
            pointer-events: none;
            opacity: 0.6;
        }
        
        .method-option.loading label::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            transform: translate(-50%, -50%);
            z-index: 3;
        }
        
        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .payment-methods {
                grid-template-columns: repeat(2, 1fr);
                gap: 18px;
            }
        }
        
        @media (max-width: 768px) {
            .payment-methods {
                grid-template-columns: 1fr;
                gap: 16px;
                margin-bottom: 20px;
            }
            
            .method-option label {
                padding: 20px 16px;
                min-height: 100px;
            }
            
            .method-option i {
                font-size: 2rem;
                margin-bottom: 10px;
            }
            
            .payment-card {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .pay-button {
                padding: 12px 24px;
                font-size: 1rem;
                margin-top: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .payment-methods {
                gap: 12px;
                margin-bottom: 16px;
            }
            
            .method-option label {
                padding: 16px 12px;
                min-height: 90px;
            }
            
            .method-option i {
                font-size: 1.8rem;
                margin-bottom: 8px;
            }
            
            .method-option .method-name {
                font-size: 0.9rem;
            }
            
            .method-option .method-description {
                font-size: 0.7rem;
            }
            
            .payment-card {
                padding: 16px;
                margin-bottom: 16px;
            }
            
            .payment-header {
                margin-bottom: 20px;
                padding-bottom: 12px;
            }
            
            .payment-header h2 {
                font-size: 1.1rem;
            }
            
            .payment-header p {
                font-size: 0.85rem;
            }
            
            .form-group {
                margin-bottom: 16px;
            }
            
            .form-group label {
                font-size: 0.9rem;
                margin-bottom: 6px;
            }
            
            .form-group input,
            .form-group select {
                padding: 10px;
                font-size: 0.9rem;
            }
            
            .pay-button {
                padding: 14px 20px;
                font-size: 0.95rem;
                width: 100%;
                margin-top: 16px;
            }
            
            .payment-details {
                margin-bottom: 20px;
            }
            
            .detail-row {
                padding: 12px 0;
            }
            
            .detail-label {
                font-size: 0.9rem;
            }
            
            .detail-value {
                font-size: 1rem;
            }
            
            .amount-due {
                font-size: 1.3rem;
            }
        }
        
        /* Enhanced Sticky Button System */
        .payment-form-wrapper {
            position: relative;
            padding-bottom: 100px;
            min-height: 100%;
        }
        
        .sticky-payment-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to up, rgba(15, 23, 42, 0.98), rgba(15, 23, 42, 0.95));
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--line);
            padding: 20px;
            z-index: 1000;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .sticky-payment-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        
        .payment-summary {
            display: flex;
            align-items: center;
            gap: 20px;
            color: var(--text-100);
        }
        
        .payment-amount-display {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .payment-amount-label {
            font-size: 0.75rem;
            color: var(--text-300);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        
        .payment-amount-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--success);
        }
        
        .payment-method-display {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 8px;
            font-size: 0.85rem;
            color: var(--primary);
        }
        
        .sticky-pay-button {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            position: relative;
            overflow: hidden;
            min-width: 200px;
        }
        
        .sticky-pay-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .sticky-pay-button:hover::before {
            left: 100%;
        }
        
        .sticky-pay-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
            background: linear-gradient(135deg, #059669, #047857);
        }
        
        .sticky-pay-button:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .sticky-pay-button.loading {
            pointer-events: none;
            opacity: 0.8;
        }
        
        .sticky-pay-button.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            transform: translate(-50%, -50%);
        }
        
        .sticky-pay-button.loading .button-text {
            opacity: 0;
        }
        
        @keyframes slideUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        
        /* Hide original button when sticky is active */
        .original-pay-button {
            display: none;
        }
        
        /* Responsive sticky footer */
        @media (max-width: 768px) {
            .sticky-payment-footer {
                padding: 16px;
            }
            
            .sticky-payment-content {
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }
            
            .payment-summary {
                flex-direction: column;
                gap: 12px;
                width: 100%;
            }
            
            .payment-amount-display {
                align-items: center;
            }
            
            .sticky-pay-button {
                width: 100%;
                padding: 14px 24px;
                font-size: 0.95rem;
            }
        }
        
        @media (max-width: 480px) {
            .sticky-payment-footer {
                padding: 12px;
            }
            
            .payment-amount-value {
                font-size: 1.1rem;
            }
            
            .payment-method-display {
                font-size: 0.8rem;
                padding: 6px 10px;
            }
            
            .sticky-pay-button {
                padding: 12px 20px;
                font-size: 0.9rem;
            }
        }
        
        /* Scroll indicator */
        .scroll-indicator {
            position: fixed;
            bottom: 100px;
            right: 20px;
            width: 40px;
            height: 40px;
            background: rgba(59, 130, 246, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            opacity: 0;
            transform: scale(0);
            transition: all 0.3s ease;
            z-index: 999;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .scroll-indicator.show {
            opacity: 1;
            transform: scale(1);
        }
        
        .scroll-indicator:hover {
            background: var(--primary);
            transform: scale(1.1);
        }
        
        /* Extra small screens */
        @media (max-width: 360px) {
            .payment-methods {
                gap: 10px;
            }
            
            .method-option label {
                padding: 14px 10px;
                min-height: 80px;
            }
            
            .method-option i {
                font-size: 1.6rem;
                margin-bottom: 6px;
            }
            
            .method-option .method-name {
                font-size: 0.85rem;
            }
            
            .method-option .method-description {
                font-size: 0.65rem;
            }
            
            .payment-card {
                padding: 12px;
            }
            
            .pay-button {
                padding: 12px 16px;
                font-size: 0.9rem;
            }
        }
        
        .pay-button {
            background: var(--success);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .pay-button:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        
        .pay-button:disabled {
            background: #6b7280;
            cursor: not-allowed;
        }
        
        /* Table Styles - Match payments.php */
        .table-container {
            overflow-x: auto;
        }
        
        .payments-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .payments-table th,
        .payments-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid var(--line);
        }
        
        .payments-table th {
            background: rgba(15, 39, 75, 0.5);
            color: var(--text-200);
            font-weight: 500;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .payments-table td {
            color: var(--text-100);
            font-size: 0.9rem;
        }
        
        .payments-table tbody tr:hover {
            background: rgba(30, 41, 59, 0.5);
        }
        
        /* Modal Styles - Professional Enhancement */
        .modal {
            display: none;
            position: fixed;
            z-index: 5000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.8), rgba(15, 23, 42, 0.9));
            backdrop-filter: blur(20px);
            animation: modalFadeIn 0.3s ease-out;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                backdrop-filter: blur(0px);
            }
            to {
                opacity: 1;
                backdrop-filter: blur(20px);
            }
        }
        
        .modal-content {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.98), rgba(15, 23, 42, 0.95));
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 20px;
            margin: 5% auto;
            padding: 0;
            width: 90%;
            max-width: 520px;
            height: auto;
            max-height: 85vh;
            overflow: visible;
            animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(59, 130, 246, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
        }
        
        .modal-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(59, 130, 246, 0.5), 
                transparent
            );
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .modal-header {
            padding: 18px 20px;
            border-bottom: 1px solid rgba(59, 130, 246, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.05), 
                transparent
            );
            border-radius: 20px 20px 0 0;
            position: relative;
        }
        
        .modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 20px;
            right: 20px;
            height: 1px;
            background: linear-gradient(90deg, 
                rgba(59, 130, 246, 0.3), 
                transparent,
                rgba(59, 130, 246, 0.3)
            );
        }
        
        .modal-header h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-100);
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .modal-close {
            background: linear-gradient(145deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .modal-close::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .modal-close:hover::before {
            left: 100%;
        }
        
        .modal-close:hover {
            background: linear-gradient(145deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1));
            border-color: rgba(239, 68, 68, 0.3);
            color: #f87171;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .modal-body {
            padding: 20px;
            overflow: visible;
            flex: 1;
            max-height: calc(85vh - 80px);
            background: linear-gradient(180deg, 
                transparent,
                rgba(15, 23, 42, 0.3)
            );
        }
        
        /* Professional Modal Form Styles - Compact */
        .modal-body .form-group {
            margin-bottom: 16px;
            position: relative;
        }
        
        .modal-body .form-group label {
            display: block;
            color: #60a5fa;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .modal-body .form-group input {
            width: 100%;
            padding: 12px 14px;
            background: linear-gradient(145deg, 
                rgba(15, 39, 75, 0.9), 
                rgba(30, 41, 59, 0.8)
            );
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 10px;
            color: var(--text-100);
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        .modal-body .form-group input:focus {
            outline: none;
            border-color: rgba(59, 130, 246, 0.5);
            box-shadow: 
                0 0 0 3px rgba(59, 130, 246, 0.1),
                0 2px 8px rgba(59, 130, 246, 0.2);
            transform: translateY(-1px);
        }
        
        .modal-body .form-group input::placeholder {
            color: rgba(148, 163, 184, 0.5);
        }
        
        .modal-body .payment-methods {
            gap: 12px;
            margin-bottom: 16px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
        }
        
        .modal-body .method-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px 12px;
            background: linear-gradient(145deg, 
                rgba(15, 39, 75, 0.8), 
                rgba(30, 41, 59, 0.6)
            );
            border: 2px solid rgba(59, 130, 246, 0.2);
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--text-300);
            font-weight: 600;
            font-size: 0.85rem;
            position: relative;
            overflow: hidden;
            min-height: 75px;
        }
        
        .modal-body .method-option input[type="radio"]:checked + label {
            background: linear-gradient(145deg, 
                rgba(59, 130, 246, 0.25), 
                rgba(37, 99, 235, 0.15)
            );
            border-color: #3b82f6;
            color: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 
                0 6px 12px rgba(59, 130, 246, 0.3),
                0 0 0 2px rgba(59, 130, 246, 0.1);
        }
        
        .modal-body .method-option label:hover {
            background: linear-gradient(145deg, 
                rgba(59, 130, 246, 0.15), 
                rgba(37, 99, 235, 0.08)
            );
            border-color: rgba(59, 130, 246, 0.4);
            color: var(--text-100);
            transform: translateY(-1px);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
        }
        
        .modal-body .method-option i {
            display: block;
            font-size: 1.6rem;
            margin-bottom: 6px;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .modal-body .method-option input[type="radio"]:checked + label i {
            transform: scale(1.1);
            filter: drop-shadow(0 0 4px rgba(59, 130, 246, 0.5));
        }
        
        .modal-body .method-option label:hover i {
            transform: scale(1.05);
        }
        
        .modal-body .form-buttons {
            margin-top: 18px;
            gap: 12px;
            display: flex;
        }
        
        .modal-body .selected-payment-info {
            margin-bottom: 16px;
            padding: 12px;
            background: linear-gradient(145deg, 
                rgba(59, 130, 246, 0.05), 
                rgba(37, 99, 235, 0.02)
            );
            border: 1px solid rgba(59, 130, 246, 0.1);
            border-radius: 10px;
        }
        
        .modal-body .btn-pay {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            flex: 1;
        }
        
        .modal-body .btn-pay::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255, 255, 255, 0.2), 
                transparent
            );
            transition: left 0.5s ease;
        }
        
        .modal-body .btn-pay:hover::before {
            left: 100%;
        }
        
        .modal-body .btn-pay:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }
        
        .modal-body .btn-cancel {
            background: linear-gradient(145deg, 
                rgba(100, 116, 139, 0.8), 
                rgba(71, 85, 105, 0.6)
            );
            color: white;
            border: 1px solid rgba(100, 116, 139, 0.3);
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            flex: 1;
        }
        
        .modal-body .btn-cancel:hover {
            background: linear-gradient(145deg, 
                rgba(71, 85, 105, 0.8), 
                rgba(51, 65, 85, 0.6)
            );
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        }
        
        @media (max-width: 480px) {
            .modal-content {
                margin: 2% auto;
                width: 98%;
                max-height: 92vh;
            }
        }
        
        @media (max-height: 600px) {
            .modal-content {
                margin: 2% auto;
                max-height: 95vh;
            }
        }
        
        .btn-pay {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .btn-pay:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        
        .priority-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .priority-high {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        
        .priority-medium {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }
        
        .priority-low {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        
        .due-overdue {
            color: #ef4444;
            font-weight: 600;
        }
        
        .due-due-soon {
            color: #fbbf24;
            font-weight: 600;
        }
        
        .due-pending {
            color: var(--text-300);
        }
        
        .payment-form-section {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid var(--line);
            border-radius: var(--card-radius);
            padding: 24px;
            margin-top: 30px;
        }
        
        .payment-form-section h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-100);
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--line);
        }
        
        .selected-payment-info {
            margin-bottom: 25px;
        }
        
        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn-cancel {
            background: #64748b;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: #475569;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-badge.overdue {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        
        .status-badge.due-soon {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }
        
        .status-badge.pending {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-300);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .payments-table {
                font-size: 0.8rem;
            }
            
            .payments-table th,
            .payments-table td {
                padding: 8px;
            }
            
            .btn-pay {
                padding: 6px 12px;
                font-size: 0.75rem;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
        }
    .payments-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(30, 41, 59, 0.95);
            border-radius: var(--card-radius);
            overflow: hidden;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            padding: 16px;
            background: rgba(30, 41, 59, 0.6);
            border-radius: 12px;
        }

        .pagination-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
            border: 1px solid var(--primary);
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .pagination-btn:hover {
            background: rgba(59, 130, 246, 0.8);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .pagination-info {
            color: var(--text-100);
            font-size: 0.9rem;
            font-weight: 500;
            padding: 8px 16px;
            background: rgba(148, 163, 184, 0.1);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
        }

        .pagination-numbers {
            display: flex;
            gap: 8px;
        }

        .pagination-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(15, 39, 75, 0.6);
            color: var(--text-100);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .pagination-number:hover {
            background: rgba(59, 130, 246, 0.1);
            color: var(--text-100);
            border-color: var(--primary);
            transform: translateY(-1px);
        }

        .pagination-number.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            font-weight: 600;
        }    padding: 0 15px;
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
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
            <a class="nav-item active" href="make-payment.php">
                <i class="fas fa-credit-card"></i> Make Payment
            </a>
            <a class="nav-item" href="client-payment-history.php">
                <i class="fas fa-history"></i> Payment History
            </a>
            <a class="nav-item" href="profile.php">
                <i class="fas fa-user"></i> Profile
            </a>

        </aside>

            <div class="content-wrap">
                <div class="dashboard-header">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?>
                        </div>
                        <div class="user-details">
                            <h3><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                            <p>Vendor</p>
                        </div>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>

            <header class="header">
                <div>
                    <h2>Make Payment</h2>
                    <p>Process your loan payments securely and efficiently</p>
                </div>
            </header>

            <main class="main-content">

            <div class="payment-container">
                <?php if ($payment): ?>
                    <!-- Specific Payment Mode -->
                    <div class="payment-form-wrapper">
                        <div class="payment-card">
                            <div class="payment-header">
                                <h2>Make Payment</h2>
                                <p>Loan <?php echo htmlspecialchars($payment['loan_id']); ?></p>
                            </div>

                            <?php if (isset($error)): ?>
                                <div class="error-message">
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>

                            <div class="payment-details">
                                <div class="detail-row">
                                    <span class="detail-label">Borrower</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($payment['full_name']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Due Date</span>
                                    <span class="detail-value"><?php echo date('F d, Y', strtotime($payment['due_date'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Amount Due</span>
                                    <span class="detail-value amount-due">
                                        ₱<?php echo number_format($payment['total_amount'], 2); ?>
                                        <?php
                                        // Calculate and show late fees if overdue
                                        $due_date = new DateTime($payment['due_date']);
                                        $today = new DateTime();
                                        $days_overdue = 0;
                                        if ($due_date < $today) {
                                            $days_overdue = -($today->diff($due_date)->days);
                                            $late_fee_calculation = $lateFeeManager->calculateLateFee($payment['total_amount'], $days_overdue, $payment['payment_id']);
                                            if (is_array($late_fee_calculation) && $late_fee_calculation['fee_amount'] > 0) {
                                                echo '<br><small style="color: #ef4444;">+ Late Fee: ₱' . number_format($late_fee_calculation['fee_amount'], 2) . '</small>';
                                                echo '<br><strong style="color: #10b981;">Total: ₱' . number_format($payment['total_amount'] + $late_fee_calculation['fee_amount'], 2) . '</strong>';
                                            }
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <form method="POST" action="" id="paymentForm">
                                <input type="hidden" name="loan_id" value="<?php echo htmlspecialchars($payment['loan_id']); ?>">
                                <input type="hidden" name="payment_id" value="<?php echo htmlspecialchars($payment['payment_id']); ?>">
                                
                                <div class="form-group">
                                    <label for="payment_amount">Payment Amount (₱)</label>
                                    <?php
                                    // Calculate total amount with late fees
                                    $total_payment_amount = $payment['total_amount'];
                                    $due_date = new DateTime($payment['due_date']);
                                    $today = new DateTime();
                                    if ($due_date < $today) {
                                        $days_overdue = -($today->diff($due_date)->days);
                                        $late_fee_calculation = $lateFeeManager->calculateLateFee($payment['total_amount'], $days_overdue, $payment['payment_id']);
                                        if (is_array($late_fee_calculation) && $late_fee_calculation['fee_amount'] > 0) {
                                            $total_payment_amount += $late_fee_calculation['fee_amount'];
                                        }
                                    }
                                    ?>
                                    <input type="number" id="payment_amount" name="payment_amount" 
                                           value="<?php echo htmlspecialchars($total_payment_amount); ?>"
                                           min="0.01" max="<?php echo htmlspecialchars($total_payment_amount); ?>" 
                                           step="0.01" required>
                                    <?php if ($total_payment_amount > $payment['total_amount']): ?>
                                    <small style="color: #10b981; font-weight: 500;">Includes late fees</small>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label>Payment Method</label>
                                    <div class="payment-methods">
                                        <div class="method-option">
                                            <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer" checked>
                                            <label for="bank_transfer">
                                                <i class="fas fa-university"></i>
                                                <span class="method-name">Bank Transfer</span>
                                                <span class="method-description">Direct bank deposit</span>
                                            </label>
                                        </div>
                                        <div class="method-option">
                                            <input type="radio" id="gcash" name="payment_method" value="gcash">
                                            <label for="gcash">
                                                <i class="fas fa-mobile-alt"></i>
                                                <span class="method-name">GCash</span>
                                                <span class="method-description">Mobile wallet</span>
                                            </label>
                                        </div>
                                        <div class="method-option">
                                            <input type="radio" id="maya" name="payment_method" value="maya">
                                            <label for="maya">
                                                <i class="fas fa-wallet"></i>
                                                <span class="method-name">Maya</span>
                                                <span class="method-description">Digital wallet</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Original button (hidden when sticky is active) -->
                                <button type="submit" class="pay-button original-pay-button">
                                    <i class="fas fa-credit-card"></i> Process Payment
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Enhanced Sticky Payment Footer -->
                    <div class="sticky-payment-footer">
                        <div class="sticky-payment-content">
                            <div class="payment-summary">
                                <div class="payment-amount-display">
                                    <span class="payment-amount-label">Total Amount</span>
                                    <span class="payment-amount-value" id="stickyAmount">₱<?php echo number_format($total_payment_amount ?? $payment['total_amount'], 2); ?></span>
                                </div>
                                <div class="payment-method-display" id="stickyMethod">
                                    <i class="fas fa-university"></i>
                                    <span>Bank Transfer</span>
                                </div>
                            </div>
                            <button type="submit" form="paymentForm" class="sticky-pay-button" id="stickyPayButton">
                                <span class="button-text">
                                    <i class="fas fa-credit-card"></i> Process Payment
                                </span>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Loan Selection Mode - Table Format -->
                    <div class="payment-card">
                        <div class="payment-header">
                            <h2>Select Payment to Process</h2>
                            <p>Choose a payment from your pending payments</p>
                        </div>

                        <?php if (isset($error)): ?>
                            <div class="error-message">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($available_payments)): ?>
                            <div class="table-container">
                                <table class="payments-table">
                                    <thead>
                                        <tr>
                                            <th>Payment ID</th>
                                            <th>Loan ID</th>
                                            <th>Due Date</th>
                                            <th>Principal</th>
                                            <th>Interest</th>
                                            <th>Total Amount (with Late Fees)</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
// Apply pagination to pending payments
$paginated_payments = [];
$payment_counter = 0;
foreach ($available_payments as $loan_id => $loan) {
    foreach ($loan['pending_payments'] as $payment) {
        if ($payment_counter >= $offset && $payment_counter < ($offset + $per_page)) {
            if (!isset($paginated_payments[$loan_id])) {
                $paginated_payments[$loan_id] = $loan;
                $paginated_payments[$loan_id]['pending_payments'] = [];
            }
            $paginated_payments[$loan_id]['pending_payments'][] = $payment;
        }
        $payment_counter++;
    }
}
?>

                        <?php foreach ($paginated_payments as $loan_id => $loan): ?>
                                            <?php if (!empty($loan['pending_payments'])): ?>
                                                <?php foreach ($loan['pending_payments'] as $payment): ?>
                                                    <?php
                                                    $due_date = new DateTime($payment['due_date']);
                                                    $today = new DateTime();
                                                    
                                                    // Proper calculation for days until due
                                                    if ($due_date >= $today) {
                                                        $days_until_due = $today->diff($due_date)->days;
                                                        $status = $days_until_due == 0 ? 'Due Today' : ($days_until_due <= 3 ? 'Due Soon' : 'Pending');
                                                        $priority = $days_until_due == 0 ? 'High' : ($days_until_due <= 3 ? 'Medium' : 'Low');
                                                    } else {
                                                        $days_until_due = -($today->diff($due_date)->days);
                                                        $status = 'Overdue';
                                                        $priority = 'High';
                                                    }
                                                    
                                                    // Calculate principal and interest (assuming 5% annual rate, daily payments)
                                                    $annual_rate = 0.05;
                                                    $daily_rate = $annual_rate / 365;
                                                    $interest = round($payment['total_amount'] * ($daily_rate / (1 + $daily_rate)), 2);
                                                    $principal = round($payment['total_amount'] - $interest, 2);
                                                    
                                                    // Calculate late fees if overdue
                                                    $late_fee_amount = 0;
                                                    if ($days_until_due < 0) {
                                                        $late_fee_calculation = $lateFeeManager->calculateLateFee($payment['total_amount'], abs($days_until_due), $payment['payment_id']);
                                                        if (is_array($late_fee_calculation)) {
                                                            $late_fee_amount = $late_fee_calculation['fee_amount'];
                                                        }
                                                    }
                                                    
                                                    $total_with_late_fees = $payment['total_amount'] + $late_fee_amount;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                                                        <td><?php echo htmlspecialchars($loan_id); ?></td>
                                                        <td>
                                                            <?php echo date('M d, Y', strtotime($payment['due_date'])); ?>
                                                            <br>
                                                            <small class="due-<?php echo strtolower(str_replace(' ', '-', $status)); ?>">
                                                                <?php
                                                            if ($days_until_due < 0) {
                                                                echo abs($days_until_due) . ' days overdue';
                                                            } elseif ($days_until_due == 0) {
                                                                echo 'Due today';
                                                            } else {
                                                                echo 'Due in ' . $days_until_due . ' days';
                                                            }
                                                            ?>
                                                            </small>
                                                        </td>
                                                        <td>₱<?php echo number_format($principal, 2); ?></td>
                                                        <td>₱<?php echo number_format($interest, 2); ?></td>
                                                        <td>
                                                            ₱<?php echo number_format($payment['total_amount'], 2); ?>
                                                            <?php if ($late_fee_amount > 0): ?>
                                                                <br><small style="color: #ef4444;">+ Late Fee: ₱<?php echo number_format($late_fee_amount, 2); ?></small>
                                                                <br><strong style="color: #10b981;">Total: ₱<?php echo number_format($total_with_late_fees, 2); ?></strong>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $status)); ?>">
                                                                <?php echo $status; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="priority-badge priority-<?php echo strtolower($priority); ?>">
                                                                <?php echo $priority; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button class="btn-pay" onclick="showPaymentForm('<?php echo $loan_id; ?>', '<?php echo $payment['payment_id']; ?>', '<?php echo $total_with_late_fees; ?>', '<?php echo date('M d, Y', strtotime($payment['due_date'])); ?>')">
                                                                <i class="fas fa-credit-card"></i> Pay
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="pagination">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <!-- Page Numbers -->
                                    <div class="pagination-numbers">
                                        <?php
                                        $show_pages = 5;
                                        $start_page = max(1, $page - floor($show_pages / 2));
                                        $end_page = min($total_pages, $start_page + $show_pages - 1);
                                        
                                        if ($start_page > 1) {
                                            echo '<a href="?page=1" class="pagination-number">1</a>';
                                            if ($start_page > 2) {
                                                echo '<span style="color: var(--text-300); padding: 0 8px;">...</span>';
                                            }
                                        }
                                        
                                        for ($i = $start_page; $i <= $end_page; $i++) {
                                            $active_class = $i == $page ? 'active' : '';
                                            echo '<a href="?page=' . $i . '" class="pagination-number ' . $active_class . '">' . $i . '</a>';
                                        }
                                        
                                        if ($end_page < $total_pages) {
                                            if ($end_page < $total_pages - 1) {
                                                echo '<span style="color: var(--text-300); padding: 0 8px;">...</span>';
                                            }
                                            echo '<a href="?page=' . $total_pages . '" class="pagination-number">' . $total_pages . '</a>';
                                        }
                                        ?>
                                    </div>
                                    
                                    <span class="pagination-info">
                                        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                                    </span>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Payment Modal -->
                            <div id="paymentModal" class="modal">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h3>Process Payment</h3>
                                        <button class="modal-close" onclick="closePaymentModal()">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="">
                                            <input type="hidden" id="modal_loan_id" name="loan_id">
                                            <input type="hidden" id="modal_payment_id" name="payment_id">
                                            <input type="hidden" id="modal_payment_amount" name="payment_amount">
                                            <input type="hidden" id="modal_payment_method" name="payment_method" value="cash">
                                            
                                            <div class="selected-payment-info">
                                                <div class="detail-row">
                                                    <span class="detail-label">Payment ID</span>
                                                    <span class="detail-value" id="modal_display_payment_id">-</span>
                                                </div>
                                                <div class="detail-row">
                                                    <span class="detail-label">Due Date</span>
                                                    <span class="detail-value" id="modal_display_due_date">-</span>
                                                </div>
                                                <div class="detail-row">
                                                    <span class="detail-label">Amount Due</span>
                                                    <span class="detail-value" id="modal_display_amount">-</span>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="modal_amount">Payment Amount</label>
                                                <input type="number" id="modal_amount" name="payment_amount_display" 
                                                       step="0.01" min="0.01" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Payment Method</label>
                                                <div class="payment-methods">
                                                    <div class="method-option">
                                                        <input type="radio" id="modal_bank_transfer" name="payment_method" value="bank_transfer" checked>
                                                        <label for="modal_bank_transfer">
                                                            <i class="fas fa-university"></i> Bank Transfer
                                                        </label>
                                                    </div>
                                                    <div class="method-option">
                                                        <input type="radio" id="modal_gcash" name="payment_method" value="gcash">
                                                        <label for="modal_gcash">
                                                            <i class="fas fa-mobile-alt"></i> GCash
                                                        </label>
                                                    </div>
                                                    <div class="method-option">
                                                        <input type="radio" id="modal_maya" name="payment_method" value="maya">
                                                        <label for="modal_maya">
                                                            <i class="fas fa-wallet"></i> Maya
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-buttons">
                                                <button type="button" class="cancel-button" onclick="closePaymentModal()">Cancel</button>
                                                <button type="submit" class="pay-button">
                                                    <i class="fas fa-check"></i> Complete Payment
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 40px; color: #94a3b8;">
                                <i class="fas fa-info-circle" style="font-size: 3rem; margin-bottom: 20px;"></i>
                                <h3>No Pending Payments</h3>
                                <p>You don't have any pending payments at the moment.</p>
                                <a href="my-loans.php" class="pay-button" style="display: inline-block; margin-top: 20px;">
                                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

    <script>
        function showPaymentForm(loanId, paymentId, amount, dueDate) {
            // Hide the main header when modal opens
            const mainHeader = document.querySelector('.header');
            if (mainHeader) {
                mainHeader.style.display = 'none';
            }
            
            // Set form values
            document.getElementById('modal_loan_id').value = loanId;
            document.getElementById('modal_payment_id').value = paymentId;
            document.getElementById('modal_payment_amount').value = amount;
            document.getElementById('modal_amount').value = amount;
            document.getElementById('modal_amount').max = amount;
            document.getElementById('modal_amount').min = '0.01';
            
            // Set display values
            document.getElementById('modal_display_payment_id').textContent = paymentId;
            document.getElementById('modal_display_due_date').textContent = dueDate;
            document.getElementById('modal_display_amount').textContent = '₱' + parseFloat(amount).toFixed(2);
            
            // Reset payment method to bank_transfer
            document.getElementById('modal_bank_transfer').checked = true;
            document.getElementById('modal_payment_method').value = 'bank_transfer';
            
            // Show modal
            document.getElementById('paymentModal').style.display = 'block';
            
            // Remove existing event listener to prevent duplicates
            const amountInput = document.getElementById('modal_amount');
            const newAmountInput = amountInput.cloneNode(true);
            amountInput.parentNode.replaceChild(newAmountInput, amountInput);
            
            // Add event listener to update hidden amount when display amount changes
            newAmountInput.addEventListener('input', function() {
                document.getElementById('modal_payment_amount').value = this.value;
            });
        }
        
        function closePaymentModal() {
            // Show the main header when modal closes
            const mainHeader = document.querySelector('.header');
            if (mainHeader) {
                mainHeader.style.display = 'flex';
            }
            
            document.getElementById('paymentModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('paymentModal');
            if (event.target == modal) {
                closePaymentModal();
            }
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('paymentModal');
                if (modal && modal.style.display === 'block') {
                    closePaymentModal();
                }
            }
        });
        
        // Enhanced Sticky Footer Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
            const paymentAmountInput = document.getElementById('payment_amount');
            const stickyAmount = document.getElementById('stickyAmount');
            const stickyMethod = document.getElementById('stickyMethod');
            const stickyPayButton = document.getElementById('stickyPayButton');
            const scrollIndicator = document.createElement('div');
            scrollIndicator.className = 'scroll-indicator';
            scrollIndicator.innerHTML = '<i class="fas fa-arrow-up"></i>';
            document.body.appendChild(scrollIndicator);
            
            // Update sticky method display
            paymentMethods.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    const methodData = {
                        'bank_transfer': { icon: 'fas fa-university', name: 'Bank Transfer' },
                        'gcash': { icon: 'fas fa-mobile-alt', name: 'GCash' },
                        'maya': { icon: 'fas fa-wallet', name: 'Maya' }
                    };
                    
                    const selectedMethod = methodData[this.value];
                    stickyMethod.innerHTML = `
                        <i class="${selectedMethod.icon}"></i>
                        <span>${selectedMethod.name}</span>
                    `;
                    
                    // Add loading animation
                    const methodOptions = document.querySelectorAll('.method-option');
                    methodOptions.forEach(option => option.classList.remove('loading'));
                    
                    if (this.checked) {
                        const selectedOption = this.closest('.method-option');
                        selectedOption.classList.add('loading');
                        
                        setTimeout(() => {
                            selectedOption.classList.remove('loading');
                        }, 300);
                    }
                });
            });
            
            // Update sticky amount display
            if (paymentAmountInput) {
                paymentAmountInput.addEventListener('input', function() {
                    const amount = parseFloat(this.value) || 0;
                    stickyAmount.textContent = '₱' + amount.toFixed(2);
                });
            }
            
            // Handle form submission with loading state
            const paymentForm = document.getElementById('paymentForm');
            if (paymentForm) {
                paymentForm.addEventListener('submit', function(e) {
                    // Add loading state to sticky button
                    stickyPayButton.classList.add('loading');
                    stickyPayButton.disabled = true;
                    
                    // Disable all form inputs
                    const inputs = this.querySelectorAll('input, button');
                    inputs.forEach(input => input.disabled = true);
                    
                    // Re-enable after 5 seconds (fallback)
                    setTimeout(() => {
                        stickyPayButton.classList.remove('loading');
                        stickyPayButton.disabled = false;
                        inputs.forEach(input => input.disabled = false);
                    }, 5000);
                });
            }
            
            // Scroll indicator functionality
            let lastScrollTop = 0;
            window.addEventListener('scroll', function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const scrollHeight = document.documentElement.scrollHeight;
                const clientHeight = document.documentElement.clientHeight;
                const scrollPercentage = (scrollTop / (scrollHeight - clientHeight)) * 100;
                
                // Show scroll indicator when not at top
                if (scrollTop > 200) {
                    scrollIndicator.classList.add('show');
                } else {
                    scrollIndicator.classList.remove('show');
                }
                
                // Update scroll indicator rotation based on scroll direction
                if (scrollTop > lastScrollTop) {
                    scrollIndicator.style.transform = 'scale(1) rotate(180deg)';
                } else {
                    scrollIndicator.style.transform = 'scale(1) rotate(0deg)';
                }
                
                lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
            });
            
            // Scroll to top when indicator is clicked
            scrollIndicator.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Add ripple effect to sticky button
            stickyPayButton.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.3);
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    z-index: 1;
                `;
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
            
            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
            
            // Auto-hide sticky footer on very large screens
            function checkStickyFooter() {
                const windowHeight = window.innerHeight;
                const documentHeight = document.documentElement.scrollHeight;
                const stickyFooter = document.querySelector('.sticky-payment-footer');
                
                if (windowHeight >= documentHeight - 100) {
                    stickyFooter.style.position = 'relative';
                    stickyFooter.style.width = '100%';
                } else {
                    stickyFooter.style.position = 'fixed';
                    stickyFooter.style.width = '';
                }
            }
            
            checkStickyFooter();
            window.addEventListener('resize', checkStickyFooter);
        });
        
        // Show receipt modal if payment was successful
        <?php if (isset($_SESSION['receipt_data'])): ?>
            window.addEventListener('load', function() {
                showReceiptModal(<?php echo json_encode($_SESSION['receipt_data']); ?>);
                <?php unset($_SESSION['receipt_data']); ?>
            });
        <?php endif; ?>
        
        function showReceiptModal(receiptData) {
            const receiptContent = `
                <div style="font-family: 'Courier New', monospace; background: white; color: #000; border: 2px solid #000; padding: 20px;">
                    <!-- Header -->
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h2 style="margin: 0; color: #000; font-size: 20px; font-weight: bold; text-transform: uppercase;">PAYMENT RECEIPT</h2>
                        <div style="font-size: 11px; margin-top: 3px; color: #666; text-transform: uppercase;">Market Vendor Loan System</div>
                    </div>
                    
                    <!-- Two Column Layout -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <!-- Left Column: Receipt Details -->
                        <div style="border: 1px solid #ccc; padding: 15px; background: #f9f9f9;">
                            <h4 style="margin: 0 0 10px 0; font-size: 12px; text-transform: uppercase; color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Receipt Details</h4>
                            <div style="display: flex; justify-content: space-between; margin: 5px 0; font-size: 11px;">
                                <span>Receipt No:</span>
                                <strong style="color: #000;">${receiptData.receipt_number}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin: 5px 0; font-size: 11px;">
                                <span>Payment ID:</span>
                                <strong style="color: #000;">${receiptData.payment_id}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin: 5px 0; font-size: 11px;">
                                <span>Loan ID:</span>
                                <strong style="color: #000;">${receiptData.loan_id}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin: 5px 0; font-size: 11px;">
                                <span>Date:</span>
                                <strong style="color: #000;">${new Date(receiptData.payment_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</strong>
                            </div>
                        </div>
                        
                        <!-- Right Column: Client Info -->
                        <div style="border: 1px solid #ccc; padding: 15px; background: #f9f9f9;">
                            <h4 style="margin: 0 0 10px 0; font-size: 12px; text-transform: uppercase; color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Client Information</h4>
                            <div style="display: flex; justify-content: space-between; margin: 5px 0; font-size: 11px;">
                                <span>Client:</span>
                                <strong style="color: #000;">${receiptData.client_name}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin: 5px 0; font-size: 11px;">
                                <span>Email:</span>
                                <strong style="color: #000;">${receiptData.client_email}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin: 5px 0; font-size: 11px;">
                                <span>Method:</span>
                                <strong style="color: #000;">${receiptData.payment_method.toUpperCase().replace('_', ' ')}</strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Summary -->
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin: 0 0 10px 0; font-size: 12px; text-transform: uppercase; color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Summary of Payment</h4>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                            <div style="text-align: center; padding: 10px; background: #f0f8ff; border: 1px solid #cce;">
                                <div style="font-size: 10px; color: #666; margin-bottom: 3px;">Principal</div>
                                <div style="font-size: 14px; font-weight: bold; color: #000;">₱${parseFloat(receiptData.principal_paid || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: #f0fff0; border: 1px solid #cfc;">
                                <div style="font-size: 10px; color: #666; margin-bottom: 3px;">Interest</div>
                                <div style="font-size: 14px; font-weight: bold; color: #000;">₱${parseFloat(receiptData.interest_paid || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: #fff0f0; border: 1px solid #fcc;">
                                <div style="font-size: 10px; color: #666; margin-bottom: 3px;">Late Fees</div>
                                <div style="font-size: 14px; font-weight: bold; color: #000;">₱${parseFloat(receiptData.late_fees || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Section -->
                    <div style="border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 10px; margin-bottom: 15px; background: #f9f9f9;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 16px; font-weight: bold; color: #000;">
                            <span>TOTAL PAID:</span>
                            <span style="color: #000; font-size: 18px;">₱${parseFloat(receiptData.payment_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div style="text-align: center; font-size: 10px; color: #666; border-top: 1px dashed #ccc; padding-top: 10px;">
                        <div style="margin-bottom: 3px;">Processed by: ${receiptData.processed_by}</div>
                        <div>Thank you for your payment!</div>
                        ${receiptData.notes ? `<div style="margin-top: 5px; font-style: italic;">${receiptData.notes}</div>` : ''}
                    </div>
                </div>
            `;
            
            document.getElementById('receiptContent').innerHTML = receiptContent;
            document.getElementById('printReceiptModal').style.display = 'block';
        }
        
        function closePrintReceiptModal() {
            document.getElementById('printReceiptModal').style.display = 'none';
        }
        
        function printReceipt() {
            const receiptContent = document.getElementById('receiptContent').innerHTML;
            const printWindow = window.open('', '_blank');
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Payment Receipt</title>
                    <style>
                        body { 
                            margin: 0; 
                            padding: 20px; 
                            font-family: monospace; 
                        }
                        @media print { 
                            body { 
                                margin: 0; 
                            } 
                        }
                    </style>
                </head>
                <body>
                    ${receiptContent}
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
    </script>
</main>
</div>
</div>
</div>

<!-- Print Receipt Modal -->
<div id="printReceiptModal" class="modal">
    <div class="modal-content" style="max-width: 900px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3><i class="fas fa-receipt"></i> Payment Receipt</h3>
            <button class="modal-close" onclick="closePrintReceiptModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="receiptContent" style="padding: 20px; background: white; border: 2px solid #333;">
                <!-- Receipt content will be loaded here -->
            </div>
            <div class="form-buttons" style="margin-top: 20px; display: flex; gap: 12px; justify-content: center;">
                <button type="button" class="btn-secondary" onclick="closePrintReceiptModal()" style="flex: 1; min-width: 150px; padding: 12px 24px;">Close</button>
                <button type="button" class="btn-primary" onclick="printReceipt()" style="flex: 1; min-width: 150px; padding: 12px 24px;">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>
<script src="responsive-script.js"></script>

</body>
</html>
