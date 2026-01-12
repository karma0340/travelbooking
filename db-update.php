<?php
/**
 * Database Update Script
 * Adds missing columns to existing tables
 */

// Display all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Update Script</h1>";

// Include database functions
define('SECURE_ACCESS', true);
require_once 'includes/db.php';

try {
    // Get database connection
    $conn = getDbConnection();
    
    echo "<h2>Checking and adding missing columns...</h2>";
    
    // Check if ip_address column exists in bookings table
    $result = $conn->query("SHOW COLUMNS FROM `bookings` LIKE 'ip_address'");
    if ($result->num_rows == 0) {
        // Column doesn't exist, add it
        echo "<p>Adding 'ip_address' column to bookings table...</p>";
        
        $sql = "ALTER TABLE `bookings` ADD COLUMN `ip_address` VARCHAR(45) NULL AFTER `updated_by`";
        
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>Successfully added 'ip_address' column to bookings table!</p>";
        } else {
            echo "<p style='color: red;'>Error adding column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>'ip_address' column already exists in bookings table.</p>";
    }
    
    echo "<h2>Checking database schema compatibility...</h2>";
    
    // Also check parameters in bookings table match our code
    echo "<pre>";
    $params = $conn->query("DESCRIBE `bookings`");
    if ($params) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($param = $params->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$param['Field']}</td>";
            echo "<td>{$param['Type']}</td>";
            echo "<td>{$param['Null']}</td>";
            echo "<td>{$param['Key']}</td>";
            echo "<td>{$param['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</pre>";
    
    echo "<h2>Update Complete</h2>";
    echo "<p><a href='index.php'>Return to homepage</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red;'>";
    echo "<h3>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
