<?php
/**
 * Complete Database Setup for Travel In Peace
 * Run this file ONCE on your hosting: www.travelinpeace.in/setup-database.php
 * 
 * This will create all tables and apply all recent changes
 */

// Enable error reporting for setup
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('SECURE_ACCESS', true);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Setup - Travel In Peace</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #ffc107; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        .step { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #2980b9; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
<h1>🚀 Travel In Peace - Database Setup</h1>";

try {
    // Load configuration
    echo "<div class='step'><strong>Step 1:</strong> Loading configuration...</div>";
    $config = require_once __DIR__ . '/includes/config.php';
    echo "<div class='success'>✅ Configuration loaded successfully</div>";
    
    // Database connection details
    $db_host = $config['db_host'] ?? 'localhost';
    $db_port = $config['db_port'] ?? 3306;
    $db_user = $config['db_user'] ?? 'root';
    $db_pass = $config['db_pass'] ?? '';
    $db_name = $config['db_name'] ?? 'codexmlt_shimla_airlines';
    
    echo "<div class='info'><strong>Database:</strong> $db_name<br><strong>Host:</strong> $db_host:$db_port<br><strong>User:</strong> $db_user</div>";
    
    // Connect to MySQL
    echo "<div class='step'><strong>Step 2:</strong> Connecting to MySQL server...</div>";
    $conn = new mysqli($db_host, $db_user, $db_pass, '', $db_port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "<div class='success'>✅ Connected to MySQL server</div>";
    
    // Create database if not exists
    echo "<div class='step'><strong>Step 3:</strong> Creating database if not exists...</div>";
    $sql = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }
    echo "<div class='success'>✅ Database '$db_name' ready</div>";
    
    // Select database
    if (!$conn->select_db($db_name)) {
        throw new Exception("Error selecting database: " . $conn->error);
    }
    
    // Set SQL mode
    $conn->query("SET sql_mode = ''");
    
    echo "<h2>📊 Creating Tables</h2>";
    
    // Define all tables
    $tables = [
        // Users table
        "users" => "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL,
            `password_hash` VARCHAR(255) NOT NULL,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `role` ENUM('admin', 'manager', 'user') NOT NULL DEFAULT 'user',
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `last_login` DATETIME NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NULL,
            UNIQUE KEY `idx_username` (`username`),
            UNIQUE KEY `idx_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Login attempts
        "login_attempts" => "CREATE TABLE IF NOT EXISTS `login_attempts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ip_address` VARCHAR(45) NOT NULL,
            `attempts` INT NOT NULL DEFAULT 0,
            `last_attempt` DATETIME NOT NULL,
            `last_username` VARCHAR(50),
            `locked_until` DATETIME,
            UNIQUE KEY `ip_unique` (`ip_address`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Activity log
        "activity_log" => "CREATE TABLE IF NOT EXISTS `activity_log` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user` VARCHAR(50) NOT NULL,
            `type` VARCHAR(50) NOT NULL,
            `message` TEXT NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `created_at` DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Tour categories
        "tour_categories" => "CREATE TABLE IF NOT EXISTS `tour_categories` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL,
            `description` TEXT,
            `icon` VARCHAR(50) DEFAULT NULL,
            `image` VARCHAR(255) DEFAULT NULL,
            `color` VARCHAR(20) DEFAULT 'primary',
            `display_order` INT(11) DEFAULT 0,
            `active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Tours
        "tours" => "CREATE TABLE IF NOT EXISTS `tours` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL,
            `description` TEXT NOT NULL,
            `duration` VARCHAR(50) NOT NULL,
            `duration_days` INT NOT NULL DEFAULT 3,
            `price` DECIMAL(10,2) NOT NULL,
            `location` VARCHAR(100) NOT NULL,
            `category` VARCHAR(50) NOT NULL DEFAULT 'general',
            `image` VARCHAR(255),
            `features` TEXT NOT NULL,
            `highlights` TEXT,
            `map_points` TEXT,
            `rating` DECIMAL(2,1) NOT NULL DEFAULT 4.5,
            `badge` VARCHAR(50),
            `accommodation_included` TINYINT(1) NOT NULL DEFAULT 0,
            `meals_included` TINYINT(1) NOT NULL DEFAULT 0,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Tour itinerary
        "tour_itinerary" => "CREATE TABLE IF NOT EXISTS `tour_itinerary` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tour_id` INT NOT NULL,
            `day_number` INT NOT NULL,
            `title` VARCHAR(100) NOT NULL,
            `description` TEXT NOT NULL,
            `meals` TEXT,
            `accommodation` VARCHAR(100),
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME,
            INDEX `idx_tour_id` (`tour_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Vehicles
        "vehicles" => "CREATE TABLE IF NOT EXISTS `vehicles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL,
            `description` TEXT NOT NULL,
            `seats` INT NOT NULL,
            `bags` INT NOT NULL,
            `price_per_day` DECIMAL(10,2) NOT NULL,
            `image` VARCHAR(255),
            `features` TEXT NOT NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Bookings
        "bookings" => "CREATE TABLE IF NOT EXISTS `bookings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `booking_ref` VARCHAR(10) NOT NULL,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `phone` VARCHAR(20) NOT NULL,
            `tour_id` INT,
            `vehicle_id` INT,
            `tour_package` VARCHAR(100),
            `travel_date` DATE NOT NULL,
            `end_date` DATE,
            `guests` INT NOT NULL,
            `message` TEXT,
            `status` ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME,
            `updated_by` VARCHAR(50),
            INDEX `idx_email` (`email`),
            INDEX `idx_status` (`status`),
            INDEX `idx_created_at` (`created_at`),
            INDEX `idx_tour_id` (`tour_id`),
            INDEX `idx_vehicle_id` (`vehicle_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Reviews (old table - kept for compatibility)
        "reviews" => "CREATE TABLE IF NOT EXISTS `reviews` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tour_id` INT,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `rating` INT NOT NULL,
            `comment` TEXT NOT NULL,
            `approved` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL,
            INDEX `idx_tour_id` (`tour_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Customer Reviews (NEW - with Google integration)
        "customer_reviews" => "CREATE TABLE IF NOT EXISTS `customer_reviews` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) DEFAULT NULL,
            `location` VARCHAR(100) DEFAULT NULL,
            `rating` INT(1) NOT NULL DEFAULT 5,
            `review_text` TEXT NOT NULL,
            `image_path` VARCHAR(255) DEFAULT NULL,
            `google_id` VARCHAR(255) DEFAULT NULL,
            `google_picture` VARCHAR(500) DEFAULT NULL,
            `status` ENUM('pending','approved','rejected') DEFAULT 'approved',
            `is_featured` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `google_id` (`google_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    // Create each table
    foreach ($tables as $tableName => $sql) {
        echo "<div class='step'>Creating table: <code>$tableName</code></div>";
        if (!$conn->query($sql)) {
            echo "<div class='error'>❌ Error creating table '$tableName': " . $conn->error . "</div>";
        } else {
            echo "<div class='success'>✅ Table '$tableName' created or already exists</div>";
        }
    }
    
    echo "<h2>🔗 Adding Foreign Keys</h2>";
    
    // Add foreign keys (only if they don't exist)
    $foreignKeys = [
        "tour_itinerary" => "ALTER TABLE `tour_itinerary` ADD CONSTRAINT `fk_tour_itinerary_tour` FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`) ON DELETE CASCADE",
        "bookings_tour" => "ALTER TABLE `bookings` ADD CONSTRAINT `fk_bookings_tour` FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`) ON DELETE SET NULL",
        "bookings_vehicle" => "ALTER TABLE `bookings` ADD CONSTRAINT `fk_bookings_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE SET NULL",
        "reviews_tour" => "ALTER TABLE `reviews` ADD CONSTRAINT `fk_reviews_tour` FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`) ON DELETE CASCADE"
    ];
    
    foreach ($foreignKeys as $name => $sql) {
        // Check if foreign key already exists
        $checkFK = $conn->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME = 'fk_$name' AND TABLE_SCHEMA = '$db_name'");
        if ($checkFK && $checkFK->num_rows == 0) {
            if (!$conn->query($sql)) {
                echo "<div class='warning'>⚠️ Could not add foreign key '$name': " . $conn->error . " (This is usually OK)</div>";
            } else {
                echo "<div class='success'>✅ Foreign key '$name' added</div>";
            }
        } else {
            echo "<div class='info'>ℹ️ Foreign key '$name' already exists</div>";
        }
    }
    
    echo "<h2>👤 Creating Default Admin User</h2>";
    
    // Check if admin exists
    $checkAdmin = $conn->query("SELECT * FROM `users` WHERE `username` = 'admin' LIMIT 1");
    
    if ($checkAdmin && $checkAdmin->num_rows === 0) {
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $name = 'Administrator';
        $email = 'admin@travelinpeace.in';
        $role = 'admin';
        $now = date('Y-m-d H:i:s');
        
        $insertSQL = "INSERT INTO `users` 
                    (`username`, `password_hash`, `name`, `email`, `role`, `active`, `created_at`) 
                    VALUES 
                    ('$username', '$password', '$name', '$email', '$role', 1, '$now')";
        
        if ($conn->query($insertSQL)) {
            echo "<div class='success'>✅ Admin user created successfully<br>
                  <strong>Username:</strong> admin<br>
                  <strong>Password:</strong> admin123<br>
                  <strong style='color: #dc3545;'>⚠️ IMPORTANT: Change this password immediately after login!</strong>
                  </div>";
        } else {
            echo "<div class='error'>❌ Error creating admin user: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='info'>ℹ️ Admin user already exists</div>";
    }
    
    echo "<h2>🎯 Creating Default Tour Categories</h2>";
    
    // Check if categories exist
    $checkCategories = $conn->query("SELECT COUNT(*) as count FROM `tour_categories`");
    $catCount = $checkCategories ? $checkCategories->fetch_assoc()['count'] : 0;
    
    if ($catCount == 0) {
        $categorySQL = "INSERT INTO `tour_categories` (`name`, `slug`, `description`, `icon`, `image`, `color`, `display_order`, `active`) VALUES
            ('Adventure', 'adventure', 'Trekking, paragliding, and river rafting for thrill-seekers.', 'fa-hiking', 'images/placeholder/adventure-tours.png', 'primary', 1, 1),
            ('Family', 'family', 'Comfortable itineraries with kid-friendly activities.', 'fa-users', 'images/placeholder/family-tours.png', 'success', 2, 1),
            ('Honeymoon', 'honeymoon', 'Romantic getaways with luxury stays and special experiences.', 'fa-heart', 'images/placeholder/honeymoon-tours.png', 'danger', 3, 1),
            ('Spiritual', 'spiritual', 'Discover inner peace at ancient Himalayan monasteries & temples.', 'fa-om', 'images/placeholder/spiritual-tours.png', 'warning', 4, 1),
            ('Group Tours', 'group', 'Bonfires, camping, and unforgettable memories with friends.', 'fa-users-cog', 'images/placeholder/group-tours.png', 'info', 5, 1),
            ('Nature', 'nature', 'Immerse yourself in lush valleys, forests, and untouched wilderness.', 'fa-leaf', 'images/placeholder/nature-tours.png', 'success', 6, 1)";
            
        if ($conn->query($categorySQL)) {
            echo "<div class='success'>✅ Default tour categories created (6 categories)</div>";
        } else {
            echo "<div class='error'>❌ Error creating categories: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='info'>ℹ️ Tour categories already exist ($catCount categories)</div>";
    }
    
    // Summary
    echo "<h2>📊 Setup Summary</h2>";
    
    $tableCount = $conn->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$db_name'");
    $tables = $tableCount ? $tableCount->fetch_assoc()['count'] : 0;
    
    echo "<div class='success'>
        <h3>✅ Setup Completed Successfully!</h3>
        <p><strong>Database:</strong> $db_name</p>
        <p><strong>Tables Created:</strong> $tables</p>
        <p><strong>Admin Username:</strong> admin</p>
        <p><strong>Admin Password:</strong> admin123</p>
        <br>
        <p><strong>⚠️ SECURITY NOTICE:</strong></p>
        <ol>
            <li>Delete or rename this file (<code>setup-database.php</code>) immediately!</li>
            <li>Change the admin password after first login</li>
            <li>Update database credentials in <code>includes/config.php</code> if needed</li>
        </ol>
    </div>";
    
    echo "<div style='text-align: center; margin-top: 30px;'>
        <a href='index.php' class='btn'>🏠 Go to Homepage</a>
        <a href='admin/index.php' class='btn'>🔐 Go to Admin Login</a>
    </div>";
    
    echo "<h2>🗑️ Next Steps</h2>";
    echo "<div class='warning'>
        <strong>IMPORTANT:</strong> For security reasons, please delete this file after setup is complete.
        <br><br>
        You can delete it via:
        <ul>
            <li>FTP/File Manager: Delete <code>setup-database.php</code></li>
            <li>Or rename it to <code>setup-database.php.bak</code></li>
        </ul>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>
        <h2>❌ Setup Failed</h2>
        <p><strong>Error:</strong> " . $e->getMessage() . "</p>
        <p>Please check:</p>
        <ul>
            <li>Database credentials in <code>includes/config.php</code></li>
            <li>MySQL server is running</li>
            <li>Database user has proper permissions</li>
        </ul>
    </div>";
}

echo "</body></html>";
?>
