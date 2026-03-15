<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';
require_once 'includes/audit_helper.php';

// Handle export request
if (isset($_GET['export']) && $_GET['export'] == '1') {
    $report_type = $_GET['report_type'] ?? 'overview';
    $record_count = count($analytics['financial']['daily_income'] ?? []) + 
                    count($analytics['vendors']['demographics'] ?? []) + 
                    count($analytics['loans']['portfolio_summary'] ?? []);
    logDataExport("Analytics Report - {$report_type}", $record_count);
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get date range for reports
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'overview';

// Fetch analytics data
$analytics = [
    'financial' => [],
    'clients' => [],
    'loans' => [],
    'payments' => []
];

try {
    // Financial Reports    
    if ($report_type === 'financial' || $report_type === 'overview') {
        // Income Statement
        $income_sql = "SELECT 
                DATE(payment_date) as date,
                SUM(amount_paid) as daily_income,
                COUNT(*) as transaction_count
                FROM payment_history 
                WHERE payment_date BETWEEN ? AND ? AND status = 'completed'
                GROUP BY DATE(payment_date)
                ORDER BY date";
        $income_stmt = $db->prepare($income_sql);
        $income_stmt->execute([$date_from, $date_to]);
        $analytics['financial']['daily_income'] = $income_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Monthly Revenue
        $monthly_sql = "SELECT 
                MONTH(payment_date) as month,
                YEAR(payment_date) as year,
                SUM(amount_paid) as revenue,
                COUNT(*) as payments
                FROM payment_history 
                WHERE status = 'completed'
                GROUP BY YEAR(payment_date), MONTH(payment_date)
                ORDER BY year DESC, month DESC
                LIMIT 12";
        $monthly_stmt = $db->prepare($monthly_sql);
        $monthly_stmt->execute();
        $analytics['financial']['monthly_revenue'] = $monthly_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Collection Efficiency
        $collection_sql = "SELECT 
                COUNT(*) as total_payments,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_payments,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_payments,
                SUM(amount_paid) as total_collected,
                AVG(CASE WHEN status = 'completed' THEN amount_paid END) as avg_payment
                FROM payment_history 
                WHERE payment_date BETWEEN ? AND ?";
        $collection_stmt = $db->prepare($collection_sql);
        $collection_stmt->execute([$date_from, $date_to]);
        $analytics['financial']['collection_efficiency'] = $collection_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Default Analysis
        $default_sql = "SELECT 
                COUNT(*) as total_loans,
                SUM(CASE WHEN status = 'defaulted' THEN 1 ELSE 0 END) as defaulted_loans,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_loans,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_loans,
                AVG(loan_amount) as avg_loan_amount,
                SUM(loan_amount) as total_loan_portfolio
                FROM loans 
                WHERE created_at BETWEEN ? AND ?";
        $default_stmt = $db->prepare($default_sql);
        $default_stmt->execute([$date_from, $date_to]);
        $analytics['financial']['default_analysis'] = $default_stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Client (Vendor) Analytics
    if ($report_type === 'clients' || $report_type === 'overview') {
        // Vendor Demographics
        $demo_sql = "SELECT 
                COUNT(*) as total_vendors,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_vendors_30d,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY) THEN 1 END) as new_vendors_90d,
                AVG(DATEDIFF(NOW(), created_at)) as avg_vendor_age
                FROM users 
                WHERE (role = 'vendor' OR role = 'client' OR role = 'user' OR role IS NULL)";
        $demo_stmt = $db->prepare($demo_sql);
        $demo_stmt->execute();
        $analytics['vendors']['demographics'] = $demo_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vendor Borrowing Patterns
        $borrowing_sql = "SELECT 
                u.id as user_id,
                u.name as full_name,
                COUNT(l.loan_id) as total_loans,
                COALESCE(SUM(l.loan_amount), 0) as total_borrowed,
                COALESCE(AVG(l.loan_amount), 0) as avg_loan_amount,
                MAX(l.created_at) as last_loan_date,
                SUM(CASE WHEN l.status = 'completed' THEN 1 ELSE 0 END) as completed_loans
                FROM users u
                LEFT JOIN loans l ON u.id = l.user_id
                WHERE (u.role = 'vendor' OR u.role = 'client' OR u.role = 'user' OR u.role IS NULL)
                GROUP BY u.id, u.name
                HAVING total_loans > 0
                ORDER BY total_borrowed DESC
                LIMIT 20";
        $borrowing_stmt = $db->prepare($borrowing_sql);
        $borrowing_stmt->execute();
        $analytics['vendors']['borrowing_patterns'] = $borrowing_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Vendor Risk Segmentation
        $risk_sql = "SELECT 
                'Low Risk' as risk_segment,
                COUNT(*) as vendor_count,
                COALESCE(SUM(l.loan_amount), 0) as total_exposure,
                COALESCE(AVG(l.loan_amount), 0) as avg_exposure
                FROM users u
                LEFT JOIN loans l ON u.id = l.user_id
                WHERE (u.role = 'vendor' OR u.role = 'client' OR u.role = 'user' OR u.role IS NULL)
                GROUP BY 'Low Risk'";
        $risk_stmt = $db->prepare($risk_sql);
        $risk_stmt->execute();
        $analytics['vendors']['risk_segmentation'] = $risk_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Loan Portfolio Reports
    if ($report_type === 'loans' || $report_type === 'overview') {
        // Loan Portfolio Summary
        $portfolio_sql = "SELECT 
                status,
                COUNT(*) as loan_count,
                COALESCE(SUM(loan_amount), 0) as total_amount,
                COALESCE(AVG(loan_amount), 0) as avg_amount,
                COALESCE(AVG(interest_rate), 0) as avg_interest_rate,
                COALESCE(AVG(term_months), 0) as avg_term
                FROM loans 
                GROUP BY status";
        $portfolio_stmt = $db->prepare($portfolio_sql);
        $portfolio_stmt->execute();
        $analytics['loans']['portfolio_summary'] = $portfolio_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Loan Performance by Purpose
        $purpose_sql = "SELECT 
                loan_purpose,
                COUNT(*) as loan_count,
                COALESCE(SUM(loan_amount), 0) as total_amount,
                COALESCE(AVG(loan_amount), 0) as avg_amount,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_loans,
                (SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as completion_rate
                FROM loans 
                WHERE loan_purpose IS NOT NULL AND loan_purpose != ''
                GROUP BY loan_purpose
                ORDER BY loan_count DESC";
        $purpose_stmt = $db->prepare($purpose_sql);
        $purpose_stmt->execute();
        $analytics['loans']['purpose_analysis'] = $purpose_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // System Reports
    if ($report_type === 'system' || $report_type === 'overview') {
        // User Activity based on loans and payments
        $activity_sql = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as activities,
                'Loan Applications' as activity_type
                FROM loans 
                WHERE created_at BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                
                UNION ALL
                
                SELECT 
                DATE(payment_date) as date,
                COUNT(*) as activities,
                'Payments' as activity_type
                FROM payment_history 
                WHERE payment_date BETWEEN ? AND ? AND status = 'completed'
                GROUP BY DATE(payment_date)
                
                ORDER BY date DESC
                LIMIT 30";
        $activity_stmt = $db->prepare($activity_sql);
        $activity_stmt->execute([$date_from, $date_to, $date_from, $date_to]);
        $analytics['system']['user_activity'] = $activity_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // System Performance Metrics
        $performance_sql = "SELECT 
                (SELECT COUNT(*) FROM users) as total_users,
                (SELECT COUNT(*) FROM loans) as total_loans,
                (SELECT COUNT(*) FROM payment_history) as total_payments,
                (SELECT COALESCE(SUM(loan_amount), 0) FROM loans WHERE status = 'active') as active_portfolio,
                (SELECT COUNT(*) FROM loans WHERE status = 'pending') as pending_applications,
                (SELECT COALESCE(AVG(DATEDIFF(NOW(), created_at)), 0) FROM loans WHERE status = 'active') as avg_loan_age";
        $performance_stmt = $db->prepare($performance_sql);
        $performance_stmt->execute();
        $analytics['system']['performance'] = $performance_stmt->fetch(PDO::FETCH_ASSOC);
    }
    
} catch (Exception $e) {
    $error_message = "Error loading analytics: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Analytics & Reports - Market Vendor Loan</title>
    <link rel="stylesheet" href="enhanced-styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            color: #94a3b8;
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
        
        .analytics-header {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 39, 75, 0.95) 100%);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .report-filters {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .report-filters input,
        .report-filters select {
            padding: 10px 15px;
            background: rgba(15, 39, 75, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            color: #f1f5f9;
            font-size: 0.9rem;
        }
        
        .report-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(148, 163, 184, 0.2);
        }
        
        .tab-btn {
            padding: 12px 24px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        
        .tab-btn.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
        }
        
        .tab-btn:hover {
            color: #f1f5f9;
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }
        
        .analytics-card {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 24px;
        }
        
        .analytics-card h3 {
            margin: 0 0 20px 0;
            color: #f1f5f9;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .analytics-card h3 i {
            color: #3b82f6;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .stats-table th,
        .stats-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }
        
        .stats-table th {
            background: rgba(15, 39, 75, 0.5);
            color: #e2e8f0;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .stats-table tbody tr:hover {
            background: rgba(15, 39, 75, 0.3);
        }
        
        .metric-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 5px;
        }
        
        .metric-label {
            color: #94a3b8;
            font-size: 0.9rem;
        }
        
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 39, 75, 0.95) 100%);
            color: white;
            border: 1px solid rgba(148, 163, 184, 0.2);
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(15, 39, 75, 0.5);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 8px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #2563eb);
            transition: width 0.3s ease;
        }
        
        .risk-high { color: #ef4444; }
        .risk-medium { color: #fbbf24; }

        /* Navigation Styles - Override to ensure hover effects work */
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
        .risk-low { color: #10b981; }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
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
            <a class="nav-item" href="payment-management.php">
                <i class="fas fa-credit-card"></i> Process Payment
            </a>
            <a class="nav-item active" href="analytics.php">
                <i class="fas fa-chart-line"></i> Analytics & Reports
            </a>
            <a class="nav-item" href="audit-log.php">
                <i class="fas fa-clipboard-list"></i> Audit Log
            </a>
            <a class="nav-item" href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
        </aside>

        <!-- Main Content -->
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
                    <h2>Analytics & Reports</h2>
                    <p>Comprehensive business intelligence and insights</p>
                </div>
            </header>

            <?php if (isset($error_message)): ?>
                <div class="message error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <main>
                <!-- Analytics Header with Filters -->
                <div class="analytics-header">
                    <div class="report-filters">
                        <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                        <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                        <button class="export-btn" onclick="exportReport()">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                    </div>
                </div>
                
                <!-- Report Tabs -->
                <div class="report-tabs">
                    <button class="tab-btn active" onclick="switchTab('overview')">
                        <i class="fas fa-chart-pie"></i> Overview
                    </button>
                    <button class="tab-btn" onclick="switchTab('financial')">
                        <i class="fas fa-dollar-sign"></i> Financial
                    </button>
                    <button class="tab-btn" onclick="switchTab('clients')">
                        <i class="fas fa-users"></i> Clients
                    </button>
                    <button class="tab-btn" onclick="switchTab('loans')">
                        <i class="fas fa-hand-holding-usd"></i> Loans
                    </button>
                    <button class="tab-btn" onclick="switchTab('system')">
                        <i class="fas fa-cogs"></i> System
                    </button>
                </div>
                
                <!-- Overview Tab -->
                <div id="overview-tab" class="tab-content active">
                    <div class="metric-grid">
                        <div class="metric-card">
                            <div class="metric-value">₱<?php echo number_format($analytics['financial']['collection_efficiency']['total_collected'] ?? 0, 2); ?></div>
                            <div class="metric-label">Total Collected</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value"><?php echo number_format($analytics['vendors']['demographics']['total_vendors'] ?? 0); ?></div>
                            <div class="metric-label">Total Vendors</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value"><?php echo number_format($analytics['system']['performance']['total_loans'] ?? 0); ?></div>
                            <div class="metric-label">Total Loans</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value"><?php echo number_format(($analytics['financial']['collection_efficiency']['completed_payments'] / max($analytics['financial']['collection_efficiency']['total_payments'], 1)) * 100, 1); ?>%</div>
                            <div class="metric-label">Collection Rate</div>
                        </div>
                    </div>
                    
                    <div class="analytics-grid">
                        <div class="analytics-card">
                            <h3><i class="fas fa-chart-line"></i> Revenue Trend</h3>
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="analytics-card">
                            <h3><i class="fas fa-chart-pie"></i> Loan Status Distribution</h3>
                            <div class="chart-container">
                                <canvas id="loanStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Financial Tab -->
                <div id="financial-tab" class="tab-content">
                    <div class="analytics-grid">
                        <div class="analytics-card">
                            <h3><i class="fas fa-chart-bar"></i> Daily Income</h3>
                            <div class="chart-container">
                                <canvas id="dailyIncomeChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="analytics-card">
                            <h3><i class="fas fa-percentage"></i> Collection Efficiency</h3>
                            <div class="metric-grid">
                                <div class="metric-card">
                                    <div class="metric-value"><?php echo number_format($analytics['financial']['collection_efficiency']['completed_payments'] ?? 0); ?></div>
                                    <div class="metric-label">Completed</div>
                                </div>
                                <div class="metric-card">
                                    <div class="metric-value"><?php echo number_format($analytics['financial']['collection_efficiency']['pending_payments'] ?? 0); ?></div>
                                    <div class="metric-label">Pending</div>
                                </div>
                                <div class="metric-card">
                                    <div class="metric-value"><?php echo number_format($analytics['financial']['collection_efficiency']['failed_payments'] ?? 0); ?></div>
                                    <div class="metric-label">Failed</div>
                                </div>
                                <div class="metric-card">
                                    <div class="metric-value">₱<?php echo number_format($analytics['financial']['collection_efficiency']['avg_payment'] ?? 0, 2); ?></div>
                                    <div class="metric-label">Avg Payment</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <h3><i class="fas fa-exclamation-triangle"></i> Default Analysis</h3>
                        <table class="stats-table">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Total Loans</td>
                                    <td><?php echo number_format($analytics['financial']['default_analysis']['total_loans'] ?? 0); ?></td>
                                </tr>
                                <tr>
                                    <td>Defaulted Loans</td>
                                    <td class="risk-high"><?php echo number_format($analytics['financial']['default_analysis']['defaulted_loans'] ?? 0); ?></td>
                                </tr>
                                <tr>
                                    <td>Completed Loans</td>
                                    <td class="risk-low"><?php echo number_format($analytics['financial']['default_analysis']['completed_loans'] ?? 0); ?></td>
                                </tr>
                                <tr>
                                    <td>Active Loans</td>
                                    <td><?php echo number_format($analytics['financial']['default_analysis']['active_loans'] ?? 0); ?></td>
                                </tr>
                                <tr>
                                    <td>Average Loan Amount</td>
                                    <td>₱<?php echo number_format($analytics['financial']['default_analysis']['avg_loan_amount'] ?? 0, 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Total Portfolio</td>
                                    <td>₱<?php echo number_format($analytics['financial']['default_analysis']['total_loan_portfolio'] ?? 0, 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Clients Tab -->
                <div id="clients-tab" class="tab-content">
                    <div class="analytics-grid">
                        <div class="analytics-card">
                            <h3><i class="fas fa-store"></i> Vendor Demographics</h3>
                            <div class="metric-grid">
                                <div class="metric-card">
                                    <div class="metric-value"><?php echo number_format($analytics['vendors']['demographics']['total_vendors'] ?? 0); ?></div>
                                    <div class="metric-label">Total Vendors</div>
                                </div>
                                <div class="metric-card">
                                    <div class="metric-value"><?php echo number_format($analytics['vendors']['demographics']['new_vendors_30d'] ?? 0); ?></div>
                                    <div class="metric-label">New (30 days)</div>
                                </div>
                                <div class="metric-card">
                                    <div class="metric-value"><?php echo number_format($analytics['vendors']['demographics']['new_vendors_90d'] ?? 0); ?></div>
                                    <div class="metric-label">New (90 days)</div>
                                </div>
                                <div class="metric-card">
                                    <div class="metric-value"><?php echo number_format($analytics['vendors']['demographics']['avg_vendor_age'] ?? 0, 0); ?> days</div>
                                    <div class="metric-label">Avg Vendor Age</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="analytics-card">
                            <h3><i class="fas fa-shield-alt"></i> Risk Segmentation</h3>
                            <div class="chart-container">
                                <canvas id="riskChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <h3><i class="fas fa-crown"></i> Top Borrowers</h3>
                        <table class="stats-table">
                            <thead>
                                <tr>
                                    <th>Vendor</th>
                                    <th>Total Loans</th>
                                    <th>Total Borrowed</th>
                                    <th>Avg Loan</th>
                                    <th>Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($analytics['vendors']['borrowing_patterns'] ?? [], 0, 10) as $vendor): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($vendor['full_name']); ?></td>
                                    <td><?php echo number_format($vendor['total_loans']); ?></td>
                                    <td>₱<?php echo number_format($vendor['total_borrowed'], 2); ?></td>
                                    <td>₱<?php echo number_format($vendor['avg_loan_amount'], 2); ?></td>
                                    <td><?php echo number_format($vendor['completed_loans']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Loans Tab -->
                <div id="loans-tab" class="tab-content">
                    <div class="analytics-card">
                        <h3><i class="fas fa-briefcase"></i> Loan Portfolio Summary</h3>
                        <div class="chart-container">
                            <canvas id="portfolioChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <h3><i class="fas fa-bullseye"></i> Loan Purpose Analysis</h3>
                        <table class="stats-table">
                            <thead>
                                <tr>
                                    <th>Purpose</th>
                                    <th>Count</th>
                                    <th>Total Amount</th>
                                    <th>Average</th>
                                    <th>Completion Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($analytics['loans']['purpose_analysis'] ?? [] as $purpose): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($purpose['loan_purpose']); ?></td>
                                    <td><?php echo number_format($purpose['loan_count']); ?></td>
                                    <td>₱<?php echo number_format($purpose['total_amount'], 2); ?></td>
                                    <td>₱<?php echo number_format($purpose['avg_amount'], 2); ?></td>
                                    <td>
                                        <?php echo number_format($purpose['completion_rate'], 1); ?>%
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo min($purpose['completion_rate'], 100); ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- System Tab -->
                <div id="system-tab" class="tab-content">
                    <div class="analytics-grid">
                        <div class="analytics-card">
                            <h3><i class="fas fa-users-cog"></i> User Activity</h3>
                            <div class="chart-container">
                                <canvas id="activityChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="analytics-card">
                            <h3><i class="fas fa-tachometer-alt"></i> System Performance</h3>
                            <table class="stats-table">
                                <thead>
                                    <tr>
                                        <th>Metric</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Total Users</td>
                                        <td><?php echo number_format($analytics['system']['performance']['total_users'] ?? 0); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Total Loans</td>
                                        <td><?php echo number_format($analytics['system']['performance']['total_loans'] ?? 0); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Total Payments</td>
                                        <td><?php echo number_format($analytics['system']['performance']['total_payments'] ?? 0); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Active Portfolio</td>
                                        <td>₱<?php echo number_format($analytics['system']['performance']['active_portfolio'] ?? 0, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Pending Applications</td>
                                        <td><?php echo number_format($analytics['system']['performance']['pending_applications'] ?? 0); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Average Loan Age</td>
                                        <td><?php echo number_format($analytics['system']['performance']['avg_loan_age'] ?? 0, 0); ?> days</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Chart configurations
        const chartColors = {
            primary: '#3b82f6',
            success: '#10b981',
            warning: '#fbbf24',
            danger: '#ef4444',
            info: '#8b5cf6'
        };
        
        // Initialize charts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });
        
        function initializeCharts() {
            // Daily Income Chart
            const dailyIncomeCtx = document.getElementById('dailyIncomeChart');
            if (dailyIncomeCtx) {
                new Chart(dailyIncomeCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_map(function($item) { return date('M d', strtotime($item['date'])); }, $analytics['financial']['daily_income'] ?? [])); ?>,
                        datasets: [{
                            label: 'Daily Income',
                            data: <?php echo json_encode(array_column($analytics['financial']['daily_income'] ?? [], 'daily_income')); ?>,
                            backgroundColor: chartColors.primary,
                            borderColor: chartColors.primary,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_map(function($item) { return date('M Y', strtotime($item['year'] . '-' . $item['month'] . '-01')); }, $analytics['financial']['monthly_revenue'] ?? [])); ?>,
                        datasets: [{
                            label: 'Monthly Revenue',
                            data: <?php echo json_encode(array_column($analytics['financial']['monthly_revenue'] ?? [], 'revenue')); ?>,
                            borderColor: chartColors.primary,
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Loan Status Chart
            const loanStatusCtx = document.getElementById('loanStatusChart');
            if (loanStatusCtx) {
                new Chart(loanStatusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode(array_column($analytics['loans']['portfolio_summary'] ?? [], 'status')); ?>,
                        datasets: [{
                            data: <?php echo json_encode(array_column($analytics['loans']['portfolio_summary'] ?? [], 'loan_count')); ?>,
                            backgroundColor: [chartColors.success, chartColors.warning, chartColors.danger, chartColors.primary]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
            
            // Risk Chart
            const riskCtx = document.getElementById('riskChart');
            if (riskCtx) {
                new Chart(riskCtx, {
                    type: 'pie',
                    data: {
                        labels: <?php echo json_encode(array_column($analytics['vendors']['risk_segmentation'] ?? [], 'risk_segment')); ?>,
                        datasets: [{
                            data: <?php echo json_encode(array_column($analytics['vendors']['risk_segmentation'] ?? [], 'vendor_count')); ?>,
                            backgroundColor: [chartColors.danger, chartColors.warning, chartColors.success]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
            
            // Portfolio Chart
            const portfolioCtx = document.getElementById('portfolioChart');
            if (portfolioCtx) {
                new Chart(portfolioCtx, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode(array_column($analytics['loans']['portfolio_summary'] ?? [], 'status')); ?>,
                        datasets: [{
                            data: <?php echo json_encode(array_column($analytics['loans']['portfolio_summary'] ?? [], 'loan_count')); ?>,
                            backgroundColor: [chartColors.success, chartColors.warning, chartColors.danger, chartColors.primary, chartColors.info]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
            
            // User Activity Chart
            const activityCtx = document.getElementById('activityChart');
            if (activityCtx) {
                // Process activity data for chart
                const activityData = <?php echo json_encode($analytics['system']['user_activity'] ?? []); ?>;
                const dates = [...new Set(activityData.map(item => item.date))].sort().reverse();
                const loanData = dates.map(date => {
                    const item = activityData.find(a => a.date === date && a.activity_type === 'Loan Applications');
                    return item ? item.activities : 0;
                });
                const paymentData = dates.map(date => {
                    const item = activityData.find(a => a.date === date && a.activity_type === 'Payments');
                    return item ? item.activities : 0;
                });
                
                new Chart(activityCtx, {
                    type: 'line',
                    data: {
                        labels: dates.map(date => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
                        datasets: [{
                            label: 'Loan Applications',
                            data: loanData,
                            borderColor: chartColors.primary,
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4
                        }, {
                            label: 'Payments',
                            data: paymentData,
                            borderColor: chartColors.success,
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
        
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
            
            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('report_type', tabName);
            window.history.pushState({}, '', url);
        }
        
        function exportReport() {
            const dateFrom = document.getElementById('date_from').value;
            const dateTo = document.getElementById('date_to').value;
            const reportType = document.querySelector('.tab-btn.active').textContent.trim();
            
            // Create comprehensive CSV content
            let csvContent = "Analytics Report - " + reportType + "\n";
            csvContent += "Date Range: " + dateFrom + " to " + dateTo + "\n";
            csvContent += "Generated: " + new Date().toLocaleString() + "\n\n";
            
            // Financial Summary
            csvContent += "FINANCIAL SUMMARY\n";
            csvContent += "Metric,Value\n";
            csvContent += "Total Collected,₱" + <?php echo number_format($analytics['financial']['collection_efficiency']['total_collected'] ?? 0, 2); ?> + "\n";
            csvContent += "Total Revenue,₱" + <?php echo number_format($analytics['financial']['collection_efficiency']['total_revenue'] ?? 0, 2); ?> + "\n";
            csvContent += "Collection Rate," + <?php echo number_format($analytics['financial']['collection_efficiency']['collection_rate'] ?? 0, 1); ?> + "%\n\n";
            
            // Client Demographics
            csvContent += "CLIENT DEMOGRAPHICS\n";
            csvContent += "Total Clients," + <?php echo number_format($analytics['clients']['demographics']['total_clients'] ?? 0); ?> + "\n";
            csvContent += "Active Clients," + <?php echo number_format($analytics['clients']['demographics']['active_clients'] ?? 0); ?> + "\n";
            csvContent += "New Clients This Month," + <?php echo number_format($analytics['clients']['demographics']['new_clients_month'] ?? 0); ?> + "\n\n";
            
            // Loan Portfolio
            csvContent += "LOAN PORTFOLIO\n";
            csvContent += "Total Loans," + <?php echo number_format($analytics['system']['performance']['total_loans'] ?? 0); ?> + "\n";
            csvContent += "Active Loans," + <?php echo number_format($analytics['loans']['portfolio_summary']['active_loans'] ?? 0); ?> + "\n";
            csvContent += "Completed Loans," + <?php echo number_format($analytics['loans']['portfolio_summary']['completed_loans'] ?? 0); ?> + "\n";
            csvContent += "Total Loan Amount,₱" + <?php echo number_format($analytics['loans']['portfolio_summary']['total_amount'] ?? 0, 2); ?> + "\n";
            csvContent += "Average Loan Size,₱" + <?php echo number_format($analytics['loans']['portfolio_summary']['avg_loan_size'] ?? 0, 2); ?> + "\n\n";
            
            // Loan Purpose Analysis
            csvContent += "LOAN PURPOSE ANALYSIS\n";
            csvContent += "Purpose,Count,Total Amount,Average Amount,Completion Rate\n";
            <?php foreach ($analytics['loans']['purpose_analysis'] ?? [] as $purpose): ?>
            csvContent += "<?php echo htmlspecialchars($purpose['loan_purpose']); ?>," + 
                         "<?php echo number_format($purpose['loan_count']); ?>," +
                         "₱<?php echo number_format($purpose['total_amount'], 2); ?>," +
                         "₱<?php echo number_format($purpose['avg_amount'], 2); ?>," +
                         "<?php echo number_format($purpose['completion_rate'], 1); ?>%\n";
            <?php endforeach; ?>
            csvContent += "\n";
            
            // Top Borrowers
            csvContent += "TOP BORROWERS\n";
            csvContent += "Vendor,Total Loans,Total Borrowed,Average Loan,Completed Loans\n";
            <?php foreach (array_slice($analytics['vendors']['borrowing_patterns'] ?? [], 0, 10) as $vendor): ?>
            csvContent += "<?php echo htmlspecialchars($vendor['full_name']); ?>," +
                         "<?php echo number_format($vendor['total_loans']); ?>," +
                         "₱<?php echo number_format($vendor['total_borrowed'], 2); ?>," +
                         "₱<?php echo number_format($vendor['avg_loan_amount'], 2); ?>," +
                         "<?php echo number_format($vendor['completed_loans']); ?>\n";
            <?php endforeach; ?>
            csvContent += "\n";
            
            // System Performance
            csvContent += "SYSTEM PERFORMANCE\n";
            csvContent += "Metric,Value\n";
            csvContent += "Total Payments," + <?php echo number_format($analytics['system']['performance']['total_payments'] ?? 0); ?> + "\n";
            csvContent += "Success Rate," + <?php echo number_format($analytics['system']['performance']['success_rate'] ?? 0, 1); ?> + "%\n";
            csvContent += "Average Processing Time," + <?php echo number_format($analytics['system']['performance']['avg_processing_time'] ?? 0, 2); ?> + " seconds\n";
            
            // Create download
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'comprehensive_analytics_report_' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
        
        // Update date filters
        document.getElementById('date_from').addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('date_from', this.value);
            window.location.href = url.toString();
        });
        
        document.getElementById('date_to').addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('date_to', this.value);
            window.location.href = url.toString();
        });
    </script>
    <script src="responsive-script.js"></script>

</body>
</html>
