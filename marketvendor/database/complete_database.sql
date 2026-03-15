-- =====================================================
-- LOAN MANAGEMENT SYSTEM - COMPLETE DATABASE STRUCTURE
-- =====================================================
-- This file contains all database tables, indexes, and sample data
-- for the complete loan management system
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS loan_management_system;
USE loan_management_system;

-- =====================================================
-- CORE USER MANAGEMENT TABLES
-- =====================================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'vendor') DEFAULT 'vendor',
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Password resets table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    reset_token VARCHAR(255) NOT NULL,
    token_expiry TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (reset_token),
    INDEX idx_expiry (token_expiry),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- LOAN MANAGEMENT TABLES
-- =====================================================

-- Loans table (Complete version with all fields)
CREATE TABLE IF NOT EXISTS loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    birthdate DATE NOT NULL,
    address TEXT NOT NULL,
    civil_status VARCHAR(20) NOT NULL,
    business_name VARCHAR(255) NOT NULL,
    business_type VARCHAR(50) NOT NULL,
    business_address TEXT NOT NULL,
    monthly_revenue DECIMAL(12,2) NOT NULL,
    business_description TEXT NOT NULL,
    payment_frequency VARCHAR(20) NOT NULL,
    custom_loan_amount DECIMAL(12,2) DEFAULT 0,
    loan_amount DECIMAL(12,2) NOT NULL,
    interest_rate DECIMAL(5,2) NOT NULL DEFAULT 2.5,
    term_months INT NOT NULL,
    loan_purpose VARCHAR(50) NOT NULL,
    preferred_term INT NOT NULL,
    collateral VARCHAR(50),
    status ENUM('pending', 'approved', 'active', 'completed', 'defaulted', 'rejected') DEFAULT 'pending',
    loan_start_date DATE,
    first_payment_date DATE,
    next_payment_date DATE,
    remaining_balance DECIMAL(12,2),
    total_paid DECIMAL(12,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_loan_id (loan_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_loan_start_date (loan_start_date),
    INDEX idx_next_payment_date (next_payment_date),
    INDEX idx_remaining_balance (remaining_balance)
);

-- =====================================================
-- PAYMENT MANAGEMENT TABLES
-- =====================================================

-- Payment schedules table
CREATE TABLE IF NOT EXISTS payment_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id VARCHAR(50) NOT NULL UNIQUE,
    loan_id VARCHAR(50) NOT NULL,
    user_id INT NOT NULL,
    borrower_name VARCHAR(255) NOT NULL,
    due_date DATE NOT NULL,
    principal_amount DECIMAL(12,2) NOT NULL,
    interest_amount DECIMAL(12,2) NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    amount_paid DECIMAL(12,2) DEFAULT 0.00,
    payment_date TIMESTAMP NULL,
    status ENUM('pending', 'paid', 'overdue', 'scheduled') DEFAULT 'pending',
    days_overdue INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_loan_id (loan_id),
    INDEX idx_due_date (due_date),
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_payment_date (payment_date)
);

-- Payment history table (Enhanced with verification fields)
CREATE TABLE IF NOT EXISTS payment_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id VARCHAR(50) NOT NULL,
    loan_id VARCHAR(50) NOT NULL,
    user_id INT NOT NULL,
    borrower_name VARCHAR(255) NOT NULL,
    payment_date DATE NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    principal_paid DECIMAL(12,2) NOT NULL,
    interest_paid DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'cash',
    transaction_id VARCHAR(100),
    receipt_number VARCHAR(50),
    status ENUM('completed', 'partial', 'failed') DEFAULT 'completed',
    notes TEXT,
    -- Enhanced verification fields
    verification_status ENUM('pending_verification', 'verified', 'rejected') DEFAULT 'pending_verification',
    payment_type ENUM('on_time', 'early', 'late') NULL,
    reference_number VARCHAR(100) NULL,
    payment_notes TEXT NULL,
    screenshot_path VARCHAR(500) NULL,
    verified_at TIMESTAMP NULL,
    verified_by VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_payment_id (payment_id),
    INDEX idx_loan_id (loan_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_verification_status (verification_status)
);

-- =====================================================
-- DOCUMENT MANAGEMENT TABLES
-- =====================================================

-- Loan documents table (Enhanced version)
CREATE TABLE IF NOT EXISTS loan_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id VARCHAR(50) NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255),
    file_size INT,
    mime_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES loans(loan_id) ON DELETE CASCADE,
    INDEX idx_loan_id (loan_id),
    INDEX idx_document_type (document_type)
);

-- =====================================================
-- LATE FEES MANAGEMENT TABLES
-- =====================================================

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

-- Late fees charged history
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

-- =====================================================
-- SYSTEM MANAGEMENT TABLES
-- =====================================================

-- Audit log table for tracking system activities
CREATE TABLE IF NOT EXISTS audit_log (
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
);

-- System settings table for configurable parameters
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    setting_type VARCHAR(50) DEFAULT 'string',
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
);

-- Admin notifications table for system alerts
CREATE TABLE IF NOT EXISTS admin_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
    status ENUM('read', 'unread') DEFAULT 'unread',
    user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Payment verification queue table
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

-- =====================================================
-- FOREIGN KEY CONSTRAINTS FOR LATE FEES TABLES
-- =====================================================

-- Add foreign key constraints for late fees tables
ALTER TABLE late_fees 
ADD CONSTRAINT fk_late_fees_loan_id FOREIGN KEY (loan_id) REFERENCES loans(loan_id),
ADD CONSTRAINT fk_late_fees_payment_schedule_id FOREIGN KEY (payment_schedule_id) REFERENCES payment_schedules(id),
ADD CONSTRAINT fk_late_fees_waived_by FOREIGN KEY (waived_by) REFERENCES users(id),
ADD CONSTRAINT fk_late_fees_payment_id FOREIGN KEY (payment_id) REFERENCES payment_history(id);

-- Add foreign key constraints for late fee notifications
ALTER TABLE late_fee_notifications 
ADD CONSTRAINT fk_late_fee_notifications_loan_id FOREIGN KEY (loan_id) REFERENCES loans(loan_id),
ADD CONSTRAINT fk_late_fee_notifications_fee_id FOREIGN KEY (fee_id) REFERENCES late_fees(id);

-- Add foreign key constraint for admin notifications
ALTER TABLE admin_notifications 
ADD CONSTRAINT fk_admin_notifications_user_id FOREIGN KEY (user_id) REFERENCES users(id);

-- =====================================================
-- TRIGGERS AND STORED PROCEDURES
-- =====================================================

-- Trigger to update verification queue when payment is updated
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

-- =====================================================
-- SAMPLE DATA INSERTION
-- =====================================================

-- Insert default admin user (password: Admin@123)
INSERT INTO users (name, email, password, role) VALUES 
('System Administrator', 'admin@blueledger.com', '$2y$10$drj2LLIKTyPIbhoQXYsVlOjSjNhC36YWhrKNqnJ1SBR2KWeopbelO', 'admin')
ON DUPLICATE KEY UPDATE email = email;

-- Insert sample vendor users
INSERT INTO users (name, email, password, role, phone) VALUES 
('Coastal Enterprises', 'coastal@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '09123456789'),
('Summit Retail Corp', 'summit@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '09123456788'),
('Global Trading Co.', 'global@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '09123456787'),
('Northline Foods', 'northline@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '09123456786'),
('Blue Harbor Trading', 'blueharbor@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '09123456785'),
('Metro Farm Supply', 'metrofarm@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '09123456784')
ON DUPLICATE KEY UPDATE email = email;

-- Insert sample loans with complete data
INSERT INTO loans (loan_id, user_id, full_name, email, phone, birthdate, address, civil_status, business_name, business_type, business_address, monthly_revenue, business_description, payment_frequency, custom_loan_amount, loan_amount, interest_rate, term_months, loan_purpose, preferred_term, collateral, status) VALUES 
('LOAN-2026-001', 2, 'Maria Santos', 'maria.santos@email.com', '0917-234-5678', '1985-03-15', '123 Market St, Manila, Philippines', 'married', 'Coastal Enterprises', 'retail', '456 Commercial Ave, Manila', 45000.00, 'Retail store selling fresh seafood and local products', 'monthly', 350000.00, 350000.00, 2.5, 12, 'inventory', 12, 'property', 'active'),
('LOAN-2026-002', 3, 'Juan Cruz', 'juan.cruz@email.com', '0928-345-6789', '1988-07-22', '789 Business Rd, Quezon City, Philippines', 'single', 'Summit Retail Corp', 'retail', '321 Shopping Center, Quezon City', 38000.00, 'Supermarket chain with multiple locations', 'weekly', 280000.00, 280000.00, 2.8, 10, 'expansion', 10, 'equipment', 'active'),
('LOAN-2026-003', 4, 'Ana Reyes', 'ana.reyes@email.com', '0939-456-7890', '1990-11-08', '456 Trade Blvd, Makati, Philippines', 'married', 'Global Trading Co.', 'manufacturing', '987 Industrial Zone, Makati', 52000.00, 'Manufacturing and distribution of consumer goods', 'daily', 420000.00, 420000.00, 2.3, 15, 'working-capital', 15, 'inventory', 'active'),
('LOAN-2026-004', 5, 'Carlos Martinez', 'carlos.martinez@email.com', '0950-567-8901', '1992-02-14', '234 Commerce St, Pasig, Philippines', 'single', 'Northline Foods', 'food', '654 Food Park, Pasig', 28000.00, 'Food processing and restaurant business', 'monthly', 150000.00, 150000.00, 2.5, 8, 'equipment', 8, 'none', 'pending'),
('LOAN-2026-005', 6, 'Liza Fernandez', 'liza.fernandez@email.com', '0961-678-9012', '1987-09-30', '890 Enterprise Ave, Cebu, Philippines', 'widowed', 'Blue Harbor Trading', 'services', '123 Port Area, Cebu', 35000.00, 'Import-export and logistics services', 'weekly', 200000.00, 200000.00, 2.5, 12, 'renovation', 12, 'vehicle', 'pending')
ON DUPLICATE KEY UPDATE loan_id = loan_id;

-- Insert sample payment schedules
INSERT INTO payment_schedules (payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, status, days_overdue) VALUES 
('PAY-2026-001-1', 'LOAN-2026-001', 2, 'Coastal Enterprises', '2026-02-28', 29166.67, 729.17, 29895.83, 'paid', 0),
('PAY-2026-001-2', 'LOAN-2026-001', 2, 'Coastal Enterprises', '2026-03-28', 29166.67, 729.17, 29895.83, 'paid', 0),
('PAY-2026-001-3', 'LOAN-2026-001', 2, 'Coastal Enterprises', '2026-04-28', 29166.67, 729.17, 29895.83, 'overdue', 3),
('PAY-2026-002-1', 'LOAN-2026-002', 3, 'Summit Retail Corp', '2026-02-25', 28000.00, 7840.00, 35840.00, 'overdue', 6),
('PAY-2026-002-2', 'LOAN-2026-002', 3, 'Summit Retail Corp', '2026-03-25', 28000.00, 7840.00, 35840.00, 'overdue', 3),
('PAY-2026-003-1', 'LOAN-2026-003', 4, 'Global Trading Co.', '2026-02-20', 28000.00, 6440.00, 34440.00, 'overdue', 11),
('PAY-2026-004-1', 'LOAN-2026-004', 5, 'Northline Foods', '2026-03-05', 18750.00, 5625.00, 24375.00, 'pending', 0),
('PAY-2026-005-1', 'LOAN-2026-005', 6, 'Blue Harbor Trading', '2026-03-06', 16666.67, 4500.00, 21166.67, 'pending', 0)
ON DUPLICATE KEY UPDATE payment_id = payment_id;

-- Insert sample payment history
INSERT INTO payment_history (payment_id, loan_id, user_id, borrower_name, payment_date, amount_paid, principal_paid, interest_paid, receipt_number, status) VALUES 
('PAY-2026-001-1', 'LOAN-2026-001', 2, 'Coastal Enterprises', '2026-02-28', 29895.83, 29166.67, 729.17, 'RCP-2026-02-001', 'completed'),
('PAY-2026-001-2', 'LOAN-2026-001', 2, 'Coastal Enterprises', '2026-03-28', 29895.83, 29166.67, 729.17, 'RCP-2026-03-001', 'completed')
ON DUPLICATE KEY UPDATE payment_id = payment_id;

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

-- Insert sample late fees
INSERT INTO late_fees (loan_id, payment_schedule_id, original_due_date, days_late, fee_type, fee_amount, fee_percentage, calculation_details, status) VALUES 
('LOAN-2026-001', 3, '2026-04-28', 3, 'percentage', 1494.79, 5.00, '{"base_amount": 29895.83, "fee_rate": 0.05, "grace_period_used": 3}', 'applied'),
('LOAN-2026-002', 1, '2026-02-25', 6, 'percentage', 1792.00, 5.00, '{"base_amount": 35840.00, "fee_rate": 0.05, "grace_period_used": 3}', 'applied'),
('LOAN-2026-002', 2, '2026-03-25', 3, 'percentage', 1792.00, 5.00, '{"base_amount": 35840.00, "fee_rate": 0.05, "grace_period_used": 3}', 'applied'),
('LOAN-2026-003', 1, '2026-02-20', 11, 'percentage', 1722.00, 5.00, '{"base_amount": 34440.00, "fee_rate": 0.05, "grace_period_used": 3}', 'applied')
ON DUPLICATE KEY UPDATE loan_id = loan_id;

-- Insert sample admin notifications
INSERT INTO admin_notifications (title, message, type, status, user_id) VALUES 
('New Loan Application', 'Coastal Enterprises has submitted a new loan application for ₱350,000.00', 'info', 'read', 1),
('Payment Overdue', 'LOAN-2026-001 payment is 3 days overdue', 'warning', 'unread', 1),
('System Update', 'Late fee processing completed successfully', 'success', 'read', 1),
('High Risk Alert', 'LOAN-2026-003 is 11 days overdue - immediate attention required', 'error', 'unread', 1)
ON DUPLICATE KEY UPDATE title = title;

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('interest_rate_daily', '5.0', 'percentage', 'Interest rate for daily payments'),
('interest_rate_weekly', '4.5', 'percentage', 'Interest rate for weekly payments'),
('interest_rate_monthly', '3.5', 'percentage', 'Interest rate for monthly payments'),
('max_loan_amount', '500000', 'decimal', 'Maximum loan amount allowed'),
('min_loan_amount', '10000', 'decimal', 'Minimum loan amount allowed'),
('max_term_months', '60', 'integer', 'Maximum loan term in months'),
('late_fee_rate', '2.0', 'percentage', 'Late payment fee rate'),
('auto_approve_limit', '0', 'decimal', 'Auto-approval limit (0 = disabled)')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Update existing loans with new field values
UPDATE loans 
SET 
    loan_start_date = created_at,
    first_payment_date = DATE_ADD(created_at, INTERVAL 1 MONTH),
    next_payment_date = DATE_ADD(created_at, INTERVAL 1 MONTH),
    remaining_balance = loan_amount + (loan_amount * interest_rate / 100),
    total_paid = 0.00
WHERE 
    loan_start_date IS NULL;

-- =====================================================
-- DATABASE OPTIMIZATION AND PERFORMANCE
-- =====================================================

-- Create composite indexes for better performance
CREATE INDEX IF NOT EXISTS idx_loans_user_status ON loans(user_id, status);
CREATE INDEX IF NOT EXISTS idx_payment_schedules_loan_status ON payment_schedules(loan_id, status);
CREATE INDEX IF NOT EXISTS idx_payment_history_loan_date ON payment_history(loan_id, payment_date);
CREATE INDEX IF NOT EXISTS idx_loan_documents_loan_type ON loan_documents(loan_id, document_type);

-- Additional performance indexes for late fees system
CREATE INDEX IF NOT EXISTS idx_late_fees_loan_status ON late_fees(loan_id, status);
CREATE INDEX IF NOT EXISTS idx_late_fee_notifications_loan_sent ON late_fee_notifications(loan_id, sent_at);
CREATE INDEX IF NOT EXISTS idx_admin_notifications_user_status ON admin_notifications(user_id, status);

-- Composite indexes for common queries
CREATE INDEX IF NOT EXISTS idx_loans_status_created ON loans(status, created_at);
CREATE INDEX IF NOT EXISTS idx_payment_schedules_status_due ON payment_schedules(status, due_date);
CREATE INDEX IF NOT EXISTS idx_payment_history_verification ON payment_history(verification_status, payment_date);

-- =====================================================
-- SUMMARY
-- =====================================================
-- 
-- This complete database structure includes:
-- 
-- 1. USER MANAGEMENT:
--    - users (admin and vendor accounts)
--    - password_resets (password reset functionality)
-- 
-- 2. LOAN MANAGEMENT:
--    - loans (complete loan information with all business details and tracking fields)
--    - loan_documents (document storage and management with metadata)
-- 
-- 3. PAYMENT MANAGEMENT:
--    - payment_schedules (scheduled payments tracking with payment tracking)
--    - payment_history (payment records with verification system)
--    - payment_verification_queue (automated payment verification)
-- 
-- 4. LATE FEES MANAGEMENT:
--    - late_fee_settings (configurable late fee parameters)
--    - late_fees (late fee charges history and tracking)
--    - late_fee_tiers (tiered late fee structure)
--    - late_fee_notifications (late fee notification system)
-- 
-- 5. SYSTEM MANAGEMENT:
--    - audit_log (system activity tracking)
--    - system_settings (configurable system parameters)
--    - admin_notifications (admin alert system)
-- 
-- 6. ENHANCED FEATURES:
--    - Complete payment verification system
--    - Advanced late fee management with tiered structure
--    - Document management with full metadata
--    - Comprehensive audit trail for compliance
--    - Flexible system settings configuration
--    - Admin notification system
--    - Optimized indexes for maximum performance
--    - Complete foreign key constraints for data integrity
-- 
-- Total Tables: 13
-- Total Sample Records: 30+
-- Complete with indexes, foreign keys, triggers, and sample data
-- 
-- UPDATES IN THIS VERSION:
-- + Added comprehensive late fees management system
-- + Enhanced loans table with payment tracking fields
-- + Added admin notifications system
-- + Updated payment schedules with payment tracking
-- + Added comprehensive foreign key constraints
-- + Enhanced performance indexes
-- + Updated sample data for all new features
-- 
-- =====================================================
