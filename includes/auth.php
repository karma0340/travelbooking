<?php
/**
 * Authentication System
 * Handles user login, session management, and security
 */

// Only set session params and start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    // Start secure session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
    session_start();
}

require_once __DIR__ . '/db.php';

// Get application config
$config = require_once __DIR__ . '/config.php';

if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

/**
 * Authenticate user with brute force protection
 * 
 * @param string $username Username
 * @param string $password Password
 * @return array Login result with success status and message
 */
function login($username, $password) {
    global $config;
    
    // Check for IP lockout
    $ip = getClientIP();
    if (isLocked($ip)) {
        return [
            'success' => false,
            'message' => 'Too many failed login attempts. Please try again later.'
        ];
    }
    
    // Input validation
    if (empty($username) || empty($password)) {
        logFailedAttempt($ip, $username);
        return [
            'success' => false, 
            'message' => 'Username and password are required'
        ];
    }
    
    // Sanitize username
    $username = trim(htmlspecialchars($username, ENT_QUOTES, 'UTF-8'));
    
    // Get user from database
    $conn = getDbConnection();
    $sql = "SELECT * FROM `users` WHERE `username` = ? AND `active` = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result || $result->num_rows === 0) {
        logFailedAttempt($ip, $username);
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Invalid username or password'
        ];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        logFailedAttempt($ip, $username);
        return [
            'success' => false,
            'message' => 'Invalid username or password'
        ];
    }
    
    // Rehash password if needed
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $conn = getDbConnection();
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateSql = "UPDATE `users` SET `password_hash` = ? WHERE `id` = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $newHash, $user['id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Update last login
    $conn = getDbConnection();
    $now = date('Y-m-d H:i:s');
    $updateSql = "UPDATE `users` SET `last_login` = ?, `updated_at` = ? WHERE `id` = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssi", $now, $now, $user['id']);
    $stmt->execute();
    $stmt->close();
    
    // Set user session
    $_SESSION['admin_user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'last_activity' => time()
    ];
    
    // Generate CSRF token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    // Log successful login
    logActivity('login', "$username logged in successfully");
    
    // Reset failed attempts for this IP
    resetFailedAttempts($ip);
    
    return [
        'success' => true,
        'message' => 'Login successful',
        'user' => $_SESSION['admin_user']
    ];
}

/**
 * Update user's last login time
 * 
 * @param int $userId User ID
 * @return bool Success or failure
 */
function updateUserLastLogin($userId) {
    require_once __DIR__ . '/db.php';
    
    $conn = getDbConnection();
    $sql = "UPDATE `users` SET `last_login` = NOW() WHERE `id` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Log out current user
 */
function logout() {
    if (isset($_SESSION['admin_user'])) {
        $username = $_SESSION['admin_user']['username'] ?? 'unknown';
        logActivity('logout', "$username logged out");
    }
    
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['admin_user']) && !empty($_SESSION['admin_user']);
}

/**
 * Check if current user has admin role
 * 
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['admin_user']) && $_SESSION['admin_user']['role'] === 'admin';
}

/**
 * Get current user data
 */
function getCurrentUser() {
    return $_SESSION['admin_user'] ?? null;
}

/**
 * Require user to be logged in
 */
function requireLogin($redirectUrl = '../admin/index.php') {
    global $config;
    
    if (!isLoggedIn()) {
        // Set intended URL for redirect after login
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        header("Location: $redirectUrl");
        exit;
    }
    
    // Check for session timeout
    if (isset($_SESSION['admin_user']['last_activity']) && 
        time() - $_SESSION['admin_user']['last_activity'] > $config['auth_timeout']) {
        // Session expired
        logout();
        header("Location: $redirectUrl?timeout=1");
        exit;
    }
    
    // Update last activity time
    $_SESSION['admin_user']['last_activity'] = time();
}

/**
 * Require user to have admin role
 */
function requireAdmin($redirectUrl = '../admin/index.php') {
    requireLogin($redirectUrl);
    
    if (!isset($_SESSION['admin_user']['role']) || $_SESSION['admin_user']['role'] !== 'admin') {
        logActivity('security', 'Unauthorized admin access attempt: ' . $_SESSION['admin_user']['username']);
        header("Location: $redirectUrl?error=unauthorized");
        exit;
    }
}

/**
 * Check if IP is locked out due to too many failed attempts
 */
function isLocked($ip) {
    $conn = getDbConnection();
    $sql = "SELECT * FROM `login_attempts` WHERE `ip_address` = ? AND `locked_until` > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result();
    $isLocked = $result && $result->num_rows > 0;
    $stmt->close();
    
    return $isLocked;
}

/**
 * Log a failed login attempt
 */
function logFailedAttempt($ip, $username) {
    global $config;
    $conn = getDbConnection();
    
    // Check if entry exists for this IP
    $sql = "SELECT * FROM `login_attempts` WHERE `ip_address` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $now = date('Y-m-d H:i:s');
    
    if ($result && $result->num_rows > 0) {
        // Entry exists, update it
        $row = $result->fetch_assoc();
        $attempts = $row['attempts'] + 1;
        
        $updateSql = "UPDATE `login_attempts` SET 
                      `attempts` = ?, 
                      `last_attempt` = ?,
                      `last_username` = ?";
        
        $params = [$attempts, $now, $username];
        $types = "iss";
        
        // If max attempts reached, lock the IP
        if ($attempts >= $config['login_attempts']) {
            $lockedUntil = date('Y-m-d H:i:s', time() + $config['lockout_time']);
            $updateSql .= ", `locked_until` = ?";
            $params[] = $lockedUntil;
            $types .= "s";
            logActivity('security', "IP $ip locked out for too many failed login attempts");
        }
        
        $updateSql .= " WHERE `ip_address` = ?";
        $params[] = $ip;
        $types .= "s";
        
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param($types, ...$params);
        $updateStmt->execute();
        $updateStmt->close();
        
    } else {
        // Create new entry
        $insertSql = "INSERT INTO `login_attempts` 
                      (`ip_address`, `attempts`, `last_attempt`, `last_username`) 
                      VALUES (?, 1, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("sss", $ip, $now, $username);
        $insertStmt->execute();
        $insertStmt->close();
    }
    
    $stmt->close();
    logActivity('security', "Failed login attempt for user '$username' from IP $ip");
}

/**
 * Reset failed attempts for an IP
 */
function resetFailedAttempts($ip) {
    $conn = getDbConnection();
    $sql = "UPDATE `login_attempts` SET `attempts` = 0, `locked_until` = NULL WHERE `ip_address` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $stmt->close();
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        logActivity('security', 'Invalid CSRF token', getCurrentUser()['username'] ?? 'guest');
        return false;
    }
    return true;
}

/**
 * Log an activity
 */
function logActivity($type, $message, $username = null) {
    if ($username === null) {
        $username = isLoggedIn() ? getCurrentUser()['username'] : 'guest';
    }
    
    $conn = getDbConnection();
    $sql = "INSERT INTO `activity_log` 
            (`user`, `type`, `message`, `ip_address`, `created_at`) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $ip = getClientIP();
    $now = date('Y-m-d H:i:s');
    $stmt->bind_param("sssss", $username, $type, $message, $ip, $now);
    $stmt->execute();
    $stmt->close();
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ip = '';
    
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

/**
 * Generate random password
 * 
 * @param int $length Password length
 * @return string Random password
 */
function generateRandomPassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    
    return $password;
}
