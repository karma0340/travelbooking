<?php
/**
 * Admin Authentication Check
 * This file should be included at the beginning of all admin pages
 */

// Start session only if one doesn't already exist
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_user'])) {
    // Store the requested URL for redirect after login
    if (!isset($_SESSION['redirect_url']) && !strpos($_SERVER['PHP_SELF'], 'index.php')) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    }
    
    // Redirect to login page
    header('Location: index.php');
    exit;
}

// Check session timeout (30 minutes = 1800 seconds)
$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    // Session expired
    session_unset();
    session_destroy();
    
    // Redirect with timeout parameter
    header('Location: index.php?timeout=1');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Check user permissions based on role if needed
// You can add additional checks here based on user role and page access
// For example, restrict certain pages to admin role only

// Define common admin variables
$admin_user = $_SESSION['admin_user'];
$admin_username = $admin_user['username'];
$admin_role = $admin_user['role'];
$admin_name = $admin_user['name'] ?? $admin_username;
?>
