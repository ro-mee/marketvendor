-- Create loan_documents table for storing uploaded loan documents
CREATE TABLE IF NOT EXISTS `loan_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` varchar(50) NOT NULL,
  `document_type` varchar(100) NOT NULL,
  `file_path` text NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_loan_id` (`loan_id`),
  KEY `idx_document_type` (`document_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: Foreign key constraint removed to avoid creation errors
-- You can add it later if needed after verifying the loans table structure:
-- ALTER TABLE `loan_documents` 
-- ADD CONSTRAINT `fk_loan_documents_loan_id` 
-- FOREIGN KEY (`loan_id`) REFERENCES `loans` (`loan_id`) 
-- ON DELETE CASCADE ON UPDATE CASCADE;
