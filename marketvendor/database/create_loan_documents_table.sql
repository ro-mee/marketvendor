-- Loan Documents Table
CREATE TABLE IF NOT EXISTS loan_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id VARCHAR(50) NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255),
    file_size INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES loans(loan_id) ON DELETE CASCADE,
    INDEX idx_loan_id (loan_id),
    INDEX idx_document_type (document_type)
);
