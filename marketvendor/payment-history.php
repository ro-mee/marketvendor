<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get pagination parameters
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count for pagination (admin sees all payments)
$stmt = $db->prepare("SELECT COUNT(*) as total FROM payment_history");
$stmt->execute();
$total_result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_payments = $total_result['total'];
$total_pages = ceil($total_payments / $limit);

// Get payment history with pagination (admin sees all payments)
$stmt = $db->prepare("SELECT ph.*, l.loan_amount, l.loan_purpose, u.name as client_name, u.email as client_email 
                      FROM payment_history ph 
                      LEFT JOIN loans l ON ph.loan_id = l.loan_id
                      LEFT JOIN users u ON l.user_id = u.id 
                      ORDER BY ph.payment_date DESC, ph.created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$limit, $offset]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug: Show what we retrieved
error_log("Payment History Query: " . $stmt->queryString);
error_log("Payments Retrieved: " . count($payments) . " records");
if (!empty($payments)) {
    error_log("First Payment: " . json_encode($payments[0]));
}

// Calculate principal and interest for payments that don't have them
foreach ($payments as &$payment) {
    if (is_null($payment['principal_paid']) || is_null($payment['interest_paid'])) {
        // Calculate principal and interest (5% annual rate) if not provided
        $annualRate = 0.05;
        $dailyRate = $annualRate / 365;
        $interest = round($payment['amount_paid'] * ($dailyRate / (1 + $dailyRate)) * 100) / 100;
        $principal = round(($payment['amount_paid'] - $interest) * 100) / 100;
        
        $payment['principal_paid'] = $principal;
        $payment['interest_paid'] = $interest;
    }
    
    // Calculate late fees for this payment
    $payment['late_fees'] = 0;
    try {
        $late_fee_stmt = $db->prepare("
            SELECT SUM(fee_amount) as total_late_fees
            FROM late_fees 
            WHERE payment_id = ? AND status = 'applied'
        ");
        $late_fee_stmt->execute([$payment['payment_id']]);
        $late_fee_result = $late_fee_stmt->fetch(PDO::FETCH_ASSOC);
        $payment['late_fees'] = $late_fee_result['total_late_fees'] ?? 0;
    } catch (Exception $e) {
        $payment['late_fees'] = 0;
    }
    
    // Calculate total amount (principal + interest + late fees)
    $payment['total_amount'] = $payment['principal_paid'] + $payment['interest_paid'] + $payment['late_fees'];
}

// Calculate statistics
$stmt = $db->prepare("SELECT 
    COUNT(*) as total_count,
    SUM(amount_paid) as total_amount,
    SUM(principal_paid) as total_principal,
    SUM(interest_paid) as total_interest,
    MIN(payment_date) as first_payment,
    MAX(payment_date) as last_payment
    FROM payment_history");
$stmt->execute();
$all_time_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get today's statistics
$stmt = $db->prepare("SELECT 
    COUNT(*) as today_count,
    SUM(amount_paid) as today_amount
    FROM payment_history 
    WHERE payment_date = CURDATE()");
$stmt->execute();
$today_stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - Admin Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="enhanced-styles.css">
    <style>
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
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
            color: #f1f5f9;
        }
        
        .user-details p {
            font-size: 14.4px;
            color: #94a3b8;
            margin: 0;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 32px;
            margin-bottom: 16px;
            color: #3b82f6;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #e2e8f0;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #94a3b8;
        }
        
        .table-section {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table-header {
            background: rgba(15, 39, 75, 0.8);
            padding: 20px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .table-info {
            color: #94a3b8;
            font-size: 0.85rem;
        }
        
        .search-filter {
            display: flex;
            gap: 16px;
            padding: 20px;
            background: rgba(15, 39, 75, 0.6);
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            flex-wrap: wrap;
        }
        
        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 12px 16px;
            background: rgba(15, 39, 75, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .filter-select {
            padding: 12px 16px;
            background: rgba(15, 39, 75, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn-export {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-export:hover {
            background: rgba(16, 185, 129, 0.2);
            transform: translateY(-2px);
        }
        
        .table-container {
            overflow-x: auto;
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
            color: #e2e8f0;
            border-bottom: 2px solid rgba(148, 163, 184, 0.2);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .payments-table td {
            padding: 16px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            color: #cbd5e1;
            font-size: 0.9rem;
        }
        
        .payments-table tbody tr {
            transition: all 0.2s ease;
        }
        
        .payments-table tbody tr:hover {
            background: rgba(59, 130, 246, 0.1);
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        
        .print-btn {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(16, 163, 74, 0.2));
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        
        .print-btn:hover {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.3), rgba(16, 163, 74, 0.3));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
        }
        
        .payment-id {
            font-weight: 600;
            color: #3b82f6;
        }
        
        .amount {
            font-weight: 600;
            color: #10b981;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-block;
        }
        
        .status-completed {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .status-partial {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }
        
        .status-failed {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 40px;
            color: #94a3b8;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
            color: #64748b;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 12px;
            color: #e2e8f0;
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
                grid-template-columns: repeat(2, 1fr);
            }
            
            .search-filter {
                flex-direction: column;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .payments-table {
                min-width: 600px;
            }
        }
        
        /* Pagination Styles */
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
            background: rgba(15, 39, 75, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            color: #e2e8f0;
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: center;
        }

        .pagination a:hover {
            background: rgba(59, 130, 246, 0.2);
            color: #e2e8f0;
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateY(-1px);
        }

        .pagination .current {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
            font-weight: 600;
        }
    </style>
    <link rel="stylesheet" href="responsive-styles-fixed.css">
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
            <a class="nav-item active" href="payment-history.php">
                <i class="fas fa-history"></i> Payment History
            </a>
            <a class="nav-item" href="payment-management.php">
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
                    <h2>Payment History</h2>
                    <p>View and manage all payment transactions in the system</p>
                </div>
            </header>

            <main>
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-value">₱<?php echo number_format($all_time_stats['total_amount'] ?: 0, 2); ?></div>
                        <div class="stat-label">Total Amount</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($all_time_stats['total_count'] ?: 0); ?></div>
                        <div class="stat-label">Total Payments</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-value">₱<?php echo number_format($today_stats['today_amount'] ?: 0, 2); ?></div>
                        <div class="stat-label">Today's Amount</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($today_stats['today_count'] ?: 0); ?></div>
                        <div class="stat-label">Today's Payments</div>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="table-section">
                    <div class="table-header">
                        <div class="table-title">
                            <i class="fas fa-history"></i> Payment Transactions
                        </div>
                        <div class="table-info">
                            <?php echo $total_payments; ?> total payments
                        </div>
                    </div>

                    <div class="search-filter">
                        <input type="text" class="search-input" placeholder="Search by payment ID, loan ID, or borrower..." id="searchInput">
                        <select class="filter-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="partial">Partial</option>
                            <option value="failed">Failed</option>
                        </select>
                        <button class="btn-export" onclick="exportData()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>

                    <div class="table-container">
                        <?php if (count($payments) > 0): ?>
                            <table class="payments-table">
                                <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>Loan ID</th>
                                        <th>Borrower</th>
                                        <th>Date</th>
                                        <th>Principal</th>
                                        <th>Interest</th>
                                        <th>Late Fees</th>
                                        <th>Total Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr data-status="<?php echo strtolower($payment['status']); ?>" data-email="<?php echo htmlspecialchars($payment['client_email'] ?: ''); ?>">
                                            <td class="payment-id"><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['loan_id']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['borrower_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                            <td>₱<?php echo number_format($payment['principal_paid'], 2); ?></td>
                                            <td>₱<?php echo number_format($payment['interest_paid'], 2); ?></td>
                                            <td style="color: <?php echo $payment['late_fees'] > 0 ? '#ef4444' : '#10b981'; ?>; font-weight: bold;">₱<?php echo number_format($payment['late_fees'], 2); ?></td>
                                            <td class="amount" style="color: #10b981; font-weight: bold;">₱<?php echo number_format($payment['total_amount'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($payment['payment_method'] ?: '-'); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo strtolower($payment['status']); ?>">
                                                    <?php echo ucfirst($payment['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="action-btn print-btn" onclick="printReceipt('<?php echo $payment['payment_id']; ?>')">
                                                    <i class="fas fa-print"></i> Print Receipt
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-receipt"></i>
                                <h3>No Payment History</h3>
                                <p>No payment transactions found in the system.</p>
                            </div>
                        <?php endif; ?>
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
                </div>
            </main>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('.payments-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });

        // Status filter
        document.getElementById('statusFilter').addEventListener('change', function() {
            const statusValue = this.value;
            const rows = document.querySelectorAll('.payments-table tbody tr');
            
            rows.forEach(row => {
                if (statusValue === '') {
                    row.style.display = '';
                } else {
                    row.style.display = row.dataset.status === statusValue ? '' : 'none';
                }
            });
        });

        // Export functionality
        function exportData() {
            const table = document.querySelector('.payments-table');
            let csv = [];
            
            // Get headers
            const headers = [];
            table.querySelectorAll('thead th').forEach(th => {
                headers.push(th.textContent.trim());
            });
            csv.push(headers.join(','));
            
            // Get rows
            table.querySelectorAll('tbody tr').forEach(row => {
                if (row.style.display !== 'none') {
                    const rowData = [];
                    row.querySelectorAll('td').forEach(td => {
                        // Skip action buttons in CSV export
                        if (!td.querySelector('.action-btn')) {
                            rowData.push(td.textContent.trim());
                        }
                    });
                    csv.push(rowData.join(','));
                }
            });
            
            // Create and download CSV
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'payment_history_' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
        
        // Print receipt functionality
        function printReceipt(paymentId) {
            // Find the payment row
            const rows = document.querySelectorAll('.payments-table tbody tr');
            let paymentData = null;
            
            rows.forEach(row => {
                const paymentIdCell = row.querySelector('.payment-id');
                if (paymentIdCell && paymentIdCell.textContent.trim() === paymentId) {
                    const cells = row.querySelectorAll('td');
                    paymentData = {
                        paymentId: cells[0].textContent.trim(),
                        loanId: cells[1].textContent.trim(),
                        borrower: cells[2].textContent.trim(),
                        date: cells[3].textContent.trim(),
                        amount: cells[4].textContent.trim(),
                        principal: cells[4].textContent.trim(),
                        interest: cells[5].textContent.trim(),
                        lateFees: cells[6].textContent.trim(),
                        totalAmount: cells[7].textContent.trim(),
                        method: cells[8].textContent.trim(),
                        status: cells[9].textContent.trim(),
                        email: row.dataset.email || ''
                    };
                }
            });
            
            if (paymentData) {
                // Create receipt HTML
                const receiptHTML = `
                    <div class="receipt-container">
                        <div class="receipt-header">
                            <h1>PAYMENT RECEIPT</h1>
                            <div class="company-name">Market Vendor Loan System</div>
                        </div>
                        
                        <div class="receipt-section">
                            <div class="receipt-row">
                                <span class="label">Receipt No:</span>
                                <span class="value">RCP${paymentData.paymentId.replace('PAY', '')}</span>
                            </div>
                            <div class="receipt-row">
                                <span class="label">Payment ID:</span>
                                <span class="value">${paymentData.paymentId}</span>
                            </div>
                            <div class="receipt-row">
                                <span class="label">Loan ID:</span>
                                <span class="value">${paymentData.loanId}</span>
                            </div>
                            <div class="receipt-row">
                                <span class="label">Date:</span>
                                <span class="value">${paymentData.date}</span>
                            </div>
                        </div>
                        
                        <div class="receipt-section">
                            <div class="receipt-row">
                                <span class="label">Client:</span>
                                <span class="value">${paymentData.borrower}</span>
                            </div>
                            <div class="receipt-row">
                                <span class="label">Email:</span>
                                <span class="value">${paymentData.email || 'N/A'}</span>
                            </div>
                            <div class="receipt-row">
                                <span class="label">Method:</span>
                                <span class="value">${paymentData.method.toUpperCase()}</span>
                            </div>
                        </div>
                        
                        <div class="receipt-section">
                            <div class="section-title">Summary of Payment</div>
                            <div class="receipt-row">
                                <span class="label">Principal:</span>
                                <span class="value">${paymentData.principal}</span>
                            </div>
                            <div class="receipt-row">
                                <span class="label">Interest:</span>
                                <span class="value">${paymentData.interest}</span>
                            </div>
                            <div class="receipt-row">
                                <span class="label">Late Fees:</span>
                                <span class="value">${paymentData.lateFees}</span>
                            </div>
                        </div>
                        
                        <div class="total-section">
                            <div class="receipt-row total-row">
                                <span class="total-label">TOTAL PAID:</span>
                                <span class="total-value">${paymentData.totalAmount}</span>
                            </div>
                        </div>
                        
                        <div class="receipt-footer">
                            <div class="receipt-row">
                                <span class="label">Processed by:</span>
                                <span class="value">${paymentData.borrower}</span>
                            </div>
                            <div class="thank-you">Thank you for your payment!</div>
                        </div>
                    </div>
                `;
                
                // Create print window with CSS
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Payment Receipt - ${paymentData.paymentId}</title>
                        <style>
                            * {
                                margin: 0;
                                padding: 0;
                                box-sizing: border-box;
                            }
                            
                            body {
                                font-family: 'Courier New', monospace;
                                background: white;
                                color: #000;
                                padding: 20px;
                                line-height: 1.4;
                            }
                            
                            .receipt-container {
                                max-width: 400px;
                                margin: 0 auto;
                                border: 2px solid #000;
                                padding: 30px 20px;
                                background: white;
                            }
                            
                            .receipt-header {
                                text-align: center;
                                margin-bottom: 30px;
                                border-bottom: 2px solid #000;
                                padding-bottom: 20px;
                            }
                            
                            .receipt-header h1 {
                                font-size: 24px;
                                font-weight: bold;
                                margin-bottom: 5px;
                                text-transform: uppercase;
                            }
                            
                            .company-name {
                                font-size: 12px;
                                color: #666;
                                text-transform: uppercase;
                            }
                            
                            .receipt-section {
                                margin-bottom: 20px;
                                padding-bottom: 15px;
                                border-bottom: 1px dashed #000;
                            }
                            
                            .section-title {
                                font-weight: bold;
                                margin-bottom: 10px;
                                text-transform: uppercase;
                                font-size: 14px;
                                text-align: center;
                            }
                            
                            .receipt-row {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                margin-bottom: 8px;
                                font-size: 14px;
                            }
                            
                            .label {
                                text-align: left;
                                font-weight: normal;
                            }
                            
                            .value {
                                text-align: right;
                                font-weight: bold;
                            }
                            
                            .total-section {
                                margin: 20px 0;
                                padding: 15px 0;
                                border-top: 2px solid #000;
                                border-bottom: 2px solid #000;
                            }
                            
                            .total-row {
                                font-size: 18px;
                                font-weight: bold;
                            }
                            
                            .total-label {
                                text-align: left;
                            }
                            
                            .total-value {
                                text-align: right;
                                font-size: 20px;
                            }
                            
                            .receipt-footer {
                                margin-top: 20px;
                                text-align: center;
                            }
                            
                            .thank-you {
                                margin-top: 15px;
                                padding-top: 15px;
                                border-top: 1px dashed #000;
                                font-size: 12px;
                                font-style: italic;
                            }
                            
                            @media print {
                                body {
                                    margin: 0;
                                    padding: 10px;
                                }
                                
                                .receipt-container {
                                    margin: 0;
                                    max-width: 100%;
                                    box-shadow: none;
                                }
                                
                                @page {
                                    margin: 10mm;
                                    size: auto;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        ${receiptHTML}
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            }
        }
    </script>
    <script src="responsive-script.js"></script>

</body>
</html>
