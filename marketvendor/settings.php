<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';
require_once 'includes/late_fee_functions.php';
require_once 'includes/audit_helper.php';

$database = new Database();
$db = $database->getConnection();
$lateFeeManager = new LateFeeManager();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_interest_rates'])) {
        try {
            // Update or insert interest rates
            $rates = [
                'daily' => floatval($_POST['daily_rate']),
                'weekly' => floatval($_POST['weekly_rate']),
                'monthly' => floatval($_POST['monthly_rate'])
            ];
            
            foreach ($rates as $frequency => $rate) {
                // Check if setting exists
                $stmt = $db->prepare("SELECT id FROM system_settings WHERE setting_key = ?");
                $stmt->execute(["interest_rate_{$frequency}"]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    // Update existing
                    $stmt = $db->prepare("UPDATE system_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?");
                    $stmt->execute([$rate, "interest_rate_{$frequency}"]);
                } else {
                    // Insert new
                    $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, 'percentage', ?)");
                    $stmt->execute(["interest_rate_{$frequency}", $rate, "Interest rate for {$frequency} payments"]);
                }
            }
            
            $_SESSION['success_message'] = "Interest rates updated successfully!";
            
            // Log the action
            logAudit('update_interest_rates', json_encode($rates), $_SESSION['user_id']);
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error updating interest rates: " . $e->getMessage();
        }
        
        header("Location: settings.php");
        exit();
    }
    
    if (isset($_POST['save_late_fee_settings'])) {
        try {
            $settings = [
                'id' => $_POST['settings_id'],
                'fee_type' => $_POST['fee_type'],
                'percentage_rate' => floatval($_POST['percentage_rate']),
                'fixed_amount' => floatval($_POST['fixed_amount']),
                'grace_period_days' => intval($_POST['grace_period_days']),
                'max_fee_percentage' => floatval($_POST['max_fee_percentage']),
                'compound_daily' => isset($_POST['compound_daily']),
                'apply_weekends' => isset($_POST['apply_weekends']),
                'min_fee_amount' => floatval($_POST['min_fee_amount']),
                'description' => $_POST['description']
            ];
            
            $result = $lateFeeManager->updateFeeSettings($settings);
            if ($result) {
                $_SESSION['success_message'] = "Late fee settings updated successfully!";
                
                // Log the action
                logAudit('update_late_fee_settings', json_encode($settings), $_SESSION['user_id']);
            } else {
                $_SESSION['error_message'] = "Failed to update late fee settings. Please check your database connection.";
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error updating late fee settings: " . $e->getMessage();
        }
        
        header("Location: settings.php");
        exit();
    }
    
    if (isset($_POST['assess_fees'])) {
        try {
            $fees_assessed = $lateFeeManager->assessLateFees();
            $_SESSION['success_message'] = "Successfully assessed {$fees_assessed} late fees!";
            
            // Log the action
            logAudit('assess_late_fees', "Assessed {$fees_assessed} late fees", $_SESSION['user_id']);
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error assessing late fees: " . $e->getMessage();
        }
        
        header("Location: settings.php");
        exit();
    }
    
    if (isset($_POST['update_tiers'])) {
        try {
            $tiers = [];
            for ($i = 0; $i < count($_POST['days_from']); $i++) {
                $tiers[] = [
                    'days_from' => intval($_POST['days_from'][$i]),
                    'days_to' => intval($_POST['days_to'][$i]),
                    'fee_type' => $_POST['tier_fee_type'][$i],
                    'fee_value' => floatval($_POST['fee_value'][$i]),
                    'max_fee_amount' => !empty($_POST['max_fee_amount'][$i]) ? floatval($_POST['max_fee_amount'][$i]) : null
                ];
            }
            
            $lateFeeManager->updateTierStructure($tiers);
            $_SESSION['success_message'] = "Tier structure updated successfully!";
            
            // Log the action
            logAudit('update_late_fee_tiers', json_encode($tiers), $_SESSION['user_id']);
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error updating tiers: " . $e->getMessage();
        }
        
        header("Location: settings.php");
        exit();
    }
    
    if (isset($_POST['waive_fee'])) {
        try {
            $fee_id = $_POST['fee_id'];
            $reason = $_POST['waiver_reason'];
            
            $lateFeeManager->waiveLateFee($fee_id, $_SESSION['user_id'], $reason);
            $_SESSION['success_message'] = "Late fee waived successfully!";
            
            // Log the action
            logAudit('waive_late_fee', "Waived fee ID: {$fee_id}, Reason: {$reason}", $_SESSION['user_id']);
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error waiving fee: " . $e->getMessage();
        }
        
        header("Location: settings.php");
        exit();
    }
}

// Get current interest rates
$interest_rates = [];
$frequencies = ['daily', 'weekly', 'monthly'];

foreach ($frequencies as $frequency) {
    $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    $stmt->execute(["interest_rate_{$frequency}"]);
    $result = $stmt->fetch();
    $interest_rates[$frequency] = $result ? floatval($result['setting_value']) : 5.0; // Default 5%
}

// Get late fee data
$feeSettings = $lateFeeManager->getFeeSettings();
$tiers = $lateFeeManager->getTiers();
$statistics = $lateFeeManager->getLateFeeStatistics();

// Get recent late fees
$stmt = $db->prepare("
    SELECT lf.*, u.name as client_name, u.email, l.loan_purpose
    FROM late_fees lf
    JOIN loans l ON lf.loan_id = l.loan_id
    JOIN users u ON l.user_id = u.id
    ORDER BY lf.created_at DESC
    LIMIT 10
");
$stmt->execute();
$recentFees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Market Vendor Loan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="enhanced-styles.css">
    <link rel="stylesheet" href="responsive-styles-fixed.css">
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
            font-size: 14px;
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
        
        .settings-section {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 18px;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }
        
        .section-header i {
            font-size: 1.5rem;
            color: #60a5fa;
        }
        
        .section-title {
            color: #e2e8f0;
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
        }
        
        .rates-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        
        /* Responsive for rates grid */
        @media (max-width: 768px) {
            .rates-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .rates-grid {
                grid-template-columns: 1fr 1fr;
                gap: 18px;
            }
        }
        
        .rate-card {
            background: linear-gradient(135deg, rgba(15, 39, 75, 0.6), rgba(30, 41, 59, 0.4));
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .rate-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.05), transparent);
            transition: left 0.5s ease;
        }
        
        .rate-card:hover::before {
            left: 100%;
        }
        
        .rate-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(96, 165, 250, 0.3);
        }
        
        .rate-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 16px;
        }
        
        .rate-icon.daily {
            background: linear-gradient(135deg, rgba(251, 146, 60, 0.2), rgba(245, 158, 11, 0.2));
            color: #fb923c;
        }
        
        .rate-icon.weekly {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(16, 163, 74, 0.2));
            color: #22c55e;
        }
        
        .rate-icon.monthly {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(147, 51, 234, 0.2));
            color: #a855f7;
        }
        
        .rate-title {
            color: #e2e8f0;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .rate-input-group {
            position: relative;
            margin-bottom: 16px;
        }
        
        .rate-input {
            width: 100%;
            padding: 14px 50px 14px 16px;
            background: rgba(30, 41, 59, 0.8);
            border: 2px solid rgba(148, 163, 184, 0.2);
            border-radius: 10px;
            color: #e2e8f0;
            font-size: 1.2rem;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        
        .rate-input:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.1);
            background: rgba(30, 41, 59, 0.9);
        }
        
        .rate-suffix {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #60a5fa;
            font-weight: 700;
            font-size: 1.1rem;
            pointer-events: none;
        }
        
        .rate-description {
            color: #94a3b8;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .info-box {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1));
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        
        .info-box-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .info-box-header i {
            color: #60a5fa;
            font-size: 1.2rem;
        }
        
        .info-box-title {
            color: #60a5fa;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .info-content {
            color: #cbd5e1;
            line-height: 1.6;
        }
        
        .info-content ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .info-content li {
            margin-bottom: 8px;
        }
        
        .action-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn-save {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(16, 163, 74, 0.2));
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #22c55e;
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-save:hover {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.3), rgba(16, 163, 74, 0.3));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.2);
        }
        
        .btn-reset {
            background: rgba(148, 163, 184, 0.2);
            border: 1px solid rgba(148, 163, 184, 0.3);
            color: #94a3b8;
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-reset:hover {
            background: rgba(148, 163, 184, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(148, 163, 184, 0.2);
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
        
        /* Override global input styles for checkboxes */
        input[type="checkbox"] {
            width: 18px !important;
            height: 18px !important;
            background: transparent !important;
            border: 2px solid #3b82f6 !important;
            border-radius: 4px !important;
            padding: 0 !important;
            position: relative !important;
            z-index: 9999 !important;
            cursor: pointer !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
        }
        
        input[type="checkbox"]:checked {
            background: #3b82f6 !important;
            border-color: #3b82f6 !important;
        }
        
        input[type="checkbox"]:checked::after {
            content: '✓' !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            color: white !important;
            font-size: 12px !important;
            font-weight: bold !important;
        }
        
        input[type="checkbox"]:hover {
            border-color: #60a5fa !important;
            transform: scale(1.05) !important;
        }
        
        /* Responsive Two-Column Layout */
        .settings-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .settings-container {
                grid-template-columns: 1fr;
                gap: 18px;
            }
        }
        
        @media (max-width: 768px) {
            .settings-container {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .settings-section {
                margin-bottom: 16px;
            }
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
            <a class="nav-item" href="payment-management.php">
                <i class="fas fa-credit-card"></i> Process Payment
            </a>

            <a class="nav-item" href="analytics.php">
                <i class="fas fa-chart-line"></i> Analytics & Reports
            </a>
            <a class="nav-item" href="audit-log.php">
                <i class="fas fa-clipboard-list"></i> Audit Log
            </a>
                <a class="nav-item active" href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
        </aside>

        <div class="content-wrap">
            <div class="dashboard-header">
                <div class="user-info">
                    <div class="user-avatar">
<?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?>                    </div>
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
                    <h2>System Settings</h2>
                    <p>Configure interest rates and late fee settings</p>
                </div>
            </header>

            <main>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="message success-message">
                    <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="message error-message">
                    <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Settings Container - Two Column Layout -->
            <div class="settings-container">
                <!-- Interest Rate Configuration Section -->
                <div class="settings-section">
                    <div class="section-header">
                        <i class="fas fa-percentage"></i>
                        <h2 class="section-title">Interest Rate Configuration</h2>
                    </div>
                    
                    <div class="info-box">
                        <div class="info-box-header">
                            <i class="fas fa-info-circle"></i>
                            <div class="info-box-title">How Interest Rates Work</div>
                        </div>
                        <div class="info-content">
                            <ul>
                                <li><strong>Daily Rate:</strong> Applied to loans with daily payment schedules (e.g., market vendors who pay daily)</li>
                                <li><strong>Weekly Rate:</strong> Applied to loans with weekly payment schedules (e.g., weekly income earners)</li>
                                <li><strong>Monthly Rate:</strong> Applied to loans with monthly payment schedules (e.g., salaried employees)</li>
                                <li>These rates are automatically applied when creating new loans based on the selected payment frequency</li>
                            </ul>
                        </div>
                    </div>

                    <form method="POST" id="interestRatesForm">
                        <div class="rates-grid">
                            <div class="rate-card">
                                <div class="rate-icon daily">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div class="rate-title">Daily Rate</div>
                                <div class="rate-input-group">
                                <input type="number" name="daily_rate" step="0.1" min="0" max="100" value="<?php echo $interest_rates['daily']; ?>" required>
                                <span class="rate-suffix">%</span>
                            </div>
                            <div class="rate-description">
                                Interest rate for daily payment loans. Typically higher due to frequent payment cycles.
                            </div>
                        </div>

                        <div class="rate-card">
                            <div class="rate-icon weekly">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                            <div class="rate-title">Weekly Rate</div>
                            <div class="rate-input-group">
                                <input type="number" name="weekly_rate" step="0.1" min="0" max="100" value="<?php echo $interest_rates['weekly']; ?>" required>
                                <span class="rate-suffix">%</span>
                            </div>
                            <div class="rate-description">
                                Interest rate for weekly payment loans. Balanced rate for regular weekly income.
                            </div>
                        </div>

                        <div class="rate-card">
                            <div class="rate-icon monthly">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="rate-title">Monthly Rate</div>
                            <div class="rate-input-group">
                                <input type="number" name="monthly_rate" step="0.1" min="0" max="100" value="<?php echo $interest_rates['monthly']; ?>" required>
                                <span class="rate-suffix">%</span>
                            </div>
                            <div class="rate-description">
                                Interest rate for monthly payment loans. Typically lower due to longer payment cycles.
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" name="save_interest_rates" class="btn-save">
                            <i class="fas fa-save"></i> Save Interest Rates
                        </button>
                        <button type="button" class="btn-reset" onclick="resetToDefaults()">
                            <i class="fas fa-undo"></i> Reset to Defaults
                        </button>
                    </div>
                </form>
                </div>

                <!-- Late Fee Settings Section -->
                <div class="settings-section">
                    <div class="section-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h2 class="section-title">Late Fee Settings</h2>
                    </div>
                    
                    <form method="POST" id="lateFeeSettingsForm">
                        <input type="hidden" name="settings_id" value="<?php echo $feeSettings['id']; ?>">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div>
                                <label style="display: block; color: #cbd5e1; margin-bottom: 8px; font-weight: 500;">Fee Type</label>
                                <select name="fee_type" style="width: 100%; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(148, 163, 184, 0.3); color: #f1f5f9; padding: 12px; border-radius: 8px;" required>
                                    <option value="percentage" <?php echo $feeSettings['fee_type'] === 'percentage' ? 'selected' : ''; ?>>Percentage</option>
                                    <option value="fixed" <?php echo $feeSettings['fee_type'] === 'fixed' ? 'selected' : ''; ?>>Fixed Amount</option>
                                    <option value="tiered" <?php echo $feeSettings['fee_type'] === 'tiered' ? 'selected' : ''; ?>>Tiered</option>
                            </select>
                        </div>
                        
                        <div>
                            <label style="display: block; color: #cbd5e1; margin-bottom: 8px; font-weight: 500;">Percentage Rate (%)</label>
                            <input type="number" name="percentage_rate" step="0.01" value="<?php echo $feeSettings['percentage_rate']; ?>" style="width: 100%; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(148, 163, 184, 0.3); color: #f1f5f9; padding: 12px; border-radius: 8px;" required>
                        </div>
                        
                        <div>
                            <label style="display: block; color: #cbd5e1; margin-bottom: 8px; font-weight: 500;">Fixed Amount (₱)</label>
                            <input type="number" name="fixed_amount" step="0.01" value="<?php echo $feeSettings['fixed_amount']; ?>" style="width: 100%; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(148, 163, 184, 0.3); color: #f1f5f9; padding: 12px; border-radius: 8px;" required>
                        </div>
                        
                        <div>
                            <label style="display: block; color: #cbd5e1; margin-bottom: 8px; font-weight: 500;">Grace Period (Days)</label>
                            <input type="number" name="grace_period_days" value="<?php echo $feeSettings['grace_period_days']; ?>" style="width: 100%; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(148, 163, 184, 0.3); color: #f1f5f9; padding: 12px; border-radius: 8px;" required>
                        </div>
                        
                        <div>
                            <label style="display: block; color: #cbd5e1; margin-bottom: 8px; font-weight: 500;">Max Fee Percentage (%)</label>
                            <input type="number" name="max_fee_percentage" step="0.01" value="<?php echo $feeSettings['max_fee_percentage']; ?>" style="width: 100%; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(148, 163, 184, 0.3); color: #f1f5f9; padding: 12px; border-radius: 8px;" required>
                        </div>
                        
                        <div>
                            <label style="display: block; color: #cbd5e1; margin-bottom: 8px; font-weight: 500;">Minimum Fee Amount (₱)</label>
                            <input type="number" name="min_fee_amount" step="0.01" value="<?php echo $feeSettings['min_fee_amount']; ?>" style="width: 100%; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(148, 163, 184, 0.3); color: #f1f5f9; padding: 12px; border-radius: 8px;" required>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 20px; background: rgba(15, 39, 75, 0.3); padding: 20px; border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.2);">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                            <div>
                                <label for="compound_daily" style="display: flex; align-items: center; gap: 12px; cursor: pointer; color: #e2e8f0;">
                                    <input type="checkbox" name="compound_daily" id="compound_daily" value="1" <?php echo $feeSettings['compound_daily'] ? 'checked' : ''; ?>>
                                    <span style="font-weight: 500;">Compound Daily</span>
                                </label>
                                <p style="margin: 8px 0 0 30px; color: #94a3b8; font-size: 0.85rem;">Apply compound interest to late fees</p>
                            </div>
                            
                            <div>
                                <label for="apply_weekends" style="display: flex; align-items: center; gap: 12px; cursor: pointer; color: #e2e8f0;">
                                    <input type="checkbox" name="apply_weekends" id="apply_weekends" value="1" <?php echo $feeSettings['apply_weekends'] ? 'checked' : ''; ?>>
                                    <span style="font-weight: 500;">Apply Weekends</span>
                                </label>
                                <p style="margin: 8px 0 0 30px; color: #94a3b8; font-size: 0.85rem;">Count weekends in late fee calculation</p>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                        <h4 style="color: #60a5fa; margin: 0 0 8px 0; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-info-circle"></i> Calculation Preview
                        </h4>
                        <p style="color: #cbd5e1; margin: 0; font-size: 0.85rem; line-height: 1.4;">
                            <?php if ($feeSettings['compound_daily']): ?>
                                <strong>Compound Interest:</strong> Fees will compound daily for exponential growth.
                            <?php else: ?>
                                <strong>Simple Interest:</strong> Fees will accumulate linearly based on daily rate.
                            <?php endif; ?>
                            <?php if ($feeSettings['apply_weekends']): ?>
                                Weekends are included in the calculation.
                            <?php else: ?>
                                Only weekdays (Monday-Friday) are counted.
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; color: #cbd5e1; margin-bottom: 8px; font-weight: 500;">Description</label>
                        <textarea name="description" rows="3" style="width: 100%; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(148, 163, 184, 0.3); color: #f1f5f9; padding: 12px; border-radius: 8px;"><?php echo $feeSettings['description']; ?></textarea>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" name="save_late_fee_settings" class="btn-save">
                            <i class="fas fa-save"></i> Update Late Fee Settings
                        </button>
                    </div>
                </form>
                </div>
            </div>
        </main>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('interestRatesForm').addEventListener('submit', function(e) {
            const dailyRate = parseFloat(document.querySelector('input[name="daily_rate"]').value);
            const weeklyRate = parseFloat(document.querySelector('input[name="weekly_rate"]').value);
            const monthlyRate = parseFloat(document.querySelector('input[name="monthly_rate"]').value);
            
            // Validate rates are within reasonable bounds
            if (dailyRate < 0 || dailyRate > 100) {
                e.preventDefault();
                alert('Daily rate must be between 0% and 100%');
                return;
            }
            
            if (weeklyRate < 0 || weeklyRate > 100) {
                e.preventDefault();
                alert('Weekly rate must be between 0% and 100%');
                return;
            }
            
            if (monthlyRate < 0 || monthlyRate > 100) {
                e.preventDefault();
                alert('Monthly rate must be between 0% and 100%');
                return;
            }
            
            // Confirm before saving
            if (!confirm('Are you sure you want to update the interest rates? This will affect new loan calculations.')) {
                e.preventDefault();
                return;
            }
        });

        // Reset to defaults function
        function resetToDefaults() {
            if (confirm('Are you sure you want to reset all interest rates to default values?')) {
                document.querySelector('input[name="daily_rate"]').value = '5.0';
                document.querySelector('input[name="weekly_rate"]').value = '4.5';
                document.querySelector('input[name="monthly_rate"]').value = '3.5';
                
                // Trigger change event for auto-save warning
                document.querySelectorAll('.rate-input').forEach(input => {
                    input.dispatchEvent(new Event('input'));
                });
            }
        }

        // Auto-save warning
        let formChanged = false;
        document.querySelectorAll('.rate-input').forEach(input => {
            input.addEventListener('input', function() {
                formChanged = true;
            });
        });
        
        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
        
        // Reset form changed flag after save
        document.getElementById('interestRatesForm').addEventListener('submit', function() {
            formChanged = false;
        });
    </script>
    <script src="responsive-script.js"></script>

</body>
</html>
