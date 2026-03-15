<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$client_id = $_GET['client_id'] ?? '';

if (empty($client_id)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Client ID required']);
    exit();
}

try {
    // Get client's pending payments
    $payments_sql = "SELECT ps.*, l.loan_amount, l.loan_purpose, l.loan_amount
                    FROM payment_schedules ps 
                    LEFT JOIN loans l ON ps.loan_id = l.loan_id 
                    LEFT JOIN users u ON l.user_id = u.id 
                    WHERE u.id = ? AND ps.status = 'pending'
                    ORDER BY ps.due_date ASC";
    
    $stmt = $db->prepare($payments_sql);
    $stmt->execute([$client_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get client info
    $client_sql = "SELECT name, email FROM users WHERE id = ?";
    $client_stmt = $db->prepare($client_sql);
    $client_stmt->execute([$client_id]);
    $client = $client_stmt->fetch(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'client' => $client,
        'payments' => $payments
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
