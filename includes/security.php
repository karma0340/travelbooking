<?php
/**
 * Security Helper Functions
 * 
 * This file contains functions for enhancing security throughout the application
 */

// Define secure access constant only if not already defined
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate CSRF token and store in session
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token from form submission
 * 
 * @param string $token Token from form submission
 * @return bool True if token is valid
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    
    return true;
}

/**
 * Sanitize output to prevent XSS
 * 
 * @param string $output String to be sanitized
 * @return string Sanitized string
 */
function sanitizeOutput($output) {
    return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
}

/**
 * Create a secure password hash
 * 
 * @param string $password Password to hash
 * @return string Hashed password
 */
function secureHash($password) {
    return password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 2048, 'time_cost' => 4, 'threads' => 3]);
}

/**
 * Verify a password against a hash
 * 
 * @param string $password Password to check
 * @param string $hash Hash to verify against
 * @return bool True if password matches hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Sanitize user input to prevent XSS attacks
 * 
 * @param string $input The input to sanitize
 * @return string Sanitized input
 */
function sanitizeInput($input) {
    if (!isset($input)) {
        return '';
    }
    
    if (is_array($input)) {
        $clean = [];
        foreach ($input as $key => $value) {
            $clean[$key] = sanitizeInput($value);
        }
        return $clean;
    }
    
    // For strings, apply sanitization
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 * 
 * @param string $email Email to validate
 * @return bool True if email is valid
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Set security headers
 */
function setSecurityHeaders() {
        // Content Security Policy
    $csp = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://*.googleapis.com https://*.gstatic.com",
        "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
        "img-src 'self' data: blob: https: http:",
        "font-src 'self' https://fonts.gstatic.com https://fonts.googleapis.com https://cdnjs.cloudflare.com",
        "frame-src 'self' https://*.google.com https://*.googleapis.com",  // Allow Google Maps iframe
        "connect-src 'self' https://api.open-meteo.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://*.googleapis.com https://*.gstatic.com",
        "media-src 'self' blob:",
        "object-src 'none'",
        "base-uri 'self'"
    ];
    header("Content-Security-Policy: " . implode("; ", $csp));


    // Content Security Policy - Updated to allow all necessary resources
    // header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://cdn.tailwindcss.com 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://unpkg.com 'unsafe-inline'; img-src 'self' https: data:; font-src 'self' https://fonts.gstatic.com https://fonts.googleapis.com https://cdnjs.cloudflare.com; connect-src 'self' https://api.open-meteo.com;");
    
    // Prevent clickjacking
    header("X-Frame-Options: SAMEORIGIN");
    
    // Prevent MIME type sniffing
    header("X-Content-Type-Options: nosniff");
    
    // Enable XSS protection in browsers
    header("X-XSS-Protection: 1; mode=block");
    
    // HSTS (uncomment in production with HTTPS)
    // header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
    
    // Referrer policy
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // Permissions policy
    header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
}

/**
 * Log security events
 * 
 * @param string $event Description of security event
 * @param string $type Type of event (e.g., 'warning', 'critical')
 * @param array $details Additional details about the event
 */
function logSecurityEvent($event, $type = 'warning', $details = []) {
    // Get client IP address
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Get user agent
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    // Get current timestamp
    $timestamp = date('Y-m-d H:i:s');
    
    // Format log message
    $logMessage = sprintf(
        "[%s] [%s] [IP: %s] %s | UA: %s | Details: %s\n",
        $timestamp,
        strtoupper($type),
        $ip,
        $event,
        $userAgent,
        !empty($details) ? json_encode($details) : 'None'
    );
    
    // Path to security log file
    $logFile = __DIR__ . '/../logs/security.log';
    $logDir = dirname($logFile);
    
    // Create log directory if it doesn't exist
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Append to log file
    error_log($logMessage, 3, $logFile);
    
    // For critical security events, we might want to alert administrators
    if ($type === 'critical') {
        // In a production environment, consider implementing email alerts
        // or integration with a monitoring system
    }
}

/**
 * Create an anti-CSRF token for forms
 *
 * @param string $formName Unique name for the form
 * @return string HTML hidden input field with CSRF token
 */
function csrfField($formName = 'default') {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . sanitizeOutput($token) . '">';
}

/**
 * Validate request rate limiting
 * 
 * @param string $action The action being rate limited
 * @param int $maxAttempts Maximum number of attempts allowed
 * @param int $timeWindow Time window in seconds
 * @return bool True if request is allowed, false if rate limited
 */
function checkRateLimit($action, $maxAttempts = 5, $timeWindow = 60) {
    // Use client IP as identifier
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = "rate_limit:{$action}:{$ip}";
    
    // In a production environment, this should use Redis or another
    // distributed cache rather than sessions which are unreliable
    // for rate limiting purposes
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 1,
            'timestamp' => time()
        ];
        return true;
    }
    
    $rateData = $_SESSION[$key];
    
    // Check if we're still within the time window
    if (time() - $rateData['timestamp'] > $timeWindow) {
        // Reset if outside window
        $_SESSION[$key] = [
            'attempts' => 1,
            'timestamp' => time()
        ];
        return true;
    }
    
    // Check if attempts exceeded
    if ($rateData['attempts'] >= $maxAttempts) {
        logSecurityEvent(
            "Rate limit exceeded for action '{$action}'", 
            'warning', 
            ['ip' => $ip, 'attempts' => $rateData['attempts']]
        );
        return false;
    }
    
    // Increment attempts
    $_SESSION[$key]['attempts']++;
    return true;
}

/**
 * Check if a request is valid (CSRF protection)
 */
function checkValidRequest() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            http_response_code(403);
            die('Invalid request');
        }
    }
}
?>
