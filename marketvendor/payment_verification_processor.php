<?php
/**
 * Payment Verification Processor
 * This script should be run as a cron job every 15-30 minutes
 * It processes payments that have been submitted and are pending verification
 */

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Payment Verification Processor Started: " . date('Y-m-d H:i:s') . "\n";
    
    // Get payments that are due for verification
    $sql = "SELECT pvq.*, ph.* 
            FROM payment_verification_queue pvq
            JOIN payment_history ph ON pvq.payment_id = ph.payment_id
            WHERE pvq.verification_status = 'pending' 
            AND pvq.scheduled_verification_time <= NOW()
            AND pvq.verification_attempts < pvq.max_attempts
            ORDER BY pvq.scheduled_verification_time ASC
            LIMIT 50";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $pending_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($pending_payments) . " payments to verify\n";
    
    foreach ($pending_payments as $payment) {
        echo "Processing payment: " . $payment['payment_id'] . "\n";
        
        // Update attempt count
        $update_attempt_sql = "UPDATE payment_verification_queue 
                              SET verification_status = 'processing', 
                                  verification_attempts = verification_attempts + 1,
                                  last_attempt_at = CURRENT_TIMESTAMP
                              WHERE payment_id = ?";
        $update_attempt_stmt = $db->prepare($update_attempt_sql);
        $update_attempt_stmt->execute([$payment['payment_id']]);
        
        // Simulate verification process (in real implementation, this would check with payment providers)
        $verification_result = simulatePaymentVerification($payment);
        
        if ($verification_result['verified']) {
            // Payment verified - update status to paid
            $update_payment_sql = "UPDATE payment_history 
                                   SET status = 'paid',
                                       verification_status = 'verified',
                                       verified_at = CURRENT_TIMESTAMP,
                                       verified_by = 'system'
                                   WHERE payment_id = ?";
            $update_payment_stmt = $db->prepare($update_payment_sql);
            $update_payment_stmt->execute([$payment['payment_id']]);
            
            // Update verification queue
            $update_queue_sql = "UPDATE payment_verification_queue 
                                 SET verification_status = 'completed',
                                     verification_notes = ?
                                 WHERE payment_id = ?";
            $update_queue_stmt = $db->prepare($update_queue_sql);
            $update_queue_stmt->execute([$verification_result['notes'], $payment['payment_id']]);
            
            // Check if loan is fully paid
            checkLoanCompletion($db, $payment['loan_id']);
            
            echo "Payment " . $payment['payment_id'] . " verified successfully\n";
            
            // Send notification (in real implementation)
            sendPaymentNotification($payment, 'verified');
            
        } else {
            // Payment verification failed
            $update_payment_sql = "UPDATE payment_history 
                                   SET verification_status = 'rejected'
                                   WHERE payment_id = ?";
            $update_payment_stmt = $db->prepare($update_payment_sql);
            $update_payment_stmt->execute([$payment['payment_id']]);
            
            // Update verification queue
            $update_queue_sql = "UPDATE payment_verification_queue 
                                 SET verification_status = 'failed',
                                     verification_notes = ?
                                 WHERE payment_id = ?";
            $update_queue_stmt = $db->prepare($update_queue_sql);
            $update_queue_stmt->execute([$verification_result['notes'], $payment['payment_id']]);
            
            echo "Payment " . $payment['payment_id'] . " verification failed: " . $verification_result['notes'] . "\n";
            
            // Send notification (in real implementation)
            sendPaymentNotification($payment, 'rejected');
        }
        
        // Small delay to prevent overwhelming
        usleep(100000); // 0.1 second
    }
    
    echo "Payment Verification Processor Completed: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "Error in payment verification: " . $e->getMessage() . "\n";
    error_log("Payment Verification Error: " . $e->getMessage());
}

/**
 * Simulate payment verification (replace with actual payment provider integration)
 */
function simulatePaymentVerification($payment) {
    // Simulate different verification scenarios based on payment method
    $success_rate = 0.95; // 95% success rate for demo
    
    if (mt_rand(1, 100) <= ($success_rate * 100)) {
        return [
            'verified' => true,
            'notes' => 'Payment verified successfully via ' . $payment['payment_method']
        ];
    } else {
        $reasons = [
            'Reference number not found',
            'Payment amount mismatch',
            'Transaction expired',
            'Invalid payment method'
        ];
        
        return [
            'verified' => false,
            'notes' => $reasons[array_rand($reasons)]
        ];
    }
}

/**
 * Check if loan is fully paid and update status
 */
function checkLoanCompletion($db, $loan_id) {
    $sql = "SELECT 
                l.loan_amount,
                COALESCE(SUM(ph.amount_paid), 0) as total_paid,
                l.status
            FROM loans l
            LEFT JOIN payment_history ph ON l.loan_id = ph.loan_id AND ph.status = 'paid'
            WHERE l.loan_id = ?
            GROUP BY l.loan_id, l.loan_amount, l.status";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$loan_id]);
    $loan_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($loan_info && $loan_info['total_paid'] >= $loan_info['loan_amount'] && $loan_info['status'] != 'completed') {
        // Update loan status to completed
        $update_sql = "UPDATE loans SET status = 'completed', updated_at = CURRENT_TIMESTAMP WHERE loan_id = ?";
        $update_stmt = $db->prepare($update_sql);
        $update_stmt->execute([$loan_id]);
        
        echo "Loan " . $loan_id . " marked as completed\n";
    }
}

/**
 * Send payment notification (placeholder for actual notification system)
 */
function sendPaymentNotification($payment, $status) {
    // In real implementation, this would send email/SMS notifications
    $message = ($status === 'verified') 
        ? "Your payment of ₱" . number_format($payment['amount_paid'], 2) . " has been verified and reflected in your account."
        : "Your payment verification failed. Reason: " . $payment['verification_notes'];
    
    // Log notification (replace with actual sending mechanism)
    error_log("Payment notification for user {$payment['user_id']}: {$message}");
}

/**
 * Clean up old verification records
 */
function cleanupOldRecords($db) {
    // Delete verification records older than 30 days
    $sql = "DELETE FROM payment_verification_queue 
            WHERE verification_status = 'completed' 
            AND updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    echo "Cleaned up old verification records\n";
}

// Run cleanup
cleanupOldRecords($db);
?>
