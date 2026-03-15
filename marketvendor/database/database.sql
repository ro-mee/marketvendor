-- Database: loan_management_system
CREATE DATABASE IF NOT EXISTS loan_management_system;
USE loan_management_system;

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
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Loans table
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
    loan_purpose VARCHAR(50) NOT NULL,
    preferred_term INT NOT NULL,
    collateral VARCHAR(50),
    status ENUM('pending', 'approved', 'active', 'completed', 'defaulted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_loan_id (loan_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
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
    transaction_id VARCHAR(100),
    receipt_number VARCHAR(50),
    status ENUM('completed', 'partial', 'failed') DEFAULT 'completed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_payment_id (payment_id),
    INDEX idx_loan_id (loan_id),
    INDEX idx_payment_date (payment_date)
);

-- Loan documents table
CREATE TABLE IF NOT EXISTS loan_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
    INDEX idx_loan_id (loan_id),
    INDEX idx_document_type (document_type)
);

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
INSERT INTO loans (loan_id, user_id, full_name, email, phone, birthdate, address, civil_status, business_name, business_type, business_address, monthly_revenue, business_description, payment_frequency, custom_loan_amount, loan_amount, loan_purpose, preferred_term, collateral, status) VALUES 
('LOAN-2026-001', 2, 'Maria Santos', 'maria.santos@email.com', '0917-234-5678', '1985-03-15', '123 Market St, Manila, Philippines', 'married', 'Coastal Enterprises', 'retail', '456 Commercial Ave, Manila', 45000.00, 'Retail store selling fresh seafood and local products', 'monthly', 350000.00, 350000.00, 'inventory', 12, 'property', 'active'),
('LOAN-2026-002', 3, 'Juan Cruz', 'juan.cruz@email.com', '0928-345-6789', '1988-07-22', '789 Business Rd, Quezon City, Philippines', 'single', 'Summit Retail Corp', 'retail', '321 Shopping Center, Quezon City', 38000.00, 'Supermarket chain with multiple locations', 'weekly', 280000.00, 280000.00, 'expansion', 10, 'equipment', 'active'),
('LOAN-2026-003', 4, 'Ana Reyes', 'ana.reyes@email.com', '0939-456-7890', '1990-11-08', '456 Trade Blvd, Makati, Philippines', 'married', 'Global Trading Co.', 'manufacturing', '987 Industrial Zone, Makati', 52000.00, 'Manufacturing and distribution of consumer goods', 'daily', 420000.00, 420000.00, 'working-capital', 15, 'inventory', 'active'),
('LOAN-2026-004', 5, 'Carlos Martinez', 'carlos.martinez@email.com', '0950-567-8901', '1992-02-14', '234 Commerce St, Pasig, Philippines', 'single', 'Northline Foods', 'food', '654 Food Park, Pasig', 28000.00, 'Food processing and restaurant business', 'monthly', 150000.00, 150000.00, 'equipment', 8, 'none', 'pending'),
('LOAN-2026-005', 6, 'Liza Fernandez', 'liza.fernandez@email.com', '0961-678-9012', '1987-09-30', '890 Enterprise Ave, Cebu, Philippines', 'widowed', 'Blue Harbor Trading', 'services', '123 Port Area, Cebu', 35000.00, 'Import-export and logistics services', 'weekly', 200000.00, 200000.00, 'renovation', 12, 'vehicle', 'pending')
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
