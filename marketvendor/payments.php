<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get all upcoming and overdue payments with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

$stmt = $db->prepare("SELECT * FROM payment_schedules WHERE user_id = ? AND status = 'pending' ORDER BY due_date ASC LIMIT ? OFFSET ?");
$stmt->execute([$user_id, $per_page, $offset]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$count_stmt = $db->prepare("SELECT COUNT(*) as total FROM payment_schedules WHERE user_id = ? AND status = 'pending'");
$count_stmt->execute([$user_id]);
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $per_page);

// Get payment statistics
$total_upcoming = 0;
$total_overdue = 0;
$next_payment = null;
$next_payment_amount = 0;

foreach ($payments as $payment) {
    if ($payment['due_date'] < date('Y-m-d')) {
        $total_overdue += $payment['total_amount'];
    } else {
        $total_upcoming += $payment['total_amount'];
        if (!$next_payment) {
            $next_payment = $payment;
            $next_payment_amount = $payment['total_amount'];
        }
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
    <title>Payment Schedule - BlueLedger Finance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="enhanced-styles.css">
    <link rel="stylesheet" href="responsive-styles-fixed.css">
    <style>
        .alert-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .alert-card {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid var(--line);
            border-radius: var(--card-radius);
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        .alert-card.overdue {
            border-left: 4px solid #ef4444;
        }

        .alert-card.upcoming {
            border-left: 4px solid var(--success);
        }

        .alert-card.next-payment {
            border-left: 4px solid #3b82f6;
        }

        .alert-card h3 {
            color: var(--text-100);
            font-size: 1rem;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-card .amount {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-100);
            margin-bottom: 8px;
        }

        .alert-card .info {
            color: var(--text-300);
            font-size: 0.875rem;
        }

        .payments-section {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid var(--line);
            border-radius: var(--card-radius);
            padding: 24px;
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

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 16px;
            background: rgba(15, 39, 75, 0.5);
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--text-200);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.875rem;
        }

        .filter-btn:hover {
            border-color: var(--success);
        }

        .filter-btn.active {
            background: var(--success);
            color: white;
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

        .amount {
            font-weight: 600;
            color: #60a5fa;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-badge.overdue {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .status-badge.due-soon {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .status-badge.scheduled {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .priority-high {
            color: #ef4444;
            font-weight: bold;
        }

        .priority-medium {
            color: #fbbf24;
            font-weight: bold;
        }

        .priority-normal {
            color: #22c55e;
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

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 25px;
            padding: 20px;
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
        }

        .pagination-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            border: 1px solid var(--primary);
        }

        .pagination-btn:hover {
            background: rgba(59, 130, 246, 0.8);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .pagination-info {
            color: var(--text-100);
            font-size: 0.9rem;
            font-weight: 500;
            padding: 8px 16px;
            background: rgba(15, 39, 75, 0.5);
            border-radius: 20px;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }

        .pagination-numbers {
            display: flex;
            gap: 8px;
        }

        .pagination-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: transparent;
            color: var(--text-200);
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }

        .pagination-number:hover {
            background: rgba(59, 130, 246, 0.1);
            color: var(--text-100);
            border-color: var(--primary);
        }

        .pagination-number.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        @media (max-width: 768px) {
            .alert-cards {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .payments-table {
                font-size: 0.8rem;
            }
            
            .payments-table th,
            .payments-table td {
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
            <a class="nav-item" href="my-loans.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a class="nav-item" href="apply-loan.php">
                <i class="fas fa-plus-circle"></i> Apply for Loan
            </a>
            <a class="nav-item active" href="payments.php">
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
                    <h2>Payment Schedule</h2>
                    <p>View your upcoming and overdue payments</p>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="main-content">
                <!-- Alert Cards -->
                <div class="alert-cards">
                    <?php if ($total_overdue > 0): ?>
                        <div class="alert-card overdue">
                            <h3><i class="fas fa-exclamation-triangle"></i> Overdue Payments</h3>
                            <div class="amount">₱<?php echo number_format($total_overdue, 2); ?></div>
                            <div class="priority-high">Immediate attention required</div>
                        </div>
                    <?php endif; ?>

                    <?php if ($next_payment): ?>
                        <div class="alert-card next-payment">
                            <h3><i class="fas fa-calendar-check"></i> Next Payment</h3>
                            <div class="amount">₱<?php echo number_format($next_payment_amount, 2); ?></div>
                            <div class="info">Due: <?php echo date('M d, Y', strtotime($next_payment['due_date'])); ?></div>
                            <?php 
                            $days_until = (new DateTime($next_payment['due_date']))->diff(new DateTime())->days;
                            if ($days_until <= 3) {
                                echo '<div class="priority-medium">Due in ' . $days_until . ' days</div>';
                            } else {
                                echo '<div class="priority-normal">Due in ' . $days_until . ' days</div>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="alert-card upcoming">
                        <h3><i class="fas fa-clock"></i> Total Upcoming</h3>
                        <div class="amount">₱<?php echo number_format($total_upcoming, 2); ?></div>
                        <div class="info"><?php echo count($payments); ?> scheduled payments</div>
                    </div>
                </div>

                <!-- Payments Section -->
                <div class="payments-section">
                    <div class="section-header">
                        <h2 class="section-title">Payment Schedule</h2>
                        <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                    </div>

                    <div class="filters">
                        <button class="filter-btn active" onclick="filterPayments('all')">All Payments</button>
                        <button class="filter-btn" onclick="filterPayments('overdue')">Overdue</button>
                        <button class="filter-btn" onclick="filterPayments('due-soon')">Due Soon (7 days)</button>
                        <button class="filter-btn" onclick="filterPayments('scheduled')">Scheduled</button>
                    </div>

                    <?php if (count($payments) > 0): ?>
                        <div class="table-container">
                            <table class="payments-table" id="paymentsTable">
                                <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>Loan ID</th>
                                        <th>Due Date</th>
                                        <th>Principal</th>
                                        <th>Interest</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <?php
                                        $due_date = new DateTime($payment['due_date']);
                                        $today = new DateTime();
                                        $days_diff = $today->diff($due_date)->days;
                                        $is_overdue = $today > $due_date;
                                        $is_due_soon = !$is_overdue && $days_diff <= 7;
                                        
                                        $priority_class = $is_overdue ? 'priority-high' : ($is_due_soon ? 'priority-medium' : 'priority-normal');
                                        $status_class = $is_overdue ? 'overdue' : ($is_due_soon ? 'due-soon' : 'scheduled');
                                        $status_text = $is_overdue ? 'Overdue' : ($is_due_soon ? 'Due Soon' : 'Scheduled');
                                        ?>
                                        <tr data-status="<?php echo $status_class; ?>" data-days="<?php echo $days_diff; ?>" data-overdue="<?php echo $is_overdue ? '1' : '0'; ?>">
                                            <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['loan_id']); ?></td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($payment['due_date'])); ?>
                                                <?php if ($is_overdue): ?>
                                                    <br><small class="<?php echo $priority_class; ?>"><?php echo $days_diff; ?> days overdue</small>
                                                <?php elseif ($days_diff <= 7): ?>
                                                    <br><small class="<?php echo $priority_class; ?>">Due in <?php echo $days_diff; ?> days</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="amount">₱<?php echo number_format($payment['principal_amount'], 2); ?></td>
                                            <td class="amount">₱<?php echo number_format($payment['interest_amount'], 2); ?></td>
                                            <td class="amount">₱<?php echo number_format($payment['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $status_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                            <td class="<?php echo $priority_class; ?>">
                                                <?php 
                                                if ($is_overdue) {
                                                    echo 'High';
                                                } elseif ($is_due_soon) {
                                                    echo 'Medium';
                                                } else {
                                                    echo 'Normal';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-check"></i>
                            <h3>No Scheduled Payments</h3>
                            <p>You don't have any upcoming payments at the moment.</p>
                        </div>
                    <?php endif; ?>
                    
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
        function filterPayments(filter) {
            const rows = document.querySelectorAll('#paymentsTable tbody tr');
            const buttons = document.querySelectorAll('.filter-btn');
            
            // Update active button
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            rows.forEach(row => {
                const status = row.dataset.status;
                const isOverdue = row.dataset.overdue === '1';
                const daysUntil = parseInt(row.dataset.days);
                
                switch(filter) {
                    case 'all':
                        row.style.display = '';
                        break;
                    case 'overdue':
                        row.style.display = isOverdue ? '' : 'none';
                        break;
                    case 'due-soon':
                        row.style.display = (status === 'due-soon' || isOverdue) ? '' : 'none';
                        break;
                    case 'scheduled':
                        row.style.display = status === 'scheduled' ? '' : 'none';
                        break;
                }
            });
        }

        // Auto-refresh every 5 minutes
        setTimeout(() => {
            location.reload();
        }, 300000);
    </script>
    <script src="responsive-script.js"></script>

</body>
</html>
