<?php
/**
 * Debug Helper Functions
 * 
 * Collection of debugging utilities to help diagnose issues in the admin panel
 */

/**
 * Check if a session is active and valid
 * 
 * @return array Session status details
 */
function checkSessionStatus() {
    $result = [
        'active' => false,
        'id' => session_id(),
        'status' => session_status(),
        'status_text' => 'Unknown',
        'user_logged_in' => false,
        'username' => null,
        'role' => null,
        'session_data' => []
    ];
    
    // Check session status
    switch(session_status()) {
        case PHP_SESSION_DISABLED:
            $result['status_text'] = 'Sessions are disabled';
            break;
        case PHP_SESSION_NONE:
            $result['status_text'] = 'Session is not started';
            break;
        case PHP_SESSION_ACTIVE:
            $result['status_text'] = 'Session is active';
            $result['active'] = true;
            break;
    }
    
    // Check if user data exists in session
    if ($result['active']) {
        $result['user_logged_in'] = isset($_SESSION['user_id']) && isset($_SESSION['username']);
        $result['username'] = $_SESSION['username'] ?? null;
        $result['role'] = $_SESSION['role'] ?? null;
        
        // Get all session data
        foreach ($_SESSION as $key => $value) {
            // Don't include password or sensitive data
            if (!in_array($key, ['password', 'password_hash'])) {
                $result['session_data'][$key] = $value;
            }
        }
    }
    
    return $result;
}

/**
 * Log debug information to a file
 * 
 * @param string $message Debug message
 * @param mixed $data Additional data to log
 * @return void
 */
function debugLog($message, $data = null) {
    $logFile = __DIR__ . '/../../logs/admin_debug.log';
    $logDir = dirname($logFile);
    
    // Create log directory if it doesn't exist
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Format log message
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}";
    
    if ($data !== null) {
        $logMessage .= " Data: " . print_r($data, true);
    }
    
    $logMessage .= PHP_EOL;
    
    // Append to log file
    error_log($logMessage, 3, $logFile);
}

/**
 * Display a debug panel (only in development)
 * 
 * @param array $data Data to display
 * @return void
 */
function showDebugPanel($data = []) {
    // Only show in development
    if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
        return;
    }
    
    // Get session status
    $sessionStatus = checkSessionStatus();
    
    // Combine with passed data
    $debugData = array_merge($sessionStatus, $data);
    
    echo '<div style="position: fixed; bottom: 0; right: 0; background: #f8f9fa; border: 1px solid #ddd; padding: 10px; max-width: 400px; max-height: 300px; overflow: auto; z-index: 9999; font-size: 12px;">';
    echo '<h5>Debug Information</h5>';
    echo '<pre>';
    print_r($debugData);
    echo '</pre>';
    echo '<button onclick="this.parentNode.style.display=\'none\'">Close</button>';
    echo '</div>';
}
