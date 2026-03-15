-- Update Market Vendor Loan Database Structure (Fixed Version)
-- Add missing columns for complete loan management

-- Add missing columns to loans table
ALTER TABLE loans 
ADD COLUMN IF NOT EXISTS loan_start_date DATE AFTER status,
ADD COLUMN IF NOT EXISTS first_payment_date DATE AFTER loan_start_date,
ADD COLUMN IF NOT EXISTS next_payment_date DATE AFTER first_payment_date,
ADD COLUMN IF NOT EXISTS interest_rate DECIMAL(5,2) DEFAULT 5.00 AFTER loan_amount,
ADD COLUMN IF NOT EXISTS term_months INT DEFAULT 12 AFTER preferred_term,
ADD COLUMN IF NOT EXISTS remaining_balance DECIMAL(12,2) AFTER term_months,
ADD COLUMN IF NOT EXISTS total_paid DECIMAL(12,2) DEFAULT 0.00 AFTER remaining_balance;

-- Update existing loans with default values
UPDATE loans 
SET 
    loan_start_date = created_at,
    first_payment_date = DATE_ADD(created_at, INTERVAL 1 MONTH),
    next_payment_date = DATE_ADD(created_at, INTERVAL 1 MONTH),
    interest_rate = 5.00,
    term_months = 12,
    remaining_balance = loan_amount + (loan_amount * 0.05),
    total_paid = 0.00
WHERE 
    loan_start_date IS NULL;

-- Add indexes for better performance
ALTER TABLE loans 
ADD INDEX IF NOT EXISTS idx_loan_start_date (loan_start_date),
ADD INDEX IF NOT EXISTS idx_next_payment_date (next_payment_date),
ADD INDEX IF NOT EXISTS idx_remaining_balance (remaining_balance);

-- Update payment_schedules table structure
ALTER TABLE payment_schedules 
ADD COLUMN IF NOT EXISTS amount_paid DECIMAL(12,2) DEFAULT 0.00 AFTER total_amount,
ADD COLUMN IF NOT EXISTS payment_date TIMESTAMP NULL AFTER days_overdue;

-- Add indexes to payment_schedules
ALTER TABLE payment_schedules 
ADD INDEX IF NOT EXISTS idx_payment_date (payment_date);

-- Create admin notifications table if not exists
CREATE TABLE IF NOT EXISTS admin_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
    status ENUM('read', 'unread') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Insert sample admin user if not exists
INSERT IGNORE INTO users (id, name, email, password, role, phone) 
VALUES (3, 'Administrator', 'admin@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '09111111111');

-- Insert sample client users if not exist
INSERT IGNORE INTO users (id, name, email, password, role, phone) 
VALUES 
(1, 'John Doe', 'john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '09123456789'),
(2, 'Jane Smith', 'jane.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '09198765432');

-- Insert sample loans if not exist
INSERT IGNORE INTO loans (
    loan_id, user_id, full_name, email, phone, birthdate, address, civil_status, 
    business_name, business_type, business_address, monthly_revenue, business_description, 
    payment_frequency, loan_amount, loan_purpose, preferred_term, collateral, status
) VALUES 
('L2024001', 1, 'John Doe', 'john.doe@email.com', '09123456789', '1990-05-15', '123 Market St, Manila', 'Single', 
 'John''s Sari-Sari Store', 'Retail', '456 Market Ave, Manila', 25000.00, 'Working capital for inventory', 
 'monthly', 50000.00, 'Business expansion', 'Store inventory', 12, 'active'),

('L2024002', 2, 'Jane Smith', 'jane.smith@email.com', '09198765432', '1985-08-22', '789 Commerce St, Manila', 'Married', 
 'Jane''s Food Stall', 'Food Service', '321 Food Ave, Manila', 18000.00, 'Purchase cooking equipment', 
 'weekly', 30000.00, 'Equipment purchase', 'Kitchen equipment', 6, 'active'),

('L2024003', 1, 'John Doe', 'john.doe@email.com', '09123456789', '1990-05-15', '123 Market St, Manila', 'Single', 
 'John''s Sari-Sari Store', 'Retail', '456 Market Ave, Manila', 25000.00, 'Additional working capital', 
 'daily', 25000.00, 'Daily operations', 'Cash on hand', 6, 'pending');

-- Update sample loans with proper dates and amounts
UPDATE loans 
SET 
    loan_start_date = '2024-03-01',
    first_payment_date = CASE 
        WHEN payment_frequency = 'daily' THEN '2024-03-02'
        WHEN payment_frequency = 'weekly' THEN '2024-03-08'
        WHEN payment_frequency = 'monthly' THEN '2024-04-01'
    END,
    next_payment_date = CASE 
        WHEN payment_frequency = 'daily' THEN '2024-03-02'
        WHEN payment_frequency = 'weekly' THEN '2024-03-08'
        WHEN payment_frequency = 'monthly' THEN '2024-04-01'
    END,
    interest_rate = 5.00,
    term_months = CASE 
        WHEN payment_frequency = 'daily' THEN 6
        WHEN payment_frequency = 'weekly' THEN 6
        WHEN payment_frequency = 'monthly' THEN 12
    END,
    remaining_balance = loan_amount + (loan_amount * 0.05 * term_months / 12),
    total_paid = 0.00
WHERE loan_id IN ('L2024001', 'L2024002', 'L2024003');

-- Generate payment schedules for sample loans - Fixed Version

-- For L2024001 (Monthly loan - 12 payments)
INSERT INTO payment_schedules 
(payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, status)
VALUES 
('PAY20240401001', 'L2024001', 1, 'John Doe', '2024-04-01', 4166.67, 208.33, 4375.00, 'pending'),
('PAY20240501002', 'L2024001', 1, 'John Doe', '2024-05-01', 4166.67, 208.33, 4375.00, 'pending'),
('PAY20240601003', 'L2024001', 1, 'John Doe', '2024-06-01', 4166.67, 208.33, 4375.00, 'pending'),
('PAY20240701004', 'L2024001', 1, 'John Doe', '2024-07-01', 4166.67, 208.33, 4375.00, 'pending'),
('PAY20240801005', 'L2024001', 1, 'John Doe', '2024-08-01', 4166.67, 208.33, 4375.00, 'pending'),
('PAY20240901006', 'L2024001', 1, 'John Doe', '2024-09-01', 4166.67, 208.33, 4375.00, 'pending'),
('PAY20241001007', 'L2024001', 1, 'John Doe', '2024-10-01', 4166.67, 208.33, 4375.00, 'pending'),
('PAY20241101008', 'L2024001', 1, 'John Doe', '2024-11-01', 4166.67, 208.33, 4375.00, 'pending'),
('PAY20241201009', 'L2024001', 1, 'John Doe', '2024-12-01', 4166.67, 208.33, 4375.00, 'pending'),
('PAY20250101010', 'L2024001', 1, 'John Doe', '2025-01-01', 4166.67, 208.33, 4375.00, 'pending'),
('PAY20250201011', 'L2024001', 1, 'John Doe', '2025-02-01', 4166.67, 208.33, 4375.00, 'pending'),
('PAY20250301012', 'L2024001', 1, 'John Doe', '2025-03-01', 4166.67, 208.33, 4375.00, 'pending');

-- For L2024002 (Weekly loan - 24 payments)
INSERT INTO payment_schedules 
(payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, status)
VALUES 
('PAY20240308001', 'L2024002', 2, 'Jane Smith', '2024-03-08', 1250.00, 62.50, 1312.50, 'paid'),
('PAY20240315002', 'L2024002', 2, 'Jane Smith', '2024-03-15', 1250.00, 62.50, 1312.50, 'paid'),
('PAY20240322003', 'L2024002', 2, 'Jane Smith', '2024-03-22', 1250.00, 62.50, 1312.50, 'paid'),
('PAY20240329004', 'L2024002', 2, 'Jane Smith', '2024-03-29', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240405005', 'L2024002', 2, 'Jane Smith', '2024-04-05', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240412006', 'L2024002', 2, 'Jane Smith', '2024-04-12', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240419007', 'L2024002', 2, 'Jane Smith', '2024-04-19', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240426008', 'L2024002', 2, 'Jane Smith', '2024-04-26', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240503009', 'L2024002', 2, 'Jane Smith', '2024-05-03', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240510010', 'L2024002', 2, 'Jane Smith', '2024-05-10', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240517011', 'L2024002', 2, 'Jane Smith', '2024-05-17', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240524012', 'L2024002', 2, 'Jane Smith', '2024-05-24', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240531013', 'L2024002', 2, 'Jane Smith', '2024-05-31', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240607014', 'L2024002', 2, 'Jane Smith', '2024-06-07', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240614015', 'L2024002', 2, 'Jane Smith', '2024-06-14', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240621016', 'L2024002', 2, 'Jane Smith', '2024-06-21', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240628017', 'L2024002', 2, 'Jane Smith', '2024-06-28', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240705018', 'L2024002', 2, 'Jane Smith', '2024-07-05', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240712019', 'L2024002', 2, 'Jane Smith', '2024-07-12', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240719020', 'L2024002', 2, 'Jane Smith', '2024-07-19', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240726021', 'L2024002', 2, 'Jane Smith', '2024-07-26', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240802022', 'L2024002', 2, 'Jane Smith', '2024-08-02', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240809023', 'L2024002', 2, 'Jane Smith', '2024-08-09', 1250.00, 62.50, 1312.50, 'pending'),
('PAY20240816024', 'L2024002', 2, 'Jane Smith', '2024-08-16', 1250.00, 62.50, 1312.50, 'pending');

-- For L2024003 (Daily loan - First 30 payments only for sample)
INSERT INTO payment_schedules 
(payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, status)
VALUES 
('PAY20240309001', 'L2024003', 1, 'John Doe', '2024-03-09', 138.89, 6.94, 145.83, 'pending'),
('PAY20240310002', 'L2024003', 1, 'John Doe', '2024-03-10', 138.89, 6.94, 145.83, 'pending'),
('PAY20240311003', 'L2024003', 1, 'John Doe', '2024-03-11', 138.89, 6.94, 145.83, 'pending'),
('PAY20240312004', 'L2024003', 1, 'John Doe', '2024-03-12', 138.89, 6.94, 145.83, 'pending'),
('PAY20240313005', 'L2024003', 1, 'John Doe', '2024-03-13', 138.89, 6.94, 145.83, 'pending'),
('PAY20240314006', 'L2024003', 1, 'John Doe', '2024-03-14', 138.89, 6.94, 145.83, 'pending'),
('PAY20240315007', 'L2024003', 1, 'John Doe', '2024-03-15', 138.89, 6.94, 145.83, 'pending'),
('PAY20240316008', 'L2024003', 1, 'John Doe', '2024-03-16', 138.89, 6.94, 145.83, 'pending'),
('PAY20240317009', 'L2024003', 1, 'John Doe', '2024-03-17', 138.89, 6.94, 145.83, 'pending'),
('PAY20240318010', 'L2024003', 1, 'John Doe', '2024-03-18', 138.89, 6.94, 145.83, 'pending'),
('PAY20240319011', 'L2024003', 1, 'John Doe', '2024-03-19', 138.89, 6.94, 145.83, 'pending'),
('PAY20240320012', 'L2024003', 1, 'John Doe', '2024-03-20', 138.89, 6.94, 145.83, 'pending'),
('PAY20240321013', 'L2024003', 1, 'John Doe', '2024-03-21', 138.89, 6.94, 145.83, 'pending'),
('PAY20240322014', 'L2024003', 1, 'John Doe', '2024-03-22', 138.89, 6.94, 145.83, 'pending'),
('PAY20240323015', 'L2024003', 1, 'John Doe', '2024-03-23', 138.89, 6.94, 145.83, 'pending'),
('PAY20240324016', 'L2024003', 1, 'John Doe', '2024-03-24', 138.89, 6.94, 145.83, 'pending'),
('PAY20240325017', 'L2024003', 1, 'John Doe', '2024-03-25', 138.89, 6.94, 145.83, 'pending'),
('PAY20240326018', 'L2024003', 1, 'John Doe', '2024-03-26', 138.89, 6.94, 145.83, 'pending'),
('PAY20240327019', 'L2024003', 1, 'John Doe', '2024-03-27', 138.89, 6.94, 145.83, 'pending'),
('PAY20240328020', 'L2024003', 1, 'John Doe', '2024-03-28', 138.89, 6.94, 145.83, 'pending'),
('PAY20240329021', 'L2024003', 1, 'John Doe', '2024-03-29', 138.89, 6.94, 145.83, 'pending'),
('PAY20240330022', 'L2024003', 1, 'John Doe', '2024-03-30', 138.89, 6.94, 145.83, 'pending'),
('PAY20240331023', 'L2024003', 1, 'John Doe', '2024-03-31', 138.89, 6.94, 145.83, 'pending'),
('PAY20240401024', 'L2024003', 1, 'John Doe', '2024-04-01', 138.89, 6.94, 145.83, 'pending'),
('PAY20240402025', 'L2024003', 1, 'John Doe', '2024-04-02', 138.89, 6.94, 145.83, 'pending'),
('PAY20240403026', 'L2024003', 1, 'John Doe', '2024-04-03', 138.89, 6.94, 145.83, 'pending'),
('PAY20240404027', 'L2024003', 1, 'John Doe', '2024-04-04', 138.89, 6.94, 145.83, 'pending'),
('PAY20240405028', 'L2024003', 1, 'John Doe', '2024-04-05', 138.89, 6.94, 145.83, 'pending'),
('PAY20240406029', 'L2024003', 1, 'John Doe', '2024-04-06', 138.89, 6.94, 145.83, 'pending'),
('PAY20240407030', 'L2024003', 1, 'John Doe', '2024-04-07', 138.89, 6.94, 145.83, 'pending');

-- Update payment schedules as paid for Jane's loan (first 3 payments)
UPDATE payment_schedules 
SET status = 'paid', payment_date = NOW()
WHERE loan_id = 'L2024002' AND due_date <= '2024-03-22';

-- Update loan totals for Jane's loan
UPDATE loans 
SET total_paid = 3 * 1312.50,
    remaining_balance = (30000.00 + (30000.00 * 0.05 * 6 / 12)) - (3 * 1312.50)
WHERE loan_id = 'L2024002';

-- Insert sample payment history
INSERT IGNORE INTO payment_history 
(payment_id, loan_id, user_id, borrower_name, payment_date, amount_paid, principal_paid, interest_paid, payment_method, transaction_id, receipt_number)
VALUES 
('PAY20240308001', 'L2024002', 2, 'Jane Smith', '2024-03-08', 1312.50, 1250.00, 62.50, 'GCash', 'TXN202403080001', 'RCP202403080001'),
('PAY20240315002', 'L2024002', 2, 'Jane Smith', '2024-03-15', 1312.50, 1250.00, 62.50, 'Bank Transfer', 'TXN202403150002', 'RCP202403150002'),
('PAY20240322003', 'L2024002', 2, 'Jane Smith', '2024-03-22', 1312.50, 1250.00, 62.50, 'GCash', 'TXN202403220003', 'RCP202403220003');

-- Insert sample admin notifications
INSERT IGNORE INTO admin_notifications (title, message, type, status) VALUES 
('New Loan Application', 'John Doe submitted a loan application for ₱50,000.00', 'info', 'unread'),
('Loan Approved', 'Loan L2024001 has been approved and activated', 'success', 'unread'),
('Payment Received', 'Payment received for loan L2024002', 'success', 'unread'),
('Overdue Payment Alert', 'Some payments are now overdue', 'warning', 'unread');

-- Show final status
SELECT 'Database Updated Successfully' as status,
       (SELECT COUNT(*) FROM loans) as total_loans,
       (SELECT COUNT(*) FROM payment_schedules) as total_schedules,
       (SELECT COUNT(*) FROM users) as total_users;
