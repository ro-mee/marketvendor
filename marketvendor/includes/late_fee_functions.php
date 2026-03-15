<?php
/**
 * Late Payment Fees System
 * Automated penalty calculation and management
 */

require_once 'config/database.php';

class LateFeeManager {
    private $db;
    
    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
            
            if ($this->db === null) {
                throw new Exception("Database connection failed");
            }
        } catch (Exception $e) {
            // Log the error but don't break the application
            error_log("LateFeeManager Database Error: " . $e->getMessage());
            $this->db = null;
        }
    }
    
    /**
     * Check if database connection is available
     */
    private function isDbConnected() {
        return $this->db !== null;
    }
    
    /**
     * Get current late fee settings
     */
    public function getFeeSettings() {
        if (!$this->isDbConnected()) {
            // Return default settings if database is not available
            return $this->getDefaultSettings();
        }
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM late_fee_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Return result if found, otherwise return default settings
            if ($result) {
                return $result;
            }
        } catch (PDOException $e) {
            error_log("Error getting fee settings: " . $e->getMessage());
        }
        
        return $this->getDefaultSettings();
    }
    
    /**
     * Get default settings
     */
    private function getDefaultSettings() {
        return [
            'id' => 1,
            'fee_type' => 'percentage',
            'percentage_rate' => 5.00,
            'fixed_amount' => 100.00,
            'grace_period_days' => 3,
            'max_fee_percentage' => 25.00,
            'compound_daily' => false,
            'apply_weekends' => true,
            'min_fee_amount' => 50.00,
            'description' => 'Default late fee: 5% of payment amount after 3-day grace period'
        ];
    }
    
    /**
     * Calculate compound interest
     */
    private function calculateCompoundInterest($principal, $daily_rate, $days) {
        return $principal * pow(1 + $daily_rate, $days) - $principal;
    }
    
    /**
     * Calculate weekdays only (exclude weekends)
     */
    private function calculateWeekdaysOnly($total_days) {
        $weekdays = 0;
        $current_day = 0;
        
        while ($current_day < $total_days) {
            $day_of_week = date('N', strtotime("+$current_day days"));
            if ($day_of_week < 6) { // Monday (1) to Friday (5)
                $weekdays++;
            }
            $current_day++;
        }
        
        return $weekdays;
    }
    
    /**
     * Get tiered fee structure
     */
    public function getTiers() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM late_fee_tiers WHERE is_active = 1 ORDER BY days_from ASC");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Return result if found, otherwise return default tiers
            if (!empty($result)) {
                return $result;
            }
        } catch (PDOException $e) {
            // Table doesn't exist or other database error
        }
        
        // Return default tier structure
        return [
            ['days_from' => 1, 'days_to' => 7, 'fee_type' => 'percentage', 'fee_value' => 2.00, 'max_fee_amount' => 500.00],
            ['days_from' => 8, 'days_to' => 14, 'fee_type' => 'percentage', 'fee_value' => 3.00, 'max_fee_amount' => 1000.00],
            ['days_from' => 15, 'days_to' => 30, 'fee_type' => 'percentage', 'fee_value' => 5.00, 'max_fee_amount' => 2500.00],
            ['days_from' => 31, 'days_to' => 60, 'fee_type' => 'percentage', 'fee_value' => 7.50, 'max_fee_amount' => 5000.00],
            ['days_from' => 61, 'days_to' => 999, 'fee_type' => 'percentage', 'fee_value' => 10.00, 'max_fee_amount' => 10000.00]
        ];
    }
    
    /**
     * Calculate late fee for a payment
     */
    public function calculateLateFee($payment_amount, $days_late, $payment_schedule_id = null) {
        $settings = $this->getFeeSettings();
        
        if (!$settings || $days_late <= $settings['grace_period_days']) {
            return 0;
        }
        
        $effective_days_late = $days_late - $settings['grace_period_days'];
        
        // Apply weekends logic
        if (!$settings['apply_weekends']) {
            $effective_days_late = $this->calculateWeekdaysOnly($effective_days_late);
        }
        
        $fee = 0;
        $calculation_details = [];
        
        switch ($settings['fee_type']) {
            case 'percentage':
                if ($settings['compound_daily']) {
                    // Compound interest calculation
                    $fee = $this->calculateCompoundInterest(
                        $payment_amount, 
                        $settings['percentage_rate'] / 100, 
                        $effective_days_late
                    );
                    $calculation_details['method'] = 'percentage_compounded';
                    $calculation_details['daily_rate'] = $settings['percentage_rate'];
                    $calculation_details['principal'] = $payment_amount;
                    $calculation_details['days_charged'] = $effective_days_late;
                    $calculation_details['compounded'] = true;
                } else {
                    // Simple interest - accumulated daily
                    $daily_fee = $payment_amount * ($settings['percentage_rate'] / 100);
                    $fee = $daily_fee * $effective_days_late;
                    $calculation_details['method'] = 'percentage_simple';
                    $calculation_details['daily_rate'] = $settings['percentage_rate'];
                    $calculation_details['daily_fee'] = $daily_fee;
                    $calculation_details['base_amount'] = $payment_amount;
                    $calculation_details['days_charged'] = $effective_days_late;
                    $calculation_details['compounded'] = false;
                }
                break;
                
            case 'fixed':
                // Fixed amount PER DAY, accumulated
                $daily_fee = $settings['fixed_amount'];
                $fee = $daily_fee * $effective_days_late;
                $calculation_details['method'] = 'fixed_accumulated';
                $calculation_details['daily_amount'] = $settings['fixed_amount'];
                $calculation_details['days_charged'] = $effective_days_late;
                break;
                
            case 'tiered':
                // For tiered, find applicable tier and calculate accumulated
                $tiers = $this->getTiers();
                $applicable_tier = null;
                
                foreach ($tiers as $tier) {
                    if ($effective_days_late >= $tier['days_from'] && $effective_days_late <= $tier['days_to']) {
                        $applicable_tier = $tier;
                        break;
                    }
                }
                
                if ($applicable_tier) {
                    $daily_fee = $payment_amount * ($applicable_tier['fee_value'] / 100);
                    $fee = $daily_fee * $effective_days_late;
                    if ($applicable_tier['max_fee_amount'] && $fee > $applicable_tier['max_fee_amount']) {
                        $fee = $applicable_tier['max_fee_amount'];
                    }
                    $calculation_details['method'] = 'tiered_accumulated';
                    $calculation_details['tier'] = $applicable_tier;
                    $calculation_details['daily_fee'] = $daily_fee;
                    $calculation_details['days_charged'] = $effective_days_late;
                } else {
                    return 0;
                }
                break;
                
            default:
                return 0;
        }
        
        // Apply minimum and maximum limits
        if ($fee < $settings['min_fee_amount']) {
            $fee = $settings['min_fee_amount'];
        }
        
        $max_fee = $payment_amount * ($settings['max_fee_percentage'] / 100);
        if ($fee > $max_fee) {
            $fee = $max_fee;
        }
        
        // Final hard cap to prevent unreasonable fees (never more than 10x payment amount)
        $hard_cap = $payment_amount * 10;
        if ($fee > $hard_cap) {
            $fee = $hard_cap;
            $calculation_details['hard_capped'] = true;
        }
        
        // Note: Compound daily disabled for accumulated fees to prevent double charging
        $calculation_details['final_fee'] = $fee;
        $calculation_details['days_late'] = $days_late;
        $calculation_details['grace_period'] = $settings['grace_period_days'];
        $calculation_details['note'] = 'Accumulated fee for ' . $effective_days_late . ' days';
        
        return [
            'fee_amount' => round($fee, 2),
            'calculation_details' => $calculation_details
        ];
    }
    
    /**
     * Calculate tiered fee
     */
    private function calculateTieredFee($payment_amount, $days_late) {
        $tiers = $this->getTiers();
        
        foreach ($tiers as $tier) {
            if ($days_late >= $tier['days_from'] && $days_late <= $tier['days_to']) {
                $fee = $tier['fee_type'] === 'percentage' 
                    ? $payment_amount * ($tier['fee_value'] / 100)
                    : $tier['fee_value'];
                
                if ($tier['max_fee_amount'] && $fee > $tier['max_fee_amount']) {
                    $fee = $tier['max_fee_amount'];
                }
                
                return $fee;
            }
        }
        
        return 0;
    }
    
    /**
     * Get pending late fees for a specific loan
     */
    public function getPendingFeesByLoan($loan_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT SUM(fee_amount) as total_pending
                FROM late_fees 
                WHERE loan_id = ? AND status = 'pending'
            ");
            $stmt->execute([$loan_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Return array with fee_amount for compatibility
            return $result ? [['fee_amount' => $result['total_pending']]] : [];
            
        } catch (PDOException $e) {
            // Table doesn't exist or other database error
            return [];
        }
    }
    
    /**
     * Assess and create late fees for overdue payments
     */
    public function assessLateFees() {
        $stmt = $this->db->prepare("
            SELECT ps.*, l.loan_amount, l.user_id, u.name as client_name, u.email
            FROM payment_schedules ps
            JOIN loans l ON ps.loan_id = l.loan_id
            JOIN users u ON l.user_id = u.id
            WHERE ps.status = 'pending' 
            AND ps.due_date < CURDATE()
            AND ps.payment_id NOT IN (
                SELECT DISTINCT payment_schedule_id FROM late_fees WHERE status != 'waived'
            )
            ORDER BY ps.due_date ASC
        ");
        $stmt->execute();
        $overdue_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $fees_assessed = 0;
        
        foreach ($overdue_payments as $payment) {
            $days_late = $this->calculateDaysLate($payment['due_date']);
            
            if ($days_late > 0) {
                $fee_calculation = $this->calculateLateFee($payment['total_amount'], $days_late, $payment['payment_id']);
                
                if ($fee_calculation['fee_amount'] > 0) {
                    $this->createLateFee($payment, $days_late, $fee_calculation);
                    $fees_assessed++;
                    
                    // Send notification
                    $this->sendLateFeeNotification($payment, $fee_calculation);
                }
            }
        }
        
        return $fees_assessed;
    }
    
    /**
     * Create a late fee record
     */
    private function createLateFee($payment, $days_late, $fee_calculation) {
        $settings = $this->getFeeSettings();
        
        $stmt = $this->db->prepare("
            INSERT INTO late_fees (
                loan_id, payment_schedule_id, original_due_date, days_late,
                fee_type, fee_amount, fee_percentage, calculation_details, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $payment['loan_id'],
            $payment['payment_id'],
            $payment['due_date'],
            $days_late,
            $settings['fee_type'],
            $fee_calculation['fee_amount'],
            $settings['percentage_rate'] ?? null,
            json_encode($fee_calculation['calculation_details'])
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Calculate days late
     */
    private function calculateDaysLate($due_date) {
        $due = new DateTime($due_date);
        $today = new DateTime();
        
        // Don't count weekends if configured
        $settings = $this->getFeeSettings();
        if (!$settings['apply_weekends']) {
            $period = new DatePeriod($due, new DateInterval('P1D'), $today);
            $business_days = 0;
            
            foreach ($period as $day) {
                if ($day->format('N') < 6) { // Monday = 1, Friday = 5
                    $business_days++;
                }
            }
            return $business_days;
        }
        
        return $today->diff($due)->days;
    }
    
    /**
     * Send late fee notification
     */
    private function sendLateFeeNotification($payment, $fee_calculation) {
        $subject = "Late Fee Assessed - Loan {$payment['loan_id']}";
        $message = "Dear {$payment['client_name']},\n\n";
        $message .= "A late fee of ₱" . number_format($fee_calculation['fee_amount'], 2) . " has been assessed on your loan payment.\n\n";
        $message .= "Loan ID: {$payment['loan_id']}\n";
        $message .= "Original Due Date: " . date('M j, Y', strtotime($payment['due_date'])) . "\n";
        $message .= "Days Late: " . $this->calculateDaysLate($payment['due_date']) . "\n";
        $message .= "Late Fee Amount: ₱" . number_format($fee_calculation['fee_amount'], 2) . "\n\n";
        $message .= "Please make your payment as soon as possible to avoid additional fees.\n\n";
        $message .= "Thank you,\nMarket Vendor Loan System";
        
        // Log notification
        $stmt = $this->db->prepare("
            INSERT INTO late_fee_notifications (loan_id, fee_id, notification_type, notification_method, recipient, message)
            SELECT ?, MAX(id), 'fee_assessed', 'email', ?, ?
            FROM late_fees 
            WHERE loan_id = ? AND payment_schedule_id = ?
        ");
        
        $stmt->execute([
            $payment['loan_id'],
            $payment['email'],
            $message,
            $payment['loan_id'],
            $payment['payment_id']
        ]);
        
        // In a real implementation, you would send the actual email here
        // mail($payment['email'], $subject, $message);
    }
    
    /**
     * Get late fees for a loan
     */
    public function getLateFeesByLoan($loan_id) {
        $stmt = $this->db->prepare("
            SELECT lf.*, ps.due_date, ps.total_amount as payment_amount
            FROM late_fees lf
            JOIN payment_schedules ps ON lf.payment_schedule_id = ps.payment_id
            WHERE lf.loan_id = ?
            ORDER BY lf.created_at DESC
        ");
        $stmt->execute([$loan_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Apply late fee to payment
     */
    public function applyLateFee($fee_id, $payment_id = null) {
        $stmt = $this->db->prepare("
            UPDATE late_fees 
            SET status = 'applied', applied_date = CURRENT_TIMESTAMP, payment_id = ?
            WHERE id = ?
        ");
        return $stmt->execute([$payment_id, $fee_id]);
    }
    
    /**
     * Waive late fee
     */
    public function waiveLateFee($fee_id, $waived_by, $reason) {
        $stmt = $this->db->prepare("
            UPDATE late_fees 
            SET status = 'waived', waived_by = ?, waiver_reason = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        return $stmt->execute([$waived_by, $reason, $fee_id]);
    }
    
    /**
     * Get late fee statistics
     */
    public function getLateFeeStatistics($date_from = null, $date_to = null) {
        $date_from = $date_from ?? date('Y-m-01');
        $date_to = $date_to ?? date('Y-m-d');
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_fees,
                SUM(CASE WHEN status = 'applied' THEN fee_amount ELSE 0 END) as collected_fees,
                SUM(CASE WHEN status = 'waived' THEN fee_amount ELSE 0 END) as waived_fees,
                SUM(CASE WHEN status = 'pending' THEN fee_amount ELSE 0 END) as pending_fees,
                AVG(days_late) as avg_days_late,
                MAX(days_late) as max_days_late
            FROM late_fees 
            WHERE DATE(created_at) BETWEEN ? AND ?
        ");
        
        $stmt->execute([$date_from, $date_to]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update fee settings
     */
    public function updateFeeSettings($settings) {
        if (!$this->isDbConnected()) {
            // Cannot update without database connection
            error_log("Cannot update fee settings: Database not connected");
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE late_fee_settings 
                SET fee_type = ?, percentage_rate = ?, fixed_amount = ?, grace_period_days = ?,
                    max_fee_percentage = ?, compound_daily = ?, apply_weekends = ?, 
                    min_fee_amount = ?, description = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $settings['fee_type'],
                $settings['percentage_rate'],
                $settings['fixed_amount'],
                $settings['grace_period_days'],
                $settings['max_fee_percentage'],
                $settings['compound_daily'],
                $settings['apply_weekends'],
                $settings['min_fee_amount'],
                $settings['description'],
                $settings['id']
            ]);
        } catch (PDOException $e) {
            error_log("Error updating fee settings: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update tier structure
     */
    public function updateTierStructure($tiers) {
        $this->db->beginTransaction();
        
        try {
            // Deactivate existing tiers
            $this->db->prepare("UPDATE late_fee_tiers SET is_active = 0")->execute();
            
            // Insert new tiers
            $stmt = $this->db->prepare("
                INSERT INTO late_fee_tiers (days_from, days_to, fee_type, fee_value, max_fee_amount)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($tiers as $tier) {
                $stmt->execute([
                    $tier['days_from'],
                    $tier['days_to'],
                    $tier['fee_type'],
                    $tier['fee_value'],
                    $tier['max_fee_amount'] ?? null
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
?>
