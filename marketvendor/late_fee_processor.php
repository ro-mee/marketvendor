<?php
/**
 * Late Fee Processor
 * Automated cron job for assessing and processing late fees
 * Run this script daily via cron: php late_fee_processor.php
 */

require_once 'config/database.php';
require_once 'includes/late_fee_functions.php';
require_once 'includes/audit_helper.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $lateFeeManager = new LateFeeManager();
    
    echo "Late Fee Processor Started: " . date('Y-m-d H:i:s') . "\n";
    echo "================================================\n";
    
    // Assess new late fees
    echo "Step 1: Assessing new late fees...\n";
    $fees_assessed = $lateFeeManager->assessLateFees();
    echo "✓ Assessed {$fees_assessed} new late fees\n";
    
    // Get statistics
    $stats = $lateFeeManager->getLateFeeStatistics();
    echo "Step 2: Current statistics...\n";
    echo "  - Total Fees: {$stats['total_fees']}\n";
    echo "  - Collected: ₱" . number_format($stats['collected_fees'], 2) . "\n";
    echo "  - Pending: ₱" . number_format($stats['pending_fees'], 2) . "\n";
    echo "  - Waived: ₱" . number_format($stats['waived_fees'], 2) . "\n";
    echo "  - Average Days Late: " . round($stats['avg_days_late'], 1) . "\n";
    
    // Check for very overdue payments (30+ days)
    echo "\nStep 3: Checking for very overdue payments...\n";
    $stmt = $db->prepare("
        SELECT lf.*, u.name as client_name, u.email, l.loan_purpose
        FROM late_fees lf
        JOIN loans l ON lf.loan_id = l.loan_id
        JOIN users u ON l.user_id = u.id
        WHERE lf.days_late >= 30
        AND lf.status = 'pending'
        ORDER BY lf.days_late DESC
        LIMIT 10
    ");
    $stmt->execute();
    $very_overdue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($very_overdue)) {
        echo "⚠️  Found " . count($very_overdue) . " payments 30+ days overdue:\n";
        foreach ($very_overdue as $fee) {
            echo "  - {$fee['client_name']} ({$fee['loan_id']}): {$fee['days_late']} days late, ₱" . number_format($fee['fee_amount'], 2) . "\n";
        }
    } else {
        echo "✓ No payments 30+ days overdue\n";
    }
    
    // Send summary notification to admin
    echo "\nStep 4: Sending admin summary...\n";
    $admin_email = 'admin@marketvendor.com'; // Configure this
    $subject = "Late Fee Processor Summary - " . date('Y-m-d');
    $message = "Late Fee Processor Summary for " . date('F j, Y') . "\n\n";
    $message .= "Fees Assessed Today: {$fees_assessed}\n";
    $message .= "Total Pending Fees: ₱" . number_format($stats['pending_fees'], 2) . "\n";
    $message .= "Total Collected Fees: ₱" . number_format($stats['collected_fees'], 2) . "\n";
    $message .= "Very Overdue Payments: " . count($very_overdue) . "\n\n";
    $message .= "Processor completed successfully at: " . date('Y-m-d H:i:s') . "\n";
    
    // In production, uncomment the mail line
    // mail($admin_email, $subject, $message);
    echo "✓ Admin summary prepared\n";
    
    // Log the processing
    logAudit('late_fee_processor_run', "Processed {$fees_assessed} fees", null, 'System');
    
    echo "\n================================================\n";
    echo "Late Fee Processor Completed Successfully!\n";
    echo "Total runtime: " . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) . " seconds\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    // Log the error
    logAudit('late_fee_processor_error', $e->getMessage(), null, 'System');
    
    // Send error notification to admin
    $admin_email = 'admin@marketvendor.com';
    $subject = "❌ Late Fee Processor ERROR - " . date('Y-m-d');
    $message = "The Late Fee Processor encountered an error:\n\n";
    $message .= "Error: " . $e->getMessage() . "\n";
    $message .= "Time: " . date('Y-m-d H:i:s') . "\n";
    $message .= "Please check the system immediately.\n";
    
    // mail($admin_email, $subject, $message);
    
    exit(1);
}
?>
