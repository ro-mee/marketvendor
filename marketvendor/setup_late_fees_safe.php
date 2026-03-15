<?php
/**
 * Safe Late Fee Database Setup Script
 * Handles existing tables gracefully
 */

require_once 'config/database.php';

echo "Setting up Late Fee System Database Tables...\n";
echo "================================================\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if tables already exist
    $existing_tables = [];
    $stmt = $db->query("SHOW TABLES LIKE 'late_fee%'");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $existing_tables[] = $row[0];
    }
    
    echo "Checking existing tables...\n";
    foreach ($existing_tables as $table) {
        echo "- Found: $table\n";
    }
    
    if (!in_array('late_fee_settings', $existing_tables)) {
        echo "Creating late_fee_settings table...\n";
        $db->exec("
            CREATE TABLE late_fee_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fee_type ENUM('percentage', 'fixed', 'tiered') DEFAULT 'percentage',
                percentage_rate DECIMAL(5,2) DEFAULT 5.00,
                fixed_amount DECIMAL(10,2) DEFAULT 100.00,
                grace_period_days INT DEFAULT 3,
                max_fee_percentage DECIMAL(5,2) DEFAULT 25.00,
                compound_daily BOOLEAN DEFAULT FALSE,
                apply_weekends BOOLEAN DEFAULT TRUE,
                min_fee_amount DECIMAL(10,2) DEFAULT 50.00,
                description TEXT,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    } else {
        echo "- late_fee_settings already exists, skipping...\n";
    }
    
    if (!in_array('late_fees', $existing_tables)) {
        echo "Creating late_fees table...\n";
        $db->exec("
            CREATE TABLE late_fees (
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
            )
        ");
    } else {
        echo "- late_fees already exists, checking structure...\n";
        
        // Check if we need to add missing columns
        $columns = $db->query("SHOW COLUMNS FROM late_fees")->fetchAll(PDO::FETCH_ASSOC);
        $column_names = array_map(function($col) { return $col['Field']; }, $columns);
        
        if (!in_array('calculation_details', $column_names)) {
            echo "Adding calculation_details column...\n";
            $db->exec("ALTER TABLE late_fees ADD COLUMN calculation_details JSON");
        }
    }
    
    if (!in_array('late_fee_tiers', $existing_tables)) {
        echo "Creating late_fee_tiers table...\n";
        $db->exec("
            CREATE TABLE late_fee_tiers (
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
            )
        ");
    } else {
        echo "- late_fee_tiers already exists, skipping...\n";
    }
    
    if (!in_array('late_fee_notifications', $existing_tables)) {
        echo "Creating late_fee_notifications table...\n";
        $db->exec("
            CREATE TABLE late_fee_notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                loan_id VARCHAR(50) NOT NULL,
                payment_schedule_id INT NOT NULL,
                fee_id INT NOT NULL,
                notification_type ENUM('assessment', 'application', 'waiver', 'reminder') DEFAULT 'assessment',
                recipient_email VARCHAR(255) NOT NULL,
                recipient_name VARCHAR(255) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
                error_message TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_loan_id (loan_id),
                INDEX idx_fee_id (fee_id),
                INDEX idx_status (status)
            )
        ");
    } else {
        echo "- late_fee_notifications already exists, skipping...\n";
    }
    
    // Insert default settings if table is empty
    $stmt = $db->query("SELECT COUNT(*) FROM late_fee_settings");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "Inserting default settings...\n";
        $db->exec("
            INSERT INTO late_fee_settings 
            (fee_type, percentage_rate, fixed_amount, grace_period_days, max_fee_percentage, compound_daily, apply_weekends, min_fee_amount, description) 
            VALUES 
            ('percentage', 5.00, 100.00, 3, 25.00, FALSE, TRUE, 50.00, 'Default late fee configuration')
        ");
    } else {
        echo "- Settings already exist, skipping defaults...\n";
    }
    
    // Insert default tiers if table is empty
    $stmt = $db->query("SELECT COUNT(*) FROM late_fee_tiers");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "Inserting default tiers...\n";
        $db->exec("
            INSERT INTO late_fee_tiers 
            (days_from, days_to, fee_type, fee_value, max_fee_amount) VALUES 
            (1, 7, 'percentage', 2.00, 500.00),
            (8, 14, 'percentage', 3.00, 1000.00),
            (15, 30, 'percentage', 5.00, 2500.00),
            (31, 60, 'percentage', 7.50, 5000.00),
            (61, 999, 'percentage', 10.00, 10000.00)
        ");
    } else {
        echo "- Tiers already exist, skipping defaults...\n";
    }
    
    echo "\n✅ Late Fee System setup completed successfully!\n";
    echo "================================================\n";
    echo "Status:\n";
    foreach ($existing_tables as $table) {
        echo "✅ $table - Ready\n";
    }
    
    echo "\nYou can now manage late fees in Settings page!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Please check your database connection and permissions.\n";
    echo "\nDebug Info:\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Error File: " . $e->getFile() . "\n";
    echo "Error Line: " . $e->getLine() . "\n";
    exit(1);
}

echo "\nSetup completed successfully!\n";
?>
