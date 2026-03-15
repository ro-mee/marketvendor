<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';
require_once 'includes/loan_functions.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize LoanManager
$loanManager = new LoanManager();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'submit_loan_application':
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
                
                // Get user_id from session (assuming user is logged in)
                $user_id = $_SESSION['user_id'] ?? 1;
                
                if ($full_name && $email && $phone && $monthly_revenue > 0) {
                    try {
                        $db->beginTransaction();
                        
                        // Generate unique loan ID
                        $loan_id = $loanManager->generateLoanId();
                        
                        // Insert loan application
                        $stmt = $db->prepare("INSERT INTO loans (loan_id, user_id, full_name, email, phone, birthdate, address, civil_status, business_name, business_type, business_address, monthly_revenue, business_description, payment_frequency, loan_amount, loan_purpose, preferred_term, collateral, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP)");
                        $stmt->execute([$loan_id, $user_id, $full_name, $email, $phone, $birthdate, $address, $civil_status, $business_name, $business_type, $business_address, $monthly_revenue, $business_description, $payment_frequency, $loan_amount, $loan_purpose, $preferred_term, $collateral]);
                        
                        $db->commit();
                        $_SESSION['success_message'] = "Loan application submitted successfully! Loan ID: $loan_id";
                    } catch (Exception $e) {
                        $db->rollback();
                        $_SESSION['error_message'] = "Error submitting loan application: " . $e->getMessage();
                    }
                } else {
                    $_SESSION['error_message'] = "Please fill in all required fields correctly.";
                }
                break;
            case 'approve_loan':
                $loan_id = $_POST['loan_id'] ?? '';
                if ($loan_id) {
                    $result = $loanManager->approveLoan($loan_id);
                    if ($result['success']) {
                        $_SESSION['success_message'] = $result['message'];
                    } else {
                        $_SESSION['error_message'] = $result['error'];
                    }
                }
                break;
            case 'reject_loan':
                $loan_id = $_POST['loan_id'] ?? '';
                if ($loan_id) {
                    try {
                        $stmt = $db->prepare("UPDATE loans SET status = 'rejected', updated_at = CURRENT_TIMESTAMP WHERE loan_id = ?");
                        $stmt->execute([$loan_id]);
                        $_SESSION['success_message'] = "Loan rejected successfully!";
                    } catch (Exception $e) {
                        $_SESSION['error_message'] = "Error rejecting loan: " . $e->getMessage();
                    }
                }
                break;
            case 'delete_loan':
                $loan_id = $_POST['loan_id'] ?? '';
                if ($loan_id) {
                    try {
                        $db->beginTransaction();
                        
                        // Delete related payment schedules first
                        $stmt = $db->prepare("DELETE FROM payment_schedules WHERE loan_id = ?");
                        $stmt->execute([$loan_id]);
                        
                        // Delete the loan
                        $stmt = $db->prepare("DELETE FROM loans WHERE loan_id = ?");
                        $stmt->execute([$loan_id]);
                        
                        $db->commit();
                        $_SESSION['success_message'] = "Loan deleted successfully!";
                    } catch (Exception $e) {
                        $db->rollback();
                        $_SESSION['error_message'] = "Error deleting loan: " . $e->getMessage();
                    }
                }
                break;
        }
        header("Location: loan-management.php");
        exit();
    }
}

// Fetch loans data
try {
    // Get search and filter parameters
    $filters = [
        'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
        'status' => isset($_GET['status']) ? $_GET['status'] : '',
        'date_from' => isset($_GET['date_from']) ? $_GET['date_from'] : '',
        'date_to' => isset($_GET['date_to']) ? $_GET['date_to'] : ''
    ];
    
    // Get loans using LoanManager
    $loans = $loanManager->getAllLoans($filters);
    
    // Get statistics using LoanManager
    $stats = $loanManager->getLoanStatistics();
    $total_loans = $stats['total_loans'];
    $pending_loans = $stats['pending_loans'];
    $active_loans = $stats['active_loans'];
    $completed_loans = $stats['completed_loans'];
    $total_active_amount = $stats['total_active_amount'];
    
} catch(PDOException $exception) {
    $error_message = "Error loading loan data.";
    $loans = [];
    $total_loans = 0;
    $pending_loans = 0;
    $active_loans = 0;
    $completed_loans = 0;
    $total_active_amount = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Loan Management - Market Vendor Loan</title>
    <link rel="stylesheet" href="enhanced-styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="responsive-styles-fixed.css">
    <style>
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(30, 41, 59, 0.95);
            border-radius: 12px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .user-details h3 {
            font-size: 16px;
            margin: 0;
            color: #f1f5f9;
        }
        
        .user-details p {
            font-size: 14.4px;
            color: #94a3b8;
            margin: 0;
        }
        
        .logout-btn {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
        }
        
        .content-wrap {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .header {
            margin-bottom: 30px;
        }
        
        .header h2 {
            margin: 0 0 8px 0;
            color: #f1f5f9;
            font-size: 1.5rem;
        }
        
        .header p {
            margin: 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(24, 58, 109, 0.35);
            margin-bottom: 4px;
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            border-radius: 8px;
        }

        .brand-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand-content h1 {
            font-size: 1rem;
            margin-bottom: 4px;
        }

        .brand-content p {
            color: var(--text-300);
            font-size: .8rem;
        }

        .nav-section-title {
            color: var(--text-200);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
            padding: 0 12px;
            border-bottom: 1px solid var(--line);
        }

        .nav-item {
            display: flex !important;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-200) !important;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 500;
            border: 1px solid transparent;
            background: transparent;
        }

        .nav-item:hover {
            background: rgba(59, 130, 246, 0.1) !important;
            border-color: var(--primary) !important;
            color: var(--text-100) !important;
            transform: translateX(4px);
        }

        .nav-item.active {
            background: var(--primary) !important;
            border-color: var(--primary) !important;
            color: white !important;
        }

        .nav-item i {
            font-size: 1rem !important;
            width: 20px;
            text-align: center;
        }

        .message {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .success-message {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }
        
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
        
        .search-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .search-filter input,
        .search-filter select {
            flex: 1;
            min-width: 200px;
            padding: 12px 16px;
            background: rgba(15, 39, 75, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            color: black;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .search-filter select option {
            color: black;
            background: white;
        }
        
        .search-filter input:focus,
        .search-filter select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: rgba(15, 39, 75, 0.8);
        }
        
        .search-filter button {
            padding: 12px 24px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-filter button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 32px;
            margin-bottom: 16px;
            color: #3b82f6;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #e2e8f0;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: var(--text-300);
            font-size: .9rem;
        }
        
        .loans-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        
        /* Loan Modal Styles */
        .loan-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
        
        .loan-modal-content {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid var(--line);
            border-radius: var(--card-radius);
            margin: 2% auto;
            padding: 0;
            width: 95%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .loan-modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(15, 39, 75, 0.5);
        }
        
        .loan-modal-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-100);
            margin: 0;
        }
        
        .loan-modal-close {
            background: none;
            border: none;
            color: var(--text-300);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .loan-modal-close:hover {
            color: var(--text-100);
            background: rgba(255, 255, 255, 0.1);
        }
        
        .loan-modal-body {
            padding: 24px;
        }
        
        .loan-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }
        
        .detail-section {
            background: rgba(15, 39, 75, 0.3);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 16px;
        }
        
        .detail-section h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-100);
            margin: 0 0 16px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--line);
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            gap: 12px;
        }
        
        .detail-row:last-child {
            margin-bottom: 0;
        }
        
        .detail-label {
            font-weight: 500;
            color: var(--text-300);
            min-width: 120px;
            flex-shrink: 0;
        }
        
        .detail-value {
            color: var(--text-100);
            text-align: right;
            word-break: break-word;
        }
        
        .documents-list {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .document-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px;
            margin-bottom: 8px;
            background: rgba(15, 39, 75, 0.5);
            border-radius: 6px;
            transition: background 0.3s ease;
        }
        
        .document-item:hover {
            background: rgba(30, 41, 59, 0.7);
        }
        
        .document-item i {
            color: var(--primary);
            font-size: 1rem;
        }
        
        .document-item a {
            color: var(--text-100);
            text-decoration: none;
            flex: 1;
            font-weight: 500;
        }
        
        .document-item a:hover {
            color: var(--primary);
        }
        
        .document-date {
            color: var(--text-300);
            font-size: 0.8rem;
        }
        
        .loan-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 39, 75, 0.95) 100%);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .loan-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #2563eb, #1d4ed8);
        }
        
        .loan-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
            border-color: rgba(59, 130, 246, 0.3);
        }
        
        .loan-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .loan-id {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .loan-label {
            font-size: 0.75rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .loan-number {
            font-size: 1.1rem;
            font-weight: 700;
            color: #3b82f6;
        }
        
        .loan-borrower {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            padding: 16px;
            background: rgba(15, 39, 75, 0.5);
            border-radius: 12px;
        }
        
        .borrower-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        
        .borrower-info h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #f1f5f9;
            margin: 0 0 8px 0;
        }
        
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: #94a3b8;
        }
        
        .contact-item i {
            width: 16px;
            color: #3b82f6;
        }
        
        .loan-details {
            margin-bottom: 20px;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 12px;
            width: 100%;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px;
            background: rgba(15, 39, 75, 0.35);
            border-radius: 6px;
            text-align: center;
        }
        
        .detail-icon {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(37,99,235,0.2));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3b82f6;
            font-size: 0.75rem;
            flex-shrink: 0;
        }
        
        .detail-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            align-items: center;
            text-align: center;
        }
        
        .detail-label {
            font-size: 0.70rem;
            color: #94a3b8;
        }
        
        .detail-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #f1f5f9;
        }
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    margin-bottom: 12px;
    width: 100%;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    background: rgba(15, 39, 75, 0.35);
    border-radius: 6px;
}

.detail-icon {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(37,99,235,0.2));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    font-size: 0.75rem;
    flex-shrink: 0;
}

.detail-info {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

.detail-label {
    font-size: 0.70rem;
    color: #94a3b8;
}

.detail-value {
    font-size: 0.85rem;
    font-weight: 600;
    color: #f1f5f9;
}
        
        .loan-purpose {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .purpose-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #10b981;
            font-size: 0.85rem;
        }
        
        .purpose-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .purpose-label {
            font-size: 0.75rem;
            color: #94a3b8;
        }
        
        .purpose-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #10b981;
        }
        
        .loan-documents-summary {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 12px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 8px;
            margin-bottom: 16px;
        }
        
        .doc-summary-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.2));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3b82f6;
            font-size: 0.85rem;
        }
        
        .doc-summary-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .doc-summary-label {
            font-size: 0.75rem;
            color: #94a3b8;
        }
        
        .doc-summary-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #3b82f6;
        }
        
        .loan-documents {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 12px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 8px;
            margin-bottom: 16px;
        }
        
        .documents-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ef4444;
            font-size: 0.85rem;
        }
        
        .documents-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .documents-label {
            font-size: 0.75rem;
            color: #94a3b8;
        }
        
        .documents-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #ef4444;
        }
        
        .document-previews {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 8px;
        }
        
        .document-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px;
            background: rgba(15, 39, 75, 0.3);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 6px;
        }
        
        .document-item.document-image {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.2);
        }
        
        .document-item.document-file {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.2);
        }
        
        .doc-thumbnail {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        
        .doc-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(239, 68, 68, 0.2);
            border-radius: 4px;
            color: #ef4444;
            font-size: 1rem;
        }
        
        .doc-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .doc-type {
            font-size: 0.75rem;
            color: #60a5fa;
            font-weight: 600;
        }
        
        .doc-name {
            font-size: 0.8rem;
            color: #cbd5e1;
            word-break: break-all;
        }
        
        .doc-view-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 4px;
            color: #60a5fa;
            text-decoration: none;
            font-size: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .doc-view-btn:hover {
            background: rgba(59, 130, 246, 0.3);
            border-color: rgba(59, 130, 246, 0.5);
        }
        
        .doc-missing {
            font-size: 0.75rem;
            color: #ef4444;
            font-style: italic;
        }
        
        /* Modal Document Styles */
        .modal-document-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        
        .modal-document-item {
            display: flex;
            gap: 12px;
            padding: 16px;
            background: rgba(15, 39, 75, 0.5);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .modal-document-item:hover {
            background: rgba(15, 39, 75, 0.7);
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateY(-2px);
        }
        
        .modal-document-item.modal-document-image {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.3);
        }
        
        .modal-document-item.modal-document-image:hover {
            background: rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.5);
        }
        
        .modal-document-item.modal-document-file {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
        }
        
        .modal-document-item.modal-document-file:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.5);
        }
        
        .modal-doc-preview {
            flex-shrink: 0;
        }
        
        .modal-doc-thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid rgba(148, 163, 184, 0.3);
        }
        
        .modal-doc-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(239, 68, 68, 0.2);
            border: 2px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            color: #ef4444;
            font-size: 2rem;
        }
        
        .modal-doc-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .modal-doc-type {
            font-size: 0.9rem;
            color: #60a5fa;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .modal-doc-name {
            font-size: 0.85rem;
            color: #cbd5e1;
            word-break: break-all;
        }
        
        .modal-doc-date {
            font-size: 0.75rem;
            color: #94a3b8;
        }
        
        .modal-doc-view-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 6px;
            color: #60a5fa;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
            align-self: flex-start;
        }
        
        .modal-doc-view-btn:hover {
            background: rgba(59, 130, 246, 0.3);
            border-color: rgba(59, 130, 246, 0.5);
            transform: translateY(-1px);
        }
        
        .modal-doc-error {
            color: #ef4444;
            font-size: 0.75rem;
            text-align: center;
            padding: 8px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 4px;
            margin-top: 4px;
        }
        
        .loan-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
        }
        
        .view-btn {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.2));
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        
        .view-btn:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), rgba(37, 99, 235, 0.3));
        }
        
        .approve-btn {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .approve-btn:hover {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.3), rgba(5, 150, 105, 0.3));
        }
        
        .reject-btn {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2));
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .reject-btn:hover {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.3), rgba(220, 38, 38, 0.3));
        }
        
        .delete-btn {
            background: linear-gradient(135deg, rgba(107, 114, 128, 0.2), rgba(75, 85, 99, 0.2));
            color: #6b7280;
            border: 1px solid rgba(107, 114, 128, 0.3);
        }
        
        .delete-btn:hover {
            background: linear-gradient(135deg, rgba(107, 114, 128, 0.3), rgba(75, 85, 99, 0.3));
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-block;
        }
        
        .status-badge.pending {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }
        
        .status-badge.active {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .status-badge.completed {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        
        .status-badge.rejected {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .status-badge.defaulted {
            background: rgba(107, 114, 128, 0.2);
            color: #6b7280;
            border: 1px solid rgba(107, 114, 128, 0.3);
        }
        
        .card {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .split-head {
            background: rgba(15, 39, 75, 0.8);
            padding: 20px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .split-head h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #e2e8f0;
            margin: 0;
        }
        
        .split-head button {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .split-head button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        @media (max-width: 768px) {
            .loans-grid {
                grid-template-columns: 1fr;
                padding: 16px;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .loan-actions {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <img src="images/loo.png" alt="Market Vendor Loan Logo" class="brand-logo">
                <div class="brand-content">
                    <h1>Market Vendor Loan</h1>
                    <p>Admin Portal</p>
                </div>
            </div>

            <p class="nav-section-title">Admin Menu</p>
            <a class="nav-item" href="admin-dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a class="nav-item active" href="loan-management.php">
                <i class="fas fa-hand-holding-usd"></i> Loan Management
            </a>
            <a class="nav-item" href="payment-history.php">
                <i class="fas fa-history"></i> Payment History
            </a>
            <a class="nav-item" href="payment-management.php">
                <i class="fas fa-credit-card"></i> Process Payment
            </a>
            <a class="nav-item" href="analytics.php">
                <i class="fas fa-chart-line"></i> Analytics & Reports
            </a>
            <a class="nav-item" href="audit-log.php">
                <i class="fas fa-clipboard-list"></i> Audit Log
            </a>
            <a class="nav-item" href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
        </aside>

        <div class="content-wrap">
            <div class="dashboard-header">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?>
                    </div>
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                        <p>Administrator</p>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <header class="header">
                <div>
                    <h2>Loan Management</h2>
                    <p>Manage loan applications and approvals</p>
                </div>
            </header>

            <?php if (isset($error_message)): ?>
                <div class="message error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="message success-message">
                    <?php echo $_SESSION['success_message']; ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <main>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo number_format($total_loans); ?></div>
                        <div class="stat-label">Total Loans</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo number_format($pending_loans); ?></div>
                        <div class="stat-label">Pending Applications</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo number_format($active_loans); ?></div>
                        <div class="stat-label">Active Loans</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">₱<?php echo number_format($total_active_amount, 2); ?></div>
                        <div class="stat-label">Total Active Amount</div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="split-head">
                        <h3>All Loans (<?php echo count($loans); ?>)</h3>
                        <button type="button" onclick="exportLoans()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                    
                    <form method="GET" class="search-filter">
                        <input type="text" name="search" placeholder="Search by Loan ID, Borrower, or Email..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                        <select name="status">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo (isset($status_filter) && $status_filter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="active" <?php echo (isset($status_filter) && $status_filter === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                            <option value="defaulted">Defaulted</option>
                        </select>
                        <input type="date" name="date_from" placeholder="From Date" value="<?php echo htmlspecialchars($date_from ?? ''); ?>">
                        <input type="date" name="date_to" placeholder="To Date" value="<?php echo htmlspecialchars($date_to ?? ''); ?>">
                        <button type="submit">Filter</button>
                        <button type="button" onclick="clearFilters()">Clear</button>
                    </form>
                    
                    <div class="loans-grid">
                        <?php foreach ($loans as $loan): ?>
                            <div class="loan-card">
                                <div class="loan-header">
                                    <div class="loan-id">
                                        <span class="loan-label">Loan ID</span>
                                        <span class="loan-number"><?php echo htmlspecialchars($loan['loan_id']); ?></span>
                                    </div>
                                    <div class="loan-status">
                                        <span class="status-badge <?php echo $loan['status']; ?>">
                                            <?php echo ucfirst($loan['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="loan-borrower">
                                    <div class="borrower-avatar">
                                        <?php echo strtoupper(substr($loan['full_name'], 0, 2)); ?>
                                    </div>
                                    <div class="borrower-info">
                                        <h4><?php echo htmlspecialchars($loan['full_name']); ?></h4>
                                        <div class="contact-info">
                                            <div class="contact-item">
                                                <i class="fas fa-envelope"></i>
                                                <span><?php echo htmlspecialchars($loan['email'] ?? 'N/A'); ?></span>
                                            </div>
                                            <div class="contact-item">
                                                <i class="fas fa-phone"></i>
                                                <span><?php echo htmlspecialchars($loan['phone'] ?? 'N/A'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="loan-details">
                                    <div class="detail-grid">
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </div>
                                            <div class="detail-info">
                                                <span class="detail-label">Loan Amount</span>
                                                <span class="detail-value">₱<?php echo number_format($loan['loan_amount'], 2); ?></span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="fas fa-percentage"></i>
                                            </div>
                                            <div class="detail-info">
                                                <span class="detail-label">Interest Rate</span>
                                                <span class="detail-value"><?php echo $loan['interest_rate'] ?? '5.0'; ?>%</span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <div class="detail-info">
                                                <span class="detail-label">Term</span>
                                                <span class="detail-value"><?php echo $loan['term_months'] ?? '12'; ?> months</span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="fas fa-calendar"></i>
                                            </div>
                                            <div class="detail-info">
                                                <span class="detail-label">Applied Date</span>
                                                <span class="detail-value"><?php echo date('M d, Y', strtotime($loan['created_at'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="loan-purpose">
                                    <div class="purpose-icon">
                                        <i class="fas fa-bullseye"></i>
                                    </div>
                                    <div class="purpose-text">
                                        <span class="purpose-label">Purpose</span>
                                        <span class="purpose-value"><?php echo htmlspecialchars($loan['loan_purpose'] ?? 'General Loan'); ?></span>
                                    </div>
                                </div>
                                
                                <div class="loan-documents-summary">
                                    <div class="doc-summary-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="doc-summary-text">
                                        <span class="doc-summary-label">Documents</span>
                                        <span class="doc-summary-value">
                                            <?php
                                            // Get document count for this loan
                                            $doc_count_query = "SELECT COUNT(*) as doc_count FROM loan_documents WHERE loan_id = ?";
                                            $doc_count_stmt = $db->prepare($doc_count_query);
                                            $doc_count_stmt->execute([$loan['loan_id']]);
                                            $doc_count = $doc_count_stmt->fetch()['doc_count'];
                                            echo $doc_count > 0 ? $doc_count . ' files' : 'No files';
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="loan-actions">
                                    <button type="button" class="action-btn view-btn" onclick="viewLoanDetails('<?php echo $loan['loan_id']; ?>')">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                    <?php if ($loan['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="approve_loan">
                                            <input type="hidden" name="loan_id" value="<?php echo $loan['loan_id']; ?>">
                                            <button type="submit" class="action-btn approve-btn">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="reject_loan">
                                            <input type="hidden" name="loan_id" value="<?php echo $loan['loan_id']; ?>">
                                            <button type="submit" class="action-btn reject-btn">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display: inline;" id="deleteForm-<?php echo $loan['loan_id']; ?>" onsubmit="return showDeleteConfirm('<?php echo $loan['loan_id']; ?>', '<?php echo str_replace("'", "\'", htmlspecialchars($loan['full_name'], ENT_QUOTES, 'UTF-8')); ?>')">
                                        <input type="hidden" name="action" value="delete_loan">
                                        <input type="hidden" name="loan_id" value="<?php echo $loan['loan_id']; ?>">
                                        <button type="submit" class="action-btn delete-btn">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        function viewLoanDetails(loanId) {
            // View loan details functionality
            alert('Viewing details for loan: ' + loanId);
        }
        
        function showDeleteConfirm(loanId, borrowerName) {
            // Create custom confirmation modal
            const modal = document.createElement('div');
            modal.innerHTML = `
                <div id="deleteConfirmModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 10000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
                    <div style="background: linear-gradient(135deg, rgba(8, 22, 45, 0.98), rgba(15, 39, 75, 0.95)); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 16px; padding: 40px; max-width: 450px; width: 90%; text-align: center; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3); animation: modalSlideIn 0.3s ease-out;">
                        <div style="color: #ef4444; font-size: 4rem; margin-bottom: 25px; animation: pulse 2s infinite;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 style="color: #fff; margin-bottom: 20px; font-size: 1.5rem; font-weight: 700;">Delete Loan Confirmation</h3>
                        <p style="color: #94a3b8; margin-bottom: 15px; font-size: 1rem;">Are you sure you want to permanently delete this loan?</p>
                        <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                            <p style="color: #60a5fa; font-weight: 600; margin: 5px 0; font-size: 0.9rem;">Loan ID: <span style="font-family: monospace;">${loanId}</span></p>
                            <p style="color: #cbd5e1; margin: 5px 0; font-size: 0.9rem;">Borrower: ${borrowerName}</p>
                        </div>
                        <div style="color: #f87171; font-size: 0.95rem; margin-bottom: 30px; padding: 20px; background: rgba(239, 68, 68, 0.1); border-radius: 10px; border: 1px solid rgba(239, 68, 68, 0.2); line-height: 1.5;">
                            <strong style="display: block; margin-bottom: 8px; font-size: 1rem;">⚠️ IRREVERSIBLE ACTION</strong>
                            This will permanently delete the loan and all associated payment schedules and history. This action cannot be undone.
                        </div>
                        <div style="display: flex; gap: 15px; justify-content: center;">
                            <button onclick="closeDeleteModal()" style="background: rgba(148, 163, 184, 0.2); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.3); padding: 12px 24px; border-radius: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                <i class="fas fa-times" style="margin-right: 8px;"></i> Cancel
                            </button>
                            <button onclick="confirmDelete('${loanId}')" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.3), rgba(220, 38, 38, 0.3)); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.4); padding: 12px 24px; border-radius: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                <i class="fas fa-trash" style="margin-right: 8px;"></i> Delete Loan
                            </button>
                        </div>
                    </div>
                </div>
                <style>
                    @keyframes modalSlideIn {
                        from { opacity: 0; transform: scale(0.9); }
                        to { opacity: 1; transform: scale(1); }
                    }
                    @keyframes pulse {
                        0%, 100% { transform: scale(1); }
                        50% { transform: scale(1.1); }
                    }
                </style>
            `;
            document.body.appendChild(modal);
            return false;
        }
        
        function closeDeleteModal() {
            const modal = document.getElementById('deleteConfirmModal');
            if (modal) {
                modal.parentElement.remove();
            }
        }
        
        function confirmDelete(loanId) {
            closeDeleteModal();
            const form = document.getElementById('deleteForm-' + loanId);
            if (form) {
                form.submit();
            }
        }
        
        function exportLoans() {
            // Export loans functionality - create CSV from current filtered results
            let csv = 'Loan ID,Borrower Name,Email,Phone,Loan Amount,Interest Rate,Term,Status,Applied Date\n';
            
            <?php foreach ($loans as $loan): ?>
                csv += '<?php echo addslashes($loan['loan_id']); ?>,';
                csv += '<?php echo addslashes($loan['full_name']); ?>,';
                csv += '<?php echo addslashes($loan['email'] ?? 'N/A'); ?>,';
                csv += '<?php echo addslashes($loan['phone'] ?? 'N/A'); ?>,';
                csv += '<?php echo $loan['loan_amount']; ?>,';
                csv += '<?php echo $loan['interest_rate'] ?? '5.0'; ?>%,';
                csv += '<?php echo $loan['term_months'] ?? '12'; ?> months,';
                csv += '<?php echo ucfirst($loan['status']); ?>,';
                csv += '<?php echo date('M d, Y', strtotime($loan['created_at'])); ?>\n';
            <?php endforeach; ?>
            
            // Create download
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'loans_export_' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
        
        function clearFilters() {
            // Clear all filters and redirect to clean page
            window.location.href = 'loan-management.php';
        }
        
        // Auto-submit search on Enter key
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        document.querySelector('.search-filter').submit();
                    }
                });
            }
        });
        
        function viewLoanDetails(loanId) {
            console.log('Loading details for loan:', loanId);
            
            // Fetch loan details via AJAX
            fetch('get_loan_details.php?loan_id=' + encodeURIComponent(loanId))
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received data:', data);
                    if (data.success) {
                        // Populate modal with loan details
                        document.getElementById('modal_loan_id').textContent = data.loan.loan_id || 'N/A';
                        document.getElementById('modal_full_name').textContent = data.loan.full_name || 'N/A';
                        document.getElementById('modal_email').textContent = data.loan.email || 'N/A';
                        document.getElementById('modal_phone').textContent = data.loan.phone || 'N/A';
                        document.getElementById('modal_birthdate').textContent = data.loan.birthdate || 'Not specified';
                        document.getElementById('modal_address').textContent = data.loan.address || 'N/A';
                        document.getElementById('modal_civil_status').textContent = data.loan.civil_status || 'Not specified';
                        document.getElementById('modal_business_name').textContent = data.loan.business_name || 'Not specified';
                        document.getElementById('modal_business_type').textContent = data.loan.business_type || 'Not specified';
                        document.getElementById('modal_business_address').textContent = data.loan.business_address || 'Not specified';
                        document.getElementById('modal_monthly_revenue').textContent = '₱' + parseFloat(data.loan.monthly_revenue || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
                        document.getElementById('modal_business_description').textContent = data.loan.business_description || 'Not specified';
                        document.getElementById('modal_loan_amount').textContent = '₱' + parseFloat(data.loan.loan_amount || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
                        document.getElementById('modal_loan_purpose').textContent = data.loan.loan_purpose || 'Not specified';
                        document.getElementById('modal_preferred_term').textContent = data.loan.preferred_term || 'Not specified';
                        document.getElementById('modal_collateral').textContent = data.loan.collateral || 'Not specified';
                        document.getElementById('modal_status').textContent = data.loan.status || 'N/A';
                        document.getElementById('modal_created_at').textContent = data.loan.created_at ? new Date(data.loan.created_at).toLocaleString() : 'N/A';
                        
                        // Display documents if available
                        let documentsHtml = '';
                        if (data.documents && data.documents.length > 0) {
                            documentsHtml = '<div class="modal-document-grid">';
                            data.documents.forEach(doc => {
                                const isImage = doc.mime_type && (doc.mime_type.includes('image/jpeg') || doc.mime_type.includes('image/jpg') || doc.mime_type.includes('image/png'));
                                const docClass = isImage ? 'modal-document-image' : 'modal-document-file';
                                
                                if (isImage) {
                                    // Try multiple path formats to find the correct file
                                    const possiblePaths = [
                                        `uploads/loan_documents/${doc.file_path.split('/').slice(-2).join('/')}`,
                                        doc.file_path.replace(/^\/+/, ''),
                                        `uploads/${doc.file_path.replace(/^uploads\//, '')}`,
                                        doc.file_path
                                    ];
                                    
                                    // Find the first working path
                                    let workingUrl = `http://localhost/marketvendor/${doc.file_path.replace(/^\/+/, '')}`;
                                    
                                    documentsHtml += `
                                        <div class="modal-document-item ${docClass}">
                                            <div class="modal-doc-preview">
                                                <img src="${workingUrl}" alt="${doc.document_type}" class="modal-doc-thumbnail" 
                                                     onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\\'modal-doc-icon\\'><i class=\\'fas fa-file-image\\'></i></div><div class=\\'modal-doc-error\\'>Image not found<br><small>Path: ${doc.file_path}</small></div>';">
                                            </div>
                                            <div class="modal-doc-info">
                                                <div class="modal-doc-type">${doc.document_type.replace('_', ' ').toUpperCase()}</div>
                                                <div class="modal-doc-name">${doc.file_name || 'Unknown file'}</div>
                                                <div class="modal-doc-date">Uploaded: ${doc.uploaded_at ? new Date(doc.uploaded_at).toLocaleDateString() : 'N/A'}</div>
                                                <a href="${workingUrl}" target="_blank" class="modal-doc-view-btn">
                                                    <i class="fas fa-eye"></i> View Full Size
                                                </a>
                                            </div>
                                        </div>
                                    `;
                                } else {
                                    const cleanPath = doc.file_path.replace(/^\/+/, '').replace(/^uploads\//, 'uploads/');
                                    const fullUrl = `http://localhost/marketvendor/${cleanPath}`;
                                    
                                    documentsHtml += `
                                        <div class="modal-document-item ${docClass}">
                                            <div class="modal-doc-preview">
                                                <div class="modal-doc-icon">
                                                    <i class="fas fa-file-pdf"></i>
                                                </div>
                                            </div>
                                            <div class="modal-doc-info">
                                                <div class="modal-doc-type">${doc.document_type.replace('_', ' ').toUpperCase()}</div>
                                                <div class="modal-doc-name">${doc.file_name || 'Unknown file'}</div>
                                                <div class="modal-doc-date">Uploaded: ${doc.uploaded_at ? new Date(doc.uploaded_at).toLocaleDateString() : 'N/A'}</div>
                                                <a href="${fullUrl}" target="_blank" class="modal-doc-view-btn">
                                                    <i class="fas fa-eye"></i> View Document
                                                </a>
                                            </div>
                                        </div>
                                    `;
                                }
                            });
                            documentsHtml += '</div>';
                        } else {
                            documentsHtml = '<p style="color: #94a3b8; text-align: center; padding: 20px;">No documents uploaded</p>';
                        }
                        document.getElementById('modal_documents').innerHTML = documentsHtml;
                        
                        // Show modal
                        document.getElementById('loanDetailsModal').style.display = 'block';
                    } else {
                        alert('Error loading loan details: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error loading loan details: ' + error.message);
                });
        }
        
        function closeLoanModal() {
            document.getElementById('loanDetailsModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('loanDetailsModal');
            if (event.target == modal) {
                closeLoanModal();
            }
        }
    </script>
    <script src="responsive-script.js"></script>
    
    <!-- Loan Details Modal -->
    <div id="loanDetailsModal" class="loan-modal">
        <div class="loan-modal-content">
            <div class="loan-modal-header">
                <h3>Loan Application Details</h3>
                <button class="loan-modal-close" onclick="closeLoanModal()">&times;</button>
            </div>
            <div class="loan-modal-body">
                <div class="loan-details-grid">
                    <div class="detail-section">
                        <h4>Personal Information</h4>
                        <div class="detail-row">
                            <span class="detail-label">Loan ID:</span>
                            <span class="detail-value" id="modal_loan_id"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Full Name:</span>
                            <span class="detail-value" id="modal_full_name"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value" id="modal_email"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value" id="modal_phone"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Birthdate:</span>
                            <span class="detail-value" id="modal_birthdate"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Address:</span>
                            <span class="detail-value" id="modal_address"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Civil Status:</span>
                            <span class="detail-value" id="modal_civil_status"></span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Business Information</h4>
                        <div class="detail-row">
                            <span class="detail-label">Business Name:</span>
                            <span class="detail-value" id="modal_business_name"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Business Type:</span>
                            <span class="detail-value" id="modal_business_type"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Business Address:</span>
                            <span class="detail-value" id="modal_business_address"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Monthly Revenue:</span>
                            <span class="detail-value" id="modal_monthly_revenue"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Business Description:</span>
                            <span class="detail-value" id="modal_business_description"></span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Loan Details</h4>
                        <div class="detail-row">
                            <span class="detail-label">Loan Amount:</span>
                            <span class="detail-value" id="modal_loan_amount"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Loan Purpose:</span>
                            <span class="detail-value" id="modal_loan_purpose"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Preferred Term:</span>
                            <span class="detail-value" id="modal_preferred_term"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Collateral:</span>
                            <span class="detail-value" id="modal_collateral"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value" id="modal_status"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Applied Date:</span>
                            <span class="detail-value" id="modal_created_at"></span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Uploaded Documents</h4>
                        <div id="modal_documents" class="documents-list">
                            <!-- Documents will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
