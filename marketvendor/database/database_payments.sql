-- Payment Management Database Tables
USE loan_management_system;

-- Drop existing admin user and recreate with correct hash
DELETE FROM users WHERE email = 'admin@blueledger.com';

-- Insert admin user with working password (password)
INSERT INTO users (name, email, password, role) VALUES 
('System Administrator', 'admin@blueledger.com', '$2y$10$drj2LLIKTyPIbhoQXYsVlOjSjNhC36YWhrKNqnJ1SBR2KWeopbelO', 'admin');

-- Insert sample users for testing
INSERT INTO users (name, email, password, role) VALUES 
('Coastal Enterprises', 'coastal@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor'),
('Summit Retail Corp', 'summit@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor'),
('Global Trading Co.', 'global@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor'),
('Northline Foods', 'northline@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor'),
('Blue Harbor Trading', 'blueharbor@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor'),
('Metro Farm Supply', 'metrofarm@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor')
ON DUPLICATE KEY UPDATE email = email;

-- Loans table
CREATE TABLE IF NOT EXISTS loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    borrower_name VARCHAR(255) NOT NULL,
    loan_amount DECIMAL(12,2) NOT NULL,
    interest_rate DECIMAL(5,2) NOT NULL,
    term_months INT NOT NULL,
    status ENUM('pending', 'approved', 'active', 'completed', 'defaulted') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

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
    status ENUM('pending', 'paid', 'overdue', 'scheduled') DEFAULT 'pending',
    days_overdue INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_loan_id (loan_id),
    INDEX idx_due_date (due_date),
    INDEX idx_status (status)
);

-- Payment history table
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
    receipt_number VARCHAR(50),
    status ENUM('completed', 'partial', 'failed') DEFAULT 'completed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_payment_id (payment_id),
    INDEX idx_loan_id (loan_id),
    INDEX idx_payment_date (payment_date)
);

-- Insert sample data for testing (using correct user IDs)
INSERT INTO loans (loan_id, user_id, borrower_name, loan_amount, interest_rate, term_months, status) VALUES 
('LOAN-2026-048', 2, 'Coastal Enterprises', 350000.00, 2.5, 12, 'active'),
('LOAN-2026-049', 3, 'Summit Retail Corp', 280000.00, 2.8, 10, 'active'),
('LOAN-2026-050', 4, 'Global Trading Co.', 420000.00, 2.3, 15, 'active');

-- Insert sample payment schedules
INSERT INTO payment_schedules (payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, status, days_overdue) VALUES 
('PAY-2026-048', 'LOAN-2026-048', 2, 'Coastal Enterprises', '2026-02-28', 15000.00, 3000.00, 18000.00, 'overdue', 3),
('PAY-2026-049', 'LOAN-2026-049', 3, 'Summit Retail Corp', '2026-02-25', 18000.00, 4000.00, 22000.00, 'overdue', 6),
('PAY-2026-050', 'LOAN-2026-050', 4, 'Global Trading Co.', '2026-02-20', 28000.00, 7000.00, 35000.00, 'overdue', 11),
('PAY-2026-045', 'LOAN-2026-045', 5, 'Northline Foods', '2026-03-05', 10000.00, 2000.00, 12000.00, 'pending', 0),
('PAY-2026-046', 'LOAN-2026-046', 6, 'Blue Harbor Trading', '2026-03-06', 12000.00, 3000.00, 15000.00, 'scheduled', 0),
('PAY-2026-047', 'LOAN-2026-047', 7, 'Metro Farm Supply', '2026-03-04', 6000.00, 2000.00, 8000.00, 'paid', 0);

-- Insert sample payment history
INSERT INTO payment_history (payment_id, loan_id, user_id, borrower_name, payment_date, amount_paid, principal_paid, interest_paid, receipt_number, status) VALUES 
('PAY-2026-047', 'LOAN-2026-047', 7, 'Metro Farm Supply', '2026-03-04', 8000.00, 6000.00, 2000.00, 'RCP-2026-03-001', 'completed');
