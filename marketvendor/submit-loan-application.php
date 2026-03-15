<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once 'config/database.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Handle loan application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_loan_application') {
    
    // Debug: Log received data
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
    
    // Check database connection
    if (!$db) {
        echo "error|Database connection failed";
        exit;
    }
    
    // Get form data
    $full_name = $_POST['fullName'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $address = $_POST['address'] ?? '';
    $civil_status = $_POST['civilStatus'] ?? '';
    $business_name = $_POST['businessName'] ?? '';
    $business_type = $_POST['businessType'] ?? '';
    $business_address = $_POST['businessAddress'] ?? '';
    $monthly_revenue = $_POST['monthlyRevenue'] ?? 0;
    $business_description = $_POST['businessDescription'] ?? '';
    $payment_frequency = $_POST['paymentFrequency'] ?? '';
    $custom_loan_amount = $_POST['customLoanAmount'] ?? 0;
    $loan_amount = $_POST['loanAmount'] ?? 0;
    $loan_purpose = $_POST['loanPurpose'] ?? '';
    $preferred_term = $_POST['preferredTerm'] ?? '';
    $collateral = $_POST['collateral'] ?? '';
    
    // Use custom loan amount if provided, otherwise use 0 (will be calculated)
    $final_loan_amount = $custom_loan_amount > 0 ? $custom_loan_amount : $loan_amount;
    
    // Validate required fields
    if ($full_name && $email && $phone && $monthly_revenue > 0 && $final_loan_amount > 0) {
        try {
            echo "Starting loan submission process...<br>";
            
            $db->beginTransaction();
            echo "Transaction started...<br>";
            
            // Generate unique loan ID first
            $loan_id = 'LOAN-' . date('Y') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
            echo "Generated loan_id: $loan_id<br>";
            
            // Insert loan application
            $sql = "INSERT INTO loans (id, loan_id, user_id, full_name, email, phone, birthdate, address, civil_status, business_name, business_type, business_address, monthly_revenue, business_description, payment_frequency, custom_loan_amount, loan_amount, loan_purpose, preferred_term, collateral, status, created_at, updated_at) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            
            echo "Preparing SQL...<br>";
            echo "SQL: " . $sql . "<br>";
            echo "Column count: " . substr_count($sql, ',') + 1 . " columns<br>";
            
            $stmt = $db->prepare($sql);
            
            if (!$stmt) {
                echo "SQL PREPARE FAILED: " . print_r($db->errorInfo(), true) . "<br>";
                throw new Exception("SQL prepare failed");
            }
            
            echo "SQL prepared successfully...<br>";
            
            $params = [$loan_id, 1, $full_name, $email, $phone, $birthdate, $address, $civil_status, $business_name, $business_type, $business_address, $monthly_revenue, $business_description, $payment_frequency, $custom_loan_amount, $final_loan_amount, $loan_purpose, $preferred_term, $collateral, 'pending'];
            echo "Parameter count: " . count($params) . " parameters<br>";
            echo "Executing with: loan_id=$loan_id, user_id=1, name=$full_name, email=$email<br>";
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                echo "SQL EXECUTE FAILED: " . print_r($stmt->errorInfo(), true) . "<br>";
                throw new Exception("SQL execute failed: " . implode(", ", $stmt->errorInfo()));
            }
            
            echo "SQL executed successfully!<br>";
            
            $inserted_loan_id = $db->lastInsertId();
            echo "Inserted loan ID: $inserted_loan_id<br>";
            
            // Handle file upload
            if (isset($_FILES['requirementsFile']) && $_FILES['requirementsFile']['error'] === UPLOAD_ERR_OK) {
                echo "Processing file upload...<br>";
                $file = $_FILES['requirementsFile'];
                $file_name = time() . '_' . $file['name'];
                $upload_path = 'uploads/documents/' . $file_name;
                
                if (!is_dir('uploads/documents')) {
                    mkdir('uploads/documents', 0777, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    echo "File uploaded to: $upload_path<br>";
                    $stmt = $db->prepare("INSERT INTO loan_documents (id, loan_id, document_type, file_name, file_path, uploaded_at) VALUES (NULL, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
                    $document_type = 'requirements';
                    $stmt->execute([$inserted_loan_id, $document_type, $file_name, $upload_path]);
                    echo "File info saved to database<br>";
                } else {
                    echo "File upload failed<br>";
                }
            }
            
            echo "Committing transaction...<br>";
            $db->commit();
            echo "Transaction committed!<br>";
            
            // Return success response
            echo "success|Loan application submitted successfully!";
            
        } catch (Exception $e) {
            $db->rollback();
            error_log("Loan submission error: " . $e->getMessage());
            echo "error|Error submitting loan application: " . $e->getMessage();
        }
    } else {
        echo "error|Please fill in all required fields correctly.";
    }
} else {
    echo "error|Invalid request method.";
}
?>
