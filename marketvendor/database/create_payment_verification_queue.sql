-- Payment Verification Queue Table
CREATE TABLE IF NOT EXISTS payment_verification_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id VARCHAR(50) NOT NULL UNIQUE,
    scheduled_verification_time TIMESTAMP NOT NULL,
    verification_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    verification_attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    last_attempt_at TIMESTAMP NULL,
    verification_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_payment_id (payment_id),
    INDEX idx_scheduled_time (scheduled_verification_time),
    INDEX idx_status (verification_status)
);

-- Add verification_status column to payment_history table if it doesn't exist
ALTER TABLE payment_history 
ADD COLUMN IF NOT EXISTS verification_status ENUM('pending_verification', 'verified', 'rejected') DEFAULT 'pending_verification',
ADD COLUMN IF NOT EXISTS payment_type ENUM('on_time', 'early', 'late') NULL,
ADD COLUMN IF NOT EXISTS reference_number VARCHAR(100) NULL,
ADD COLUMN IF NOT EXISTS payment_notes TEXT NULL,
ADD COLUMN IF NOT EXISTS screenshot_path VARCHAR(500) NULL,
ADD COLUMN IF NOT EXISTS verified_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS verified_by VARCHAR(50) NULL;

-- Create trigger to update verification queue when payment is updated
DELIMITER //
CREATE TRIGGER IF NOT EXISTS update_verification_queue 
AFTER UPDATE ON payment_history
FOR EACH ROW
BEGIN
    IF NEW.verification_status = 'verified' AND OLD.verification_status != 'verified' THEN
        UPDATE payment_verification_queue 
        SET verification_status = 'completed', 
            updated_at = CURRENT_TIMESTAMP 
        WHERE payment_id = NEW.payment_id;
    END IF;
END//
DELIMITER ;
