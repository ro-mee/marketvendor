<?php
session_start();
require_once 'config/database.php';
require_once 'includes/audit_helper.php';

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Handle receipt download
if (isset($_GET['download_receipt']) && isset($_GET['payment_id'])) {
    $payment_id = $_GET['payment_id'];
    
    try {
        // Get payment details
        $stmt = $db->prepare("SELECT ph.*, l.loan_purpose, u.name as client_name, u.email as client_email 
                              FROM payment_history ph 
                              LEFT JOIN loans l ON ph.loan_id = l.loan_id 
                              LEFT JOIN users u ON l.user_id = u.id
                              WHERE ph.payment_id = ? AND ph.user_id = ?");
        $stmt->execute([$payment_id, $user_id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($payment) {
            // Get user info for receipt
            $user_stmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
            $user_stmt->execute([$user_id]);
            $receipt_user_info = $user_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get loan details for remaining balance
            $loan_stmt = $db->prepare("SELECT loan_amount, loan_purpose FROM loans WHERE loan_id = ?");
            $loan_stmt->execute([$payment['loan_id']]);
            $loan_details = $loan_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calculate remaining balance
            $total_paid_stmt = $db->prepare("SELECT SUM(amount_paid) as total_paid FROM payment_history WHERE loan_id = ? AND status = 'Completed'");
            $total_paid_stmt->execute([$payment['loan_id']]);
            $total_paid = $total_paid_stmt->fetch(PDO::FETCH_ASSOC)['total_paid'] ?? 0;
            $remaining_balance = ($loan_details['loan_amount'] ?? 0) - $total_paid;
            
            // Log receipt download
            logAudit('download', "Receipt downloaded for payment #{$payment_id} - Amount: ₱" . number_format($payment['amount_paid'], 2));
            
            // Store receipt data in session for popup
            $_SESSION['receipt_data'] = [
                'payment' => $payment,
                'user_info' => $receipt_user_info,
                'loan_details' => $loan_details,
                'remaining_balance' => $remaining_balance
            ];
            
            // Redirect back with receipt popup flag
            header('Location: client-payment-history.php?show_receipt=1');
            exit();
        } else {
            $_SESSION['error_message'] = "Receipt not found or access denied.";
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error generating receipt: " . $e->getMessage();
    }
    
    header('Location: client-payment-history.php');
    exit();
}

// Handle Excel export
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    try {
        // Check if exporting all data
        $export_all = isset($_GET['all']) && $_GET['all'] === 'true';
        
        if ($export_all) {
            // Export all payment history without filters
            $query = "SELECT ph.payment_id, ph.loan_id, ph.payment_date, ph.amount_paid, ph.payment_method, 
                             ph.transaction_id, ph.status, ph.receipt_number, l.loan_purpose
                      FROM payment_history ph 
                      LEFT JOIN loans l ON ph.loan_id = l.loan_id 
                      WHERE ph.user_id = ?
                      ORDER BY ph.payment_date DESC";
            
            $params = [$user_id];
        } else {
            // Get filters (for backward compatibility)
            $search = $_GET['search'] ?? '';
            $status_filter = $_GET['status'] ?? '';
            $date_from = $_GET['date_from'] ?? '';
            $date_to = $_GET['date_to'] ?? '';
            
            // Build query with filters
            $query = "SELECT ph.payment_id, ph.loan_id, ph.payment_date, ph.amount_paid, ph.payment_method, 
                             ph.transaction_id, ph.status, ph.receipt_number, l.loan_purpose
                      FROM payment_history ph 
                      LEFT JOIN loans l ON ph.loan_id = l.loan_id 
                      WHERE ph.user_id = ?";
            
            $params = [$user_id];
            
            if (!empty($search)) {
                $query .= " AND (ph.transaction_id LIKE ? OR ph.receipt_number LIKE ? OR l.loan_purpose LIKE ?)";
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            if (!empty($status_filter)) {
                $query .= " AND ph.status = ?";
                $params[] = $status_filter;
            }
            
            if (!empty($date_from)) {
                $query .= " AND ph.payment_date >= ?";
                $params[] = $date_from;
            }
            
            if (!empty($date_to)) {
                $query .= " AND ph.payment_date <= ?";
                $params[] = $date_to;
            }
            
            $query .= " ORDER BY ph.payment_date DESC";
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create Excel file content
        $filename = ($export_all ? "all_" : "filtered_") . "payment_history_" . date('Y-m-d_H-i-s') . ".xls";
        
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        header('Pragma: public');
        header('Content-Transfer-Encoding: binary');
        
        // Create tab-separated values for Excel compatibility
        $excel_content = "Payment ID\tLoan ID\tPayment Date\tAmount Paid\tPayment Method\tTransaction ID\tStatus\tReceipt Number\tLoan Purpose\n";
        
        foreach ($payments as $payment) {
            $excel_content .= sprintf(
                "%s\t%s\t%s\t%.2f\t%s\t%s\t%s\t%s\t%s\n",
                $payment['payment_id'],
                $payment['loan_id'],
                $payment['payment_date'],
                $payment['amount_paid'],
                $payment['payment_method'],
                $payment['transaction_id'],
                $payment['status'],
                $payment['receipt_number'],
                str_replace(["\t", "\n"], [" ", " "], $payment['loan_purpose'] ?? '')
            );
        }
        
        // Output content
        header('Content-Length: ' . strlen($excel_content));
        echo $excel_content;
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error exporting data: " . $e->getMessage();
        header('Location: client-payment-history.php');
        exit();
    }
}

// Function to generate receipt HTML
function generateReceiptHTML($payment, $user_info, $loan_details = null, $remaining_balance = 0) {
    $receipt_number = $payment['receipt_number'] ?? 'RCP' . date('Ymd') . str_pad($payment['payment_id'], 4, '0', STR_PAD_LEFT);
    $payment_date = date('F d, Y', strtotime($payment['payment_date']));
    $amount = number_format($payment['amount_paid'], 2);
    $status = $payment['status'] ?? 'Completed';
    $client_name = $user_info['name'] ?? 'Client';
    $client_email = $user_info['email'] ?? 'client@example.com';
    $loan_purpose = $payment['loan_purpose'] ?? 'General Loan';
    $payment_method = $payment['payment_method'] ?? 'Online Payment';
    $transaction_id = $payment['transaction_id'] ?? $receipt_number;
    $loan_amount = number_format($loan_details['loan_amount'] ?? 0, 2);
    $remaining_balance_formatted = number_format($remaining_balance, 2);
    
    // Calculate principal and interest (5% annual rate)
    $payment_amount = $payment['amount_paid'];
    $annualRate = 0.05;
    $dailyRate = $annualRate / 365;
    $interest = round($payment_amount * ($dailyRate / (1 + $dailyRate)) * 100) / 100;
    $principal = round(($payment_amount - $interest) * 100) / 100;
    
    // Get late fees for this payment
    $late_fees = 0;
    try {
        global $db;
        $late_fee_stmt = $db->prepare("
            SELECT SUM(fee_amount) as total_late_fees
            FROM late_fees 
            WHERE payment_id = ? AND status = 'applied'
        ");
        $late_fee_stmt->execute([$payment['payment_id']]);
        $late_fee_result = $late_fee_stmt->fetch(PDO::FETCH_ASSOC);
        $late_fees = $late_fee_result['total_late_fees'] ?? 0;
    } catch (Exception $e) {
        $late_fees = 0;
    }
    
    return "
    <div class='receipt-print-container'>
        <div class='receipt-container'>
            <div class='receipt-header'>
                <h1>PAYMENT RECEIPT</h1>
                <div class='company-name'>Market Vendor Loan System</div>
            </div>
            
            <div class='receipt-section'>
                <div class='receipt-row'>
                    <span class='label'>Receipt No:</span>
                    <span class='value'>$receipt_number</span>
                </div>
                <div class='receipt-row'>
                    <span class='label'>Payment ID:</span>
                    <span class='value'>" . htmlspecialchars($payment['payment_id']) . "</span>
                </div>
                <div class='receipt-row'>
                    <span class='label'>Loan ID:</span>
                    <span class='value'>#" . str_pad($payment['loan_id'] ?? 0, 6, '0', STR_PAD_LEFT) . "</span>
                </div>
                <div class='receipt-row'>
                    <span class='label'>Date:</span>
                    <span class='value'>$payment_date</span>
                </div>
            </div>
            
            <div class='receipt-section'>
                <div class='receipt-row'>
                    <span class='label'>Client:</span>
                    <span class='value'>" . htmlspecialchars($client_name) . "</span>
                </div>
                <div class='receipt-row'>
                    <span class='label'>Email:</span>
                    <span class='value'>" . htmlspecialchars($client_email) . "</span>
                </div>
                <div class='receipt-row'>
                    <span class='label'>Method:</span>
                    <span class='value'>" . htmlspecialchars(strtoupper($payment_method)) . "</span>
                </div>
            </div>
            
            <div class='receipt-section'>
                <div class='section-title'>Summary of Payment</div>
                <div class='receipt-row'>
                    <span class='label'>Principal:</span>
                    <span class='value'>₱" . number_format($principal, 2) . "</span>
                </div>
                <div class='receipt-row'>
                    <span class='label'>Interest:</span>
                    <span class='value'>₱" . number_format($interest, 2) . "</span>
                </div>
                <div class='receipt-row'>
                    <span class='label'>Late Fees:</span>
                    <span class='value'>₱" . number_format($late_fees, 2) . "</span>
                </div>
            </div>
            
            <div class='total-section'>
                <div class='receipt-row total-row'>
                    <span class='total-label'>TOTAL PAID:</span>
                    <span class='total-value'>₱$amount</span>
                </div>
            </div>
            
            <div class='receipt-footer'>
                <div class='receipt-row'>
                    <span class='label'>Processed by:</span>
                    <span class='value'>" . htmlspecialchars($client_name) . "</span>
                </div>
                <div class='thank-you'>Thank you for your payment!</div>
            </div>
        </div>
    </div>
    
    <style>
        /* Receipt-specific styles - scoped to receipt container */
        .receipt-print-container * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        .receipt-print-container body {
            font-family: 'Courier New', monospace;
            background: white;
            color: #000;
            padding: 20px;
            line-height: 1.4;
        }
        
        .receipt-print-container .receipt-container {
            max-width: 400px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 30px 20px;
            background: white;
            color: #000;
        }
        
        .receipt-print-container .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        
        .receipt-print-container .receipt-header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .receipt-print-container .company-name {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        
        .receipt-print-container .receipt-section {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        
        .receipt-print-container .section-title {
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-size: 12px;
            text-align: center;
        }
        
        .receipt-print-container .receipt-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            font-size: 12px;
        }
        
        .receipt-print-container .label {
            text-align: left;
            font-weight: normal;
            font-size: 11px;
        }
        
        .receipt-print-container .value {
            text-align: right;
            font-weight: bold;
            font-size: 11px;
        }
        
        .receipt-print-container .total-section {
            margin: 15px 0;
            padding: 12px 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }
        
        .receipt-print-container .total-row {
            font-size: 14px;
            font-weight: bold;
        }
        
        .receipt-print-container .total-label {
            text-align: left;
            font-size: 13px;
        }
        
        .receipt-print-container .total-value {
            text-align: right;
            font-size: 16px;
        }
        
        .receipt-print-container .receipt-footer {
            margin-top: 15px;
            text-align: center;
        }
        
        .receipt-print-container .thank-you {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed #000;
            font-size: 10px;
            font-style: italic;
        }
        
        @media print {
            .receipt-print-container body {
                margin: 0;
                padding: 10px;
            }
            
            .receipt-print-container .receipt-container {
                margin: 0;
                max-width: 100%;
                box-shadow: none;
            }
            
            @page {
                margin: 10mm;
                size: auto;
            }
        }
    </style>";
    }

// Get user info for header
$stmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count for pagination
$stmt = $db->prepare("SELECT COUNT(*) as total FROM payment_history WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_payments = $total_result['total'];
$total_pages = ceil($total_payments / $limit);
           

// Get user info for header
$stmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$where_conditions = ["ph.user_id = ?"];
$params = [$user_id];

if ($search) {
    $where_conditions[] = "(ph.loan_id LIKE ? OR ph.payment_method LIKE ? OR ph.transaction_id LIKE ? OR ph.receipt_number LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($status_filter) {
    $where_conditions[] = "ph.status = ?";
    $params[] = $status_filter;
}

if ($date_from) {
    $where_conditions[] = "ph.payment_date >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "ph.payment_date <= ?";
    $params[] = $date_to;
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM payment_history ph $where_clause";
$count_stmt = $db->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $per_page);

// Get payment history with loan details
$sql = "SELECT ph.*, l.loan_amount, l.loan_purpose, l.created_at as application_date,
        CASE 
            WHEN ph.status = 'completed' THEN 'Completed'
            WHEN ph.status = 'partial' THEN 'Partial'
            WHEN ph.status = 'failed' THEN 'Failed'
            ELSE ph.status
        END as status_label
        FROM payment_history ph
        LEFT JOIN loans l ON ph.loan_id = l.loan_id
        $where_clause
        ORDER BY ph.payment_date DESC, ph.created_at DESC
        LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$stmt = $db->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$stats_sql = "SELECT 
        COUNT(*) as total_payments,
        SUM(CASE WHEN status = 'completed' THEN COALESCE(amount_paid, 0) ELSE 0 END) as total_paid,
        SUM(CASE WHEN status = 'partial' THEN COALESCE(amount_paid, 0) ELSE 0 END) as pending_amount,
        SUM(CASE WHEN status = 'failed' THEN COALESCE(amount_paid, 0) ELSE 0 END) as failed_amount
        FROM payment_history 
        WHERE user_id = ?";

$stats_params = [$user_id];

if ($date_from) {
    $stats_sql .= " AND payment_date >= ?";
    $stats_params[] = $date_from;
}

if ($date_to) {
    $stats_sql .= " AND payment_date <= ?";
    $stats_params[] = $date_to;
}

$stats_stmt = $db->prepare($stats_sql);
$stats_stmt->execute($stats_params);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get monthly trends for current year
$monthly_sql = "SELECT 
        MONTH(payment_date) as month,
        SUM(CASE WHEN status = 'paid' THEN amount_paid ELSE 0 END) as total_paid,
        COUNT(*) as payment_count
        FROM payment_history 
        WHERE user_id = ? AND YEAR(payment_date) = YEAR(CURRENT_DATE)
        GROUP BY MONTH(payment_date)
        ORDER BY month";
$monthly_stmt = $db->prepare($monthly_sql);
$monthly_stmt->execute([$user_id]);
$monthly_data = $monthly_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - Market Vendor Loan</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 39, 75, 0.95) 100%);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 28px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #2563eb, #1d4ed8);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 16px;
            display: inline-block;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-100);
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .stat-label {
            color: var(--text-300);
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .controls-section {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .con.table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px 20px;
            background: rgba(30, 41, 59, 0.95);
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }

        .table-header h2 {
            color: #f1f5f9;
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-header h2 i {
            color: #94a3b8;
            font-size: 16px;
        }

        .table-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .table-info {
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
        }

        .btn-export {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-export:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-export i {
            font-size: 12px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 16px 12px 45px;
            background: rgba(15, 39, 75, 0.6);
            border: 1px solid var(--line);
            border-radius: 12px;
            color: var(--text-100);
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: rgba(15, 39, 75, 0.8);
        }

        .search-box::before {
            content: '🔍';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
        }

        .filter-dropdown {
            padding: 12px 16px;
            background: rgba(15, 39, 75, 0.6);
            border: 1px solid var(--line);
            border-radius: 12px;
            color: var(--text-100);
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .filter-dropdown:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: rgba(15, 39, 75, 0.8);
        }

        .btn-export {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }

        .table-container {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .table-header {
            background: linear-gradient(135deg, rgba(15, 39, 75, 0.9) 0%, rgba(30, 41, 59, 0.9) 100%);
            padding: 20px 24px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-100);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-info {
            color: var(--text-300);
            font-size: 0.85rem;
        }

        .payments-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payments-table th {
            background: rgba(15, 39, 75, 0.8);
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: var(--text-100);
            border-bottom: 2px solid var(--line);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .payments-table td {
            padding: 16px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            color: var(--text-200);
            font-size: 0.9rem;
        }

        .payments-table tbody tr {
            transition: all 0.2s ease;
        }

        .payments-table tbody tr:hover {
            background: rgba(59, 130, 246, 0.1);
        }

        .loan-id-cell {
            font-weight: 600;
            color: #3b82f6;
        }

        .loan-purpose {
            font-size: 0.8rem;
            color: var(--text-300);
            margin-top: 4px;
        }

        .amount-cell {
            font-weight: 600;
            color: #10b981;
            font-size: 1rem;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .status-paid {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-pending {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.2), rgba(245, 158, 11, 0.2));
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .status-failed {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2));
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 40px;
            color: var(--text-300);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.4;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 12px;
            color: var(--text-200);
        }

        .empty-state p {
            font-size: 0.95rem;
            max-width: 400px;
            margin: 0 auto;
            line-height: 1.6;
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
            flex-wrap: wrap;
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
        }

        .pagination a,
        .pagination span {
            padding: 10px 16px;
            background: rgba(15, 39, 75, 0.6);
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--text-200);
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: center;
        }

        .pagination a:hover {
            background: rgba(59, 130, 246, 0.2);
            color: var(--text-100);
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateY(-1px);
        }

        .pagination .current {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border-color: #3b82f6;
            font-weight: 600;
        }

        .receipt-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .receipt-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            background: linear-gradient(135deg, #059669, #047857);
        }

        .receipt-disabled {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: rgba(100, 116, 139, 0.2);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.3);
            border-radius: 8px;
            font-size: 14px;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .message {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-weight: 500;
            animation: slideInDown 0.3s ease-out;
        }

        .message.error-message {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .message.success-message {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }

        .message i {
            font-size: 18px;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Modal Styles */
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
            max-width: 800px;
            width: 95%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(30, 41, 59, 0.95);
            border-radius: 12px 12px 0 0;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #f1f5f9;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-close {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 24px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .modal-close:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .modal-body {
            padding: 24px;
            background: rgba(30, 41, 59, 0.95);
            max-height: 60vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 20px 24px;
            border-top: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(30, 41, 59, 0.95);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            border-radius: 0 0 12px 12px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            min-width: 120px;
            font-size: 14px;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #64748b, #475569);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            min-width: 120px;
            font-size: 14px;
        }

        .btn-secondary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.3);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 768px) {
            .controls {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .payments-table {
                min-width: 600px;
            }
            
            .controls-header {
                flex-direction: column;
                gap: 16px;
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
            <a class="nav-item active" href="client-payment-history.php">
                <i class="fas fa-history"></i> Payment History
            </a>
            <a class="nav-item" href="profile.php">
                <i class="fas fa-user"></i> Profile
            </a>

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
                    <h2>Payment History</h2>
                    <p>View your complete payment history and statistics</p>
                </div>
            </header>

            <!-- Messages -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="message error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['error_message']); ?></span>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>
            
            
            <!-- Receipt Modal -->
            <?php if (isset($_GET['show_receipt']) && isset($_SESSION['receipt_data'])): ?>
                <?php
                $receipt_payment = $_SESSION['receipt_data']['payment'];
                $receipt_user = $_SESSION['receipt_data']['user_info'];
                $receipt_loan_details = $_SESSION['receipt_data']['loan_details'] ?? null;
                $receipt_remaining_balance = $_SESSION['receipt_data']['remaining_balance'] ?? 0;
                unset($_SESSION['receipt_data']);
                ?>
                <div id="receiptModal" class="modal" style="display: block;">
                    <div class="modal-content" style="max-width: 800px; width: 95%;">
                        <div class="modal-header">
                            <h3><i class="fas fa-receipt"></i> Payment Receipt</h3>
                            <button class="modal-close" onclick="closeReceiptModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div id="receiptContent">
                                <?php echo generateReceiptHTML($receipt_payment, $receipt_user, $receipt_loan_details, $receipt_remaining_balance); ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-primary" onclick="printReceipt()">
                                <i class="fas fa-print"></i> Print to PDF
                            </button>
                            <button type="button" class="btn-secondary" onclick="closeReceiptModal()">
                                <i class="fas fa-times"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <main class="main-content">
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value">₱<?php echo number_format($stats['total_paid'] ?? 0, 2); ?></div>
                        <div class="stat-label">Total Amount Paid</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_payments'] ?? 0; ?></div>
                        <div class="stat-label">Total Payments</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">₱<?php echo number_format($stats['pending_amount'] ?? 0, 2); ?></div>
                        <div class="stat-label">Pending Amount</div>
                    </div>
     
                </div>

                <!-- Payments Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2><i class="fas fa-history"></i> Payment History</h2>
                        <div class="table-actions">
                            <span class="table-info"><?php echo $total_records; ?> payment records found</span>
                            <a href="client-payment-history.php?export=excel&all=true" class="btn-export">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </div>
                    </div>
                    <?php if (count($payments) > 0): ?>
                        <table class="payments-table">
                            <thead>
                                <tr>
                                    <th>Payment Date</th>
                                    <th>Loan Details</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Status</th>
                                    <th>Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                        <td>
                                            <?php if ($payment['loan_id']): ?>
                                                <div class="loan-id-cell">#<?php echo str_pad($payment['loan_id'], 6, '0', STR_PAD_LEFT); ?></div>
                                                <div class="loan-purpose"><?php echo htmlspecialchars($payment['loan_purpose'] ?? 'Loan'); ?></div>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="amount-cell">₱<?php echo number_format($payment['amount_paid'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_method'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($payment['transaction_id'] ?? $payment['receipt_number'] ?? '-'); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($payment['status_label']); ?>">
                                                <?php echo htmlspecialchars($payment['status_label']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($payment['status_label'] === 'Completed'): ?>
                                                <a href="client-payment-history.php?download_receipt=1&payment_id=<?php echo $payment['payment_id']; ?>" 
                                                   class="receipt-btn" 
                                                   title="Download Receipt">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="receipt-disabled" title="Receipt available for completed payments only">
                                                    <i class="fas fa-download"></i>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-receipt"></i>
                            <h3>No payment history found</h3>
                            <p>Your payment transactions will appear here once you make payments. Start by applying for a loan and making your first payment!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="pagination-btn">
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
                                echo '<a href="?page=1&search=' . urlencode($search) . '&status=' . urlencode($status_filter) . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to) . '" class="pagination-number">1</a>';
                                if ($start_page > 2) {
                                    echo '<span style="color: var(--text-300); padding: 0 8px;">...</span>';
                                }
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++) {
                                $active_class = $i == $page ? 'active' : '';
                                echo '<a href="?page=' . $i . '&search=' . urlencode($search) . '&status=' . urlencode($status_filter) . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to) . '" class="pagination-number ' . $active_class . '">' . $i . '</a>';
                            }
                            
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<span style="color: var(--text-300); padding: 0 8px;">...</span>';
                                }
                                echo '<a href="?page=' . $total_pages . '&search=' . urlencode($search) . '&status=' . urlencode($status_filter) . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to) . '" class="pagination-number">' . $total_pages . '</a>';
                            }
                            ?>
                        </div>
                        
                        <span class="pagination-info">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </span>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="pagination-btn">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        // Search functionality
        document.querySelector('.search-box input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchValue = this.value;
                const currentUrl = new URL(window.location);
                currentUrl.searchParams.set('search', searchValue);
                currentUrl.searchParams.set('page', '1');
                window.location.href = currentUrl.toString();
            }
        });

        // Filter functionality
        document.querySelectorAll('.filter-dropdown').forEach(dropdown => {
            dropdown.addEventListener('change', function() {
                const currentUrl = new URL(window.location);
                if (this.value) {
                    currentUrl.searchParams.set(this.name || this.getAttribute('data-filter'), this.value);
                } else {
                    currentUrl.searchParams.delete(this.name || this.getAttribute('data-filter'));
                }
                currentUrl.searchParams.set('page', '1');
                window.location.href = currentUrl.toString();
            });
        });

        // Export functionality
        document.querySelector('.btn-export').addEventListener('click', function(e) {
            e.preventDefault();
            exportPaymentHistory();
        });

        function exportPaymentHistory() {
    // Build export URL for all data
    const params = new URLSearchParams();
    params.set('export', 'excel');
    params.set('all', 'true'); // Export all data
    
    // Create download link
    const exportUrl = 'client-payment-history.php?' + params.toString();
    const link = document.createElement('a');
    link.href = exportUrl;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

        // Receipt modal functions
        function closeReceiptModal() {
            const modal = document.getElementById('receiptModal');
            if (modal) {
                modal.style.display = 'none';
                // Remove modal from DOM to prevent layout issues
                setTimeout(() => {
                    if (modal.parentNode) {
                        modal.parentNode.removeChild(modal);
                    }
                }, 300);
            }
        }

        function printReceipt() {
            const receiptContent = document.getElementById('receiptContent');
            if (receiptContent) {
                // Store original body content
                const originalContent = document.body.innerHTML;
                const originalBodyClass = document.body.className;
                
                // Create a temporary container for receipt
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = receiptContent.innerHTML;
                tempContainer.style.position = 'fixed';
                tempContainer.style.top = '0';
                tempContainer.style.left = '0';
                tempContainer.style.width = '100%';
                tempContainer.style.height = '100%';
                tempContainer.style.backgroundColor = 'white';
                tempContainer.style.zIndex = '9999';
                tempContainer.style.padding = '20px';
                tempContainer.style.boxSizing = 'border-box';
                tempContainer.className = 'receipt-print-container';
                
                // Add to body
                document.body.appendChild(tempContainer);
                
                // Hide original content temporarily
                const originalElements = document.body.children;
                for (let i = 0; i < originalElements.length - 1; i++) {
                    originalElements[i].style.display = 'none';
                }
                
                // Print the receipt
                window.print();
                
                // Restore original content after printing
                setTimeout(() => {
                    // Remove temp container
                    if (tempContainer.parentNode) {
                        tempContainer.parentNode.removeChild(tempContainer);
                    }
                    
                    // Show original content
                    for (let i = 0; i < originalElements.length - 1; i++) {
                        originalElements[i].style.display = '';
                    }
                }, 100);
            }
        }

        // Auto-close modal if show_receipt parameter is present
        if (window.location.search.includes('show_receipt=1')) {
            // Remove the parameter from URL without page reload
            const url = new URL(window.location);
            url.searchParams.delete('show_receipt');
            window.history.replaceState({}, document.title, url.toString());
            
            // Close modal after a delay to allow user to see it
            setTimeout(() => {
                // Don't auto-close, let user close manually
            }, 100);
        }
    </script>
    <script src="responsive-script.js"></script>

</body>
</html>
