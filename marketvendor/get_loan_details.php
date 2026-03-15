<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once 'config/database.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

if (!isset($_GET['loan_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Loan ID parameter is required']);
    exit();
}

$loan_id = trim($_GET['loan_id']);

if (empty($loan_id)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Loan ID cannot be empty']);
    exit();
}

try {
    // Fetch loan details
    $stmt = $db->prepare("SELECT * FROM loans WHERE loan_id = ?");
    $stmt->execute([$loan_id]);
    $loan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$loan) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Loan not found with ID: ' . $loan_id]);
        exit();
    }
    
    // Fetch uploaded documents for this loan
    $documents = [];
    try {
        $stmt = $db->prepare("SELECT * FROM loan_documents WHERE loan_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$loan_id]);
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format document file paths - use the actual file_path from database
        foreach ($documents as &$doc) {
            // Ensure the path has the correct format
            $doc['file_path'] = ltrim($doc['file_path'], '/');
        }
    } catch (Exception $docEx) {
        // If documents table doesn't exist or has error, continue without documents
        $documents = [];
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'loan' => $loan,
        'documents' => $documents
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'debug' => [
            'loan_id' => $loan_id,
            'error_line' => $e->getLine(),
            'error_file' => $e->getFile()
        ]
    ]);
}
?>
