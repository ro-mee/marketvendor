<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';
require_once 'includes/audit_helper.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Create audit_log table if it doesn't exist
try {
    $create_table_sql = "CREATE TABLE IF NOT EXISTS audit_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        user_name VARCHAR(255) NOT NULL,
        action VARCHAR(100) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_created_at (created_at),
        INDEX idx_user_id (user_id),
        INDEX idx_action (action)
    )";
    $db->exec($create_table_sql);
} catch (Exception $e) {
    // Table creation failed, continue with error handling
}

// Get search parameters
$search = $_GET['search'] ?? '';
$action_filter = $_GET['action'] ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Handle export request
if (isset($_GET['export']) && $_GET['export'] == '1') {
    // Build the same query as for display
    $where_conditions = ["1=1"];
    $params = [];

    if (!empty($search)) {
        $where_conditions[] = "(al.user_name LIKE ? OR al.action LIKE ? OR al.details LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if (!empty($action_filter)) {
        $where_conditions[] = "al.action = ?";
        $params[] = $action_filter;
    }

    if (!empty($date_from)) {
        $where_conditions[] = "DATE(al.created_at) >= ?";
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $where_conditions[] = "DATE(al.created_at) <= ?";
        $params[] = $date_to;
    }

    $where_clause = "WHERE " . implode(" AND ", $where_conditions);

    // Get all data for export (no pagination)
    $export_sql = "SELECT al.* FROM audit_log al 
                   $where_clause 
                   ORDER BY al.created_at DESC";
    
    $export_stmt = $db->prepare($export_sql);
    $export_stmt->execute($params);
    $export_data = $export_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log the export action
    logDataExport('Audit Log', count($export_data));

    // Generate CSV
    $filename = 'audit_log_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Add BOM for proper UTF-8 encoding in Excel
    fwrite($output, "\xEF\xBB\xBF");

    // CSV headers
    fputcsv($output, [
        'Date & Time',
        'User Name',
        'Action',
        'Details',
        'IP Address',
        'User Agent'
    ]);

    // CSV data
    foreach ($export_data as $row) {
        fputcsv($output, [
            date('M d, Y H:i:s', strtotime($row['created_at'])),
            $row['user_name'],
            ucfirst($row['action']),
            $row['details'],
            $row['ip_address'],
            $row['user_agent']
        ]);
    }

    fclose($output);
    exit();
}

// Check if table exists and has data
$table_exists = false;
$audit_logs = [];
$total_records = 0;
$total_pages = 1;
$action_types = [];

try {
    // Check if table exists
    $check_table = $db->query("SHOW TABLES LIKE 'audit_log'")->rowCount() > 0;
    
    if ($check_table) {
        $table_exists = true;
        
        // Build query
        $where_conditions = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where_conditions[] = "(al.user_name LIKE ? OR al.action LIKE ? OR al.details LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($action_filter)) {
            $where_conditions[] = "al.action = ?";
            $params[] = $action_filter;
        }

        if (!empty($date_from)) {
            $where_conditions[] = "DATE(al.created_at) >= ?";
            $params[] = $date_from;
        }

        if (!empty($date_to)) {
            $where_conditions[] = "DATE(al.created_at) <= ?";
            $params[] = $date_to;
        }

        $where_clause = "WHERE " . implode(" AND ", $where_conditions);

        // Get total count
        $count_sql = "SELECT COUNT(*) as total FROM audit_log al $where_clause";
        $count_stmt = $db->prepare($count_sql);
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $total_pages = ceil($total_records / $limit);

        // Get audit logs
        $sql = "SELECT al.* FROM audit_log al 
                $where_clause 
                ORDER BY al.created_at DESC 
                LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $audit_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get action types for filter
        $action_types = $db->query("SELECT DISTINCT action FROM audit_log ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);
        
        // If table is empty, add a sample entry for demonstration
        if ($total_records == 0) {
            $sample_sql = "INSERT INTO audit_log (user_id, user_name, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)";
            $sample_stmt = $db->prepare($sample_sql);
            $sample_stmt->execute([
                $_SESSION['user_id'] ?? 1,
                $_SESSION['user_name'] ?? 'Admin User',
                'login',
                'Audit log system initialized',
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            
            // Refresh the data
            $total_records = 1;
            $audit_logs = $db->query("SELECT * FROM audit_log ORDER BY created_at DESC LIMIT 1")->fetchAll(PDO::FETCH_ASSOC);
            $action_types = ['login'];
        }
    }
} catch (Exception $e) {
    // Handle database errors gracefully
    $error_message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Audit Log - Market Vendor Loan</title>
    <link rel="stylesheet" href="enhanced-styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        
        .audit-header {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 39, 75, 0.95) 100%);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .header-title h3 {
            margin: 0 0 8px 0;
            color: #f1f5f9;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header-title p {
            margin: 0;
            color: #94a3b8;
            font-size: 0.95rem;
        }
        
        .header-stats {
            display: flex;
            gap: 24px;
        }
        
        .stat-item {
            text-align: center;
            padding: 16px 24px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 8px;
            min-width: 100px;
        }
        
        .stat-value {
            display: block;
            font-size: 1.5rem;
            font-weight: 600;
            color: #3b82f6;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .search-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .search-form {
            flex: 1;
            min-width: 300px;
        }
        
        .search-group {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-input-wrapper,
        .filter-select-wrapper,
        .date-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .search-input-wrapper i,
        .filter-select-wrapper i,
        .date-input-wrapper i {
            position: absolute;
            left: 12px;
            color: #94a3b8;
            font-size: 0.9rem;
            z-index: 1;
        }
        
        .search-input-wrapper input,
        .filter-select-wrapper select,
        .date-input-wrapper input {
            padding: 10px 15px 10px 36px;
            background: rgba(15, 39, 75, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            color: #f1f5f9;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .search-input-wrapper input:focus,
        .filter-select-wrapper select:focus,
        .date-input-wrapper input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .date-input-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .date-input-wrapper span {
            color: #94a3b8;
            font-size: 0.85rem;
        }
        
        .search-btn {
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        
        .clear-btn {
            padding: 10px 16px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .clear-btn:hover {
            background: rgba(239, 68, 68, 0.2);
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
        }
        
        .export-btn {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .export-btn:hover {
            background: rgba(16, 185, 129, 0.3);
            transform: translateY(-1px);
        }
        
        .refresh-btn {
            background: rgba(99, 102, 241, 0.2);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #6366f1;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            background: rgba(99, 102, 241, 0.3);
            transform: translateY(-1px);
        }
        
        .table-section {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .audit-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .audit-table th {
            background: rgba(15, 39, 75, 0.8);
            color: #f1f5f9;
            padding: 16px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid rgba(59, 130, 246, 0.3);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .audit-table td {
            padding: 16px;
            color: #e2e8f0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            transition: background-color 0.2s ease;
        }
        
        .audit-table tbody tr:hover {
            background: rgba(59, 130, 246, 0.1);
        }
        
        .audit-table tbody tr:nth-child(even) {
            background: rgba(15, 39, 75, 0.2);
        }
        
        .audit-table tbody tr:nth-child(even):hover {
            background: rgba(59, 130, 246, 0.15);
        }
        
        .action-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .action-login { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
        .action-logout { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
        .action-create { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
        .action-update { background: rgba(251, 191, 36, 0.2); color: #fbbf24; }
        .action-delete { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
        .action-payment { background: rgba(16, 185, 129, 0.2); color: #10b981; }

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
        .action-export { background: rgba(6, 182, 212, 0.2); color: #06b6d4; }
        .action-print { background: rgba(168, 85, 247, 0.2); color: #a855f7; }
        .action-failed { background: rgba(239, 68, 68, 0.3); color: #dc2626; }
        
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
        }

        .pagination a, .pagination span {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            color: var(--text-200);
            background: rgba(15, 39, 75, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.2);
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: rgba(59, 130, 246, 0.2);
            color: var(--text-100);
            transform: translateY(-1px);
        }

        .pagination .current {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .export-btn {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .export-btn:hover {
            background: rgba(16, 185, 129, 0.3);
        }
        
        @media (max-width: 768px) {
            .content-wrap {
                margin-left: 0;
                padding: 16px;
            }
            
            .search-filters {
                flex-direction: column;
                width: 100%;
            }
            
            .search-filters input,
            .search-filters select {
                width: 100%;
            }
        }
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
            <a class="nav-item" href="analytics.php">
                <i class="fas fa-chart-line"></i> Analytics & Reports
            </a>
            <a class="nav-item active" href="audit-log.php">
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
                    <h2>Audit Log</h2>
                    <p>System activity tracking and security monitoring</p>
                </div>
            </header>

            <main>
                <!-- Enhanced Audit Header with Filters -->
                <div class="audit-header">
                    <div class="header-section">
                        <div class="header-title">
                            <h3><i class="fas fa-shield-alt"></i> Audit Trail</h3>
                            <p>Monitor and track all system activities</p>
                        </div>
                        <div class="header-stats">
                            <div class="stat-item">
                                <span class="stat-value"><?php echo number_format($total_records); ?></span>
                                <span class="stat-label">Total Logs</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value"><?php echo count($action_types); ?></span>
                                <span class="stat-label">Action Types</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="search-section">
                        <div class="search-filters">
                            <form method="GET" class="search-form">
                                <div class="search-group">
                                    <div class="search-input-wrapper">
                                        <i class="fas fa-search"></i>
                                        <input type="text" name="search" placeholder="Search user, action, or details..." value="<?php echo htmlspecialchars($search); ?>">
                                    </div>
                                    
                                    <div class="filter-select-wrapper">
                                        <i class="fas fa-filter"></i>
                                        <select name="action">
                                            <option value="">All Actions</option>
                                            <?php foreach ($action_types as $action): ?>
                                                <option value="<?php echo htmlspecialchars($action); ?>" <?php echo ($action_filter === $action) ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst(htmlspecialchars($action)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="date-input-wrapper">
                                        <i class="fas fa-calendar"></i>
                                        <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                                        <span>to</span>
                                        <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                                    </div>
                                    
                                    <button type="submit" class="search-btn">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    
                                    <?php if (!empty($search) || !empty($action_filter) || $date_from !== date('Y-m-01') || $date_to !== date('Y-m-d')): ?>
                                        <a href="audit-log.php" class="clear-btn">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="audit-log.php?export=1&<?php echo http_build_query($_GET); ?>" 
                               class="export-btn" 
                               onclick="handleExport(event, this, <?php echo $total_records; ?>)">
                                <i class="fas fa-file-csv"></i> Download CSV
                            </a>
                            <button onclick="refreshAuditLog()" class="refresh-btn">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Audit Logs Table -->
                <div class="table-section">
                    <table class="audit-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($audit_logs)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">
                                        <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                        No audit logs found matching your criteria.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($audit_logs as $log): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                                        <td>
                                            <span class="action-badge action-<?php echo strtolower($log['action']); ?>">
                                                <?php echo ucfirst(htmlspecialchars($log['action'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="pagination-btn">
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
                                echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '" class="pagination-number">1</a>';
                                if ($start_page > 2) {
                                    echo '<span style="color: var(--text-300); padding: 0 8px;">...</span>';
                                }
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++) {
                                $active_class = $i == $page ? 'active' : '';
                                echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => $i])) . '" class="pagination-number ' . $active_class . '">' . $i . '</a>';
                            }
                            
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<span style="color: var(--text-300); padding: 0 8px;">...</span>';
                                }
                                echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => $total_pages])) . '" class="pagination-number">' . $total_pages . '</a>';
                            }
                            ?>
                        </div>
                        
                        <span class="pagination-info">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </span>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="pagination-btn">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script>
        function handleExport(event, element, recordCount) {
            event.preventDefault();
            
            // Show confirmation for large exports
            if (recordCount > 1000) {
                const confirmed = confirm(`You are about to export ${recordCount.toLocaleString()} records. This may take a moment. Continue?`);
                if (!confirmed) {
                    return;
                }
            }
            
            // Show loading state
            const originalContent = element.innerHTML;
            element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing CSV...';
            element.style.pointerEvents = 'none';
            
            // Create and trigger download link
            setTimeout(() => {
                const link = document.createElement('a');
                link.href = element.href;
                link.download = 'audit_log_' + new Date().toISOString().split('T')[0] + '.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Restore button state
                element.innerHTML = '<i class="fas fa-check"></i> Downloaded!';
                element.style.pointerEvents = 'auto';
                
                // Reset after 2 seconds
                setTimeout(() => {
                    element.innerHTML = originalContent;
                }, 2000);
            }, 1000);
        }
        
        function refreshAuditLog() {
            // Show loading state
            const refreshBtn = document.querySelector('.refresh-btn');
            const originalContent = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
            refreshBtn.disabled = true;
            
            // Reload the page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+K or Cmd+K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.querySelector('input[name="search"]').focus();
            }
            // Escape to clear search
            if (e.key === 'Escape') {
                const searchInput = document.querySelector('input[name="search"]');
                if (searchInput.value) {
                    window.location.href = 'audit-log.php';
                }
            }
            // Ctrl+E or Cmd+E to export
            if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
                e.preventDefault();
                const exportBtn = document.querySelector('.export-btn');
                if (exportBtn) {
                    exportBtn.click();
                }
            }
        });
    </script>
    <script src="responsive-script.js"></script>
</body>
</html>
