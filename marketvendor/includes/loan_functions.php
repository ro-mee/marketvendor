<?php
/**
 * Loan Management Functions
 * Core business logic for market vendor loan system
 */

require_once 'config/database.php';

class LoanManager {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Generate unique loan ID
     */
    public function generateLoanId() {
        do {
            $loan_id = 'L' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM loans WHERE loan_id = ?");
            $stmt->execute([$loan_id]);
            $count = $stmt->fetch()['count'];
        } while ($count > 0);
        
        return $loan_id;
    }
    
    /**
     * Approve loan and generate payment schedule
     */
    public function approveLoan($loan_id) {
        try {
            $this->db->beginTransaction();
            
            // Get loan details
            $stmt = $this->db->prepare("SELECT * FROM loans WHERE loan_id = ? AND status = 'pending'");
            $stmt->execute([$loan_id]);
            $loan = $stmt->fetch();
            
            if (!$loan) {
                throw new Exception("Loan not found or already processed");
            }
            
            // Calculate interest and total amount using configurable rates
            $payment_frequency = $loan['payment_frequency'] ?? 'monthly';
            
            // Get interest rate from system settings based on payment frequency
            $interest_rate = $this->getInterestRate($payment_frequency);
            
            $term_months = $loan['preferred_term'] ?? 12;
            $total_interest = $loan['loan_amount'] * ($interest_rate / 100) * ($term_months / 12);
            $total_amount = $loan['loan_amount'] + $total_interest;
            
            // Set loan dates
            $loan_start_date = date('Y-m-d');
            $first_payment_date = $this->calculateFirstPaymentDate($loan['payment_frequency']);
            
            // Update loan status
            $stmt = $this->db->prepare("UPDATE loans SET 
                status = 'active', 
                loan_start_date = ?, 
                first_payment_date = ?,
                next_payment_date = ?,
                interest_rate = ?,
                term_months = ?,
                remaining_balance = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE loan_id = ?");
            
            $stmt->execute([
                $loan_start_date, 
                $first_payment_date, 
                $first_payment_date,
                $interest_rate,
                $term_months,
                $total_amount,
                $loan_id
            ]);
            
            // Generate payment schedule
            $this->generatePaymentSchedule($loan_id, $loan, $total_amount, $first_payment_date);
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Loan approved successfully'];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Calculate first payment date based on frequency
     */
    private function calculateFirstPaymentDate($frequency) {
        $today = date('Y-m-d');
        
        switch ($frequency) {
            case 'daily':
                return date('Y-m-d', strtotime($today . '+1 day'));
            case 'weekly':
                return date('Y-m-d', strtotime($today . '+7 days'));
            case 'monthly':
                return date('Y-m-d', strtotime($today . '+1 month'));
            default:
                return date('Y-m-d', strtotime($today . '+1 month'));
        }
    }
    
    /**
     * Generate payment schedule for approved loan
     */
    private function generatePaymentSchedule($loan_id, $loan, $total_amount, $first_payment_date) {
        $payment_frequency = $loan['payment_frequency'];
        $term_months = $loan['preferred_term'] ?? 12;
        
        // Validate inputs to prevent division by zero
        if (empty($term_months) || $term_months <= 0) {
            $term_months = 12; // Default to 12 months if invalid
        }
        
        if (empty($total_amount) || $total_amount <= 0) {
            throw new Exception("Invalid total amount for payment schedule generation");
        }
        
        // Calculate payment count and amount
        $payment_count = $this->getPaymentCount($payment_frequency, $term_months);
        
        // Additional validation to prevent division by zero
        if ($payment_count <= 0) {
            throw new Exception("Invalid payment count calculated: $payment_count");
        }
        
        $payment_amount = $total_amount / $payment_count;
        
        // Generate payment schedules
        for ($i = 0; $i < $payment_count; $i++) {
            $due_date = $this->calculateNextPaymentDate($first_payment_date, $i, $payment_frequency);
            
            $stmt = $this->db->prepare("INSERT INTO payment_schedules 
                (payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            
            $payment_id = 'PAY' . date('YmdHis') . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            $principal_amount = $loan['loan_amount'] / $payment_count;
            $interest_amount = ($total_amount - $loan['loan_amount']) / $payment_count;
            
            $stmt->execute([
                $payment_id,
                $loan_id,
                $loan['user_id'],
                $loan['full_name'],
                $due_date,
                $principal_amount,
                $interest_amount,
                $payment_amount
            ]);
        }
    }
    
    /**
     * Get payment count based on frequency and term
     */
    private function getPaymentCount($frequency, $term_months) {
        // Validate term_months to prevent zero results
        if (empty($term_months) || $term_months <= 0) {
            return 12; // Default to 12 payments
        }
        
        switch ($frequency) {
            case 'daily':
                return max(1, $term_months * 30); // Approximate, minimum 1
            case 'weekly':
                return max(1, $term_months * 4); // Approximate, minimum 1
            case 'monthly':
                return max(1, $term_months); // Minimum 1
            default:
                return max(1, $term_months); // Default to monthly, minimum 1
        }
    }
    
    /**
     * Calculate next payment date
     */
    private function calculateNextPaymentDate($first_date, $interval, $frequency) {
        switch ($frequency) {
            case 'daily':
                return date('Y-m-d', strtotime($first_date . '+' . $interval . ' days'));
            case 'weekly':
                return date('Y-m-d', strtotime($first_date . '+' . $interval . ' weeks'));
            case 'monthly':
                return date('Y-m-d', strtotime($first_date . '+' . $interval . ' months'));
            default:
                return date('Y-m-d', strtotime($first_date . '+' . $interval . ' months'));
        }
    }
    
    /**
     * Process payment
     */
    public function processPayment($loan_id, $payment_id, $amount, $payment_method = 'cash') {
        try {
            $this->db->beginTransaction();
            
            // Get payment schedule details
            $stmt = $this->db->prepare("SELECT * FROM payment_schedules 
                WHERE payment_id = ? AND loan_id = ? AND status = 'pending'");
            $stmt->execute([$payment_id, $loan_id]);
            $schedule = $stmt->fetch();
            
            if (!$schedule) {
                throw new Exception("Payment schedule not found or already paid");
            }
            
            // Update payment schedule
            $stmt = $this->db->prepare("UPDATE payment_schedules SET 
                status = 'paid', 
                days_overdue = 0,
                updated_at = CURRENT_TIMESTAMP
                WHERE payment_id = ?");
            
            $stmt->execute([$payment_id]);
            
            // Add to payment history
            $transaction_id = 'TXN' . date('YmdHis') . mt_rand(1000, 9999);
            $receipt_number = 'RCP' . date('YmdHis') . mt_rand(1000, 9999);
            
            $stmt = $this->db->prepare("INSERT INTO payment_history 
                (payment_id, loan_id, user_id, borrower_name, payment_date, amount_paid, principal_paid, interest_paid, payment_method, transaction_id, receipt_number) 
                VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $payment_id,
                $loan_id,
                $schedule['user_id'],
                $schedule['borrower_name'],
                $amount,
                $schedule['principal_amount'],
                $schedule['interest_amount'],
                $payment_method,
                $transaction_id,
                $receipt_number
            ]);
            
            // Update loan totals
            $stmt = $this->db->prepare("UPDATE loans SET 
                total_paid = total_paid + ?,
                remaining_balance = remaining_balance - ?,
                next_payment_date = (
                    SELECT MIN(due_date) FROM payment_schedules 
                    WHERE loan_id = ? AND status = 'pending'
                ),
                updated_at = CURRENT_TIMESTAMP
                WHERE loan_id = ?");
            
            $stmt->execute([$amount, $amount, $loan_id, $loan_id]);
            
            // Check if loan is fully paid
            $stmt = $this->db->prepare("SELECT remaining_balance FROM loans WHERE loan_id = ?");
            $stmt->execute([$loan_id]);
            $remaining_balance = $stmt->fetch()['remaining_balance'];
            
            if ($remaining_balance <= 0) {
                $stmt = $this->db->prepare("UPDATE loans SET status = 'completed' WHERE loan_id = ?");
                $stmt->execute([$loan_id]);
            }
            
            $this->db->commit();
            return [
                'success' => true, 
                'transaction_id' => $transaction_id,
                'receipt_number' => $receipt_number
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get loan details with schedules
     */
    public function getLoanDetails($loan_id, $user_id = null) {
        $sql = "SELECT l.*, u.email, u.phone FROM loans l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.loan_id = ?";
        
        $params = [$loan_id];
        
        if ($user_id) {
            $sql .= " AND l.user_id = ?";
            $params[] = $user_id;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $loan = $stmt->fetch();
        
        if ($loan) {
            // Get payment schedules
            $stmt = $this->db->prepare("SELECT * FROM payment_schedules 
                WHERE loan_id = ? ORDER BY due_date ASC");
            $stmt->execute([$loan_id]);
            $loan['schedules'] = $stmt->fetchAll();
            
            // Get payment history
            $stmt = $this->db->prepare("SELECT * FROM payment_history 
                WHERE loan_id = ? ORDER BY payment_date DESC");
            $stmt->execute([$loan_id]);
            $loan['payment_history'] = $stmt->fetchAll();
        }
        
        return $loan;
    }
    
    /**
     * Get user loans
     */
    public function getUserLoans($user_id, $status = null) {
        $sql = "SELECT * FROM loans WHERE user_id = ?";
        $params = [$user_id];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all loans for admin
     */
    public function getAllLoans($filters = []) {
        $sql = "SELECT l.*, u.email, u.phone FROM loans l 
                LEFT JOIN users u ON l.user_id = u.id";
        $params = [];
        $where_conditions = [];
        
        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $where_conditions[] = "(l.loan_id LIKE ? OR l.full_name LIKE ? OR u.email LIKE ?)";
            $params = array_merge($params, [$search, $search, $search]);
        }
        
        if (!empty($filters['status'])) {
            $where_conditions[] = "l.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $where_conditions[] = "l.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = "l.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(" AND ", $where_conditions);
        }
        
        $sql .= " ORDER BY l.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get overdue payments
     */
    public function getOverduePayments() {
        $sql = "SELECT ps.*, l.full_name, l.email, l.phone 
                FROM payment_schedules ps
                JOIN loans l ON ps.loan_id = l.loan_id
                WHERE ps.due_date < CURDATE() 
                AND ps.status = 'pending'
                ORDER BY ps.due_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Update overdue payments
     */
    public function updateOverduePayments() {
        // Update overdue payments
        $sql = "UPDATE payment_schedules 
                SET status = 'overdue',
                    days_overdue = DATEDIFF(CURDATE(), due_date)
                WHERE due_date < CURDATE() 
                AND status = 'pending'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->rowCount();
    }
    
    /**
     * Get interest rate from system settings based on payment frequency
     */
    public function getInterestRate($payment_frequency) {
        try {
            $stmt = $this->db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
            $setting_key = "interest_rate_" . $payment_frequency;
            $stmt->execute([$setting_key]);
            $result = $stmt->fetch();
            
            if ($result) {
                return floatval($result['setting_value']);
            }
            
            // Default rates if not found in settings
            $default_rates = [
                'daily' => 5.0,
                'weekly' => 4.5,
                'monthly' => 3.5
            ];
            
            return $default_rates[$payment_frequency] ?? 5.0;
            
        } catch (Exception $e) {
            // Return default rate on error
            return 5.0;
        }
    }

    /**
     * Approve a loan application
     */
    public function getLoanStatistics() {
        $stats = [];
        
        // Total loans
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM loans");
        $stats['total_loans'] = $stmt->fetch()['total'];
        
        // By status
        $statuses = ['pending', 'active', 'completed', 'rejected', 'approved'];
        foreach ($statuses as $status) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM loans WHERE status = ?");
            $stmt->execute([$status]);
            $stats["{$status}_loans"] = $stmt->fetch()['count'];
        }
        
        // Total active amount
        $stmt = $this->db->query("SELECT SUM(loan_amount) as total FROM loans WHERE status = 'active'");
        $stats['total_active_amount'] = $stmt->fetch()['total'] ?: 0;
        
        // Total collected
        $stmt = $this->db->query("SELECT SUM(total_paid) as total FROM loans WHERE status IN ('active', 'completed')");
        $stats['total_collected'] = $stmt->fetch()['total'] ?: 0;
        
        // Overdue payments
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM payment_schedules 
            WHERE due_date < CURDATE() AND status = 'pending'");
        $stats['overdue_payments'] = $stmt->fetch()['count'];
        
        return $stats;
    }
}
?>
