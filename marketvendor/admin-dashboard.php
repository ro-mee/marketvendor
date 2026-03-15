<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';
require_once 'includes/audit_helper.php';

// Get dashboard statistics
$database = new Database();
$db = $database->getConnection();

try {
    // Total users
    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $total_users = $stmt->fetch()['total'];
    
    // Active loans
    $stmt = $db->query("SELECT COUNT(*) as total FROM loans WHERE status = 'active'");
    $active_loans = $stmt->fetch()['total'];
    
    // Recent applications (using loans table instead of loan_applications)
    $stmt = $db->query("SELECT COUNT(*) as total FROM loans WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recent_applications = $stmt->fetch()['total'];
    
    // Overdue payments
    $stmt = $db->query("SELECT COUNT(*) as total FROM payment_schedules WHERE status = 'overdue'");
    $overdue_payments = $stmt->fetch()['total'];
    
    // Total overdue amount
    $stmt = $db->query("SELECT SUM(total_amount) as amount FROM payment_schedules WHERE status = 'overdue'");
    $overdue_amount = $stmt->fetch()['amount'] ?: 0;
    
    // Today's payments
    $stmt = $db->query("SELECT COUNT(*) as total FROM payment_history WHERE payment_date = CURDATE()");
    $today_payments = $stmt->fetch()['total'];
    
    // Recovery rate (paid vs overdue)
    $stmt = $db->query("SELECT 
        (SELECT COUNT(*) FROM payment_history WHERE status = 'completed') as paid_count,
        (SELECT COUNT(*) FROM payment_schedules WHERE status = 'overdue') as overdue_count");
    $recovery_data = $stmt->fetch();
    $total_payments_count = $recovery_data['paid_count'] + $recovery_data['overdue_count'];
    $recovery_rate = $total_payments_count > 0 ? ($recovery_data['paid_count'] / $total_payments_count) * 100 : 0;
    
} catch(PDOException $exception) {
    $error_message = "Error loading dashboard data.";
    // Set default values in case of database errors
    $total_users = 0;
    $active_loans = 0;
    $recent_applications = 0;
    $overdue_payments = 0;
    $overdue_amount = 0;
    $today_payments = 0;
    $recovery_rate = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Market Vendor Loan Management</title>
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
        
        .quick-actions {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 24px;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 20px;
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
            transition: left 0.5s ease;
        }
        
        .action-btn:hover::before {
            left: 100%;
        }
        
        .action-btn:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.2));
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
            border-color: rgba(59, 130, 246, 0.5);
        }
        
        .action-btn i {
            display: block;
            font-size: 28px;
            margin-bottom: 12px;
            transition: transform 0.3s ease;
        }
        
        .action-btn:hover i {
            transform: scale(1.1);
        }
        
        .action-btn span {
            font-size: 0.9rem;
            font-weight: 600;
            color: #e2e8f0;
        }
        
        /* Specific action colors */
        .action-btn.payment {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 163, 74, 0.1));
            border-color: rgba(34, 197, 94, 0.3);
            color: #22c55e;
        }
        
        .action-btn.payment:hover {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(16, 163, 74, 0.2));
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);
            border-color: rgba(34, 197, 94, 0.5);
        }
        
        .action-btn.history {
            background: linear-gradient(135deg, rgba(251, 146, 60, 0.1), rgba(245, 158, 11, 0.1));
            border-color: rgba(251, 146, 60, 0.3);
            color: #fb923c;
        }
        
        .action-btn.history:hover {
            background: linear-gradient(135deg, rgba(251, 146, 60, 0.2), rgba(245, 158, 11, 0.2));
            box-shadow: 0 8px 25px rgba(251, 146, 60, 0.3);
            border-color: rgba(251, 146, 60, 0.5);
        }
        
        .action-btn.loans {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.1), rgba(147, 51, 234, 0.1));
            border-color: rgba(168, 85, 247, 0.3);
            color: #a855f7;
        }
        
        .action-btn.loans:hover {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(147, 51, 234, 0.2));
            box-shadow: 0 8px 25px rgba(168, 85, 247, 0.3);
            border-color: rgba(168, 85, 247, 0.5);
        }
        
        .action-btn.users {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.1), rgba(219, 39, 119, 0.1));
            border-color: rgba(236, 72, 153, 0.3);
            color: #ec4899;
        }
        
        .action-btn.users:hover {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.2), rgba(219, 39, 119, 0.2));
            box-shadow: 0 8px 25px rgba(236, 72, 153, 0.3);
            border-color: rgba(236, 72, 153, 0.5);
        }
        
        .action-btn.analytics {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(2, 132, 199, 0.1));
            border-color: rgba(14, 165, 233, 0.3);
            color: #0ea5e9;
        }
        
        .action-btn.analytics:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(2, 132, 199, 0.2));
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
            border-color: rgba(14, 165, 233, 0.5);
        }
        
        .action-btn.audit {
            background: linear-gradient(135deg, rgba(107, 114, 128, 0.1), rgba(75, 85, 99, 0.1));
            border-color: rgba(107, 114, 128, 0.3);
            color: #6b7280;
        }
        
        .action-btn.audit:hover {
            background: linear-gradient(135deg, rgba(107, 114, 128, 0.2), rgba(75, 85, 99, 0.2));
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.3);
            border-color: rgba(107, 114, 128, 0.5);
        }
        
        .action-btn.settings {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
            border-color: rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
        
        .action-btn.settings:hover {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2));
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
            border-color: rgba(239, 68, 68, 0.5);
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
            <a class="nav-item active" href="admin-dashboard.php">
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
                    <h2>Admin Dashboard</h2>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
                </div>
            </header>

            <main>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($total_users); ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($active_loans); ?></div>
                        <div class="stat-label">Active Loans</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($overdue_payments); ?></div>
                        <div class="stat-label">Overdue Payments</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($recovery_rate, 1); ?>%</div>
                        <div class="stat-label">Recovery Rate</div>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-value">₱<?php echo number_format($overdue_amount, 0); ?></div>
                        <div class="stat-label">Overdue Amount</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($today_payments); ?></div>
                        <div class="stat-label">Today's Payments</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($recent_applications); ?></div>
                        <div class="stat-label">Recent Applications</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-value">98.5%</div>
                        <div class="stat-label">System Uptime</div>
                    </div>
                </div>

                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="actions-grid">
                        <a href="payment-management.php" class="action-btn payment">
                            <i class="fas fa-credit-card"></i>
                            <span>Process Payment</span>
                        </a>
                        <a href="payment-history.php" class="action-btn history">
                            <i class="fas fa-history"></i>
                            <span>Payment History</span>
                        </a>
                        <a href="loan-management.php" class="action-btn loans">
                            <i class="fas fa-hand-holding-usd"></i>
                            <span>Loan Management</span>
                        </a>
                        <a href="analytics.php" class="action-btn analytics">
                            <i class="fas fa-chart-bar"></i>
                            <span>Analytics & Reports</span>
                        </a>
                        <a href="audit-log.php" class="action-btn audit">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Audit Log</span>
                        </a>
                        <a href="settings.php" class="action-btn settings">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="responsive-script.js"></script>

</body>
</html>
