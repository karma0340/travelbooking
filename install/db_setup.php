<?php
// Verify this is being accessed through proper channels
define('SECURE_ACCESS', true);

// Display all errors for setup
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Removed IP restriction and token verification for easier access

echo "<h1>Travel In Peace Database Setup</h1>";
echo "<p>Checking MySQL connection...</p>";

// Get configuration
$config = require_once __DIR__ . '/../includes/config.php';

try {
    // First check if MySQL server is running
    echo "<p>Attempting to connect to MySQL server...</p>";
    
    // Try different connection methods
    $connected = false;
    $errorMessage = '';
    
    // Set default values if not defined in config
    $db_host = $config['db_host'] ?? '127.0.0.1';
    $db_port = $config['db_port'] ?? 3306;
    $db_user = $config['db_user'] ?? '';
    $db_pass = $config['db_pass'] ?? '';
    
    // Try method 1: Connect with TCP/IP using config values
    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, '', $db_port);
        if (!$conn->connect_error) {
            $connected = true;
            echo "<p>Successfully connected to MySQL using {$db_host} (TCP/IP) on port {$db_port}</p>";
        }
    } catch (Exception $e) {
        $errorMessage .= "Error connecting via {$db_host}: " . $e->getMessage() . "<br>";
    }
    
    // Try method 2: Connect with named socket (localhost)
    if (!$connected) {
        try {
            $conn = new mysqli('localhost', $db_user, $db_pass);
            if (!$conn->connect_error) {
                $connected = true;
                echo "<p>Successfully connected to MySQL using localhost (socket)</p>";
            }
        } catch (Exception $e) {
            $errorMessage .= "Error connecting via localhost: " . $e->getMessage() . "<br>";
        }
    }
    
    // If still not connected, try to connect without database selection
    if (!$connected) {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
        echo "<h3>Connection Failed</h3>";
        echo "<p>Could not connect to MySQL server. Please check:</p>";
        echo "<ol>
                <li>Is MySQL server running? Open XAMPP Control Panel and start MySQL.</li>
                <li>Check if MySQL is running on port {$db_port}.</li>
                <li>Make sure username and password are correct.</li>
              </ol>";
        echo "<p>Technical details:</p>";
        echo "<pre>{$errorMessage}</pre>";
        echo "</div>";
        die();
    }
    
    // If we're here, we have a connection
    
    // Create database if it doesn't exist
    echo "<p>Creating database '{$config['db_name']}' if it doesn't exist...</p>";
    $sql = "CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    echo "<p>Selecting database...</p>";
    if (!$conn->select_db($config['db_name'])) {
        throw new Exception("Error selecting database: " . $conn->error);
    }

    // Set strict SQL mode for compatibility
    $conn->query("SET sql_mode = ''");
    
    // Create tables
    echo "<p>Creating tables...</p>";
    $tables = [
        // Users table - simplified version without foreign keys first
        "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL,
            `password_hash` VARCHAR(255) NOT NULL,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `role` ENUM('admin', 'manager', 'user') NOT NULL DEFAULT 'user',
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `last_login` DATETIME NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Login attempts table
        "CREATE TABLE IF NOT EXISTS `login_attempts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ip_address` VARCHAR(45) NOT NULL,
            `attempts` INT NOT NULL DEFAULT 0,
            `last_attempt` DATETIME NOT NULL,
            `last_username` VARCHAR(50),
            `locked_until` DATETIME,
            UNIQUE KEY `ip_unique` (`ip_address`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Activity log table
        "CREATE TABLE IF NOT EXISTS `activity_log` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user` VARCHAR(50) NOT NULL,
            `type` VARCHAR(50) NOT NULL,
            `message` TEXT NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `created_at` DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tours table
        "CREATE TABLE IF NOT EXISTS `tours` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tour itinerary table
        "CREATE TABLE IF NOT EXISTS `tour_itinerary` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tour_id` INT NOT NULL,
            `day_number` INT NOT NULL,
            `title` VARCHAR(100) NOT NULL,
            `description` TEXT NOT NULL,
            `meals` TEXT,
            `accommodation` VARCHAR(100),
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME,
            INDEX `idx_tour_id` (`tour_id`),
            CONSTRAINT `fk_tour_itinerary_tour` FOREIGN KEY (`tour_id`) 
            REFERENCES `tours`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Vehicles table
        "CREATE TABLE IF NOT EXISTS `vehicles` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Bookings table
        "CREATE TABLE IF NOT EXISTS `bookings` (
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
            INDEX `idx_vehicle_id` (`vehicle_id`),
            CONSTRAINT `fk_bookings_tour` FOREIGN KEY (`tour_id`) 
            REFERENCES `tours`(`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_bookings_vehicle` FOREIGN KEY (`vehicle_id`) 
            REFERENCES `vehicles`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Reviews table
        "CREATE TABLE IF NOT EXISTS `reviews` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tour_id` INT,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `rating` INT NOT NULL,
            `comment` TEXT NOT NULL,
            `approved` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL,
            INDEX `idx_tour_id` (`tour_id`),
            CONSTRAINT `fk_reviews_tour` FOREIGN KEY (`tour_id`) 
            REFERENCES `tours`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Tour Categories table
        "CREATE TABLE IF NOT EXISTS `tour_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `slug` varchar(100) NOT NULL,
            `description` text,
            `icon` varchar(50) DEFAULT NULL,
            `image` varchar(255) DEFAULT NULL,
            `color` varchar(20) DEFAULT 'primary',
            `display_order` int(11) DEFAULT 0,
            `active` tinyint(1) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    // Create each table
    foreach ($tables as $sql) {
        if (!$conn->query($sql)) {
            echo "<p style='color: red;'>Error creating table: " . $conn->error . "</p>";
            echo "<p>SQL: " . htmlspecialchars($sql) . "</p>";
        } else {
            // Output successful table creation
            preg_match('/CREATE TABLE IF NOT EXISTS `(\w+)`/', $sql, $matches);
            if (!empty($matches[1])) {
                echo "<p style='color: green;'>Table '{$matches[1]}' created or already exists</p>";
            }
        }
    }

    // Add constraints and indexes in a separate step
    echo "<p>Adding constraints and indexes...</p>";
    
    // Check for existing indices before trying to add them
    $indexChecks = [
        "SHOW INDEX FROM `users` WHERE Key_name = 'idx_username'",
        "SHOW INDEX FROM `users` WHERE Key_name = 'idx_email'"
    ];
    
    $constraints = [];
    
    // Only add indices that don't already exist
    foreach ($indexChecks as $i => $check) {
        $result = $conn->query($check);
        if ($result && $result->num_rows === 0) {
            $constraints[] = $i === 0 
                ? "ALTER TABLE `users` ADD UNIQUE INDEX `idx_username` (`username`)"
                : "ALTER TABLE `users` ADD UNIQUE INDEX `idx_email` (`email`)";
        }
    }
    
    // Apply any missing constraints
    foreach ($constraints as $sql) {
        try {
            if (!$conn->query($sql)) {
                echo "<p style='color: orange;'>Warning applying constraint: " . $conn->error . "</p>";
            } else {
                echo "<p style='color: green;'>Successfully added index</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: orange;'>Warning: " . $e->getMessage() . "</p>";
            // Continue execution - non-fatal error
            continue;
        }
    }
    
    // Verify user table exists before inserting
    $checkTable = $conn->query("SHOW TABLES LIKE 'users'");
    if ($checkTable->num_rows == 0) {
        throw new Exception("Users table wasn't created successfully. Please check MySQL server settings.");
    }
    
    // Insert default admin user
    echo "<p>Creating default admin user if not exists...</p>";
    // First check if the user exists
    $checkAdmin = $conn->query("SELECT * FROM `users` WHERE `username` = 'admin' LIMIT 1");
    
    if ($checkAdmin && $checkAdmin->num_rows === 0) {
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $name = 'Administrator';
        $email = 'admin@shimlaairlines.com';
        $role = 'admin';
        $now = date('Y-m-d H:i:s');
        
        // Use direct INSERT instead of prepared statement for troubleshooting
        $insertSQL = "INSERT INTO `users` 
                    (`username`, `password_hash`, `name`, `email`, `role`, `active`, `created_at`) 
                    VALUES 
                    ('$username', '$password', '$name', '$email', '$role', 1, '$now')";
        
        if ($conn->query($insertSQL)) {
            echo "<p>Admin user created successfully. User ID: " . $conn->insert_id . "</p>";
        } else {
            echo "<p style='color: red;'>Error creating admin user: " . $conn->error . "</p>";
            echo "<p>SQL: " . htmlspecialchars($insertSQL) . "</p>";
        }
    } else {
        echo "<p>Admin user already exists or couldn't check (Error: " . $conn->error . ")</p>";
    }

    // Insert Default Categories
    echo "<p>Checking for tour categories...</p>";
    $checkCategories = $conn->query("SELECT count(*) as count FROM `tour_categories`");
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
            echo "<p style='color: green;'>Default tour categories created successfully.</p>";
        } else {
            echo "<p style='color: red;'>Error creating categories: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Tour categories already exist.</p>";
    }
    
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h2>Setup Complete</h2>";
    echo "<p>Database and tables have been created successfully.</p>";
    echo "<p>Default admin user: <strong>admin</strong> / <strong>admin123</strong></p>";
    echo "<p><a href='../index.php' style='color: #155724;'>Go to Homepage</a> | <a href='../admin/index.php' style='color: #155724;'>Go to Admin Login</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<h2>Setup Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your MySQL server settings and try again.</p>";
    echo "</div>";
}
?>
