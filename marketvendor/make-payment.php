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
        }
        
        .payment-card {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid var(--line);
            border-radius: var(--card-radius);
            padding: 24px;
            margin-bottom: 30px;
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
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .method-option {
            position: relative;
        }
        
        .method-option label {
            display: block;
            padding: 15px;
            background: rgba(15, 39, 75, 0.8);
            border: 2px solid var(--line);
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--text-300);
            font-weight: 600;
        }
        
        .method-option input[type="radio"]:checked + label {
            background: rgba(59, 130, 246, 0.2);
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .method-option label:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: var(--primary);
        }
        
        .method-option i {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 8px;
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
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 500;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
        
        .modal-content {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid var(--line);
            border-radius: var(--card-radius);
            margin: 10% auto;
            padding: 0;
            width: 90%;
            max-width: 500px;
            height: auto;
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            padding: 24px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-100);
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            color: var(--text-300);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            color: var(--text-100);
            background: rgba(255, 255, 255, 0.1);
        }
        
        .modal-body {
            padding: 20px;
        }
        
        /* Modal form spacing adjustments */
        .modal-body .form-group {
            margin-bottom: 12px;
        }
        
        .modal-body .form-buttons {
            margin-top: 10px;
        }
        
        .modal-body .selected-payment-info {
            margin-bottom: 12px;
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

                        <form method="POST" action="">
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
                                            <i class="fas fa-university"></i> Bank Transfer
                                        </label>
                                    </div>
                                    <div class="method-option">
                                        <input type="radio" id="gcash" name="payment_method" value="gcash">
                                        <label for="gcash">
                                            <i class="fas fa-mobile-alt"></i> GCash
                                        </label>
                                    </div>
                                    <div class="method-option">
                                        <input type="radio" id="maya" name="payment_method" value="maya">
                                        <label for="maya">
                                            <i class="fas fa-wallet"></i> Maya
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="pay-button">
                                <i class="fas fa-credit-card"></i> Process Payment
                            </button>
                        </form>
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
                closePaymentModal();
            }
        });
        
        // Update payment method when radio buttons change
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
            paymentMethods.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    document.getElementById('modal_payment_method').value = this.value;
                });
            });
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
