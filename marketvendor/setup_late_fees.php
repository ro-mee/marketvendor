<?php
/**
 * Late Fee Database Setup Script
 * Run this script to create the late fee tables
 */

require_once 'config/database.php';

echo "Setting up Late Fee System Database Tables...\n";
echo "================================================\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Read and execute the SQL file
    $sql_file = __DIR__ . '/database/create_late_fees_table.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: " . $sql_file);
    }
    
    $sql = file_get_contents($sql_file);
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            echo "Executing: " . substr($statement, 0, 50) . "...\n";
            $db->exec($statement);
        }
    }
    
    echo "\n✅ All late fee tables created successfully!\n";
    echo "================================================\n";
    echo "Tables created:\n";
    echo "- late_fee_settings\n";
    echo "- late_fees\n";
    echo "- late_fee_tiers\n";
    echo "- late_fee_notifications\n";
    echo "\nDefault settings and tiers have been inserted.\n";
    echo "You can now access the Late Fees Management at: late-fees.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Please check your database connection and permissions.\n";
    exit(1);
}

echo "\nSetup completed successfully!\n";
?>
