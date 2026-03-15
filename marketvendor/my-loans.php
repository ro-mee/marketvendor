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

// Get all loans for the user with payment data
$stmt = $db->prepare("SELECT l.*, 
                       COALESCE(SUM(ph.amount_paid), 0) as paid_amount,
                       COUNT(ph.payment_id) as payment_count,
                       MAX(ph.payment_date) as last_payment_date,
                       (SELECT COUNT(*) FROM payment_schedules ps WHERE ps.loan_id = l.loan_id AND ps.status = 'pending') as pending_payments,
                       (SELECT SUM(ps.total_amount) FROM payment_schedules ps WHERE ps.loan_id = l.loan_id AND ps.status = 'pending') as pending_amount
                       FROM loans l 
                       LEFT JOIN payment_history ph ON l.loan_id = ph.loan_id AND ph.status = 'completed'
                       WHERE l.user_id = ? 
                       GROUP BY l.loan_id 
                       ORDER BY l.created_at DESC");
$stmt->execute([$user_id]);
$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_loans = count($loans);
$active_loans = 0;
$pending_loans = 0;
$completed_loans = 0;
$total_amount = 0;
$total_paid_amount = 0;
$remaining_balance = 0;

foreach ($loans as &$loan) {
    // Calculate total amount (loan amount + interest)
    $loan['total_amount'] = $loan['loan_amount'] + ($loan['loan_amount'] * ($loan['interest_rate'] / 100) * ($loan['preferred_term'] / 12));
    
    // Calculate remaining balance
    $loan['remaining_balance'] = $loan['total_amount'] - $loan['paid_amount'];
    
    // Calculate progress percentage
    $loan['progress_percentage'] = $loan['total_amount'] > 0 ? ($loan['paid_amount'] / $loan['total_amount'] * 100) : 0;
    
    // Get late fees for this loan
    $late_fees = $lateFeeManager->getLateFeesByLoan($loan['loan_id']);
    $loan['late_fees'] = $late_fees;
    $loan['pending_late_fees'] = array_filter($late_fees, function($fee) {
        return $fee['status'] === 'pending';
    });
    $loan['applied_late_fees'] = array_filter($late_fees, function($fee) {
        return $fee['status'] === 'applied';
    });
    $loan['total_late_fees'] = array_sum(array_column($loan['pending_late_fees'], 'fee_amount'));
    
    $total_amount += $loan['loan_amount'];
    $total_paid_amount += $loan['paid_amount'];
    $remaining_balance += $loan['remaining_balance'];
    
    switch ($loan['status']) {
        case 'active':
            $active_loans++;
            break;
        case 'pending':
            $pending_loans++;
            break;
        case 'completed':
            $completed_loans++;
            break;
    }
}

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
    <title>My Loans - BlueLedger Finance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="enhanced-styles.css">
    <link rel="stylesheet" href="responsive-styles-fixed.css">

    <style>
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
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-100);
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .stat-label {
            font-size: 1.1rem;
            color: var(--text-300);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Payment Summary Section - Separate from Dashboard Styles */
        .payment-summary-section {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 24px;
            margin: 20px 0;
        }

        .payment-summary-section h4 {
            color: var(--text-100);
            margin-bottom: 20px;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .payment-summary-section .payment-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .payment-summary-section .stat-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 39, 75, 0.95) 100%);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            text-align: center;
        }

        .payment-summary-section .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #2563eb, #1d4ed8);
        }

        .payment-summary-section .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .payment-summary-section .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-100);
            margin-bottom: 8px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .payment-summary-section .stat-label {
            font-size: 0.9rem;
            color: var(--text-300);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .loans-section {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid var(--line);
            border-radius: var(--card-radius);
            padding: 24px;
            margin-top: 32px;
        }

        /* Quick Actions Styles */
        .quick-actions {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }

        .quick-actions h3 {
            color: var(--text-100);
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
        }

        .action-btn {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1));
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #60a5fa;
            padding: 20px;
            border-radius: 12px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: block;
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        .action-btn:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.2));
            border-color: rgba(59, 130, 246, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }

        .action-btn i {
            font-size: 2rem;
            margin-bottom: 12px;
            display: block;
        }

        .action-btn span {
            font-size: 0.9rem;
            font-weight: 500;
            display: block;
        }

        .action-btn.apply:hover {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(22, 163, 74, 0.2));
            border-color: rgba(34, 197, 94, 0.5);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);
            color: #22c55e;
        }

        .action-btn.payment:hover {
            background: linear-gradient(135deg, rgba(251, 146, 60, 0.2), rgba(249, 115, 22, 0.2));
            border-color: rgba(251, 146, 60, 0.5);
            box-shadow: 0 8px 25px rgba(251, 146, 60, 0.3);
            color: #fb923c;
        }

        .action-btn.profile:hover {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(147, 51, 234, 0.2));
            border-color: rgba(168, 85, 247, 0.5);
            box-shadow: 0 8px 25px rgba(168, 85, 247, 0.3);
            color: #a855f7;
        }

        .action-btn.print:hover {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2));
            border-color: rgba(239, 68, 68, 0.5);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .action-btn.download:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(2, 132, 199, 0.2));
            border-color: rgba(14, 165, 233, 0.5);
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
            color: #0ea5e9;
        }

        .action-btn.calculator:hover {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.2));
            border-color: rgba(245, 158, 11, 0.5);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
            color: #f59e0b;
        }

        .action-btn.support:hover {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
            border-color: rgba(16, 185, 129, 0.5);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
            color: #10b981;
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

        .btn-primary {
            background: var(--success);
            color: white;
            border: none;
            padding: 10px 20px;
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

        .table-container {
            overflow-x: auto;
        }

        .loans-table {
            width: 100%;
            border-collapse: collapse;
        }

        .loans-table th,
        .loans-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid var(--line);
        }

        .loans-table th {
            background: rgba(15, 39, 75, 0.5);
            color: var(--text-200);
            font-weight: 500;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .loans-table td {
            color: var(--text-100);
            font-size: 0.9rem;
        }

        .loans-table tbody tr:hover {
            background: rgba(30, 41, 59, 0.5);
        }

        .loan-amount {
            font-weight: 600;
            color: #60a5fa;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 16px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border: 1px solid;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            width: fit-content;
            min-width: auto;
            max-width: none;
            flex-shrink: 0;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: default;
            user-select: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .status-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .status-badge:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .status-badge:hover::before {
            opacity: 1;
        }

        .status-badge:active {
            transform: translateY(0) scale(0.98);
            transition: transform 0.1s ease;
        }

        .status-badge.active {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.25), rgba(34, 197, 94, 0.15));
            color: #16a34a;
            border-color: rgba(34, 197, 94, 0.4);
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.2);
        }

        .status-badge.active:hover {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.35), rgba(34, 197, 94, 0.25));
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.3);
        }

        .status-badge.pending {
            background: linear-gradient(135deg, rgba(251, 146, 60, 0.25), rgba(251, 146, 60, 0.15));
            color: #ea580c;
            border-color: rgba(251, 146, 60, 0.4);
            box-shadow: 0 2px 8px rgba(251, 146, 60, 0.2);
        }

        .status-badge.pending:hover {
            background: linear-gradient(135deg, rgba(251, 146, 60, 0.35), rgba(251, 146, 60, 0.25));
            box-shadow: 0 4px 16px rgba(251, 146, 60, 0.3);
        }

        .status-badge.completed {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(59, 130, 246, 0.15));
            color: #2563eb;
            border-color: rgba(59, 130, 246, 0.4);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
        }

        .status-badge.completed:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.35), rgba(59, 130, 246, 0.25));
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        }

        .status-badge.rejected {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.25), rgba(239, 68, 68, 0.15));
            color: #dc2626;
            border-color: rgba(239, 68, 68, 0.4);
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
        }

        .status-badge.rejected:hover {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.35), rgba(239, 68, 68, 0.25));
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
        }

        .btn-view {
            background: transparent;
            color: var(--success);
            border: 1px solid var(--success);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-view:hover {
            background: var(--success);
            color: white;
        }

        /* Loan Details Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(8, 22, 45, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 16px;
            padding: 0;
            max-width: 800px;
            width: 95%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 32px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(15, 39, 75, 0.5);
            border-radius: 16px 16px 0 0;
        }

        .modal-header h3 {
            color: var(--text-100);
            margin: 0;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-close {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            font-size: 1.5rem;
            font-weight: bold;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: scale(1.1);
        }

        .modal-body {
            padding: 32px;
        }
        .loan-details-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 25px;
            margin-bottom: 20px;
        }

        .loan-info-section, .payment-summary-section {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 22px;
        }

        .loan-info-section h4, .payment-summary-section h4 {
            color: var(--text-100);
            margin-bottom: 18px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-grid {
            display: grid;
            gap: 12px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-item label {
            color: var(--text-300);
            font-size: 0.85rem;
            flex: 1;
        }

        .info-item span {
            color: var(--text-100);
            font-weight: 500;
            text-align: right;
            flex: 1;
        }

        .payment-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 22px;
        }

        .payment-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 22px;
        }

        .stat-value.paid {
            color: var(--success);
        }

        .stat-value.remaining {
            color: var(--warning);
        }

        .additional-stats {
            margin: 18px 0;
            padding: 14px;
            background: rgba(15, 39, 75, 0.3);
            border-radius: 8px;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }

        .stat-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 14px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }

        .stat-item .stat-label {
            color: var(--text-300);
            font-size: 0.8rem;
            flex: 1;
        }

        .stat-item .stat-value {
            color: var(--text-100);
            font-weight: 600;
            font-size: 0.85rem;
            text-align: right;
            flex: 1;
            word-wrap: break-word;
        }

        .progress-section {
            margin-top: 18px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .progress-header span:first-child {
            color: var(--text-200);
            font-size: 0.85rem;
        }

        .progress-header span:last-child {
            color: var(--text-100);
            font-weight: 600;
            font-size: 0.85rem;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: rgba(15, 39, 75, 0.5);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--success), #059669);
            transition: width 0.3s ease;
        }

        @media (max-width: 768px) {
            .loan-details-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .payment-stats {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-300);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--text-100);
        }

        .search-filter {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-box {
            flex: 1;
            max-width: 300px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            background: rgba(15, 39, 75, 0.5);
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--text-100);
            font-size: 0.875rem;
        }

        .search-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-300);
        }

        .filter-dropdown {
            padding: 10px 16px;
            background: rgba(15, 39, 75, 0.5);
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--text-100);
            font-size: 0.875rem;
            outline: none;
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

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .search-filter {
                flex-direction: column;
            }
            
            .search-box {
                max-width: none;
            }
            
            .loans-table {
                font-size: 0.8rem;
            }
            
            .loans-table th,
            .loans-table td {
                padding: 12px 8px;
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
            <a class="nav-item active" href="my-loans.php">
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
                    <h2>My Loans</h2>
                    <p>Manage and track all your loan applications</p>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="main-content">
                <!-- Statistics Cards -->
                <div class="stats-grid">

                    <div class="stat-card">
                        <div class="stat-value"><?php echo $active_loans; ?></div>
                        <div class="stat-label">Active Loans</div>
                    </div>
                                        <div class="stat-card">
                        <div class="stat-value">₱<?php echo number_format($total_amount, 0); ?></div>
                        <div class="stat-label">Total Amount of Loan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">₱<?php echo number_format($total_paid_amount ?? 0, 0); ?></div>
                        <div class="stat-label">Paid Amount</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">₱<?php echo number_format($remaining_balance ?? 0, 0); ?></div>
                        <div class="stat-label">Remaining Balance</div>
                    </div>

                </div>

                <!-- Loans Section -->
                <div class="loans-section">
                    <div class="section-header">
                        <h2 class="section-title">Loan Applications</h2>
                        <a href="apply-loan.php" class="btn-primary">
                            <i class="fas fa-plus"></i> Apply New Loan
                        </a>
                    </div>

                    <?php if (count($loans) > 0): ?>
                        <!-- Search and Filter -->
                        <div class="search-filter">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchInput" placeholder="Search loans...">
                            </div>
                            <select class="filter-dropdown" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>

                        <!-- Loans Table -->
                        <div class="table-container">
                            <table class="loans-table" id="loansTable">
                                <thead>
                                    <tr>
                                        <th>Loan ID</th>
                                        <th>Amount</th>
                                        <th>Frequency</th>
                                        <th>Term</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Late Fees</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($loans as $loan): ?>
                                        <tr data-status="<?php echo strtolower($loan['status']); ?>">
                                            <td><?php echo htmlspecialchars($loan['loan_id']); ?></td>
                                            <td class="loan-amount">₱<?php echo number_format($loan['loan_amount'], 2); ?></td>
                                            <td><?php echo ucfirst(htmlspecialchars($loan['payment_frequency'])); ?></td>
                                            <td><?php echo htmlspecialchars($loan['preferred_term']); ?> months</td>
                                            <td><?php echo htmlspecialchars($loan['loan_purpose']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $loan['status']; ?>">
                                                    <?php echo htmlspecialchars($loan['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($loan['total_late_fees'] > 0): ?>
                                                    <span style="color: #ef4444; font-weight: bold;">
                                                        ₱<?php echo number_format($loan['total_late_fees'], 2); ?>
                                                    </span>
                                                    <br>
                                                    <small style="color: #94a3b8;">
                                                        <?php echo count($loan['pending_late_fees']); ?> pending
                                                    </small>
                                                <?php else: ?>
                                                    <span style="color: #10b981;">₱0.00</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($loan['created_at'])); ?></td>
                                            <td>
                                                <button class="btn-view" onclick="showLoanDetails('<?php echo $loan['loan_id']; ?>')">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-invoice"></i>
                            <h3>No Loans Found</h3>
                            <p>You haven't applied for any loans yet.</p>
                            <a href="apply-loan.php" class="btn-primary">
                                <i class="fas fa-plus"></i> Apply Your First Loan
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="actions-grid">
                        <a href="apply-loan.php" class="action-btn apply">
                            <i class="fas fa-plus-circle"></i>
                            <span>Apply New Loan</span>
                        </a>
                        <a href="client-payment-history.php" class="action-btn payment">
                            <i class="fas fa-credit-card"></i>
                            <span>Payment History</span>
                        </a>
                        <a href="profile.php" class="action-btn profile">
                            <i class="fas fa-user-circle"></i>
                            <span>Update Profile</span>
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#loansTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });

        // Status filter
        document.getElementById('statusFilter').addEventListener('change', function() {
            const filterValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#loansTable tbody tr');
            
            rows.forEach(row => {
                if (filterValue === '') {
                    row.style.display = '';
                } else {
                    row.style.display = row.dataset.status === filterValue ? '' : 'none';
                }
            });
        });
        
        // Loan details modal functionality
        const loansData = <?php echo json_encode($loans); ?>;
        
        function showLoanDetails(loanId) {
            const loan = loansData.find(l => l.loan_id === loanId);
            if (!loan) {
                console.error('Loan not found for ID:', loanId);
                return;
            }
            
            // Use real database data
            const totalPayments = loan.total_amount || 0;
            const paidAmount = loan.paid_amount || 0;
            const remainingBalance = loan.remaining_balance || 0;
            const progressPercentage = loan.progress_percentage || 0;
            const pendingPayments = loan.pending_payments || 0;
            const pendingAmount = loan.pending_amount || 0;
            const paymentCount = loan.payment_count || 0;
            const lastPaymentDate = loan.last_payment_date;
            
            const loanDetailsHTML = `
                <div class="loan-details-grid">
                    <div class="loan-info-section">
                        <h4><i class="fas fa-info-circle"></i> Loan Information</h4>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Loan ID:</label>
                                <span><strong>${loan.loan_id}</strong></span>
                            </div>
                            <div class="info-item">
                                <label>Principal Amount:</label>
                                <span>₱${parseFloat(loan.loan_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                            </div>
                            <div class="info-item">
                                <label>Total Amount (with Interest):</label>
                                <span>₱${parseFloat(totalPayments).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                            </div>
                            <div class="info-item">
                                <label>Interest Rate:</label>
                                <span>${loan.interest_rate}%</span>
                            </div>
                            <div class="info-item">
                                <label>Payment Frequency:</label>
                                <span>${ucfirst(loan.payment_frequency)}</span>
                            </div>
                            <div class="info-item">
                                <label>Loan Term:</label>
                                <span>${loan.preferred_term} months</span>
                            </div>
                            <div class="info-item">
                                <label>Loan Purpose:</label>
                                <span>${loan.loan_purpose}</span>
                            </div>
                            <div class="info-item">
                                <label>Status:</label>
                                <span class="status-badge ${loan.status}">${loan.status}</span>
                            </div>
                            <div class="info-item">
                                <label>Date Applied:</label>
                                <span>${new Date(loan.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                            </div>
                            ${lastPaymentDate ? `
                            <div class="info-item">
                                <label>Last Payment:</label>
                                <span>${new Date(lastPaymentDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <div class="payment-summary-section">
                        <h4><i class="fas fa-chart-pie"></i> Payment Summary</h4>
                        <div class="payment-stats">
                            <div class="stat-card">
                                <div class="stat-label">Total Amount</div>
                                <div class="stat-value">₱${parseFloat(totalPayments).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Paid Amount</div>
                                <div class="stat-value paid">₱${parseFloat(paidAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Remaining Balance</div>
                                <div class="stat-value remaining">₱${parseFloat(remainingBalance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                            </div>
                        </div>
                        
                        <div class="additional-stats">
                            <div class="stat-row">
                                <div class="stat-item">
                                    <span class="stat-label">Payments Made:</span>
                                    <span class="stat-value">${paymentCount}</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">Pending Payments:</span>
                                    <span class="stat-value">${pendingPayments}</span>
                                </div>
                                ${pendingAmount > 0 ? `
                                <div class="stat-item">
                                    <span class="stat-label">Pending Amount:</span>
                                    <span class="stat-value">₱${parseFloat(pendingAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        
                        <div class="progress-section">
                            <div class="progress-header">
                                <span>Payment Progress</span>
                                <span>${progressPercentage.toFixed(1)}%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: ${progressPercentage}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('loanDetailsContent').innerHTML = loanDetailsHTML;
            document.getElementById('loanDetailsModal').style.display = 'block';
        }
        
        function closeLoanDetailsModal() {
            document.getElementById('loanDetailsModal').style.display = 'none';
        }
        
        function ucfirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('loanDetailsModal');
            if (event.target == modal) {
                closeLoanDetailsModal();
            }
        }

        // Download Loan Summary Function
        function downloadLoanSummary() {
            // Create loan summary data
            const loanData = {
                activeLoans: <?php echo $active_loans; ?>,
                totalAmount: <?php echo $total_amount; ?>,
                paidAmount: <?php echo $total_paid_amount ?? 0; ?>,
                remainingBalance: <?php echo $remaining_balance ?? 0; ?>,
                generatedDate: new Date().toLocaleDateString(),
                userName: '<?php echo htmlspecialchars($user_info['name'] ?? ''); ?>'
            };

            // Create CSV content
            let csvContent = "Loan Summary Report\n\n";
            csvContent += "Generated Date," + loanData.generatedDate + "\n";
            csvContent += "User," + loanData.userName + "\n\n";
            csvContent += "Metric,Value\n";
            csvContent += "Active Loans," + loanData.activeLoans + "\n";
            csvContent += "Total Amount,₱" + loanData.totalAmount.toLocaleString() + "\n";
            csvContent += "Paid Amount,₱" + loanData.paidAmount.toLocaleString() + "\n";
            csvContent += "Remaining Balance,₱" + loanData.remainingBalance.toLocaleString() + "\n";

            // Create blob and download
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", "loan_summary_" + new Date().toISOString().split('T')[0] + ".csv");
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
    <script src="responsive-script.js"></script>
</div>

<!-- Loan Details Modal -->
<div id="loanDetailsModal" class="modal">
    <div class="modal-content" style="max-width: 800px; width: 95%;">
        <div class="modal-header">
            <h3><i class="fas fa-file-invoice"></i> Loan Details</h3>
            <button class="modal-close" onclick="closeLoanDetailsModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="loanDetailsContent">
                <!-- Loan details will be loaded here -->
            </div>
            <div class="form-buttons" style="margin-top: 20px;">
                <button type="button" class="btn-secondary" onclick="closeLoanDetailsModal()">Close</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
