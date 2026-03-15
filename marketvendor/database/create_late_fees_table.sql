-- =====================================================
-- LATE PAYMENT FEES SYSTEM
-- =====================================================
-- Automated penalty calculation and tracking system

-- Late fees configuration table
CREATE TABLE IF NOT EXISTS late_fee_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fee_type ENUM('percentage', 'fixed', 'tiered') DEFAULT 'percentage',
    percentage_rate DECIMAL(5,2) DEFAULT 5.00,
    fixed_amount DECIMAL(10,2) DEFAULT 100.00,
    grace_period_days INT DEFAULT 0,
    max_fee_percentage DECIMAL(5,2) DEFAULT 25.00,
    compound_daily BOOLEAN DEFAULT FALSE,
    apply_weekends BOOLEAN DEFAULT TRUE,
    min_fee_amount DECIMAL(10,2) DEFAULT 50.00,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Late fees charged history (without foreign keys initially)
CREATE TABLE IF NOT EXISTS late_fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id VARCHAR(50) NOT NULL,
    payment_schedule_id INT NOT NULL,
    original_due_date DATE NOT NULL,
    days_late INT NOT NULL,
    fee_type ENUM('percentage', 'fixed', 'tiered') NOT NULL,
    fee_amount DECIMAL(10,2) NOT NULL,
    fee_percentage DECIMAL(5,2),
    calculation_details JSON,
    status ENUM('pending', 'applied', 'waived', 'paid') DEFAULT 'pending',
    applied_date TIMESTAMP NULL,
    waived_by INT NULL,
    waiver_reason TEXT,
    payment_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_loan_id (loan_id),
    INDEX idx_status (status),
    INDEX idx_due_date (original_due_date),
    INDEX idx_created_at (created_at)
);

-- Tiered late fee structure
CREATE TABLE IF NOT EXISTS late_fee_tiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    days_from INT NOT NULL,
    days_to INT NOT NULL,
    fee_type ENUM('percentage', 'fixed') NOT NULL,
    fee_value DECIMAL(10,2) NOT NULL,
    max_fee_amount DECIMAL(10,2),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_days_range (days_from, days_to),
    INDEX idx_active (is_active)
);

-- Late fee notifications log
CREATE TABLE IF NOT EXISTS late_fee_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id VARCHAR(50) NOT NULL,
    fee_id INT NOT NULL,
    notification_type ENUM('fee_assessed', 'reminder', 'final_notice') NOT NULL,
    notification_method ENUM('email', 'sms', 'system') NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    message TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('sent', 'failed', 'pending') DEFAULT 'sent',
    INDEX idx_loan_id (loan_id),
    INDEX idx_fee_id (fee_id),
    INDEX idx_sent_at (sent_at)
);

-- Add foreign key constraints only if referenced tables exist
-- This prevents errors during table creation

-- Check and add foreign key for loans table if it exists
SET @table_exists = 0;
SELECT COUNT(*) INTO @table_exists 
FROM information_schema.tables 
WHERE table_schema = DATABASE() AND table_name = 'loans';

SET @sql = IF(@table_exists > 0, 
    'ALTER TABLE late_fees ADD CONSTRAINT fk_late_fees_loan_id FOREIGN KEY (loan_id) REFERENCES loans(loan_id)',
    'SELECT "Skipping foreign key for loans table - table does not exist" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add foreign key for payment_schedules table if it exists
SET @table_exists = 0;
SELECT COUNT(*) INTO @table_exists 
FROM information_schema.tables 
WHERE table_schema = DATABASE() AND table_name = 'payment_schedules';

SET @sql = IF(@table_exists > 0, 
    'ALTER TABLE late_fees ADD CONSTRAINT fk_late_fees_payment_schedule_id FOREIGN KEY (payment_schedule_id) REFERENCES payment_schedules(payment_id)',
    'SELECT "Skipping foreign key for payment_schedules table - table does not exist" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add foreign key for users table if it exists
SET @table_exists = 0;
SELECT COUNT(*) INTO @table_exists 
FROM information_schema.tables 
WHERE table_schema = DATABASE() AND table_name = 'users';

SET @sql = IF(@table_exists > 0, 
    'ALTER TABLE late_fees ADD CONSTRAINT fk_late_fees_waived_by FOREIGN KEY (waived_by) REFERENCES users(id)',
    'SELECT "Skipping foreign key for users table - table does not exist" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add foreign key for payment_history table if it exists
SET @table_exists = 0;
SELECT COUNT(*) INTO @table_exists 
FROM information_schema.tables 
WHERE table_schema = DATABASE() AND table_name = 'payment_history';

SET @sql = IF(@table_exists > 0, 
    'ALTER TABLE late_fees ADD CONSTRAINT fk_late_fees_payment_id FOREIGN KEY (payment_id) REFERENCES payment_history(payment_id)',
    'SELECT "Skipping foreign key for payment_history table - table does not exist" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Insert default late fee settings
INSERT INTO late_fee_settings (fee_type, percentage_rate, fixed_amount, grace_period_days, max_fee_percentage, compound_daily, description) 
VALUES ('percentage', 5.00, 100.00, 3, 25.00, FALSE, 'Default late fee: 5% of payment amount after 3-day grace period')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Insert default tiered structure
INSERT INTO late_fee_tiers (days_from, days_to, fee_type, fee_value, max_fee_amount) VALUES
(1, 7, 'percentage', 2.00, 500.00),   -- 2% for 1-7 days late, max ₱500
(8, 14, 'percentage', 3.00, 1000.00),  -- 3% for 8-14 days late, max ₱1000
(15, 30, 'percentage', 5.00, 2500.00), -- 5% for 15-30 days late, max ₱2500
(31, 60, 'percentage', 7.50, 5000.00), -- 7.5% for 31-60 days late, max ₱5000
(61, 999, 'percentage', 10.00, 10000.00) -- 10% for 61+ days late, max ₱10000
ON DUPLICATE KEY UPDATE fee_value = VALUES(fee_value), max_fee_amount = VALUES(max_fee_amount);
