<?php
/**
 * Audit Log Helper Function
 * Tracks all system activities for security and monitoring
 */

require_once 'config/database.php';

function logAudit($action, $details, $user_id = null, $user_name = null) {
    try {
        // Get current session info if not provided
        if ($user_id === null && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        }
        if ($user_name === null && isset($_SESSION['user_name'])) {
            $user_name = $_SESSION['user_name'];
        }
        
        // Default values if still null
        if ($user_id === null) $user_id = 0;
        if ($user_name === null) $user_name = 'System';
        
        // Initialize database connection
        $database = new Database();
        $db = $database->getConnection();
        
        // Create audit_log table if it doesn't exist
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
        
        // Insert audit log entry
        $sql = "INSERT INTO audit_log (user_id, user_name, action, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $user_id,
            $user_name,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
        
        return true;
    } catch (Exception $e) {
        // Log error but don't break the application
        error_log("Audit log error: " . $e->getMessage());
        return false;
    }
}

/**
 * Log user login
 */
function logLogin($user_id, $user_name, $success = true) {
    $details = $success ? 'User logged in successfully' : 'Failed login attempt';
    logAudit('login', $details, $user_id, $user_name);
}

/**
 * Log user logout
 */
function logLogout($user_id, $user_name) {
    logAudit('logout', 'User logged out', $user_id, $user_name);
}

/**
 * Log loan creation
 */
function logLoanCreated($loan_id, $borrower_name, $amount) {
    $details = "Loan #{$loan_id} created for {$borrower_name} - Amount: ₱" . number_format($amount, 2);
    logAudit('create', $details);
}

/**
 * Log loan status change
 */
function logLoanStatusChange($loan_id, $borrower_name, $old_status, $new_status) {
    $details = "Loan #{$loan_id} status changed from {$old_status} to {$new_status} for {$borrower_name}";
    logAudit('update', $details);
}

/**
 * Log payment processing
 */
function logPaymentProcessed($payment_id, $borrower_name, $amount, $status) {
    $details = "Payment #{$payment_id} processed for {$borrower_name} - Amount: ₱" . number_format($amount, 2) . " - Status: {$status}";
    logAudit('payment', $details);
}

/**
 * Log user registration
 */
function logUserRegistered($user_id, $user_name, $role) {
    $details = "New user registered: {$user_name} (ID: {$user_id}) - Role: {$role}";
    logAudit('create', $details);
}

/**
 * Log data export
 */
function logDataExport($export_type, $record_count) {
    $details = "Data exported: {$export_type} - {$record_count} records";
    logAudit('export', $details);
}

/**
 * Log system settings change
 */
function logSettingsChange($setting_name, $old_value, $new_value) {
    $details = "Setting changed: {$setting_name} from '{$old_value}' to '{$new_value}'";
    logAudit('update', $details);
}

/**
 * Log failed attempts
 */
function logFailedAttempt($action, $details) {
    logAudit('failed', "Failed {$action}: {$details}");
}
?>
