<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';
require_once 'includes/audit_helper.php';
require_once 'includes/late_fee_functions.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
$lateFeeManager = new LateFeeManager();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'process_payment':
                $loan_id = $_POST['loan_id'] ?? '';
                $payment_amount = floatval($_POST['payment_amount'] ?? 0);
                $payment_method = $_POST['payment_method'] ?? '';
                $payment_date = $_POST['payment_date'] ?? date('Y-m-d');
                $receipt_number = 'RCP' . date('YmdHis') . rand(1000, 9999);
                $notes = $_POST['notes'] ?? '';
                
                error_log("Payment Process Started - Loan: $loan_id, Amount: $payment_amount");
                
                // Basic validation
                if (empty($loan_id)) {
                    $_SESSION['error_message'] = "Loan ID is required";
                    error_log("Payment Error: Empty Loan ID");
                    break;
                }
                
                if ($payment_amount <= 0) {
                    $_SESSION['error_message'] = "Payment amount must be greater than 0";
                    error_log("Payment Error: Invalid Amount: $payment_amount");
                    break;
                }
                
                try {
                    $db->beginTransaction();
                    
                    // Get loan details
                    $loan_stmt = $db->prepare("SELECT l.*, u.name as client_name, u.email, u.id as user_id 
                                              FROM loans l 
                                              LEFT JOIN users u ON l.user_id = u.id 
                                              WHERE l.loan_id = ?");
                    $loan_stmt->execute([$loan_id]);
                    $loan = $loan_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$loan) {
                        throw new Exception("Loan '$loan_id' not found in database");
                    }
                    
                    error_log("Loan found: " . $loan['client_name']);
                    
                    // Get any pending payment schedule
                    $schedule_stmt = $db->prepare("SELECT * FROM payment_schedules 
                                                  WHERE loan_id = ? AND status = 'pending' 
                                                  ORDER BY due_date ASC LIMIT 1");
                    $schedule_stmt->execute([$loan_id]);
                    $schedule = $schedule_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Calculate principal and interest (5% annual rate)
                    $annualRate = 0.05;
                    $dailyRate = $annualRate / 365;
                    $interest = round($payment_amount * ($dailyRate / (1 + $dailyRate)) * 100) / 100;
                    $principal = round(($payment_amount - $interest) * 100) / 100;
                    
                    // Debug: Log the calculated values
                    error_log("Payment Calculation - Amount: $payment_amount, Interest: $interest, Principal: $principal");
                    
                    // Insert payment record with principal and interest breakdown
                    $payment_id = 'PAY' . date('YmdHis') . rand(1000, 9999);
                    $insert_stmt = $db->prepare("INSERT INTO payment_history (payment_id, loan_id, user_id, borrower_name, payment_date, amount_paid, principal_paid, interest_paid, payment_method, receipt_number, status, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?, NOW())");
                    
                    $insert_result = $insert_stmt->execute([
                        $payment_id, 
                        $loan_id, 
                        $loan['user_id'], 
                        $loan['client_name'], 
                        $payment_date, 
                        $payment_amount, 
                        $principal,
                        $interest,
                        $payment_method, 
                        $receipt_number, 
                        $notes
                    ]);
                    
                    error_log("Payment Insert Result: " . ($insert_result ? "SUCCESS" : "FAILED"));
                    error_log("Payment ID: $payment_id, Principal: $principal, Interest: $interest");
                    
                    if (!$insert_result) {
                        throw new Exception("Failed to insert payment record");
                    }
                    
                    error_log("Payment record inserted: $payment_id");
                    
                    // Update payment schedule if exists
                    if ($schedule) {
                        $update_schedule = $db->prepare("UPDATE payment_schedules SET status = 'paid' WHERE id = ?");
                        $schedule_result = $update_schedule->execute([$schedule['id']]);
                        error_log("Payment schedule updated: " . ($schedule_result ? "Success" : "Failed"));
                    }
                    
                    // Update loan remaining amount - using correct column name
                    $update_loan = $db->prepare("UPDATE loans SET loan_amount = GREATEST(0, loan_amount - ?), updated_at = NOW() WHERE loan_id = ?");
                    $loan_result = $update_loan->execute([$payment_amount, $loan_id]);
                    
                    if (!$loan_result) {
                        throw new Exception("Failed to update loan balance");
                    }
                    
                    error_log("Loan balance updated");
                    
                    // Check if loan is fully paid
                    $check_stmt = $db->prepare("SELECT loan_amount FROM loans WHERE loan_id = ?");
                    $check_stmt->execute([$loan_id]);
                    $remaining = $check_stmt->fetchColumn();
                    
                    if ($remaining <= 0) {
                        $complete_stmt = $db->prepare("UPDATE loans SET status = 'completed', completed_date = NOW() WHERE loan_id = ?");
                        $complete_stmt->execute([$loan_id]);
                        error_log("Loan marked as completed");
                    }
                    
                    $db->commit();
                    
                    // Log successful payment processing
                    logPaymentProcessed($payment_id, $loan['client_name'], $payment_amount, 'completed');
                    
                    $_SESSION['success_message'] = "✅ Payment processed successfully! Receipt: $receipt_number | Amount: ₱" . number_format($payment_amount, 2) . " | Client: " . $loan['client_name'];
                    
                    // Calculate late fees for this payment
                    $total_late_fees = 0;
                    try {
                        $late_fee_stmt = $db->prepare("
                            SELECT SUM(fee_amount) as total_late_fees
                            FROM late_fees 
                            WHERE loan_id = ? AND status = 'pending'
                        ");
                        $late_fee_stmt->execute([$loan_id]);
                        $late_fee_result = $late_fee_stmt->fetch(PDO::FETCH_ASSOC);
                        $total_late_fees = $late_fee_result['total_late_fees'] ?? 0;
                    } catch (Exception $e) {
                        $total_late_fees = 0;
                    }
                    
                    // Store receipt data for printing
                    $_SESSION['receipt_data'] = [
                        'receipt_number' => $receipt_number,
                        'payment_id' => $payment_id,
                        'loan_id' => $loan_id,
                        'client_name' => $loan['client_name'],
                        'client_email' => $loan['email'],
                        'payment_amount' => $payment_amount,
                        'principal_paid' => $principal,
                        'interest_paid' => $interest,
                        'late_fees' => $total_late_fees,
                        'payment_method' => $payment_method,
                        'payment_date' => $payment_date,
                        'notes' => $notes,
                        'processed_by' => $_SESSION['user_name'] ?? 'Admin'
                    ];
                    
                    error_log("Payment Success: $receipt_number");
                    
                } catch (Exception $e) {
                    $db->rollback();
                    $error_msg = "Payment Error: " . $e->getMessage();
                    error_log($error_msg);
                    
                    // Log failed payment attempt
                    logFailedAttempt('payment processing', "Loan ID: $loan_id, Amount: $payment_amount, Error: " . $e->getMessage());
                    
                    $_SESSION['error_message'] = "❌ Payment processing failed: " . $e->getMessage();
                }
                break;
                
            case 'print_receipt':
                // Log receipt printing
                $receipt_number = $_POST['receipt_number'] ?? 'Unknown';
                $client_name = $_POST['client_name'] ?? 'Unknown';
                $amount = $_POST['amount'] ?? 0;
                
                logAudit('print', "Receipt printed: {$receipt_number} for {$client_name} - Amount: ₱" . number_format($amount, 2));
                
                $_SESSION['success_message'] = "✅ Receipt printed successfully!";
                break;
        }
        header("Location: payment-management.php");
        exit();
    }
}

// Handle GET requests for direct payment processing
if (isset($_GET['action']) && $_GET['action'] === 'process_payment') {
    $loan_id = $_GET['loan_id'] ?? '';
    $payment_amount = floatval($_GET['payment_amount'] ?? 0);
    
    if (!empty($loan_id) && $payment_amount > 0) {
        // Auto-fill the payment modal with the provided data
        $_SESSION['auto_fill_payment'] = [
            'loan_id' => $loan_id,
            'payment_amount' => $payment_amount
        ];
    }
    
    header("Location: payment-management.php");
    exit();
}

// Fetch clients with pending payments
$clients_with_pending = [];
$stats = [
    'today_collections' => 0,
    'week_collections' => 0,
    'pending_count' => 0
];

// Get search parameter
$search = $_GET['search'] ?? '';

try {
    // Get clients who have pending payments with search functionality
    $clients_sql = "SELECT DISTINCT u.id, u.name as client_name, u.email,
                   COUNT(ps.payment_id) as pending_count,
                   SUM(ps.total_amount) as total_pending_amount,
                   MIN(ps.due_date) as next_due_date
                   FROM users u
                   INNER JOIN payment_schedules ps ON u.id = ps.user_id
                   WHERE ps.status = 'pending'";
    
    // Add search filter if provided
    if (!empty($search)) {
        $clients_sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR EXISTS (
            SELECT 1 FROM loans l WHERE l.user_id = u.id AND l.loan_id LIKE ?
        ))";
    }
    
    $clients_sql .= " GROUP BY u.id, u.name, u.email";
    
    $stmt = $db->prepare($clients_sql);
    
    if (!empty($search)) {
        $search_param = '%' . $search . '%';
        $stmt->execute([$search_param, $search_param, $search_param]);
    } else {
        $stmt->execute();
    }
    
    $clients_with_pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $today_sql = "SELECT COALESCE(SUM(amount_paid), 0) as total FROM payment_history WHERE payment_date = CURDATE() AND status = 'completed'";
    $stmt = $db->query($today_sql);
    $stats['today_collections'] = $stmt->fetch()['total'];
    
    $week_sql = "SELECT COALESCE(SUM(amount_paid), 0) as total FROM payment_history WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status = 'completed'";
    $stmt = $db->query($week_sql);
    $stats['week_collections'] = $stmt->fetch()['total'];
    $month_sql = "SELECT COALESCE(SUM(amount_paid), 0) as total FROM payment_history WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status = 'completed'";
    $month_stmt = $db->prepare($month_sql);
    $month_stmt->execute();
    $stats['month_collections'] = $month_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stats['pending_count'] = count($clients_with_pending);
    
} catch (Exception $e) {
    $error_message = "Error loading data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Processing - Market Vendor Loan Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="enhanced-styles.css">
    <link rel="stylesheet" href="responsive-styles-fixed.css">
    <style>
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
            margin: 0;
            color: #f1f5f9;
            font-size: 1rem;
        }
        
        .user-details p {
            margin: 0;
            color: #94a3b8;
            font-size: 0.9rem;
        }
        
        .logout-btn {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
        }
        
        .content-wrap {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .header {
            margin-bottom: 30px;
        }
        
        .header h2 {
            margin: 0 0 8px 0;
            color: #f1f5f9;
            font-size: 1.5rem;
        }
        
        .header p {
            margin: 0;
            color: #94a3b8;
        }
        
        .payment-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 39, 75, 0.95) 100%);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #94a3b8;
            font-size: .9rem;
        }
        
        .section-header {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 39, 75, 0.95) 100%);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-header h3 {
            margin: 0;
            color: #f1f5f9;
            font-size: 1.2rem;
        }
        
        .search-bar {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .search-bar input {
            padding: 10px 15px;
            background: rgba(15, 39, 75, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            color: #f1f5f9;
            width: 300px;
        }
        
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(30, 41, 59, 0.95);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .payment-table th,
        .payment-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }
        
        .payment-table th {
            background: rgba(15, 39, 75, 0.8);
            color: #e2e8f0;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        
        
        .btn-process {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-process:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .overdue {
            color: #ef4444;
            font-weight: 600;
        }
        
        .due-soon {
            color: #fbbf24;
            font-weight: 600;
        }
        
        .amount {
            font-weight: 600;
            color: #10b981;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
        
        .modal-content {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            margin: 5% auto;
            padding: 0;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #f1f5f9;
        }
        
        /* Enhanced modal table styles */
        #clientPaymentsModal .payments-table {
            width: 100%;
            table-layout: fixed;
            font-size: 0.9rem;
        }
        
        #clientPaymentsModal .payments-table th {
            padding: 12px 8px;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        
        #clientPaymentsModal .payments-table td {
            padding: 10px 8px;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        #clientPaymentsModal .payments-table th:nth-child(1),
        #clientPaymentsModal .payments-table td:nth-child(1) { width: 10%; } /* Payment ID */
        #clientPaymentsModal .payments-table th:nth-child(2),
        #clientPaymentsModal .payments-table td:nth-child(2) { width: 10%; } /* Loan ID */
        #clientPaymentsModal .payments-table th:nth-child(3),
        #clientPaymentsModal .payments-table td:nth-child(3) { width: 15%; } /* Loan Purpose */
        #clientPaymentsModal .payments-table th:nth-child(4),
        #clientPaymentsModal .payments-table td:nth-child(4) { width: 10%; } /* Due Date */
        #clientPaymentsModal .payments-table th:nth-child(5),
        #clientPaymentsModal .payments-table td:nth-child(5) { width: 10%; } /* Principal */
        #clientPaymentsModal .payments-table th:nth-child(6),
        #clientPaymentsModal .payments-table td:nth-child(6) { width: 10%; } /* Interest */
        #clientPaymentsModal .payments-table th:nth-child(7),
        #clientPaymentsModal .payments-table td:nth-child(7) { width: 10%; } /* Total Amount */
        #clientPaymentsModal .payments-table th:nth-child(8),
        #clientPaymentsModal .payments-table td:nth-child(8) { width: 10%; } /* Status */
        #clientPaymentsModal .payments-table th:nth-child(9),
        #clientPaymentsModal .payments-table td:nth-child(9) { width: 15%; } /* Action */
        
        .modal-close {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .modal-body {
            padding: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #e2e8f0;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            background: rgba(15, 39, 75, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            color: #f1f5f9;
            font-size: 0.95rem;
        }
        
        .form-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
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
        
        .btn-secondary {
            background: rgba(148, 163, 184, 0.2);
            color: #e2e8f0;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .receipt-preview {
            background: rgba(15, 39, 75, 0.3);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            padding-bottom: 16px;
            margin-bottom: 16px;
        }
        
        .receipt-header h4 {
            margin: 0;
            color: #3b82f6;
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
        
        .priority-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .priority-badge.priority-high {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        
        .priority-badge.priority-medium {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }
        
        .priority-badge.priority-low {
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
            color: #10b981;
            font-weight: 600;
        }
        
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
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }
        
        .payments-table th {
            background: rgba(15, 39, 75, 0.5);
            color: #e2e8f0;
            font-weight: 500;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .payments-table td {
            color: #e2e8f0;
            font-size: 0.9rem;
        }
        
        .payments-table tbody tr {
            transition: all 0.2s ease;
        }
        
        .payments-table tbody tr:hover {
            background: rgba(59, 130, 246, 0.1);
        }
        
        .table-section {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .message {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .success-message {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }
        
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
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
                    <p>Admin Portal</p>
                </div>
            </div>

            <p class="nav-section-title">Admin Menu</p>
            <a class="nav-item" href="admin-dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a class="nav-item" href="loan-management.php">
                <i class="fas fa-hand-holding-usd"></i> Loan Management
            </a>
            <a class="nav-item" href="payment-history.php">
                <i class="fas fa-history"></i> Payment History
            </a>
            <a class="nav-item active" href="payment-management.php">
                <i class="fas fa-credit-card"></i> Process Payment
            </a>
            <a class="nav-item" href="analytics.php">
                <i class="fas fa-chart-line"></i> Analytics & Reports
            </a>
            <a class="nav-item" href="audit-log.php">
                <i class="fas fa-clipboard-list"></i> Audit Log
            </a>
            <a class="nav-item" href="settings.php">
                <i class="fas fa-cog"></i> Settings
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
                        <p>Administrator</p>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <header class="header">
                <div>
                    <h2>Payment Processing</h2>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
                </div>
            </header>

            <?php if (isset($error_message)): ?>
                <div class="message error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="message success-message">
                    <?php echo $_SESSION['success_message']; ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="message error-message">
                    <?php echo $_SESSION['error_message']; ?>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <main>
                <!-- Payment Statistics -->
                <div class="payment-stats">
                    <div class="stat-card">
                        <div class="stat-value">₱<?php echo number_format($stats['today_collections'], 2); ?></div>
                        <div class="stat-label">Today's Collections</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">₱<?php echo number_format($stats['week_collections'], 2); ?></div>
                        <div class="stat-label">This Week</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">₱<?php echo number_format($stats['month_collections'], 2); ?></div>
                        <div class="stat-label">This Month</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['pending_count']; ?></div>
                        <div class="stat-label">Pending Payments</div>
                    </div>
                </div>
                
                <!-- Pending Payments Section -->
                <div class="section-header">
                    <h3><i class="fas fa-clock"></i> Pending Payments</h3>
                    <div class="search-bar">
                        <input type="text" 
                               id="searchInput" 
                               placeholder="Search by loan ID, client name, or email..." 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               onkeyup="performSearch(this.value)">
                        <i class="fas fa-search" style="color: #94a3b8;"></i>
                    </div>
                </div>
                
                <?php if (empty($clients_with_pending)): ?>
                    <div style="text-align: center; padding: 40px; color: #94a3b8; background: rgba(30, 41, 59, 0.95); border-radius: 12px;">
                        <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 20px; display: block; color: #10b981;"></i>
                        <h4>All Payments Up to Date</h4>
                        <p>No clients with pending payments at this time.</p>
                    </div>
                <?php else: ?>
                    <div class="table-section">
                        <div class="table-container">
                            <table class="payments-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Pending Payments</th>
                                    <th>Total Amount</th>
                                    <th>Next Payment Due</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients_with_pending as $client): ?>
                                    <?php
                                    $next_due_date = new DateTime($client['next_due_date']);
                                    $today = new DateTime();
                                    $days_until_due = $today->diff($next_due_date)->days;
                                    $status = $days_until_due < 0 ? 'Overdue' : ($days_until_due <= 3 ? 'Due Soon' : 'On Time');
                                    ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($client['client_name']); ?></strong>
                                                <br><small style="color: #94a3b8;"><?php echo htmlspecialchars($client['email']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="priority-badge priority-<?php echo $client['pending_count'] > 3 ? 'high' : ($client['pending_count'] > 1 ? 'medium' : 'low'); ?>">
                                                <?php echo $client['pending_count']; ?> payments
                                            </span>
                                        </td>
                                        <td class="amount">₱<?php echo number_format($client['total_pending_amount'], 2); ?></td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($client['next_due_date'])); ?>
                                            <br>
                                            <small class="<?php echo strtolower(str_replace(' ', '-', $status)); ?>">
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
                                        <td>
                                            <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $status)); ?>">
                                                <?php echo $status; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn-process" onclick="viewClientPayments('<?php echo htmlspecialchars($client['id']); ?>', '<?php echo htmlspecialchars($client['client_name']); ?>')">
                                                <i class="fas fa-eye"></i> View Payments
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Client Payments Modal -->
    <div id="clientPaymentsModal" class="modal">
        <div class="modal-content" style="max-width: 1400px; width: 98%; max-height: 90vh;">
            <div class="modal-header">
                <h3><i class="fas fa-user"></i> <span id="clientModalTitle">Client Payments</span></h3>
                <button class="modal-close" onclick="closeClientPaymentsModal()">&times;</button>
            </div>
            <div class="modal-body" style="max-height: calc(90vh - 120px); overflow-y: auto;">
                <div id="clientPaymentsContent">
                    <div style="text-align: center; padding: 40px; color: #94a3b8;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 20px; display: block;"></i>
                        <p>Loading payments...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Receipt Modal -->
    <div id="printReceiptModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3><i class="fas fa-receipt"></i> Payment Receipt</h3>
                <button class="modal-close" onclick="closePrintReceiptModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="receiptContent" style="text-align: center; padding: 20px; background: white; border: 2px solid #333;">
                    <!-- Receipt content will be loaded here -->
                </div>
                <div class="form-buttons" style="margin-top: 20px;">
                    <button type="button" class="btn-secondary" onclick="closePrintReceiptModal()">Close</button>
                    <button type="button" class="btn-primary" onclick="printReceipt()">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Processing Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-cash-register"></i> Process Payment</h3>
                <button class="modal-close" onclick="closePaymentModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="paymentForm" onsubmit="console.log('Form submitting...'); return true;">
                    <input type="hidden" name="action" value="process_payment">
                    <input type="hidden" id="loan_id" name="loan_id">
                    
                    <div class="receipt-preview">
                        <div class="receipt-header">
                            <h4>Payment Receipt</h4>
                            <p style="margin: 5px 0; color: #94a3b8; font-size: 0.9rem;">Market Vendor Loan System</p>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div>
                                <small style="color: #94a3b8;">Loan ID:</small>
                                <p style="margin: 0; font-weight: 600;" id="preview_loan_id">-</p>
                            </div>
                            <div>
                                <small style="color: #94a3b8;">Client:</small>
                                <p style="margin: 0; font-weight: 600;" id="preview_client">-</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_amount">Payment Amount *</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 12px; color: #94a3b8;">₱</span>
                            <input type="number" id="payment_amount" name="payment_amount" step="0.01" min="0.01" required 
                                   style="padding-left: 30px;" placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method *</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="cash">Cash</option>
                        </select>
                        <small style="color: #94a3b8; font-size: 0.85rem;">Only cash payments are accepted</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_date">Payment Date *</label>
                        <input type="date" id="payment_date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Optional notes about this payment"></textarea>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="button" class="btn-secondary" onclick="closePaymentModal()">Cancel</button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i> Process Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Simple search functionality with instant page refresh
        function performSearch(searchTerm) {
            const currentUrl = new URL(window.location);
            if (searchTerm.trim() === '') {
                currentUrl.searchParams.delete('search');
            } else {
                currentUrl.searchParams.set('search', searchTerm);
            }
            window.location.href = currentUrl.toString();
        }
        
        function viewClientPayments(clientId, clientName) {
            document.getElementById('clientModalTitle').textContent = clientName + ' - Pending Payments';
            document.getElementById('clientPaymentsModal').style.display = 'block';
            
            // Fetch client payments via AJAX
            fetch('get_client_payments.php?client_id=' + clientId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayClientPayments(data.client, data.payments);
                    } else {
                        document.getElementById('clientPaymentsContent').innerHTML = 
                            '<div style="text-align: center; padding: 40px; color: #ef4444;">' +
                            '<i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 20px; display: block;"></i>' +
                            '<p>' + (data.error || 'Error loading payments') + '</p>' +
                            '</div>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching client payments:', error);
                    document.getElementById('clientPaymentsContent').innerHTML = 
                        '<div style="text-align: center; padding: 40px; color: #ef4444;">' +
                        '<i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 20px; display: block;"></i>' +
                        '<p>Network error loading payments. Please try again.</p>' +
                        '</div>';
                });
        }
        
        function displayClientPayments(client, payments) {
            let html = '<div style="margin-bottom: 20px; padding: 16px; background: rgba(15, 39, 75, 0.3); border-radius: 8px; border: 1px solid rgba(148, 163, 184, 0.2);">' +
                '<h4 style="margin: 0 0 12px 0; color: #f1f5f9;">Client Information</h4>' +
                '<p style="margin: 4px 0;"><strong>Name:</strong> ' + client.name + '</p>' +
                '<p style="margin: 4px 0;"><strong>Email:</strong> ' + client.email + '</p>' +
                '<p style="margin: 4px 0;"><strong>Total Pending Payments:</strong> ' + payments.length + '</p>' +
                '</div>';
            
            if (payments.length === 0) {
                html += '<div style="text-align: center; padding: 40px; color: #94a3b8;">' +
                    '<i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 20px; display: block; color: #10b981;"></i>' +
                    '<p>No pending payments for this client</p>' +
                    '</div>';
            } else {
                html += '<div class="table-container">' +
                    '<table class="payments-table">' +
                    '<thead>' +
                    '<tr>' +
                    '<th>Payment ID</th>' +
                    '<th>Loan ID</th>' +
                    '<th>Loan Purpose</th>' +
                    '<th>Due Date</th>' +
                    '<th>Principal</th>' +
                    '<th>Interest</th>' +
                    '<th>Total Amount</th>' +
                    '<th>Status</th>' +
                    '<th>Action</th>' +
                    '</tr>' +
                    '</thead>' +
                    '<tbody>';
                
                payments.forEach(payment => {
                    const dueDate = new Date(payment.due_date);
                    const today = new Date();
                    const daysUntilDue = Math.floor((dueDate - today) / (1000 * 60 * 60 * 24));
                    const status = daysUntilDue < 0 ? 'Overdue' : (daysUntilDue <= 3 ? 'Due Soon' : 'Pending');
                    
                    // Calculate principal and interest (5% annual rate)
                    const annualRate = 0.05;
                    const dailyRate = annualRate / 365;
                    const interest = Math.round(payment.total_amount * (dailyRate / (1 + dailyRate)) * 100) / 100;
                    const principal = Math.round((payment.total_amount - interest) * 100) / 100;
                    
                    html += '<tr>' +
                    '<td><strong>' + payment.payment_id + '</strong></td>' +
                    '<td>' + payment.loan_id + '</td>' +
                    '<td>' + (payment.loan_purpose || 'N/A') + '</td>' +
                    '<td>' + new Date(payment.due_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + '<br>' +
                    '<small class="' + status.toLowerCase().replace(' ', '-') + '">' +
                    (daysUntilDue < 0 ? Math.abs(daysUntilDue) + ' days overdue' : 
                     daysUntilDue === 0 ? 'Due today' : 'Due in ' + daysUntilDue + ' days') +
                    '</small></td>' +
                    '<td>₱' + principal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                    '<td>₱' + interest.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                    '<td class="amount">₱' + parseFloat(payment.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                    '<td><span class="status-badge ' + status.toLowerCase().replace(' ', '-') + '">' + status + '</span></td>' +
                    '<td>' +
                    '<button class="btn-process" onclick="processPaymentFromModal(\'' + payment.loan_id + '\', \'' + payment.payment_id + '\', \'' + client.name + '\', \'' + payment.total_amount + '\')">' +
                    '<i class="fas fa-cash-register"></i> Process Payment' +
                    '</button>' +
                    '</td>' +
                    '</tr>';
                });
                
                html += '</tbody></table></div>';
            }
            
            document.getElementById('clientPaymentsContent').innerHTML = html;
        }
        
        function closeClientPaymentsModal() {
            document.getElementById('clientPaymentsModal').style.display = 'none';
        }
        
        function processPaymentFromModal(loanId, paymentId, clientName, totalAmount) {
            closeClientPaymentsModal();
            // Validate inputs before processing
            if (!loanId || !paymentId || !clientName || totalAmount <= 0) {
                alert('Please fill in all required fields correctly.');
                return;
            }
            // Auto-fill and open payment modal
            openPaymentModal(loanId, clientName, totalAmount);
        }
        
        function openPaymentModal(loanId, clientName, totalAmount) {
            document.getElementById('loan_id').value = loanId;
            document.getElementById('preview_loan_id').textContent = loanId;
            document.getElementById('preview_client').textContent = clientName;
            document.getElementById('payment_amount').value = totalAmount;
            document.getElementById('payment_amount').setAttribute('readonly', true);
            document.getElementById('paymentModal').style.display = 'block';
        }
        
        function processPayment(loanId, paymentId, clientName, totalAmount) {
            console.log('Processing payment:', { loanId, paymentId, clientName, totalAmount });
            openPaymentModal(loanId, clientName, totalAmount);
        }
        
        function showQuickPaymentModal() {
            document.getElementById('loan_id').value = '';
            document.getElementById('preview_loan_id').textContent = 'Enter Loan ID';
            document.getElementById('preview_client').textContent = 'Will be auto-filled';
            document.getElementById('payment_amount').value = '';
            document.getElementById('paymentModal').style.display = 'block';
        }
        
        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
            document.getElementById('paymentForm').reset();
            document.getElementById('payment_amount').removeAttribute('readonly');
        }
        
        // Auto-fill client info when loan ID is entered
        document.getElementById('loan_id').addEventListener('blur', function() {
            const loanId = this.value;
            if (loanId) {
                // You could add AJAX here to auto-fill client info
                document.getElementById('preview_loan_id').textContent = loanId;
            }
        });
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const clientModal = document.getElementById('clientPaymentsModal');
            const paymentModal = document.getElementById('paymentModal');
            if (event.target == clientModal) {
                closeClientPaymentsModal();
            }
            if (event.target == paymentModal) {
                closePaymentModal();
            }
        }
        
        // Format payment amount input
        document.getElementById('payment_amount').addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });
        
        // Show receipt modal if payment was successful
        <?php if (isset($_SESSION['receipt_data'])): ?>
            window.addEventListener('load', function() {
                showReceiptModal(<?php echo json_encode($_SESSION['receipt_data']); ?>);
                <?php unset($_SESSION['receipt_data']); ?>
            });
        <?php endif; ?>
        
        // Auto-fill payment modal if coming from GET request
        <?php if (isset($_SESSION['auto_fill_payment'])): ?>
            window.addEventListener('load', function() {
                const autoFill = <?php echo json_encode($_SESSION['auto_fill_payment']); ?>;
                openPaymentModal(autoFill.loan_id, 'Loading...', autoFill.payment_amount);
                <?php unset($_SESSION['auto_fill_payment']); ?>
            });
        <?php endif; ?>
        
        function showReceiptModal(receiptData) {
            const receiptContent = `
                <div style="font-family: monospace; border: 2px solid #333; padding: 20px; background: white; color: #000;">
                    <h2 style="margin: 0 0 20px 0; text-align: center; color: #000;">PAYMENT RECEIPT</h2>
                    <div style="text-align: center; margin-bottom: 20px; font-size: 12px; color: #000;">Market Vendor Loan System</div>
                    
                    <div style="border-top: 1px dashed #333; border-bottom: 1px dashed #333; padding: 10px 0; margin: 10px 0;">
                        <div style="display: flex; justify-content: space-between; margin: 5px 0; color: #000;">
                            <span>Receipt No:</span>
                            <strong style="color: #000;">${receiptData.receipt_number}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin: 5px 0; color: #000;">
                            <span>Payment ID:</span>
                            <strong style="color: #000;">${receiptData.payment_id}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin: 5px 0; color: #000;">
                            <span>Loan ID:</span>
                            <strong style="color: #000;">${receiptData.loan_id}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin: 5px 0; color: #000;">
                            <span>Date:</span>
                            <strong style="color: #000;">${new Date(receiptData.payment_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</strong>
                        </div>
                    </div>
                    
                    <div style="padding: 10px 0; margin: 10px 0;">
                        <div style="display: flex; justify-content: space-between; margin: 5px 0; color: #000;">
                            <span>Client:</span>
                            <strong style="color: #000;">${receiptData.client_name}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin: 5px 0; color: #000;">
                            <span>Email:</span>
                            <strong style="color: #000;">${receiptData.client_email}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin: 5px 0; color: #000;">
                            <span>Method:</span>
                            <strong style="color: #000;">${receiptData.payment_method.toUpperCase()}</strong>
                        </div>
                    </div>
                    
                    <div style="padding: 10px 0; margin: 10px 0; border-top: 1px dashed #333; border-bottom: 1px dashed #333;">
                        <div style="text-align: center; margin-bottom: 10px; font-weight: bold; color: #000; text-transform: uppercase;">Summary of Payment</div>
                        <div style="display: flex; justify-content: space-between; margin: 5px 0; color: #000;">
                            <span>Principal:</span>
                            <strong style="color: #000;">₱${parseFloat(receiptData.principal_paid || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin: 5px 0; color: #000;">
                            <span>Interest:</span>
                            <strong style="color: #000;">₱${parseFloat(receiptData.interest_paid || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin: 5px 0; color: #000;">
                            <span>Late Fees:</span>
                            <strong style="color: #000;">₱${parseFloat(receiptData.late_fees || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong>
                        </div>
                    </div>
                    
                    <div style="border-top: 2px solid #333; border-bottom: 2px solid #333; padding: 15px 0; margin: 10px 0;">
                        <div style="display: flex; justify-content: space-between; margin: 5px 0; font-size: 18px; font-weight: bold; color: #000;">
                            <span>TOTAL PAID:</span>
                            <span style="color: #000;">₱${parseFloat(receiptData.payment_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                        </div>
                    </div>
                    
                    ${receiptData.notes ? `
                    <div style="padding: 10px 0; margin: 10px 0; border-top: 1px dashed #333;">
                        <div style="margin-bottom: 5px; color: #000;"><strong>Notes:</strong></div>
                        <div style="font-size: 12px; color: #000;">${receiptData.notes}</div>
                    </div>
                    ` : ''}
                    
                    <div style="text-align: center; margin-top: 20px; padding-top: 10px; border-top: 1px dashed #333; font-size: 12px; color: #000;">
                        <div style="color: #000;">Processed by: ${receiptData.processed_by}</div>
                        <div style="color: #000;">Thank you for your payment!</div>
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
            const receiptData = <?php echo json_encode($_SESSION['receipt_data'] ?? []); ?>;
            
            // Log receipt printing to server
            if (receiptData.receipt_number) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.name = 'action';
                actionInput.value = 'print_receipt';
                
                const receiptInput = document.createElement('input');
                receiptInput.name = 'receipt_number';
                receiptInput.value = receiptData.receipt_number;
                
                const clientInput = document.createElement('input');
                clientInput.name = 'client_name';
                clientInput.value = receiptData.client_name;
                
                const amountInput = document.createElement('input');
                amountInput.name = 'amount';
                amountInput.value = receiptData.payment_amount;
                
                form.appendChild(actionInput);
                form.appendChild(receiptInput);
                form.appendChild(clientInput);
                form.appendChild(amountInput);
                document.body.appendChild(form);
                form.submit();
            }
            
            // Print the receipt
            const receiptContent = document.getElementById('receiptContent').innerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Payment Receipt</title>
                        <style>
                            body { 
                                font-family: monospace; 
                                margin: 20px; 
                                background: white; 
                                color: #000;
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
    <script src="responsive-script.js"></script>

</body>
</html>
