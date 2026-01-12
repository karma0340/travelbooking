<?php
/**
 * Database Schema Update Script
 * Run this script to update database tables with the latest schema
 */

// Require authentication
require_once '../includes/security.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Include database connection
require_once '../includes/db.php';

// Set content type
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Database Schema Update</h1>";

try {
    $conn = getDbConnection();
    
    // Start a transaction
    $conn->autocommit(FALSE);
    
    echo "<h2>Updating bookings table...</h2>";
    
    // Check if ip_address column exists
    $result = $conn->query("SHOW COLUMNS FROM `bookings` LIKE 'ip_address'");
    if ($result->num_rows === 0) {
        // Add ip_address column
        $sql = "ALTER TABLE `bookings` ADD COLUMN `ip_address` VARCHAR(45) NULL AFTER `updated_by`";
        if ($conn->query($sql)) {
            echo "<p>Added ip_address column to bookings table</p>";
        } else {
            throw new Exception("Error adding ip_address column: " . $conn->error);
        }
    } else {
        echo "<p>ip_address column already exists</p>";
    }
    
    // Update indexes
    echo "<h2>Updating indexes...</h2>";
    
    // Create booking_ref index if not exists
    $result = $conn->query("SHOW INDEX FROM `bookings` WHERE Key_name = 'idx_booking_ref'");
    if ($result->num_rows === 0) {
        $sql = "CREATE INDEX `idx_booking_ref` ON `bookings` (`booking_ref`)";
        if ($conn->query($sql)) {
            echo "<p>Created idx_booking_ref index</p>";
        } else {
            throw new Exception("Error creating index: " . $conn->error);
        }
    } else {
        echo "<p>idx_booking_ref index already exists</p>";
    }
    
    // Create ip_address index if not exists
    $result = $conn->query("SHOW INDEX FROM `bookings` WHERE Key_name = 'idx_ip_address'");
    if ($result->num_rows === 0) {
        $sql = "CREATE INDEX `idx_ip_address` ON `bookings` (`ip_address`)";
        if ($conn->query($sql)) {
            echo "<p>Created idx_ip_address index</p>";
        } else {
            throw new Exception("Error creating index: " . $conn->error);
        }
    } else {
        echo "<p>idx_ip_address index already exists</p>";
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "<div style='padding: 15px; background-color: #d4edda; color: #155724; margin-top: 20px; border-radius: 5px;'>";
    echo "<h3>Schema update completed successfully!</h3>";
    echo "<p><a href='index.php'>Return to Admin Dashboard</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo "<div style='padding: 15px; background-color: #f8d7da; color: #721c24; margin-top: 20px; border-radius: 5px;'>";
    echo "<h3>Error updating schema</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><a href='index.php'>Return to Admin Dashboard</a></p>";
    echo "</div>";
}
?>
