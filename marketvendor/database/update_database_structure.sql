-- Update Market Vendor Loan Database Structure
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

-- Generate payment schedules for sample loans
-- For L2024001 (Monthly loan)
INSERT IGNORE INTO payment_schedules 
(payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, status)
SELECT 
    CONCAT('PAY', DATE_FORMAT(due_date, '%Y%m%d'), LPAD(seq, 3, '0')) as payment_id,
    'L2024001' as loan_id,
    1 as user_id,
    'John Doe' as borrower_name,
    due_date,
    50000.00 / 12 as principal_amount,
    (50000.00 * 0.05 * 12 / 12) / 12 as interest_amount,
    (50000.00 + (50000.00 * 0.05 * 12 / 12)) / 12 as total_amount,
    'pending' as status
FROM (
    SELECT DATE_ADD('2024-04-01', INTERVAL (seq - 1) MONTH) as due_date
    FROM (
        SELECT 1 as seq UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
        UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
    ) as numbers
) as dates;

-- For L2024002 (Weekly loan)
INSERT IGNORE INTO payment_schedules 
(payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, status)
SELECT 
    CONCAT('PAY', DATE_FORMAT(due_date, '%Y%m%d'), LPAD(seq, 3, '0')) as payment_id,
    'L2024002' as loan_id,
    2 as user_id,
    'Jane Smith' as borrower_name,
    due_date,
    30000.00 / 24 as principal_amount,
    (30000.00 * 0.05 * 6 / 12) / 24 as interest_amount,
    (30000.00 + (30000.00 * 0.05 * 6 / 12)) / 24 as total_amount,
    CASE WHEN seq <= 3 THEN 'paid' ELSE 'pending' END as status
FROM (
    SELECT DATE_ADD('2024-03-08', INTERVAL (seq - 1) WEEK) as due_date
    FROM (
        SELECT 1 as seq UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
        UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
        UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18
        UNION SELECT 19 UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24
    ) as numbers
) as dates;

-- For L2024003 (Daily loan)
INSERT IGNORE INTO payment_schedules 
(payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, status)
SELECT 
    CONCAT('PAY', DATE_FORMAT(due_date, '%Y%m%d'), LPAD(seq, 3, '0')) as payment_id,
    'L2024003' as loan_id,
    1 as user_id,
    'John Doe' as borrower_name,
    due_date,
    25000.00 / 180 as principal_amount,
    (25000.00 * 0.05 * 6 / 12) / 180 as interest_amount,
    (25000.00 + (25000.00 * 0.05 * 6 / 12)) / 180 as total_amount,
    'pending' as status
FROM (
    SELECT DATE_ADD('2024-03-09', INTERVAL (seq - 1) DAY) as due_date
    FROM (
        SELECT 1 as seq UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
        UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
        UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18
        UNION SELECT 19 UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24
        UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION SELECT 30
        UNION SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION SELECT 36
        UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION SELECT 40 UNION SELECT 41 UNION SELECT 42
        UNION SELECT 43 UNION SELECT 44 UNION SELECT 45 UNION SELECT 46 UNION SELECT 47 UNION SELECT 48
        UNION SELECT 49 UNION SELECT 50 UNION SELECT 51 UNION SELECT 52 UNION SELECT 53 UNION SELECT 54
        UNION SELECT 55 UNION SELECT 56 UNION SELECT 57 UNION SELECT 58 UNION SELECT 59 UNION SELECT 60
        UNION SELECT 61 UNION SELECT 62 UNION SELECT 63 UNION SELECT 64 UNION SELECT 65 UNION SELECT 66
        UNION SELECT 67 UNION SELECT 68 UNION SELECT 69 UNION SELECT 70 UNION SELECT 71 UNION SELECT 72
        UNION SELECT 73 UNION SELECT 74 UNION SELECT 75 UNION SELECT 76 UNION SELECT 77 UNION SELECT 78
        UNION SELECT 79 UNION SELECT 80 UNION SELECT 81 UNION SELECT 82 UNION SELECT 83 UNION SELECT 84
        UNION SELECT 85 UNION SELECT 86 UNION SELECT 87 UNION SELECT 88 UNION SELECT 89 UNION SELECT 90
        UNION SELECT 91 UNION SELECT 92 UNION SELECT 93 UNION SELECT 94 UNION SELECT 95 UNION SELECT 96
        UNION SELECT 97 UNION SELECT 98 UNION SELECT 99 UNION SELECT 100 UNION SELECT 101 UNION SELECT 102
        UNION SELECT 103 UNION SELECT 104 UNION SELECT 105 UNION SELECT 106 UNION SELECT 107 UNION SELECT 108
        UNION SELECT 109 UNION SELECT 110 UNION SELECT 111 UNION SELECT 112 UNION SELECT 113 UNION SELECT 114
        UNION SELECT 115 UNION SELECT 116 UNION SELECT 117 UNION SELECT 118 UNION SELECT 119 UNION SELECT 120
        UNION SELECT 121 UNION SELECT 122 UNION SELECT 123 UNION SELECT 124 UNION SELECT 125 UNION SELECT 126
        UNION SELECT 127 UNION SELECT 128 UNION SELECT 129 UNION SELECT 130 UNION SELECT 131 UNION SELECT 132
        UNION SELECT 133 UNION SELECT 134 UNION SELECT 135 UNION SELECT 136 UNION SELECT 137 UNION SELECT 138
        UNION SELECT 139 UNION SELECT 140 UNION SELECT 141 UNION SELECT 142 UNION SELECT 143 UNION SELECT 144
        UNION SELECT 145 UNION SELECT 146 UNION SELECT 147 UNION SELECT 148 UNION SELECT 149 UNION SELECT 150
        UNION SELECT 151 UNION SELECT 152 UNION SELECT 153 UNION SELECT 154 UNION SELECT 155 UNION SELECT 156
        UNION SELECT 157 UNION SELECT 158 UNION SELECT 159 UNION SELECT 160 UNION SELECT 161 UNION SELECT 162
        UNION SELECT 163 UNION SELECT 164 UNION SELECT 165 UNION SELECT 166 UNION SELECT 167 UNION SELECT 168
        UNION SELECT 169 UNION SELECT 170 UNION SELECT 171 UNION SELECT 172 UNION SELECT 173 UNION SELECT 174
        UNION SELECT 175 UNION SELECT 176 UNION SELECT 177 UNION SELECT 178 UNION SELECT 179 UNION SELECT 180
    ) as numbers
) as dates;

-- Update some payment schedules as paid for Jane's loan
UPDATE payment_schedules 
SET status = 'paid', payment_date = '2024-03-08 10:30:00'
WHERE loan_id = 'L2024002' AND due_date <= '2024-03-22';

-- Update loan totals for Jane's loan
UPDATE loans 
SET total_paid = 3 * ((30000.00 + (30000.00 * 0.05 * 6 / 12)) / 24),
    remaining_balance = (30000.00 + (30000.00 * 0.05 * 6 / 12)) - (3 * ((30000.00 + (30000.00 * 0.05 * 6 / 12)) / 24))
WHERE loan_id = 'L2024002';

-- Insert sample payment history
INSERT IGNORE INTO payment_history 
(payment_id, loan_id, user_id, borrower_name, payment_date, amount_paid, principal_paid, interest_paid, payment_method, transaction_id, receipt_number)
VALUES 
('PAY202403080001', 'L2024002', 2, 'Jane Smith', '2024-03-08', (30000.00 + (30000.00 * 0.05 * 6 / 12)) / 24, 30000.00 / 24, ((30000.00 * 0.05 * 6 / 12)) / 24, 'GCash', 'TXN202403080001', 'RCP202403080001'),
('PAY202403150002', 'L2024002', 2, 'Jane Smith', '2024-03-15', (30000.00 + (30000.00 * 0.05 * 6 / 12)) / 24, 30000.00 / 24, ((30000.00 * 0.05 * 6 / 12)) / 24, 'Bank Transfer', 'TXN202403150002', 'RCP202403150002'),
('PAY202403220003', 'L2024002', 2, 'Jane Smith', '2024-03-22', (30000.00 + (30000.00 * 0.05 * 6 / 12)) / 24, 30000.00 / 24, ((30000.00 * 0.05 * 6 / 12)) / 24, 'GCash', 'TXN202403220003', 'RCP202403220003');

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
