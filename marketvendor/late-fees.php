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
    if (isset($_POST['assess_fees'])) {
        try {
            $fees_assessed = $lateFeeManager->assessLateFees();
            $_SESSION['success_message'] = "Successfully assessed {$fees_assessed} late fees!";
            
            // Log the action
            logAudit('assess_late_fees', "Assessed {$fees_assessed} late fees", $_SESSION['user_id']);
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error assessing late fees: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['update_settings'])) {
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
            
            $lateFeeManager->updateFeeSettings($settings);
            $_SESSION['success_message'] = "Late fee settings updated successfully!";
            
            // Log the action
            logAudit('update_late_fee_settings', json_encode($settings), $_SESSION['user_id']);
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error updating settings: " . $e->getMessage();
        }
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
    }
}

// Get data
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
    LIMIT 20
");
$stmt->execute();
$recentFees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Late Fees Management - Market Vendor Loan System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .late-fees-container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(147, 51, 234, 0.1));
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #f1f5f9;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #94a3b8;
            font-size: 0.9rem;
        }
        
        .section {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .section h2 {
            color: #f1f5f9;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            color: #cbd5e1;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.3);
            color: #f1f5f9;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #cbd5e1;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        
        .fees-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(15, 23, 42, 0.6);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .fees-table th {
            background: rgba(30, 41, 59, 0.8);
            color: #f1f5f9;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }
        
        .fees-table td {
            padding: 15px;
            color: #cbd5e1;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
        }
        
        .fees-table tr:hover {
            background: rgba(59, 130, 246, 0.05);
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-pending {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }
        
        .status-applied {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        
        .status-waived {
            background: rgba(107, 114, 128, 0.2);
            color: #94a3b8;
        }
        
        .tier-row {
            display: grid;
            grid-template-columns: 100px 100px 120px 120px 120px auto;
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background: rgba(15, 23, 42, 0.6);
            border-radius: 8px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <i class="fas fa-store"></i>
                <span>Market Vendor</span>
            </div>
            <nav class="sidebar-nav">
                <a href="admin-dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="loan-management.php" class="nav-link">
                    <i class="fas fa-hand-holding-usd"></i> Loan Management
                </a>
                <a href="payment-management.php" class="nav-link">
                    <i class="fas fa-credit-card"></i> Payment Management
                </a>
                <a href="late-fees.php" class="nav-link active">
                    <i class="fas fa-exclamation-triangle"></i> Late Fees
                </a>
                <a href="analytics.php" class="nav-link">
                    <i class="fas fa-chart-line"></i> Analytics
                </a>
                <a href="audit-log.php" class="nav-link">
                    <i class="fas fa-history"></i> Audit Log
                </a>
                <a href="settings.php" class="nav-link">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="dashboard-header">
                <div class="header-content">
                    <h1><i class="fas fa-exclamation-triangle"></i> Late Fees Management</h1>
                    <p>Manage automated penalty system and fee configurations</p>
                </div>
                <div class="header-actions">
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="assess_fees" class="btn btn-warning">
                            <i class="fas fa-calculator"></i> Assess Late Fees
                        </button>
                    </form>
                </div>
            </header>

            <div class="late-fees-container">
                <!-- Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value">₱<?php echo number_format($statistics['collected_fees'], 2); ?></div>
                        <div class="stat-label">Collected Fees</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">₱<?php echo number_format($statistics['pending_fees'], 2); ?></div>
                        <div class="stat-label">Pending Fees</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">₱<?php echo number_format($statistics['waived_fees'], 2); ?></div>
                        <div class="stat-label">Waived Fees</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $statistics['total_fees']; ?></div>
                        <div class="stat-label">Total Fees</div>
                    </div>
                </div>

                <!-- Fee Settings -->
                <div class="section">
                    <h2><i class="fas fa-cog"></i> Fee Settings</h2>
                    <form method="POST">
                        <input type="hidden" name="settings_id" value="<?php echo $feeSettings['id']; ?>">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Fee Type</label>
                                <select name="fee_type" required>
                                    <option value="percentage" <?php echo $feeSettings['fee_type'] === 'percentage' ? 'selected' : ''; ?>>Percentage</option>
                                    <option value="fixed" <?php echo $feeSettings['fee_type'] === 'fixed' ? 'selected' : ''; ?>>Fixed Amount</option>
                                    <option value="tiered" <?php echo $feeSettings['fee_type'] === 'tiered' ? 'selected' : ''; ?>>Tiered</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Percentage Rate (%)</label>
                                <input type="number" name="percentage_rate" step="0.01" value="<?php echo $feeSettings['percentage_rate']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Fixed Amount (₱)</label>
                                <input type="number" name="fixed_amount" step="0.01" value="<?php echo $feeSettings['fixed_amount']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Grace Period (Days)</label>
                                <input type="number" name="grace_period_days" value="<?php echo $feeSettings['grace_period_days']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Max Fee Percentage (%)</label>
                                <input type="number" name="max_fee_percentage" step="0.01" value="<?php echo $feeSettings['max_fee_percentage']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Minimum Fee Amount (₱)</label>
                                <input type="number" name="min_fee_amount" step="0.01" value="<?php echo $feeSettings['min_fee_amount']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="checkbox-group">
                                <input type="checkbox" name="compound_daily" <?php echo $feeSettings['compound_daily'] ? 'checked' : ''; ?>>
                                <label>Compound Daily</label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" name="apply_weekends" <?php echo $feeSettings['apply_weekends'] ? 'checked' : ''; ?>>
                                <label>Apply Weekends</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="3"><?php echo $feeSettings['description']; ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_settings" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Settings
                        </button>
                    </form>
                </div>

                <!-- Tier Structure (only shown when tiered is selected) -->
                <?php if ($feeSettings['fee_type'] === 'tiered'): ?>
                <div class="section">
                    <h2><i class="fas fa-layer-group"></i> Tier Structure</h2>
                    <form method="POST">
                        <div id="tier-container">
                            <?php foreach ($tiers as $index => $tier): ?>
                            <div class="tier-row">
                                <input type="number" name="days_from[]" value="<?php echo $tier['days_from']; ?>" placeholder="Days From" required>
                                <input type="number" name="days_to[]" value="<?php echo $tier['days_to']; ?>" placeholder="Days To" required>
                                <select name="tier_fee_type[]" required>
                                    <option value="percentage" <?php echo $tier['fee_type'] === 'percentage' ? 'selected' : ''; ?>>%</option>
                                    <option value="fixed" <?php echo $tier['fee_type'] === 'fixed' ? 'selected' : ''; ?>>₱</option>
                                </select>
                                <input type="number" name="fee_value[]" step="0.01" value="<?php echo $tier['fee_value']; ?>" placeholder="Value" required>
                                <input type="number" name="max_fee_amount[]" step="0.01" value="<?php echo $tier['max_fee_amount']; ?>" placeholder="Max Amount">
                                <button type="button" onclick="removeTier(this)" class="btn btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button type="button" onclick="addTier()" class="btn btn-success" style="margin-bottom: 20px;">
                            <i class="fas fa-plus"></i> Add Tier
                        </button>
                        
                        <button type="submit" name="update_tiers" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Tiers
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Recent Late Fees -->
                <div class="section">
                    <h2><i class="fas fa-history"></i> Recent Late Fees</h2>
                    <div class="table-container">
                        <table class="fees-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Loan ID</th>
                                    <th>Due Date</th>
                                    <th>Days Late</th>
                                    <th>Fee Amount</th>
                                    <th>Status</th>
                                    <th>Applied Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentFees as $fee): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($fee['client_name']); ?></strong>
                                            <br><small style="color: #94a3b8;"><?php echo htmlspecialchars($fee['email']); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($fee['loan_id']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($fee['original_due_date'])); ?></td>
                                    <td><?php echo $fee['days_late']; ?></td>
                                    <td class="amount">₱<?php echo number_format($fee['fee_amount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $fee['status']; ?>">
                                            <?php echo ucfirst($fee['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $fee['applied_date'] ? date('M j, Y H:i', strtotime($fee['applied_date'])) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php if ($fee['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="fee_id" value="<?php echo $fee['id']; ?>">
                                            <input type="hidden" name="waiver_reason" value="Administrative waiver" required>
                                            <button type="submit" name="waive_fee" class="btn btn-warning" style="padding: 8px 12px; font-size: 0.85rem;">
                                                <i class="fas fa-times"></i> Waive
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function addTier() {
            const container = document.getElementById('tier-container');
            const newTier = document.createElement('div');
            newTier.className = 'tier-row';
            newTier.innerHTML = `
                <input type="number" name="days_from[]" placeholder="Days From" required>
                <input type="number" name="days_to[]" placeholder="Days To" required>
                <select name="tier_fee_type[]" required>
                    <option value="percentage">%</option>
                    <option value="fixed">₱</option>
                </select>
                <input type="number" name="fee_value[]" step="0.01" placeholder="Value" required>
                <input type="number" name="max_fee_amount[]" step="0.01" placeholder="Max Amount">
                <button type="button" onclick="removeTier(this)" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(newTier);
        }
        
        function removeTier(button) {
            button.parentElement.remove();
        }
        
        // Toggle tier section visibility
        document.querySelector('select[name="fee_type"]').addEventListener('change', function() {
            const tierSection = document.querySelector('.section:nth-of-type(3)');
            if (tierSection) {
                tierSection.style.display = this.value === 'tiered' ? 'block' : 'none';
            }
        });
    </script>
</body>
</html>
