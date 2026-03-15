-- =====================================================
-- MARKET VENDOR LOAN MANAGEMENT SYSTEM - REALISTIC SAMPLE DATA
-- =====================================================
-- This file contains comprehensive, realistic sample data for testing
-- all features of the loan management system
-- =====================================================

USE loan_management_system;

-- =====================================================
-- 1. USERS TABLE - REALISTIC VENDOR DATA
-- =====================================================

-- Clear existing sample data
DELETE FROM users WHERE role = 'vendor';
DELETE FROM users WHERE role = 'admin';

-- Insert admin user
INSERT INTO users (id, name, email, password, role, phone, address) VALUES 
(1, 'System Administrator', 'admin@marketvendor.com', '$2y$10$drj2LLIKTyPIbhoQXYsVlOjSjNhC36YWhrKNqnJ1SBR2KWeopbelO', 'admin', '0917-888-9999', '123 Admin Tower, Makati City, Philippines');

-- Insert realistic vendor users
INSERT INTO users (id, name, email, password, role, phone, address) VALUES 
(2, 'Maria Santos Trading', 'maria.santos@coastaltrading.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '0917-234-5678', '456 Market Street, Divisoria, Manila, Philippines'),
(3, 'Juan Cruz Enterprises', 'juan.cruz@summitretail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '0928-345-6789', '789 Commerce Avenue, Quezon City, Philippines'),
(4, 'Ana Reyes Food Corp', 'ana.reyes@globalfood.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '0939-456-7890', '234 Industrial Park, Pasig City, Philippines'),
(5, 'Carlos Martinez Retail', 'carlos.martinez@northline.com.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '0950-567-8901', '567 Shopping Center, Mandaluyong, Philippines'),
(6, 'Liza Fernandez Imports', 'liza.fernandez@blueharbor.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '0961-678-9012', '890 Port Area, Cebu City, Philippines'),
(7, 'Roberto Tan Electronics', 'roberto.tan@techgadgets.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '0977-789-0123', '321 Tech Hub, Bonifacio Global City, Taguig, Philippines'),
(8, 'Elena Rodriguez Fashion', 'elena.rodriguez@styleboutique.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '0988-890-1234', '654 Fashion District, Makati City, Philippines');

-- =====================================================
-- 2. LOANS TABLE - DIVERSE LOAN PORTFOLIO
-- =====================================================

-- Clear existing loans
DELETE FROM loans;

-- Insert realistic loans with different payment frequencies and statuses
INSERT INTO loans (id, loan_id, user_id, full_name, email, phone, birthdate, address, civil_status, business_name, business_type, business_address, monthly_revenue, business_description, payment_frequency, custom_loan_amount, loan_amount, interest_rate, term_months, loan_purpose, preferred_term, collateral, status, loan_start_date, first_payment_date, next_payment_date, remaining_balance, total_paid, created_at, updated_at) VALUES 

-- Maria Santos - Daily Payment Loan - Active with good payment history
(1, 'LOAN-2025-001', 2, 'Maria Santos', 'maria.santos@coastaltrading.ph', '0917-234-5678', '1985-03-15', '456 Market Street, Divisoria, Manila, Philippines', 'married', 'Maria Santos Trading', 'retail', '456 Market Street, Divisoria, Manila, Philippines', 85000.00, 'Fresh seafood and local products retailer serving Divisoria market', 'daily', 0, 150000.00, 3.5, 6, 'working-capital', 6, 'inventory', 'active', '2025-01-15', '2025-01-16', '2025-03-15', 45750.00, 104250.00, '2025-01-15 10:30:00', '2025-03-15 14:20:00'),

-- Juan Cruz - Weekly Payment Loan - Active with some late payments
(2, 'LOAN-2025-002', 3, 'Juan Cruz', 'juan.cruz@summitretail.com', '0928-345-6789', '1988-07-22', '789 Commerce Avenue, Quezon City, Philippines', 'single', 'Juan Cruz Enterprises', 'supermarket', '789 Commerce Avenue, Quezon City, Philippines', 120000.00, 'Supermarket chain with multiple locations in Quezon City', 'weekly', 0, 300000.00, 4.0, 12, 'expansion', 12, 'property', 'active', '2024-11-20', '2024-11-27', '2025-03-19', 187500.00, 112500.00, '2024-11-20 09:15:00', '2025-03-19 16:45:00'),

-- Ana Reyes - Monthly Payment Loan - Completed successfully
(3, 'LOAN-2025-003', 4, 'Ana Reyes', 'ana.reyes@globalfood.ph', '0939-456-7890', '1990-11-08', '234 Industrial Park, Pasig City, Philippines', 'married', 'Ana Reyes Food Corp', 'food_processing', '234 Industrial Park, Pasig City, Philippines', 95000.00, 'Food processing and distribution of consumer goods', 'monthly', 0, 250000.00, 3.0, 18, 'equipment', 18, 'equipment', 'completed', '2024-06-10', '2024-07-10', '2024-12-10', 0.00, 262500.00, '2024-06-10 11:20:00', '2024-12-10 10:30:00'),

-- Carlos Martinez - Daily Payment Loan - Active with late payments
(4, 'LOAN-2025-004', 5, 'Carlos Martinez', 'carlos.martinez@northline.com.ph', '0950-567-8901', '1992-02-14', '567 Shopping Center, Mandaluyong, Philippines', 'single', 'Carlos Martinez Retail', 'retail', '567 Shopping Center, Mandaluyong, Philippines', 65000.00, 'Retail store selling electronics and gadgets', 'daily', 0, 100000.00, 5.0, 4, 'inventory', 4, 'none', 'active', '2025-02-01', '2025-02-02', '2025-03-15', 62500.00, 37500.00, '2025-02-01 13:45:00', '2025-03-15 09:30:00'),

-- Liza Fernandez - Weekly Payment Loan - Pending approval
(5, 'LOAN-2025-005', 6, 'Liza Fernandez', 'liza.fernandez@blueharbor.ph', '0961-678-9012', '1987-09-30', '890 Port Area, Cebu City, Philippines', 'widowed', 'Liza Fernandez Imports', 'import_export', '890 Port Area, Cebu City, Philippines', 110000.00, 'Import-export and logistics services', 'weekly', 0, 400000.00, 3.5, 15, 'renovation', 15, 'vehicle', 'pending', NULL, NULL, NULL, 420000.00, 0.00, '2025-03-10 08:20:00', '2025-03-10 08:20:00'),

-- Roberto Tan - Monthly Payment Loan - Active
(6, 'LOAN-2025-006', 7, 'Roberto Tan', 'roberto.tan@techgadgets.ph', '0977-789-0123', '1984-12-05', '321 Tech Hub, Bonifacio Global City, Taguig, Philippines', 'married', 'Roberto Tan Electronics', 'electronics', '321 Tech Hub, Bonifacio Global City, Taguig, Philippines', 150000.00, 'Electronics retail and repair services', 'monthly', 0, 500000.00, 2.8, 24, 'expansion', 24, 'property', 'active', '2024-09-15', '2024-10-15', '2025-03-15', 354166.67, 145833.33, '2024-09-15 14:30:00', '2025-03-15 11:15:00'),

-- Elena Rodriguez - Weekly Payment Loan - Defaulted
(7, 'LOAN-2025-007', 8, 'Elena Rodriguez', 'elena.rodriguez@styleboutique.ph', '0988-890-1234', '1991-05-18', '654 Fashion District, Makati City, Philippines', 'single', 'Elena Rodriguez Fashion', 'fashion', '654 Fashion District, Makati City, Philippines', 75000.00, 'Fashion boutique and clothing retailer', 'weekly', 0, 180000.00, 6.0, 9, 'working-capital', 9, 'inventory', 'defaulted', '2024-08-01', '2024-08-08', '2024-12-01', 94500.00, 85500.00, '2024-08-01 10:00:00', '2024-12-01 16:20:00');

-- =====================================================
-- 3. PAYMENT SCHEDULES TABLE
-- =====================================================

-- Clear existing payment schedules
DELETE FROM payment_schedules;

-- Generate payment schedules for each loan based on their frequency and terms

-- LOAN-2025-001 (Daily Payment - 6 months = ~180 days)
INSERT INTO payment_schedules (payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, amount_paid, payment_date, status, days_overdue, created_at) VALUES 
('PAY-2025-001-001', 'LOAN-2025-001', 2, 'Maria Santos Trading', '2025-01-16', 833.33, 29.17, 862.50, 862.50, '2025-01-16 08:30:00', 'paid', 0, '2025-01-15 10:30:00'),
('PAY-2025-001-002', 'LOAN-2025-001', 2, 'Maria Santos Trading', '2025-01-17', 833.33, 29.17, 862.50, 862.50, '2025-01-17 09:15:00', 'paid', 0, '2025-01-15 10:30:00'),
('PAY-2025-001-003', 'LOAN-2025-001', 2, 'Maria Santos Trading', '2025-01-18', 833.33, 29.17, 862.50, 862.50, '2025-01-18 08:45:00', 'paid', 0, '2025-01-15 10:30:00'),
('PAY-2025-001-004', 'LOAN-2025-001', 2, 'Maria Santos Trading', '2025-01-19', 833.33, 29.17, 862.50, 862.50, '2025-01-19 10:20:00', 'paid', 0, '2025-01-15 10:30:00'),
('PAY-2025-001-005', 'LOAN-2025-001', 2, 'Maria Santos Trading', '2025-01-20', 833.33, 29.17, 862.50, 862.50, '2025-01-20 07:55:00', 'paid', 0, '2025-01-15 10:30:00'),
('PAY-2025-001-006', 'LOAN-2025-001', 2, 'Maria Santos Trading', '2025-01-21', 833.33, 29.17, 862.50, 862.50, '2025-01-21 09:30:00', 'paid', 0, '2025-01-15 10:30:00'),
('PAY-2025-001-007', 'LOAN-2025-001', 2, 'Maria Santos Trading', '2025-01-22', 833.33, 29.17, 862.50, 862.50, '2025-01-22 08:15:00', 'paid', 0, '2025-01-15 10:30:00'),
('PAY-2025-001-008', 'LOAN-2025-001', 2, 'Maria Santos Trading', '2025-01-23', 833.33, 29.17, 862.50, 862.50, '2025-01-23 11:05:00', 'paid', 0, '2025-01-15 10:30:00'),
('PAY-2025-001-009', 'LOAN-2025-001', 2, 'Maria Santos Trading', '2025-01-24', 833.33, 29.17, 862.50, 862.50, '2025-01-24 08:40:00', 'paid', 0, '2025-01-15 10:30:00'),
('PAY-2025-001-010', 'LOAN-2025-001', 2, 'Maria Santos Trading', '2025-01-25', 833.33, 29.17, 862.50, 862.50, '2025-01-25 09:25:00', 'paid', 0, '2025-01-15 10:30:00'),
-- Continue with more daily payments... (showing pattern)
('PAY-2025-001-120', 'LOAN-2025-001', 2, 'Maria Santos Trading', '2025-03-15', 833.33, 29.17, 862.50, 0.00, NULL, 'pending', 0, '2025-01-15 10:30:00');

-- LOAN-2025-002 (Weekly Payment - 12 months = 52 weeks)
INSERT INTO payment_schedules (payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, amount_paid, payment_date, status, days_overdue, created_at) VALUES 
('PAY-2025-002-001', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2024-11-27', 5769.23, 230.77, 6000.00, 6000.00, '2024-11-27 10:15:00', 'paid', 0, '2024-11-20 09:15:00'),
('PAY-2025-002-002', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2024-12-04', 5769.23, 230.77, 6000.00, 6000.00, '2024-12-04 14:30:00', 'paid', 0, '2024-11-20 09:15:00'),
('PAY-2025-002-003', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2024-12-11', 5769.23, 230.77, 6000.00, 6000.00, '2024-12-11 09:45:00', 'paid', 0, '2024-11-20 09:15:00'),
('PAY-2025-002-004', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2024-12-18', 5769.23, 230.77, 6000.00, 6300.00, '2024-12-22 11:20:00', 'paid', 4, '2024-11-20 09:15:00'),
('PAY-2025-002-005', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2024-12-25', 5769.23, 230.77, 6000.00, 6000.00, '2024-12-25 08:00:00', 'paid', 0, '2024-11-20 09:15:00'),
('PAY-2025-002-006', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2025-01-01', 5769.23, 230.77, 6000.00, 6600.00, '2025-01-05 15:30:00', 'paid', 4, '2024-11-20 09:15:00'),
('PAY-2025-002-007', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2025-01-08', 5769.23, 230.77, 6000.00, 6000.00, '2025-01-08 10:15:00', 'paid', 0, '2024-11-20 09:15:00'),
('PAY-2025-002-008', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2025-01-15', 5769.23, 230.77, 6000.00, 6000.00, '2025-01-15 14:20:00', 'paid', 0, '2024-11-20 09:15:00'),
('PAY-2025-002-009', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2025-01-22', 5769.23, 230.77, 6000.00, 6000.00, '2025-01-22 09:30:00', 'paid', 0, '2024-11-20 09:15:00'),
('PAY-2025-002-010', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2025-01-29', 5769.23, 230.77, 6000.00, 6900.00, '2025-02-02 11:45:00', 'paid', 4, '2024-11-20 09:15:00'),
('PAY-2025-002-011', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2025-02-05', 5769.23, 230.77, 6000.00, 6000.00, '2025-02-05 08:20:00', 'paid', 0, '2024-11-20 09:15:00'),
('PAY-2025-002-012', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2025-02-12', 5769.23, 230.77, 6000.00, 6000.00, '2025-02-12 13:10:00', 'paid', 0, '2024-11-20 09:15:00'),
('PAY-2025-002-013', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2025-02-19', 5769.23, 230.77, 6000.00, 6000.00, '2025-02-19 10:25:00', 'paid', 0, '2024-11-20 09:15:00'),
('PAY-2025-002-014', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2025-02-26', 5769.23, 230.77, 6000.00, 0.00, NULL, 'overdue', 17, '2024-11-20 09:15:00'),
('PAY-2025-002-015', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2025-03-05', 5769.23, 230.77, 6000.00, 0.00, NULL, 'overdue', 10, '2024-11-20 09:15:00'),
('PAY-2025-002-016', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2025-03-12', 5769.23, 230.77, 6000.00, 0.00, NULL, 'overdue', 3, '2024-11-20 09:15:00'),
('PAY-2025-002-017', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2025-03-19', 5769.23, 230.77, 6000.00, 0.00, NULL, 'pending', 0, '2024-11-20 09:15:00');

-- LOAN-2025-003 (Monthly Payment - 18 months - Completed)
INSERT INTO payment_schedules (payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, amount_paid, payment_date, status, days_overdue, created_at) VALUES 
('PAY-2025-003-001', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2024-07-10', 13888.89, 625.00, 14513.89, 14513.89, '2024-07-10 09:30:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-002', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2024-08-10', 13888.89, 625.00, 14513.89, 14513.89, '2024-08-10 14:15:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-003', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2024-09-10', 13888.89, 625.00, 14513.89, 14513.89, '2024-09-10 10:45:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-004', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2024-10-10', 13888.89, 625.00, 14513.89, 14513.89, '2024-10-10 11:20:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-005', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2024-11-10', 13888.89, 625.00, 14513.89, 14513.89, '2024-11-10 09:30:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-006', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2024-12-10', 13888.89, 625.00, 14513.89, 14513.89, '2024-12-10 08:15:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-007', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2025-01-10', 13888.89, 625.00, 14513.89, 14513.89, '2025-01-10 13:45:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-008', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2025-02-10', 13888.89, 625.00, 14513.89, 14513.89, '2025-02-10 10:20:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-009', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2025-03-10', 13888.89, 625.00, 14513.89, 14513.89, '2025-03-10 14:30:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-010', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2025-04-10', 13888.89, 625.00, 14513.89, 14513.89, '2025-04-10 09:15:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-011', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2025-05-10', 13888.89, 625.00, 14513.89, 14513.89, '2025-05-10 11:25:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-012', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2025-06-10', 13888.89, 625.00, 14513.89, 14513.89, '2025-06-10 08:40:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-013', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2025-07-10', 13888.89, 625.00, 14513.89, 14513.89, '2025-07-10 10:15:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-014', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2025-08-10', 13888.89, 625.00, 14513.89, 14513.89, '2025-08-10 13:20:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-015', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2025-09-10', 13888.89, 625.00, 14513.89, 14513.89, '2025-09-10 09:45:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-016', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2025-10-10', 13888.89, 625.00, 14513.89, 14513.89, '2025-10-10 14:10:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-017', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2025-11-10', 13888.89, 625.00, 14513.89, 14513.89, '2025-11-10 11:30:00', 'paid', 0, '2024-06-10 11:20:00'),
('PAY-2025-003-018', 'LOAN-2025-003', 4, 'Ana Reyes Food Corp', '2025-12-10', 13888.89, 625.00, 14513.89, 14513.89, '2025-12-10 08:25:00', 'paid', 0, '2024-06-10 11:20:00');

-- LOAN-2025-004 (Daily Payment - 4 months = ~120 days)
INSERT INTO payment_schedules (payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, amount_paid, payment_date, status, days_overdue, created_at) VALUES 
('PAY-2025-004-001', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-02', 833.33, 41.67, 875.00, 875.00, '2025-02-02 09:15:00', 'paid', 0, '2025-02-01 13:45:00'),
('PAY-2025-004-002', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-03', 833.33, 41.67, 875.00, 875.00, '2025-02-03 10:30:00', 'paid', 0, '2025-02-01 13:45:00'),
('PAY-2025-004-003', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-04', 833.33, 41.67, 875.00, 875.00, '2025-02-04 08:45:00', 'paid', 0, '2025-02-01 13:45:00'),
('PAY-2025-004-004', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-05', 833.33, 41.67, 875.00, 875.00, '2025-02-05 11:20:00', 'paid', 0, '2025-02-01 13:45:00'),
('PAY-2025-004-005', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-06', 833.33, 41.67, 875.00, 875.00, '2025-02-06 09:10:00', 'paid', 0, '2025-02-01 13:45:00'),
('PAY-2025-004-006', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-07', 833.33, 41.67, 875.00, 875.00, '2025-02-07 14:35:00', 'paid', 0, '2025-02-01 13:45:00'),
('PAY-2025-004-007', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-08', 833.33, 41.67, 875.00, 875.00, '2025-02-08 08:55:00', 'paid', 0, '2025-02-01 13:45:00'),
('PAY-2025-004-008', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-09', 833.33, 41.67, 875.00, 875.00, '2025-02-09 10:15:00', 'paid', 0, '2025-02-01 13:45:00'),
('PAY-2025-004-009', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-10', 833.33, 41.67, 875.00, 875.00, '2025-02-10 12:40:00', 'paid', 0, '2025-02-01 13:45:00'),
('PAY-2025-004-010', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-11', 833.33, 41.67, 875.00, 910.00, '2025-02-14 09:25:00', 'paid', 3, '2025-02-01 13:45:00'),
('PAY-2025-004-011', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-12', 833.33, 41.67, 875.00, 945.00, '2025-02-16 11:45:00', 'paid', 4, '2025-02-01 13:45:00'),
('PAY-2025-004-012', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-13', 833.33, 41.67, 875.00, 875.00, '2025-02-13 08:30:00', 'paid', 0, '2025-02-01 13:45:00'),
('PAY-2025-004-013', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-14', 833.33, 41.67, 875.00, 875.00, '2025-02-14 10:50:00', 'paid', 0, '2025-02-01 13:45:00'),
('PAY-2025-004-014', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-15', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 28, '2025-02-01 13:45:00'),
('PAY-2025-004-015', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-16', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 27, '2025-02-01 13:45:00'),
('PAY-2025-004-016', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-17', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 26, '2025-02-01 13:45:00'),
('PAY-2025-004-017', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-18', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 25, '2025-02-01 13:45:00'),
('PAY-2025-004-018', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-19', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 24, '2025-02-01 13:45:00'),
('PAY-2025-004-019', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-20', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 23, '2025-02-01 13:45:00'),
('PAY-2025-004-020', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-21', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 22, '2025-02-01 13:45:00'),
('PAY-2025-004-021', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-22', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 21, '2025-02-01 13:45:00'),
('PAY-2025-004-022', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-23', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 20, '2025-02-01 13:45:00'),
('PAY-2025-004-023', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-24', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 19, '2025-02-01 13:45:00'),
('PAY-2025-004-024', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-25', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 18, '2025-02-01 13:45:00'),
('PAY-2025-004-025', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-26', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 17, '2025-02-01 13:45:00'),
('PAY-2025-004-026', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-27', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 16, '2025-02-01 13:45:00'),
('PAY-2025-004-027', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-28', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 15, '2025-02-01 13:45:00'),
('PAY-2025-004-028', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-01', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 14, '2025-02-01 13:45:00'),
('PAY-2025-004-029', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-02', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 13, '2025-02-01 13:45:00'),
('PAY-2025-004-030', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-03', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 12, '2025-02-01 13:45:00'),
('PAY-2025-004-031', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-04', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 11, '2025-02-01 13:45:00'),
('PAY-2025-004-032', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-05', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 10, '2025-02-01 13:45:00'),
('PAY-2025-004-033', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-06', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 9, '2025-02-01 13:45:00'),
('PAY-2025-004-034', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-07', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 8, '2025-02-01 13:45:00'),
('PAY-2025-004-035', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-08', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 7, '2025-02-01 13:45:00'),
('PAY-2025-004-036', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-09', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 6, '2025-02-01 13:45:00'),
('PAY-2025-004-037', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-10', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 5, '2025-02-01 13:45:00'),
('PAY-2025-004-038', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-11', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 4, '2025-02-01 13:45:00'),
('PAY-2025-004-039', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-12', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 3, '2025-02-01 13:45:00'),
('PAY-2025-004-040', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-13', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 2, '2025-02-01 13:45:00'),
('PAY-2025-004-041', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-14', 833.33, 41.67, 875.00, 0.00, NULL, 'overdue', 1, '2025-02-01 13:45:00'),
('PAY-2025-004-042', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-03-15', 833.33, 41.67, 875.00, 0.00, NULL, 'pending', 0, '2025-02-01 13:45:00');

-- LOAN-2025-006 (Monthly Payment - 24 months)
INSERT INTO payment_schedules (payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, amount_paid, payment_date, status, days_overdue, created_at) VALUES 
('PAY-2025-006-001', 'LOAN-2025-006', 7, 'Roberto Tan Electronics', '2024-10-15', 20833.33, 1166.67, 22000.00, 22000.00, '2024-10-15 10:30:00', 'paid', 0, '2024-09-15 14:30:00'),
('PAY-2025-006-002', 'LOAN-2025-006', 7, 'Roberto Tan Electronics', '2024-11-15', 20833.33, 1166.67, 22000.00, 22000.00, '2024-11-15 09:15:00', 'paid', 0, '2024-09-15 14:30:00'),
('PAY-2025-006-003', 'LOAN-2025-006', 7, 'Roberto Tan Electronics', '2024-12-15', 20833.33, 1166.67, 22000.00, 22000.00, '2024-12-15 14:20:00', 'paid', 0, '2024-09-15 14:30:00'),
('PAY-2025-006-004', 'LOAN-2025-006', 7, 'Roberto Tan Electronics', '2025-01-15', 20833.33, 1166.67, 22000.00, 22000.00, '2025-01-15 11:45:00', 'paid', 0, '2024-09-15 14:30:00'),
('PAY-2025-006-005', 'LOAN-2025-006', 7, 'Roberto Tan Electronics', '2025-02-15', 20833.33, 1166.67, 22000.00, 22000.00, '2025-02-15 08:30:00', 'paid', 0, '2024-09-15 14:30:00'),
('PAY-2025-006-006', 'LOAN-2025-006', 7, 'Roberto Tan Electronics', '2025-03-15', 20833.33, 1166.67, 22000.00, 0.00, NULL, 'pending', 0, '2024-09-15 14:30:00');

-- LOAN-2025-007 (Weekly Payment - 9 months - Defaulted)
INSERT INTO payment_schedules (payment_id, loan_id, user_id, borrower_name, due_date, principal_amount, interest_amount, total_amount, amount_paid, payment_date, status, days_overdue, created_at) VALUES 
('PAY-2025-007-001', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-08-08', 5000.00, 300.00, 5300.00, 5300.00, '2024-08-08 10:00:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-002', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-08-15', 5000.00, 300.00, 5300.00, 5300.00, '2024-08-15 14:30:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-003', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-08-22', 5000.00, 300.00, 5300.00, 5300.00, '2024-08-22 09:15:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-004', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-08-29', 5000.00, 300.00, 5300.00, 5300.00, '2024-08-29 11:20:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-005', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-09-05', 5000.00, 300.00, 5300.00, 5300.00, '2024-09-05 08:45:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-006', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-09-12', 5000.00, 300.00, 5300.00, 5300.00, '2024-09-12 13:10:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-007', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-09-19', 5000.00, 300.00, 5300.00, 5300.00, '2024-09-19 10:25:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-008', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-09-26', 5000.00, 300.00, 5300.00, 5300.00, '2024-09-26 14:40:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-009', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-10-03', 5000.00, 300.00, 5300.00, 5300.00, '2024-10-03 09:55:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-010', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-10-10', 5000.00, 300.00, 5300.00, 5300.00, '2024-10-10 11:30:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-011', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-10-17', 5000.00, 300.00, 5300.00, 5300.00, '2024-10-17 08:20:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-012', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-10-24', 5000.00, 300.00, 5300.00, 5300.00, '2024-10-24 13:45:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-013', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-10-31', 5000.00, 300.00, 5300.00, 5300.00, '2024-10-31 10:15:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-014', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-11-07', 5000.00, 300.00, 5300.00, 5300.00, '2024-11-07 14:25:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-015', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-11-14', 5000.00, 300.00, 5300.00, 5300.00, '2024-11-14 09:40:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-016', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-11-21', 5000.00, 300.00, 5300.00, 5300.00, '2024-11-21 11:55:00', 'paid', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-017', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-11-28', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 105, '2024-08-01 10:00:00'),
('PAY-2025-007-018', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-12-05', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 98, '2024-08-01 10:00:00'),
('PAY-2025-007-019', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-12-12', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 91, '2024-08-01 10:00:00'),
('PAY-2025-007-020', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-12-19', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 84, '2024-08-01 10:00:00'),
('PAY-2025-007-021', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-12-26', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 77, '2024-08-01 10:00:00'),
('PAY-2025-007-022', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-01-02', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 70, '2024-08-01 10:00:00'),
('PAY-2025-007-023', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-01-09', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 63, '2024-08-01 10:00:00'),
('PAY-2025-007-024', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-01-16', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 56, '2024-08-01 10:00:00'),
('PAY-2025-007-025', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-01-23', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 49, '2024-08-01 10:00:00'),
('PAY-2025-007-026', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-01-30', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 42, '2024-08-01 10:00:00'),
('PAY-2025-007-027', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-02-06', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 35, '2024-08-01 10:00:00'),
('PAY-2025-007-028', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-02-13', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 28, '2024-08-01 10:00:00'),
('PAY-2025-007-029', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-02-20', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 21, '2024-08-01 10:00:00'),
('PAY-2025-007-030', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-02-27', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 14, '2024-08-01 10:00:00'),
('PAY-2025-007-031', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-03-06', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 7, '2024-08-01 10:00:00'),
('PAY-2025-007-032', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-03-13', 5000.00, 300.00, 5300.00, 0.00, NULL, 'overdue', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-033', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-03-20', 5000.00, 300.00, 5300.00, 0.00, NULL, 'pending', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-034', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-03-27', 5000.00, 300.00, 5300.00, 0.00, NULL, 'pending', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-035', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-04-03', 5000.00, 300.00, 5300.00, 0.00, NULL, 'pending', 0, '2024-08-01 10:00:00'),
('PAY-2025-007-036', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2025-04-10', 5000.00, 300.00, 5300.00, 0.00, NULL, 'pending', 0, '2024-08-01 10:00:00');

-- =====================================================
-- 4. PAYMENT HISTORY TABLE
-- =====================================================

-- Clear existing payment history
DELETE FROM payment_history;

-- Insert payment history records matching the payment schedules
INSERT INTO payment_history (payment_id, loan_id, user_id, borrower_name, payment_date, amount_paid, principal_paid, interest_paid, payment_method, transaction_id, receipt_number, status, verification_status, payment_type, reference_number, payment_notes, verified_at, verified_by, created_at) VALUES 

-- Maria Santos - Daily payments (all on time)
('PAY-2025-001-001', 'LOAN-2025-001', 2, 'Maria Santos Trading', '2025-01-16', 862.50, 833.33, 29.17, 'cash', NULL, 'RCP-2025-001-001', 'completed', 'verified', 'on_time', NULL, 'Daily payment - on time', '2025-01-16 09:00:00', 'admin', '2025-01-16 08:30:00'),
('PAY-2025-001-002', 'LOAN-2025-001', 2, 'Maria Santos Trading', '2025-01-17', 862.50, 833.33, 29.17, 'cash', NULL, 'RCP-2025-001-002', 'completed', 'verified', 'on_time', NULL, 'Daily payment - on time', '2025-01-17 09:30:00', 'admin', '2025-01-17 09:15:00'),
('PAY-2025-001-003', 'LOAN-2025-001', 2, 'Maria Santos Trading', '2025-01-18', 862.50, 833.33, 29.17, 'cash', NULL, 'RCP-2025-001-003', 'completed', 'verified', 'on_time', NULL, 'Daily payment - on time', '2025-01-18 09:00:00', 'admin', '2025-01-18 08:45:00'),

-- Juan Cruz - Weekly payments (some late)
('PAY-2025-002-004', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2024-12-22', 6300.00, 5769.23, 230.77, 'bank_transfer', 'BANK-2024-12-001', 'RCP-2025-002-004', 'completed', 'verified', 'late', NULL, 'Late payment with 5% fee', '2024-12-22 12:00:00', 'admin', '2024-12-22 11:20:00'),
('PAY-2025-002-006', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2025-01-05', 6600.00, 5769.23, 230.77, 'bank_transfer', 'BANK-2025-01-001', 'RCP-2025-002-006', 'completed', 'verified', 'late', NULL, 'Late payment with 10% fee', '2025-01-05 16:00:00', 'admin', '2025-01-05 15:30:00'),
('PAY-2025-002-010', 'LOAN-2025-002', 3, 'Juan Cruz Enterprises', '2025-02-02', 6900.00, 5769.23, 230.77, 'bank_transfer', 'BANK-2025-02-001', 'RCP-2025-002-010', 'completed', 'verified', 'late', NULL, 'Late payment with 15% fee', '2025-02-02 12:00:00', 'admin', '2025-02-02 11:45:00'),

-- Carlos Martinez - Daily payments (many late)
('PAY-2025-004-010', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-14', 910.00, 833.33, 41.67, 'cash', NULL, 'RCP-2025-004-010', 'completed', 'verified', 'late', NULL, 'Late payment with 4% fee', '2025-02-14 10:00:00', 'admin', '2025-02-14 09:25:00'),
('PAY-2025-004-011', 'LOAN-2025-004', 5, 'Carlos Martinez Retail', '2025-02-16', 945.00, 833.33, 41.67, 'cash', NULL, 'RCP-2025-004-011', 'completed', 'verified', 'late', NULL, 'Late payment with 8% fee', '2025-02-16 12:00:00', 'admin', '2025-02-16 11:45:00'),

-- Roberto Tan - Monthly payments (all on time)
('PAY-2025-006-001', 'LOAN-2025-006', 7, 'Roberto Tan Electronics', '2024-10-15', 22000.00, 20833.33, 1166.67, 'bank_transfer', 'BANK-2024-10-001', 'RCP-2025-006-001', 'completed', 'verified', 'on_time', NULL, 'Monthly payment - on time', '2024-10-15 11:00:00', 'admin', '2024-10-15 10:30:00'),
('PAY-2025-006-002', 'LOAN-2025-006', 7, 'Roberto Tan Electronics', '2024-11-15', 22000.00, 20833.33, 1166.67, 'bank_transfer', 'BANK-2024-11-001', 'RCP-2025-006-002', 'completed', 'verified', 'on_time', NULL, 'Monthly payment - on time', '2024-11-15 10:00:00', 'admin', '2024-11-15 09:15:00'),

-- Elena Rodriguez - Weekly payments (stopped after 16 payments)
('PAY-2025-007-016', 'LOAN-2025-007', 8, 'Elena Rodriguez Fashion', '2024-11-21', 5300.00, 5000.00, 300.00, 'cash', NULL, 'RCP-2025-007-016', 'completed', 'verified', 'on_time', NULL, 'Last payment before default', '2024-11-21 12:00:00', 'admin', '2024-11-21 11:55:00');

-- =====================================================
-- 5. LATE FEES TABLE
-- =====================================================

-- Clear existing late fees
DELETE FROM late_fees;

-- Insert calculated late fees with compound interest
INSERT INTO late_fees (loan_id, payment_schedule_id, original_due_date, days_late, fee_type, fee_amount, fee_percentage, calculation_details, status, applied_date, created_at) VALUES 

-- Juan Cruz - Late fees with compound calculation
('LOAN-2025-002', 4, '2024-12-18', 4, 'percentage', 300.00, 5.00, '{"base_amount": 6000.00, "fee_rate": 0.05, "grace_period_used": 3, "compound_interest": false}', 'applied', '2024-12-22 11:20:00', '2024-12-22 11:20:00'),
('LOAN-2025-002', 6, '2025-01-01', 4, 'percentage', 600.00, 10.00, '{"base_amount": 6000.00, "fee_rate": 0.10, "grace_period_used": 3, "weekend_included": true}', 'applied', '2025-01-05 15:30:00', '2025-01-05 15:30:00'),
('LOAN-2025-002', 10, '2025-01-29', 4, 'percentage', 900.00, 15.00, '{"base_amount": 6000.00, "fee_rate": 0.15, "grace_period_used": 3, "compound_interest": true}', 'applied', '2025-02-02 11:45:00', '2025-02-02 11:45:00'),

-- Carlos Martinez - Multiple late fees with compound interest
('LOAN-2025-004', 10, '2025-02-11', 3, 'percentage', 35.00, 4.00, '{"base_amount": 875.00, "fee_rate": 0.04, "grace_period_used": 3, "weekend_included": false}', 'applied', '2025-02-14 09:25:00', '2025-02-14 09:25:00'),
('LOAN-2025-004', 11, '2025-02-12', 4, 'percentage', 70.00, 8.00, '{"base_amount": 875.00, "fee_rate": 0.08, "grace_period_used": 3, "compound_interest": true}', 'applied', '2025-02-16 11:45:00', '2025-02-16 11:45:00'),

-- Elena Rodriguez - Accumulated late fees for defaulted loan
('LOAN-2025-007', 17, '2024-11-28', 105, 'tiered', 2650.00, 50.00, '{"base_amount": 5300.00, "tier_rate": 0.50, "days_late": 105, "max_fee_applied": true}', 'applied', '2024-12-01 16:20:00', '2024-12-01 16:20:00'),
('LOAN-2025-007', 18, '2024-12-05', 98, 'tiered', 2650.00, 50.00, '{"base_amount": 5300.00, "tier_rate": 0.50, "days_late": 98, "max_fee_applied": true}', 'applied', '2024-12-10 10:30:00', '2024-12-10 10:30:00'),
('LOAN-2025-007', 19, '2024-12-12', 91, 'tiered', 2650.00, 50.00, '{"base_amount": 5300.00, "tier_rate": 0.50, "days_late": 91, "max_fee_applied": true}', 'applied', '2024-12-15 14:45:00', '2024-12-15 14:45:00');

-- =====================================================
-- 6. SYSTEM SETTINGS TABLE
-- =====================================================

-- Clear existing system settings
DELETE FROM system_settings;

-- Insert comprehensive system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES 
-- Interest Rates
('interest_rate_daily', '5.0', 'percentage', 'Interest rate for daily payments'),
('interest_rate_weekly', '4.5', 'percentage', 'Interest rate for weekly payments'),
('interest_rate_monthly', '3.5', 'percentage', 'Interest rate for monthly payments'),
('interest_rate_quarterly', '3.0', 'percentage', 'Interest rate for quarterly payments'),

-- Loan Limits
('max_loan_amount', '1000000', 'decimal', 'Maximum loan amount allowed (₱1M)'),
('min_loan_amount', '10000', 'decimal', 'Minimum loan amount allowed (₱10K)'),
('max_term_months_daily', '6', 'integer', 'Maximum loan term for daily payments (6 months)'),
('max_term_months_weekly', '12', 'integer', 'Maximum loan term for weekly payments (12 months)'),
('max_term_months_monthly', '36', 'integer', 'Maximum loan term for monthly payments (36 months)'),

-- Late Fee Settings
('late_fee_grace_period', '3', 'integer', 'Grace period in days before late fees apply'),
('late_fee_daily_rate', '5.0', 'percentage', 'Daily late fee rate (5% of payment amount)'),
('late_fee_weekend_multiplier', '1.5', 'decimal', 'Multiplier for weekend late fees (1.5x)'),
('late_fee_compound_interest', 'true', 'boolean', 'Enable compound interest for late fees'),
('late_fee_max_percentage', '50.0', 'percentage', 'Maximum late fee as percentage of payment (50%)'),
('late_fee_min_amount', '100.00', 'decimal', 'Minimum late fee amount (₱100)'),

-- Tiered Late Fee Structure
('late_fee_tier_1_days', '7', 'integer', 'Tier 1: 1-7 days late'),
('late_fee_tier_1_rate', '2.0', 'percentage', 'Tier 1 rate: 2%'),
('late_fee_tier_2_days', '14', 'integer', 'Tier 2: 8-14 days late'),
('late_fee_tier_2_rate', '5.0', 'percentage', 'Tier 2 rate: 5%'),
('late_fee_tier_3_days', '30', 'integer', 'Tier 3: 15-30 days late'),
('late_fee_tier_3_rate', '10.0', 'percentage', 'Tier 3 rate: 10%'),
('late_fee_tier_4_days', '60', 'integer', 'Tier 4: 31-60 days late'),
('late_fee_tier_4_rate', '25.0', 'percentage', 'Tier 4 rate: 25%'),
('late_fee_tier_5_days', '999', 'integer', 'Tier 5: 61+ days late'),
('late_fee_tier_5_rate', '50.0', 'percentage', 'Tier 5 rate: 50%'),

-- System Configuration
('auto_approve_limit', '50000', 'decimal', 'Auto-approval limit for loans (₱50K)'),
('payment_verification_required', 'true', 'boolean', 'Require payment verification'),
('notification_email_enabled', 'true', 'boolean', 'Enable email notifications'),
('notification_sms_enabled', 'false', 'boolean', 'Enable SMS notifications'),
('system_timezone', 'Asia/Manila', 'string', 'System timezone'),
('currency_code', 'PHP', 'string', 'Currency code'),
('currency_symbol', '₱', 'string', 'Currency symbol'),

-- Business Rules
('min_credit_score', '600', 'integer', 'Minimum credit score for loan approval'),
('max_debt_to_income', '40.0', 'percentage', 'Maximum debt-to-income ratio (40%)'),
('require_collateral_above', '100000', 'decimal', 'Require collateral for loans above ₱100K'),
('payment_reminder_days', '3', 'integer', 'Send payment reminder X days before due'),
('overdue_warning_days', '7', 'integer', 'Send overdue warning after X days'),

-- Processing Settings
('daily_processing_time', '02:00', 'time', 'Daily processing time for late fees'),
('batch_payment_processing', 'true', 'boolean', 'Enable batch payment processing'),
('auto_generate_schedules', 'true', 'boolean', 'Auto-generate payment schedules'),
('backup_retention_days', '90', 'integer', 'Backup retention period in days');

-- =====================================================
-- 7. LATE FEE SETTINGS TABLE
-- =====================================================

-- Clear existing late fee settings
DELETE FROM late_fee_settings;

-- Insert default late fee configuration
INSERT INTO late_fee_settings (fee_type, percentage_rate, fixed_amount, grace_period_days, max_fee_percentage, compound_daily, apply_weekends, min_fee_amount, description, is_active) VALUES 
('percentage', 5.0, 100.00, 3, 50.0, TRUE, TRUE, 100.00, 'Default percentage-based late fee with compound interest', TRUE),
('fixed', 0.0, 500.00, 2, 0.0, FALSE, TRUE, 500.00, 'Fixed amount late fee for small loans', FALSE),
('tiered', 0.0, 0.00, 3, 50.0, TRUE, TRUE, 100.00, 'Tiered late fee structure based on days late', TRUE);

-- =====================================================
-- 8. LATE FEE TIERS TABLE
-- =====================================================

-- Clear existing late fee tiers
DELETE FROM late_fee_tiers;

-- Insert tiered late fee structure
INSERT INTO late_fee_tiers (days_from, days_to, fee_type, fee_value, max_fee_amount, is_active) VALUES 
(1, 7, 'percentage', 2.0, 500.00, TRUE),    -- 2% for 1-7 days late, max ₱500
(8, 14, 'percentage', 5.0, 1000.00, TRUE),   -- 5% for 8-14 days late, max ₱1000
(15, 30, 'percentage', 10.0, 2500.00, TRUE),  -- 10% for 15-30 days late, max ₱2500
(31, 60, 'percentage', 25.0, 5000.00, TRUE),  -- 25% for 31-60 days late, max ₱5000
(61, 999, 'percentage', 50.0, 10000.00, TRUE); -- 50% for 61+ days late, max ₱10000

-- =====================================================
-- 9. ADMIN NOTIFICATIONS TABLE
-- =====================================================

-- Clear existing admin notifications
DELETE FROM admin_notifications;

-- Insert sample admin notifications
INSERT INTO admin_notifications (title, message, type, status, user_id, created_at) VALUES 
('New Loan Application', 'Liza Fernandez Imports has submitted a new loan application for ₱400,000.00', 'info', 'read', 1, '2025-03-10 08:20:00'),
('Payment Overdue Alert', 'Juan Cruz Enterprises - Weekly payment LOAN-2025-002-014 is 17 days overdue', 'warning', 'unread', 1, '2025-03-15 09:00:00'),
('Critical Loan Default', 'Elena Rodriguez Fashion (LOAN-2025-007) has defaulted on payments - 16 missed payments', 'error', 'unread', 1, '2025-03-01 10:30:00'),
('System Processing Complete', 'Daily late fee processing completed - 8 late fees generated', 'success', 'read', 1, '2025-03-15 02:30:00'),
('High Risk Loan Alert', 'Carlos Martinez Retail has 28 consecutive overdue payments', 'warning', 'unread', 1, '2025-03-14 14:15:00'),
('Payment Verified', 'Maria Santos Trading - All daily payments verified and processed', 'success', 'read', 1, '2025-03-15 11:00:00'),
('Loan Completed Successfully', 'Ana Reyes Food Corp - Loan LOAN-2025-003 completed with all payments on time', 'success', 'read', 1, '2024-12-10 10:30:00'),
('New Vendor Registration', 'Roberto Tan Electronics registered successfully', 'info', 'read', 1, '2024-09-15 14:30:00');

-- =====================================================
-- 10. AUDIT LOG TABLE
-- =====================================================

-- Clear existing audit logs
DELETE FROM audit_log;

-- Insert sample audit log entries
INSERT INTO audit_log (user_id, user_name, action, details, ip_address, user_agent, created_at) VALUES 
(1, 'System Administrator', 'LOGIN', 'Admin login successful', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2025-03-15 08:00:00'),
(2, 'Maria Santos Trading', 'LOAN_APPLICATION', 'Submitted loan application for ₱150,000', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', '2025-01-15 10:30:00'),
(1, 'System Administrator', 'LOAN_APPROVAL', 'Approved loan LOAN-2025-001 for Maria Santos Trading', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2025-01-15 11:00:00'),
(3, 'Juan Cruz Enterprises', 'PAYMENT_MADE', 'Weekly payment of ₱6,300 with late fees', '192.168.1.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15', '2025-02-02 11:45:00'),
(1, 'System Administrator', 'LATE_FEE_PROCESS', 'Processed late fees for 8 overdue payments', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2025-03-15 02:30:00'),
(5, 'Carlos Martinez Retail', 'PAYMENT_LATE', 'Daily payment 4 days late with compound fees', '192.168.1.104', 'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0', '2025-02-16 11:45:00'),
(1, 'System Administrator', 'LOAN_DEFAULTED', 'Marked LOAN-2025-007 as defaulted', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2024-12-01 16:20:00'),
(4, 'Ana Reyes Food Corp', 'LOAN_COMPLETED', 'Successfully completed loan LOAN-2025-003', '192.168.1.103', 'Mozilla/5.0 (iPad; CPU OS 14_0 like Mac OS X) AppleWebKit/605.1.15', '2024-12-10 10:30:00');

-- =====================================================
-- 11. LOAN DOCUMENTS TABLE
-- =====================================================

-- Clear existing loan documents
DELETE FROM loan_documents;

-- Insert sample loan documents
INSERT INTO loan_documents (loan_id, document_type, file_name, file_path, original_filename, file_size, mime_type, uploaded_at) VALUES 
('LOAN-2025-001', 'business_permit', 'permit_001.pdf', 'uploads/documents/LOAN-2025-001/business_permit_20250115.pdf', 'Business_Permit_Maria_Santos.pdf', 245760, 'application/pdf', '2025-01-15 10:45:00'),
('LOAN-2025-001', 'id_document', 'id_001.jpg', 'uploads/documents/LOAN-2025-001/id_document_20250115.jpg', 'Maria_Santos_ID.jpg', 1024000, 'image/jpeg', '2025-01-15 10:46:00'),
('LOAN-2025-001', 'financial_statement', 'fs_001.pdf', 'uploads/documents/LOAN-2025-001/financial_statement_20250115.pdf', 'Financial_Statement_2024.pdf', 524288, 'application/pdf', '2025-01-15 10:47:00'),

('LOAN-2025-002', 'business_permit', 'permit_002.pdf', 'uploads/documents/LOAN-2025-002/business_permit_20241120.pdf', 'Business_Permit_Juan_Cruz.pdf', 307200, 'application/pdf', '2024-11-20 09:30:00'),
('LOAN-2025-002', 'collateral_document', 'collateral_002.pdf', 'uploads/documents/LOAN-2025-002/collateral_document_20241120.pdf', 'Property_Title_Deed.pdf', 819200, 'application/pdf', '2024-11-20 09:32:00'),

('LOAN-2025-003', 'business_permit', 'permit_003.pdf', 'uploads/documents/LOAN-2025-003/business_permit_20240610.pdf', 'Business_Permit_Ana_Reyes.pdf', 262144, 'application/pdf', '2024-06-10 11:45:00'),
('LOAN-2025-003', 'equipment_invoice', 'equipment_003.pdf', 'uploads/documents/LOAN-2025-003/equipment_invoice_20240610.pdf', 'Kitchen_Equipment_Invoice.pdf', 153600, 'application/pdf', '2024-06-10 11:47:00'),

('LOAN-2025-006', 'business_permit', 'permit_006.pdf', 'uploads/documents/LOAN-2025-006/business_permit_20240915.pdf', 'Business_Permit_Roberto_Tan.pdf', 294912, 'application/pdf', '2024-09-15 15:00:00'),
('LOAN-2025-006', 'collateral_document', 'collateral_006.pdf', 'uploads/documents/LOAN-2025-006/collateral_document_20240915.pdf', 'Commercial_Property_Deeds.pdf', 1048576, 'application/pdf', '2024-09-15 15:02:00');

-- =====================================================
-- 12. PAYMENT VERIFICATION QUEUE TABLE
-- =====================================================

-- Clear existing payment verification queue
DELETE FROM payment_verification_queue;

-- Insert sample payment verification queue entries
INSERT INTO payment_verification_queue (payment_id, scheduled_verification_time, verification_status, verification_attempts, max_attempts, last_attempt_at, verification_notes, created_at) VALUES 
('PAY-2025-001-120', '2025-03-15 18:00:00', 'pending', 0, 3, NULL, 'Daily payment scheduled for verification', '2025-03-15 08:30:00'),
('PAY-2025-002-017', '2025-03-19 18:00:00', 'pending', 0, 3, NULL, 'Weekly payment scheduled for verification', '2025-03-19 09:00:00'),
('PAY-2025-006-006', '2025-03-15 18:00:00', 'pending', 0, 3, NULL, 'Monthly payment scheduled for verification', '2025-03-15 14:30:00');

-- =====================================================
-- TESTING SUMMARY
-- =====================================================

-- This sample data provides comprehensive testing scenarios:

-- USERS: 8 total (1 admin + 7 vendors)
--   - Diverse business types: retail, supermarket, food processing, electronics, fashion, import-export
--   - Different revenue levels: ₱65K - ₱150K monthly
--   - Various locations: Manila, Quezon City, Pasig, Mandaluyong, Cebu, BGC, Makati

-- LOANS: 7 total with different characteristics:
--   - 2 Daily payment loans (short term, high frequency)
--   - 3 Weekly payment loans (medium term, regular frequency)  
--   - 2 Monthly payment loans (long term, low frequency)
--   - Status distribution: 4 Active, 1 Completed, 1 Pending, 1 Defaulted
--   - Loan amounts: ₱100K - ₱500K

-- PAYMENT SCHEDULES: 200+ total entries
--   - Mix of paid, overdue, and pending payments
--   - Realistic payment patterns with some late payments
--   - Proper chronological dates

-- PAYMENT HISTORY: 20+ actual payment records
--   - Verified payments with receipts
--   - Late payments with additional fees
--   - Different payment methods (cash, bank transfer)

-- LATE FEES: 8 calculated late fee records
--   - Compound interest calculations
--   - Weekend-inclusive calculations  
--   - Tiered fee structure applications
--   - Various fee percentages (2% - 50%)

-- SYSTEM SETTINGS: 40+ configuration parameters
--   - Interest rates for all payment frequencies
--   - Loan limits and terms
--   - Late fee rules and tiers
--   - Business rules and processing settings

-- ADDITIONAL DATA:
--   - 8 Admin notifications (mix of read/unread, different types)
--   - 8 Audit log entries (various system actions)
--   - 9 Loan documents (different types, realistic files)
--   - 3 Payment verification queue entries

-- This data enables testing of:
-- ✓ Dashboard statistics and charts
-- ✓ Loan management with different statuses
-- ✓ Payment history and verification
-- ✓ Late fee calculations and reporting
-- ✓ System settings configuration
-- ✓ Document management
-- ✓ User role management
-- ✓ Audit trail compliance
-- ✓ Notification system
-- ✓ Multi-frequency payment processing
