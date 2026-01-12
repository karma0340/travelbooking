<?php
/**
 * Database Connection Handler
 * Provides database connection and error logging utilities
 */

// Define secure access constant only if not already defined
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

/**
 * Get database connection (singleton pattern)
 * 
 * @return mysqli Database connection
 */
function getDbConnection() {
    static $conn = null;
    
    if ($conn === null) {
        // Get configuration with fallback values for safety
        $config_file = __DIR__ . '/config.php';
        
        if (!file_exists($config_file)) {
            logError("Config file not found at: $config_file");
            die("Configuration error. Please contact the administrator.");
        }
        
        $config = require $config_file;
        
        // Validate config has required database parameters
        if (!is_array($config) || 
            !isset($config['db_host']) || 
            !isset($config['db_user']) || 
            !isset($config['db_name'])) {
            logError("Invalid database configuration in config.php file");
            die("Configuration error. Please contact the administrator.");
        }
        
        // Get database parameters with fallbacks
        $host = $config['db_host'] ?? 'localhost';
        $user = $config['db_user'] ?? 'root';
        $pass = $config['db_pass'] ?? '';
        $name = $config['db_name'] ?? 'shimla_airlines';
        $port = $config['db_port'] ?? 3306;
        
        try {
            // Set error mode to exceptions for better error handling
            $conn = new mysqli($host, $user, $pass, $name, $port);
            
            if ($conn->connect_error) {
                logError("Database connection failed: " . $conn->connect_error);
                die("Database connection error. Please try again later.");
            }
            
            // Set character set
            $conn->set_charset('utf8mb4');
            
            // Set SQL mode for better compatibility
            $conn->query("SET SESSION sql_mode = ''");
        } catch (Exception $e) {
            logError("Database connection error: " . $e->getMessage());
            die("Database connection error. Please try again later.");
        }
    }
    
    return $conn;
}

/**
 * Log errors securely
 * 
 * @param string $message Error message
 * @return void
 */
function logError($message) {
    $logFile = __DIR__ . '/../logs/error.log';
    $logDir = dirname($logFile);
    
    // Create log directory if it doesn't exist
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Format the log message
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    
    // Append to log file
    error_log($logMessage, 3, $logFile);
}
?>
