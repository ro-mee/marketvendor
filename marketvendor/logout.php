<?php
session_start();
require_once 'includes/audit_helper.php';

// Log logout before destroying session
if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) {
    logLogout($_SESSION['user_id'], $_SESSION['user_name']);
}

// Destroy all session data
session_unset();
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Redirect to login page
header("Location: login.php");
exit();
?>
