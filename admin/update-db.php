<?php
/**
 * Database Update Script
 * 
 * SECURITY NOTICE: This file should be protected and only accessible to administrators.
 * It's recommended to remove this file from the server after use.
 */

// Check for admin login
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

// Only process POST requests with confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    // Get database connection
    $conn = getDbConnection();
    
    // Start transaction for atomicity
    $conn->begin_transaction();
    
    try {
        // Add IP address field to bookings table if not exists
        $conn->query("
            ALTER TABLE `bookings` 
            ADD COLUMN IF NOT EXISTS `ip_address` VARCHAR(45) NULL AFTER `updated_by`
        ");
        
        // Add security fields to users table
        $conn->query("
            ALTER TABLE `users` 
            ADD COLUMN IF NOT EXISTS `failed_attempts` INT DEFAULT 0 AFTER `active`,
            ADD COLUMN IF NOT EXISTS `locked_until` DATETIME NULL AFTER `failed_attempts`,
            ADD COLUMN IF NOT EXISTS `password_reset_token` VARCHAR(100) NULL AFTER `locked_until`,
            ADD COLUMN IF NOT EXISTS `password_reset_expires` DATETIME NULL AFTER `password_reset_token`
        ");
        
        // Update vehicle features schema for better security
        $conn->query("
            ALTER TABLE `vehicles`
            MODIFY COLUMN `features` TEXT NOT NULL
        ");
        
        // Create activity_log table if not exists
        $conn->query("
            CREATE TABLE IF NOT EXISTS `activity_log` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NULL,
                `action` VARCHAR(50) NOT NULL,
                `entity_type` VARCHAR(50) NOT NULL,
                `entity_id` INT NULL,
                `details` TEXT NULL,
                `ip_address` VARCHAR(45) NOT NULL,
                `user_agent` TEXT NULL,
                `created_at` DATETIME NOT NULL,
                INDEX `idx_user_id` (`user_id`),
                INDEX `idx_entity` (`entity_type`, `entity_id`),
                INDEX `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Add indexes for performance and security
        // Bookings table
        $conn->query("CREATE INDEX IF NOT EXISTS `idx_booking_ref` ON `bookings` (`booking_ref`)");
        $conn->query("CREATE INDEX IF NOT EXISTS `idx_ip_address` ON `bookings` (`ip_address`)");
        
        // Commit all changes
        $conn->commit();
        
        echo "<div style='color: green; padding: 20px;'>Database updated successfully!</div>";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo "<div style='color: red; padding: 20px;'>Error updating database: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Update</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="m-0">Database Update Tool</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This script will update the database schema. Make sure you have a backup before proceeding.
                        </div>
                        
                        <p>The following changes will be made:</p>
                        <ul>
                            <li>Add IP address tracking to bookings table</li>
                            <li>Add security fields to users table</li>
                            <li>Update vehicle features schema</li>
                            <li>Create activity_log table</li>
                            <li>Add performance and security indexes</li>
                        </ul>
                        
                        <form method="POST" class="mt-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="confirmCheck" required>
                                <label class="form-check-label" for="confirmCheck">
                                    I confirm I want to update the database and have a backup
                                </label>
                            </div>
                            <input type="hidden" name="confirm" value="yes">
                            <button type="submit" class="btn btn-danger" id="updateBtn" disabled>Update Database</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Enable button only when checkbox is checked
        document.getElementById('confirmCheck').addEventListener('change', function() {
            document.getElementById('updateBtn').disabled = !this.checked;
        });
    </script>
</body>
</html>
<?php
}
?>
