<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if user has existing pending/active loan
        $check_loan_sql = "SELECT loan_id, status FROM loans WHERE user_id = ? AND status IN ('pending', 'approved', 'active') ORDER BY created_at DESC LIMIT 1";
        $check_stmt = $db->prepare($check_loan_sql);
        $check_stmt->execute([$user_id]);
        $existing_loan = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_loan) {
            $error_message = "You already have a {$existing_loan['status']} loan (Loan ID: {$existing_loan['loan_id']}). You cannot apply for a new loan until your current loan is completed or rejected.";
        } else {
            // Generate unique loan ID
            $loan_id = 'LOAN' . date('Y') . str_pad($user_id, 4, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Get form data
            $full_name = $_POST['full_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $birthdate = $_POST['birthdate'] ?? '';
            $address = $_POST['address'] ?? '';
            $civil_status = $_POST['civil_status'] ?? '';
            $business_name = $_POST['business_name'] ?? '';
            $business_type = $_POST['business_type'] ?? '';
            $business_address = $_POST['business_address'] ?? '';
            $monthly_revenue = $_POST['monthly_revenue'] ?? 0;
            $business_description = $_POST['business_description'] ?? '';
            $payment_frequency = $_POST['payment_frequency'] ?? '';
            $loan_amount = $_POST['loan_amount'] ?? 0;
            $loan_purpose = $_POST['loan_purpose'] ?? '';
            $preferred_term = $_POST['preferred_term'] ?? '';
            $collateral = $_POST['collateral'] ?? '';
            
            // Handle file uploads
            $uploaded_files = [];
            $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
            $max_size = 5 * 1024 * 1024; // 5MB per file
            
            $required_documents = [
                'business_permit' => 'Business Permit',
                'government_id' => 'Valid Government ID',
                'proof_of_income' => 'Proof of Income',
                'proof_of_address' => 'Proof of Address'
            ];
            
            foreach ($required_documents as $field => $document_name) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
                    $file = $_FILES[$field];
                    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    
                    if (!in_array($file_ext, $allowed_types)) {
                        $error_message = "Invalid file type for $document_name. Only PDF, JPG, JPEG, and PNG files are allowed.";
                        break;
                    }
                    
                    if ($file['size'] > $max_size) {
                        $error_message = "File size too large for $document_name. Maximum size is 5MB.";
                        break;
                    }
                    
                    // Create uploads directory if it doesn't exist
                    $upload_dir = 'uploads/loan_documents/' . date('Y/m/');
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    // Generate unique filename
                    $filename = $loan_id . '_' . $field . '_' . time() . '.' . $file_ext;
                    $filepath = $upload_dir . $filename;
                    $full_filepath = $upload_dir . $filename; // Store relative path for database with extension
                    
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $uploaded_files[$field] = $full_filepath;
                    } else {
                        $error_message = "Failed to upload $document_name. Please try again.";
                        break;
                    }
                } else {
                    $error_message = "$document_name is required. Please upload the file.";
                    break;
                }
            }
            
            // Only proceed if no file upload errors
            if (!$error_message) {
                // Insert loan application
                $sql = "INSERT INTO loans (loan_id, user_id, full_name, email, phone, birthdate, address, civil_status, business_name, business_type, business_address, monthly_revenue, business_description, payment_frequency, custom_loan_amount, loan_amount, loan_purpose, preferred_term, collateral, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
                
                $stmt = $db->prepare($sql);
                $result = $stmt->execute([$loan_id, $user_id, $full_name, $email, $phone, $birthdate, $address, $civil_status, $business_name, $business_type, $business_address, $monthly_revenue, $business_description, $payment_frequency, 0, $loan_amount, $loan_purpose, $preferred_term, $collateral, 'pending']);
                
                if ($result) {
                    // Save file information to database
                    if (!empty($uploaded_files)) {
                        foreach ($uploaded_files as $field => $filepath) {
                            $file_sql = "INSERT INTO loan_documents (loan_id, document_type, file_path, file_name, file_size, mime_type, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
                            $file_stmt = $db->prepare($file_sql);
                            
                            // Get file info
                            $file = $_FILES[$field];
                            $file_name = $file['name'];
                            $file_size = $file['size'];
                            $mime_type = $file['type'];
                            
                            $result = $file_stmt->execute([$loan_id, $field, $filepath, $file_name, $file_size, $mime_type]);
                            
                            if (!$result) {
                                $error_message = "Failed to save $document_name information to database. Please try again.";
                                break;
                            }
                        }
                        
                        if (!isset($error_message)) {
                            $success_message .= " Documents uploaded successfully!";
                        }
                    } else {
                        $success_message .= " Note: No documents were uploaded.";
                    }
                    
                    // Store submission status in session
                    $_SESSION['last_submitted_loan'] = [
                        'loan_id' => $loan_id,
                        'submitted_at' => date('Y-m-d H:i:s')
                    ];
                    
                    // Redirect to avoid form resubmission and show status
                    header("Location: apply-loan.php?status=submitted&loan_id=" . urlencode($loan_id));
                    exit();
                } else {
                    $error_message = "Failed to submit loan application. Please try again.";
                }
            }
        }
        
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Check if user has a pending or active loan
$check_loan_sql = "SELECT loan_id, status FROM loans WHERE user_id = ? AND status IN ('pending', 'approved', 'active') ORDER BY created_at DESC LIMIT 1";
$check_stmt = $db->prepare($check_loan_sql);
$check_stmt->execute([$user_id]);
$existing_loan = $check_stmt->fetch(PDO::FETCH_ASSOC);

// Handle clear status request
if (isset($_GET['clear_status']) && $_GET['clear_status'] == 1) {
    unset($_SESSION['last_submitted_loan']);
    header("Location: apply-loan.php");
    exit();
}

// Check if application was just submitted or if there's a recent submission in session
$show_waiting_status = false;
$submitted_loan_id = '';

if (isset($_GET['status']) && $_GET['status'] === 'submitted' && isset($_GET['loan_id'])) {
    $submitted_loan_id = htmlspecialchars($_GET['loan_id']);
    $show_waiting_status = true;
} elseif (isset($_SESSION['last_submitted_loan'])) {
    // Check if the submission was within the last 30 minutes
    $submitted_time = strtotime($_SESSION['last_submitted_loan']['submitted_at']);
    $current_time = time();
    $time_diff = ($current_time - $submitted_time) / 60; // Convert to minutes
    
    if ($time_diff <= 30) {
        $submitted_loan_id = htmlspecialchars($_SESSION['last_submitted_loan']['loan_id']);
        $show_waiting_status = true;
    } else {
        // Clear old submission from session
        unset($_SESSION['last_submitted_loan']);
    }
}

// Get user info for pre-filling
$stmt = $db->prepare("SELECT name, email, phone, address FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Loan - BlueLedger Finance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="enhanced-styles.css">
    <link rel="stylesheet" href="responsive-styles-fixed.css">
    <style>
        .form-container {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid var(--line);
            border-radius: var(--card-radius);
            padding: 32px;
            margin-bottom: 24px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--line);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-100);
        }

        .form-section {
            margin-bottom: 32px;
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 24px;
            backdrop-filter: blur(4px);
        }

        .form-section h3 {
            color: var(--text-100);
            font-size: 1.125rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-200);
            font-size: 0.875rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            background: rgba(15, 39, 75, 0.5);
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--text-100);
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--success);
            box-shadow: 0 0 0 3px rgba(46, 122, 214, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .required {
            color: #ef4444;
        }

        .document-requirements {
            margin-top: 20px;
        }

        .requirements-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .requirements-info h4 {
            color: var(--text-100);
            font-size: 1rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .requirements-info ul {
            list-style: none;
            padding: 0;
            margin: 0 0 16px 0;
        }

        .requirements-info li {
            color: var(--text-200);
            font-size: 0.9rem;
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
            line-height: 1.5;
        }

        .requirements-info li::before {
            content: "📄";
            position: absolute;
            left: 0;
        }

        .file-info {
            color: var(--text-300);
            font-size: 0.85rem;
            margin: 0;
            padding: 8px 12px;
            background: rgba(15, 39, 75, 0.5);
            border-radius: 8px;
            border-left: 3px solid #3b82f6;
        }

        input[type="file"] {
            width: 100%;
            padding: 12px 16px;
            background: rgba(15, 39, 75, 0.5);
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--text-100);
            font-size: 0.875rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        input[type="file"]:focus {
            outline: none;
            border-color: var(--success);
            box-shadow: 0 0 0 3px rgba(46, 122, 214, 0.1);
        }

        input[type="file"]::file-selector-button {
            background: var(--success);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            margin-right: 12px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        input[type="file"]::file-selector-button:hover {
            background: #2563eb;
        }

        .form-group small {
            display: block;
            margin-top: 6px;
            color: var(--text-300);
            font-size: 0.8rem;
        }

        .approval-status-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 60vh;
        }

        .status-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 39, 75, 0.95) 100%);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .status-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #2563eb, #1d4ed8);
        }

        .status-icon {
            font-size: 4rem;
            margin-bottom: 24px;
            animation: pulse 2s infinite;
        }

        .status-icon.existing {
            color: #f59e0b;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .status-info {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
            text-align: left;
        }

        .status-info h4 {
            color: var(--text-100);
            font-size: 1.1rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .status-details {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: rgba(15, 39, 75, 0.5);
            border-radius: 8px;
        }

        .detail-item strong {
            color: var(--text-100);
            font-weight: 600;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 16px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border: 1px solid;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            width: fit-content;
            min-width: auto;
            max-width: none;
            flex-shrink: 0;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: default;
            user-select: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .status-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .status-badge:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .status-badge:hover::before {
            opacity: 1;
        }

        .status-badge:active {
            transform: translateY(0) scale(0.98);
            transition: transform 0.1s ease;
        }

        .status-pending {
            background: linear-gradient(135deg, rgba(251, 146, 60, 0.25), rgba(251, 146, 60, 0.15));
            color: #ea580c;
            border-color: rgba(251, 146, 60, 0.4);
            box-shadow: 0 2px 8px rgba(251, 146, 60, 0.2);
        }

        .status-pending:hover {
            background: linear-gradient(135deg, rgba(251, 146, 60, 0.35), rgba(251, 146, 60, 0.25));
            box-shadow: 0 4px 16px rgba(251, 146, 60, 0.3);
        }

        .status-approved {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.25), rgba(34, 197, 94, 0.15));
            color: #16a34a;
            border-color: rgba(34, 197, 94, 0.4);
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.2);
        }

        .status-approved:hover {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.35), rgba(34, 197, 94, 0.25));
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.3);
        }

        .status-active {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.25), rgba(34, 197, 94, 0.15));
            color: #16a34a;
            border-color: rgba(34, 197, 94, 0.4);
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.2);
        }

        .status-active:hover {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.35), rgba(34, 197, 94, 0.25));
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.3);
        }

        .status-content h2 {
            font-size: 2rem;
            color: var(--text-100);
            margin-bottom: 16px;
            font-weight: 700;
        }

        .loan-id {
            font-size: 1.2rem;
            color: #3b82f6;
            margin-bottom: 16px;
            font-weight: 600;
        }

        .status-message {
            color: var(--text-200);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .timeline-info {
            background: rgba(15, 39, 75, 0.5);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
            text-align: left;
        }

        .timeline-info h4 {
            color: var(--text-100);
            font-size: 1.1rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .timeline-steps {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            position: relative;
        }

        .timeline-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 30px;
            right: 30px;
            height: 2px;
            background: var(--line);
            z-index: 0;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 1rem;
            font-weight: bold;
        }

        .step.completed .step-icon {
            background: #10b981;
            color: white;
        }

        .step.current .step-icon {
            background: #3b82f6;
            color: white;
        }

        .step.pending .step-icon {
            background: var(--line);
            color: var(--text-300);
        }

        .step-content strong {
            display: block;
            color: var(--text-100);
            font-size: 0.9rem;
            margin-bottom: 4px;
        }

        .step-content p {
            color: var(--text-300);
            font-size: 0.8rem;
            margin: 0;
        }

        .action-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .action-buttons .btn-primary,
        .action-buttons .btn-secondary {
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .action-buttons .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
        }

        .action-buttons .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }

        .action-buttons .btn-secondary {
            background: transparent;
            color: var(--text-200);
            border: 1px solid var(--line);
        }

        .action-buttons .btn-secondary:hover {
            background: rgba(107, 114, 128, 0.1);
            color: var(--text-100);
        }

        @media (max-width: 768px) {
            .approval-status-container {
                padding: 20px;
            }
            
            .status-card {
                padding: 30px 20px;
            }
            
            .timeline-steps {
                flex-direction: column;
                gap: 30px;
            }
            
            .timeline-steps::before {
                display: none;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn-primary,
            .action-buttons .btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }

        .btn-primary {
            background: var(--success);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-300);
            border: 1px solid var(--line);
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: rgba(107, 114, 128, 0.1);
            color: var(--text-100);
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border-color: rgba(34, 197, 94, 0.3);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-top: 3px solid var(--success);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

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
            color: #e2e8f0;
        }

        .user-details p {
            font-size: 12px;
            color: #94a3b8;
            margin: 0;
        }

        .logout-btn {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: translateY(-1px);
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

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="brand">
                <img src="images/loo.png" alt="Market Vendor Loan Logo" class="brand-logo">
                <div class="brand-content">
                    <h1>Market Vendor Loan</h1>
                    <p>Client Portal</p>
                </div>
            </div>

            <p class="nav-section-title">Main Menu</p>
            <a class="nav-item" href="my-loans.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a class="nav-item active" href="apply-loan.php">
                <i class="fas fa-plus-circle"></i> Apply for Loan
            </a>
            <a class="nav-item" href="payments.php">
                <i class="fas fa-calendar-alt"></i> Payment Schedule
            </a>
            <a class="nav-item" href="make-payment.php">
                <i class="fas fa-credit-card"></i> Make Payment
            </a>
            <a class="nav-item" href="client-payment-history.php">
                <i class="fas fa-history"></i> Payment History
            </a>
            <a class="nav-item" href="profile.php">
                <i class="fas fa-user"></i> Profile
            </a>
    
        </div>

        <!-- Main Content -->
        <div class="content-wrap">
            <div class="dashboard-header">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user_info['name'], 0, 2)); ?>
                    </div>
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($user_info['name']); ?></h3>
                        <p>Vendor</p>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <header class="header">
                <div>
                    <h2>Apply for Loan</h2>
                    <p>Fill out the form below to apply for a loan</p>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="main-content">
                <?php if ($existing_loan): ?>
                    <!-- Existing Loan Status -->
                    <div class="approval-status-container">
                        <div class="status-card">
                            <div class="status-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="status-content">
                                <h2>Application Restricted</h2>
                                <p class="loan-id">Current Loan ID: <strong><?php echo htmlspecialchars($existing_loan['loan_id']); ?></strong></p>
                                <p class="status-message">You already have a <strong><?php echo htmlspecialchars(ucfirst($existing_loan['status'])); ?></strong> loan. You cannot apply for a new loan until your current loan is completed or rejected.</p>
                                
                                <div class="status-info">
                                    <h4>Loan Status Details:</h4>
                                    <div class="status-details">
                                        <div class="detail-item">
                                            <strong>Loan ID:</strong> <?php echo htmlspecialchars($existing_loan['loan_id']); ?>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Status:</strong> 
                                            <span class="status-badge status-<?php echo strtolower($existing_loan['status']); ?>">
                                                <?php echo htmlspecialchars(ucfirst($existing_loan['status'])); ?>
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Next Steps:</strong>
                                            <?php
                                            switch($existing_loan['status']) {
                                                case 'pending':
                                                    echo "Your application is under review. Please wait for approval.";
                                                    break;
                                                case 'approved':
                                                    echo "Your loan has been approved. Check your payment schedule.";
                                                    break;
                                                case 'active':
                                                    echo "Your loan is active. Continue making payments as scheduled.";
                                                    break;
                                                default:
                                                    echo "Contact support for more information.";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="action-buttons">
                                    <a href="my-loans.php" class="btn-primary">
                                        <i class="fas fa-list"></i> View My Loans
                                    </a>
                                    <a href="payments.php" class="btn-secondary">
                                        <i class="fas fa-calendar"></i> Payment Schedule
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($show_waiting_status): ?>
                    <!-- Waiting for Approval Status -->
                    <div class="approval-status-container">
                        <div class="status-card">
                            <div class="status-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="status-content">
                                <h2>Application Submitted Successfully!</h2>
                                <p class="loan-id">Loan ID: <strong><?php echo $submitted_loan_id; ?></strong></p>
                                <p class="status-message">Your loan application is now under review. We will process your application and notify you once a decision has been made.</p>
                                
                                <div class="timeline-info">
                                    <h4>What happens next?</h4>
                                    <div class="timeline-steps">
                                        <div class="step completed">
                                            <div class="step-icon">✓</div>
                                            <div class="step-content">
                                                <strong>Application Submitted</strong>
                                                <p>Your application and documents have been received</p>
                                            </div>
                                        </div>
                                        <div class="step current">
                                            <div class="step-icon">
                                                <i class="fas fa-spinner fa-spin"></i>
                                            </div>
                                            <div class="step-content">
                                                <strong>Under Review</strong>
                                                <p>Our team is reviewing your application</p>
                                            </div>
                                        </div>
                                        <div class="step pending">
                                            <div class="step-icon">⏳</div>
                                            <div class="step-content">
                                                <strong>Decision</strong>
                                                <p>You will be notified of the decision</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="action-buttons">
                                    <a href="my-loans.php" class="btn-primary">
                                        <i class="fas fa-list"></i> View My Loans
                                    </a>
                                    <a href="apply-loan.php?clear_status=1" class="btn-secondary">
                                        <i class="fas fa-plus"></i> Apply Another Loan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Regular Application Form -->
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="loanApplicationForm" enctype="multipart/form-data">
                        <!-- Personal Information -->
                        <div class="form-section">
                            <h3><i class="fas fa-user"></i> Personal Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full_name">Full Name <span class="required">*</span></label>
                                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_info['name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address <span class="required">*</span></label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_info['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone">Phone Number <span class="required">*</span></label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>" placeholder="Enter 11-digit phone number" maxlength="11" pattern="[0-9]{11}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                                </div>
                                <div class="form-group">
                                    <label for="birthdate">Birthdate <span class="required">*</span></label>
                                    <input type="date" id="birthdate" name="birthdate" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="address">Address <span class="required">*</span></label>
                                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user_info['address'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="civil_status">Civil Status <span class="required">*</span></label>
                                    <select id="civil_status" name="civil_status" required>
                                        <option value="">Select Civil Status</option>
                                        <option value="single">Single</option>
                                        <option value="married">Married</option>
                                        <option value="widowed">Widowed</option>
                                        <option value="separated">Separated</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Business Information -->
                        <div class="form-section">
                            <h3><i class="fas fa-briefcase"></i> Business Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="business_name">Business Name <span class="required">*</span></label>
                                    <input type="text" id="business_name" name="business_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="business_type">Business Type <span class="required">*</span></label>
                                    <select id="business_type" name="business_type" required>
                                        <option value="">Select Business Type</option>
                                        <option value="retail">Retail</option>
                                        <option value="wholesale">Wholesale</option>
                                        <option value="food">Food Service</option>
                                        <option value="services">Services</option>
                                        <option value="manufacturing">Manufacturing</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="business_address">Business Address <span class="required">*</span></label>
                                    <input type="text" id="business_address" name="business_address" required>
                                </div>
                                <div class="form-group">
                                    <label for="monthly_revenue">Monthly Revenue (₱) <span class="required">*</span></label>
                                    <input type="number" id="monthly_revenue" name="monthly_revenue" min="0" step="0.01" oninput="calculateLoanLimit()" required>
                                    <small id="loanLimitInfo" style="color: #94a3b8; font-size: 0.85rem; margin-top: 5px; display: block;">Enter your monthly revenue to auto-calculate loan limit</small>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="business_description">Business Description <span class="required">*</span></label>
                                <textarea id="business_description" name="business_description" placeholder="Describe your business, products/services offered, years in operation, etc." required></textarea>
                            </div>
                        </div>

                        <!-- Loan Details -->
                        <div class="form-section">
                            <h3><i class="fas fa-hand-holding-usd"></i> Loan Details</h3>
                            <div class="form-row">
                                                                <div class="form-group">
                                    <label for="payment_frequency">Payment Frequency <span class="required">*</span></label>
                                    <select id="payment_frequency" name="payment_frequency" required>
                                        <option value="">Select Frequency</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="loan_amount">Loan Amount (₱) <span class="required">*</span></label>
                                    <input type="number" id="loan_amount" name="loan_amount" min="10000" step="100" oninput="if(this.value < 0) this.value = 0;" required>
                                    <small id="maxLoanInfo" style="color: #60a5fa; font-size: 0.85rem; margin-top: 5px; display: block;">Enter monthly revenue and select payment frequency to see maximum loan amount</small>
                                </div>
                            </div>
                            <div class="form-row">
                                 <div class="form-group">
                                    <label for="loan_purpose">Loan Purpose <span class="required">*</span></label>
                                    <select id="loan_purpose" name="loan_purpose" required>
                                        <option value="">Select Purpose</option>
                                        <option value="inventory">Inventory Purchase</option>
                                        <option value="equipment">Equipment Purchase</option>
                                        <option value="expansion">Business Expansion</option>
                                        <option value="working_capital">Working Capital</option>
                                        <option value="renovation">Store Renovation</option>
                                        <option value="other">Other</option>
                                    </select>
                                <div class="form-group">
                                    <label for="preferred_term">Preferred Term (months) <span class="required">*</span></label>
                                    <select id="preferred_term" name="preferred_term" required>
                                        <option value="">Select Term</option>
                                        <option value="3">3 months</option>
                                        <option value="6">6 months</option>
                                        <option value="12">12 months</option>
                                        <option value="18">18 months</option>
                                        <option value="24">24 months</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="collateral">Collateral (if any)</label>
                                <input type="text" id="collateral" name="collateral" placeholder="Describe any collateral you can provide">
                            </div>
                        </div>
                        
                        <!-- Loan Summary -->
                        <div class="form-section">
                            <h3><i class="fas fa-calculator"></i> Loan Summary</h3>
                            <div id="loanSummary" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(148, 163, 184, 0.2); border-radius: 8px; padding: 16px; margin-top: 16px;">
                                <p style="color: #cbd5e1; margin: 0; font-size: 0.9rem;">
                                    <strong>Enter your monthly revenue and select payment frequency to see your loan details here</strong>
                                </p>
                            </div>
                        </div>

                        <!-- Document Uploads -->
                        <div class="form-section">
                            <h3><i class="fas fa-file-upload"></i> Required Documents</h3>
                            <div class="document-requirements">
                                <div class="requirements-info">
                                    <h4>Required Documents:</h4>
                                    <ul>
                                        <li><strong>Business Permit:</strong> Current business permit or registration</li>
                                        <li><strong>Valid Government ID:</strong> Driver's license, passport, or national ID</li>
                                        <li><strong>Proof of Income:</strong> Bank statements, tax returns, or income certificates</li>
                                        <li><strong>Proof of Address:</strong> Utility bills or barangay certificate</li>
                                    </ul>
                                    <p class="file-info">Accepted formats: PDF, JPG, JPEG, PNG (Maximum 5MB per file)</p>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="business_permit">Business Permit <span class="required">*</span></label>
                                        <input type="file" id="business_permit" name="business_permit" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <small>Upload your current business permit</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="government_id">Valid Government ID <span class="required">*</span></label>
                                        <input type="file" id="government_id" name="government_id" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <small>Driver's license, passport, or national ID</small>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="proof_of_income">Proof of Income <span class="required">*</span></label>
                                        <input type="file" id="proof_of_income" name="proof_of_income" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <small>Bank statements, tax returns, or income certificates</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="proof_of_address">Proof of Address <span class="required">*</span></label>
                                        <input type="file" id="proof_of_address" name="proof_of_address" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <small>Utility bills or barangay certificate</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-section">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Application
                            </button>
                            <button type="button" class="btn-secondary" onclick="location.reload()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                    
                    <div class="loading" id="loading">
                        <div class="spinner"></div>
                        <p>Submitting your application...</p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        // Calculate loan limit based on monthly revenue using DTI ratio
        function calculateLoanLimit() {
            const monthlyRevenue = parseFloat(document.getElementById('monthly_revenue').value) || 0;
            const paymentFrequency = document.getElementById('payment_frequency').value;
            const loanAmountInput = document.getElementById('loan_amount');
            const loanLimitInfo = document.getElementById('loanLimitInfo');
            const maxLoanInfo = document.getElementById('maxLoanInfo');
            
            if (monthlyRevenue > 0 && paymentFrequency) {
                // Step A: Compute Payment Capacity (30-40% of monthly revenue)
                const dtiRatio = 0.35; // 35% DTI ratio (middle of 30-40% range)
                const monthlyPaymentCapacity = Math.round(monthlyRevenue * dtiRatio);
                
                // Step B: Convert to per frequency payment
                let paymentPerPeriod;
                let numberOfPeriods;
                let periodText;
                
                switch(paymentFrequency) {
                    case 'daily':
                        paymentPerPeriod = Math.round(monthlyPaymentCapacity / 30);
                        numberOfPeriods = 180; // 6 months × 30 days
                        periodText = 'daily';
                        break;
                    case 'weekly':
                        paymentPerPeriod = Math.round(monthlyPaymentCapacity / 4);
                        numberOfPeriods = 26; // 6 months × 4 weeks
                        periodText = 'weekly';
                        break;
                    case 'monthly':
                        paymentPerPeriod = monthlyPaymentCapacity;
                        numberOfPeriods = 6;
                        periodText = 'monthly';
                        break;
                    default:
                        paymentPerPeriod = monthlyPaymentCapacity;
                        numberOfPeriods = 12;
                        periodText = 'monthly';
                }
                
                // Step C: Compute Max Loan (Payment per period × # of periods)
                const maxLoanAmount = Math.round(paymentPerPeriod * numberOfPeriods);
                
                // Update input max attribute
                loanAmountInput.max = maxLoanAmount;
                
                // Update loan limit info with detailed breakdown
                loanLimitInfo.innerHTML = `
                    <strong>Payment Capacity:</strong> ₱${monthlyPaymentCapacity.toLocaleString()}/month (${(dtiRatio * 100).toFixed(0)}% DTI ratio)<br>
                    <strong>Max ${periodText} Payment:</strong> ₱${paymentPerPeriod.toLocaleString()}/${periodText}<br>
                    <strong>Max Loan Amount:</strong> ₱${maxLoanAmount.toLocaleString()} (${numberOfPeriods} ${periodText} payments)
                `;
                
                // Update loan limit info
                maxLoanInfo.innerHTML = `<strong>Maximum Loan Amount:</strong> ₱${maxLoanAmount.toLocaleString()}`;
                
                // Update loan summary with all details
                const loanSummary = document.getElementById('loanSummary');
                const paymentFrequencySelect = document.getElementById('payment_frequency');
                const loanPurposeSelect = document.getElementById('loan_purpose');
                const preferredTermSelect = document.getElementById('preferred_term');
                const collateralInput = document.getElementById('collateral');
                
                if (loanSummary && monthlyRevenue > 0 && paymentFrequency) {
                    const frequencyText = paymentFrequencySelect.options[paymentFrequencySelect.selectedIndex]?.text || paymentFrequency;
                    const purposeText = loanPurposeSelect.options[loanPurposeSelect.selectedIndex]?.text || 'Not selected';
                    const termText = preferredTermSelect.options[preferredTermSelect.selectedIndex]?.text || 'Not selected';
                    const collateralText = collateralInput?.value || 'None provided';
                    
                    loanSummary.innerHTML = `
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 12px;">
                            <div>
                                <h4 style="color: #60a5fa; margin: 0 0 8px 0;">Payment Capacity</h4>
                                <p style="color: #cbd5e1; margin: 0; font-size: 0.9rem;">₱${monthlyPaymentCapacity.toLocaleString()}/month</p>
                                <p style="color: #94a3b8; font-size: 0.8rem;">${(dtiRatio * 100).toFixed(0)}% of monthly revenue</p>
                            </div>
                            <div>
                                <h4 style="color: #60a5fa; margin: 0 0 8px 0;">Loan Terms</h4>
                                <p style="color: #cbd5e1; margin: 0; font-size: 0.9rem;"><strong>Frequency:</strong> ${frequencyText}</p>
                                <p style="color: #cbd5e1; margin: 0; font-size: 0.9rem;"><strong>Purpose:</strong> ${purposeText}</p>
                                <p style="color: #cbd5e1; margin: 0; font-size: 0.9rem;"><strong>Term:</strong> ${termText}</p>
                            </div>
                            <div>
                                <h4 style="color: #60a5fa; margin: 0 0 8px 0;">Maximum ${periodText} Payment</h4>
                                <p style="color: #cbd5e1; margin: 0; font-size: 0.9rem;">₱${paymentPerPeriod.toLocaleString()}/${periodText}</p>
                            </div>
                            <div>
                                <h4 style="color: #60a5fa; margin: 0 0 8px 0;">Maximum Loan Amount</h4>
                                <p style="color: #cbd5e1; margin: 0; font-size: 0.9rem;">₱${maxLoanAmount.toLocaleString()}</p>
                                <p style="color: #94a3b8; font-size: 0.8rem;">(${numberOfPeriods} ${periodText} payments)</p>
                            </div>
                            <div>
                                <h4 style="color: #60a5fa; margin: 0 0 8px 0;">Collateral</h4>
                                <p style="color: #cbd5e1; margin: 0; font-size: 0.9rem;">${collateralText}</p>
                            </div>
                        </div>
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(148, 163, 184, 0.2);">
                            <p style="color: #60a5fa; margin: 0; font-size: 0.85rem; font-weight: 600;">📋 <strong>Complete the form above to submit your loan application</strong></p>
                        </div>
                    `;
                }
                
                // If current amount exceeds limit, adjust it
                if (parseFloat(loanAmountInput.value) > maxLoanAmount) {
                    loanAmountInput.value = maxLoanAmount;
                }
            } else {
                // Reset if no revenue or frequency selected
                loanAmountInput.max = '';
                loanLimitInfo.textContent = 'Enter your monthly revenue and select payment frequency to auto-calculate loan limit';
                maxLoanInfo.textContent = 'Enter monthly revenue and select payment frequency to see maximum loan amount';
            }
        }
        
        // Recalculate when payment frequency changes
        document.getElementById('payment_frequency').addEventListener('change', calculateLoanLimit);
        
        // Form validation and submission
        document.getElementById('loanApplicationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#ef4444';
                } else {
                    field.style.borderColor = 'var(--line)';
                }
            });
            
            if (!isValid) {
                alert('Please fill in all required fields.');
            } else {
                // Show loading
                document.getElementById('loading').style.display = 'block';
                this.style.display = 'none';
                
                // Submit form
                this.submit();
            }
        });
        
        // Clear error styling on input
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('input', function() {
                this.style.borderColor = 'var(--line)';
            });
        });
    </script>
    <script src="responsive-script.js"></script>

</body>
</html>
