-- Create system_settings table for configurable system parameters
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT 'string',
  `description` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default interest rates (using INSERT IGNORE to avoid conflicts)
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('interest_rate_daily', '5.0', 'percentage', 'Interest rate for daily payments'),
('interest_rate_weekly', '4.5', 'percentage', 'Interest rate for weekly payments'),
('interest_rate_monthly', '3.5', 'percentage', 'Interest rate for monthly payments');

-- Alternative: Update existing records if they exist
UPDATE `system_settings` SET 
    `setting_value` = '5.0',
    `setting_type` = 'percentage',
    `description` = 'Interest rate for daily payments',
    `updated_at` = CURRENT_TIMESTAMP
WHERE `setting_key` = 'interest_rate_daily';

UPDATE `system_settings` SET 
    `setting_value` = '4.5',
    `setting_type` = 'percentage',
    `description` = 'Interest rate for weekly payments',
    `updated_at` = CURRENT_TIMESTAMP
WHERE `setting_key` = 'interest_rate_weekly';

UPDATE `system_settings` SET 
    `setting_value` = '3.5',
    `setting_type` = 'percentage',
    `description` = 'Interest rate for monthly payments',
    `updated_at` = CURRENT_TIMESTAMP
WHERE `setting_key` = 'interest_rate_monthly';
