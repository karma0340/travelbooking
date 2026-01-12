<?php
/**
 * Authentication functions for admin panel
 */

// Start session if not already started
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    startSession();
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Require authentication to access a page
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit;
    }
}

// Authenticate user
function authenticate($username, $password) {
    // Simple authentication - in production use a database with password hashing
    $validUsername = "admin";
    $validPassword = "shimla123";
    
    if ($username === $validUsername && $password === $validPassword) {
        startSession();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_last_activity'] = time();
        return true;
    }
    
    return false;
}

// Logout user
function logout() {
    startSession();
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

// Check for session timeout (automatically logout after inactivity)
function checkSessionTimeout($timeout = 1800) { // 30 minutes
    startSession();
    if (isLoggedIn()) {
        if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > $timeout)) {
            logout();
            header("Location: index.php?expired=1");
            exit;
        }
        $_SESSION['admin_last_activity'] = time();
    }
}
