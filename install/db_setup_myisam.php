<?php
/**
 * Database Setup Script with MyISAM Engine
 * Use this script if you're having issues with InnoDB
 */

// Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Shimla Air Lines Database Setup (MyISAM Engine)</h1>";

// Get configuration
$config = require_once __DIR__ . '/../includes/config.php';

try {
    echo "<p>Connecting to MySQL server...</p>";
    
    // Force new connection with explicit port
    $conn = new mysqli(
        $config['db_host'],
        $config['db_user'],
        $config['db_pass'],
        '',
        isset($config['db_port']) ? $config['db_port'] : 3306
    );
    
    if ($conn->connect_error) {
        throw new Exception("Failed to connect to MySQL: " . $conn->connect_error);
    }
    
    echo "<p style='color:green'>Successfully connected to MySQL server!</p>";
    
    // Get server info for debugging
    echo "<p>MySQL Server Version: " . $conn->server_info . "</p>";
    
    // Set sql_mode to less strict to avoid errors
    $conn->query("SET sql_mode = ''");
    
    // Create database if it doesn't exist
    echo "<p>Creating database '{$config['db_name']}'...</p>";
    $sql = "CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    echo "<p style='color:green'>Database created or already exists.</p>";
    
    // Select database
    echo "<p>Selecting database...</p>";
    if (!$conn->select_db($config['db_name'])) {
        throw new Exception("Error selecting database: " . $conn->error);
    }
    
    // Verify database selection
    $result = $conn->query("SELECT DATABASE()");
    $row = $result->fetch_row();
    if ($row[0] != $config['db_name']) {
        throw new Exception("Database selection failed. Current database: " . $row[0]);
    }
    
    echo "<p style='color:green'>Database selected successfully: {$row[0]}</p>";
    
    // Check connection after database selection
    if ($conn->query("SELECT 1")) {
        echo "<p style='color:green'>Connection is still valid.</p>";
    } else {
        throw new Exception("Connection lost after database selection.");
    }
    
    // First drop any existing tables to avoid conflicts
    echo "<p>Dropping existing tables to ensure clean setup...</p>";
    $tables = ['users', 'login_attempts', 'activity_log', 'tours', 'tour_itinerary', 'vehicles', 'bookings', 'reviews'];
    foreach ($tables as $table) {
        $conn->query("DROP TABLE IF EXISTS `$table`");
        echo "<p>Dropped table '$table' if it existed</p>";
    }
    
    // Create tables with MyISAM engine
    echo "<h2>Creating tables with MyISAM engine</h2>";
    
    // Create users table first
    echo "<p>Creating users table...</p>";
    $usersTableSql = "CREATE TABLE `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL,
        `password_hash` VARCHAR(255) NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL,
        `role` VARCHAR(20) NOT NULL DEFAULT 'user',
        `active` TINYINT(1) NOT NULL DEFAULT 1,
        `last_login` DATETIME NULL,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($usersTableSql)) {
        throw new Exception("Error creating users table: " . $conn->error);
    }
    
    // Verify users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows == 0) {
        throw new Exception("Users table not created properly. Please check your MySQL server.");
    }
    
    echo "<p style='color:green'>✓ Users table created successfully</p>";
    
    // Create other tables one by one with verification
    $tables = [
        // Login attempts table
        'login_attempts' => "CREATE TABLE `login_attempts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ip_address` VARCHAR(45) NOT NULL,
            `attempts` INT NOT NULL DEFAULT 0,
            `last_attempt` DATETIME NOT NULL,
            `last_username` VARCHAR(50),
            `locked_until` DATETIME,
            UNIQUE KEY `ip_unique` (`ip_address`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4",
        
        // Activity log table
        'activity_log' => "CREATE TABLE `activity_log` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user` VARCHAR(50) NOT NULL,
            `type` VARCHAR(50) NOT NULL,
            `message` TEXT NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `created_at` DATETIME NOT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4",
        
        // Tours table
        'tours' => "CREATE TABLE `tours` (
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
            `updated_at` DATETIME NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4",
        
        // Tour itinerary table
        'tour_itinerary' => "CREATE TABLE `tour_itinerary` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tour_id` INT NOT NULL,
            `day_number` INT NOT NULL,
            `title` VARCHAR(100) NOT NULL,
            `description` TEXT NOT NULL,
            `meals` TEXT,
            `accommodation` VARCHAR(100),
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4",
        
        // Vehicles table
        'vehicles' => "CREATE TABLE `vehicles` (
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
            `updated_at` DATETIME NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4",
        
        // Bookings table
        'bookings' => "CREATE TABLE `bookings` (
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
            `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NULL,
            `updated_by` VARCHAR(50)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4",
        
        // Reviews table
        'reviews' => "CREATE TABLE `reviews` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tour_id` INT,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `rating` INT NOT NULL,
            `comment` TEXT NOT NULL,
            `approved` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4"
    ];
    
    // Create each table and verify
    foreach ($tables as $tableName => $sql) {
        echo "<p>Creating table '$tableName'...</p>";
        
        // Force MyISAM engine
        if (!$conn->query($sql)) {
            echo "<p style='color:red'>Error creating table '$tableName': " . $conn->error . "</p>";
            continue;
        }
        
        // Verify the table was created
        $result = $conn->query("SHOW TABLES LIKE '$tableName'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color:green'>✓ Table '$tableName' created successfully</p>";
        } else {
            echo "<p style='color:red'>⚠️ Warning: Table '$tableName' could not be verified</p>";
        }
    }
    
    // Add unique indexes to users table
    echo "<p>Adding unique indexes to users table...</p>";
    
    try {
        $conn->query("ALTER TABLE `users` ADD UNIQUE INDEX `idx_username` (`username`)");
        $conn->query("ALTER TABLE `users` ADD UNIQUE INDEX `idx_email` (`email`)");
        echo "<p style='color:green'>✓ User indexes added successfully</p>";
    } catch (Exception $e) {
        echo "<p style='color:orange'>Warning: Could not add user indexes: " . $e->getMessage() . "</p>";
    }
    
    // Insert admin user
    echo "<p>Creating default admin user...</p>";
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result && $result->num_rows > 0) {
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $name = 'Administrator';
        $email = 'admin@shimlaairlines.com';
        $role = 'admin';
        $now = date('Y-m-d H:i:s');
        
        // Delete admin user if it exists
        $conn->query("DELETE FROM `users` WHERE `username` = 'admin'");
        
        // Insert admin user using direct query
        $sql = "INSERT INTO `users` 
                (`username`, `password_hash`, `name`, `email`, `role`, `active`, `created_at`) 
                VALUES 
                ('$username', '$password', '$name', '$email', '$role', 1, '$now')";
                
        if (!$conn->query($sql)) {
            echo "<p style='color:red'>Error creating admin user: " . $conn->error . "</p>";
        } else {
            echo "<p style='color:green'>Admin user created successfully! ID: " . $conn->insert_id . "</p>";
            
            // Verify admin user exists
            $checkAdmin = $conn->query("SELECT * FROM `users` WHERE `username` = 'admin'");
            if ($checkAdmin && $checkAdmin->num_rows > 0) {
                echo "<p style='color:green'>✓ Admin user verified in database</p>";
            } else {
                echo "<p style='color:red'>⚠️ Warning: Admin user not found after insertion</p>";
            }
        }
    } else {
        echo "<p style='color:red'>Cannot add admin user: users table doesn't exist</p>";
    }
    
    // Insert sample data directly instead of importing from JSON files
    echo "<h2>Creating sample data</h2>";
    
    // Create sample tours
    echo "<p>Creating sample tours...</p>";
    
    $sampleTours = [
        [
            'title' => 'Shimla City Tour',
            'description' => 'Experience the beauty of Shimla, the Queen of Hills, with our comprehensive city tour package.',
            'duration' => '2 Days / 1 Night',
            'duration_days' => 2,
            'price' => 4999,
            'location' => 'Shimla',
            'category' => 'city',
            'image' => 'https://images.unsplash.com/photo-1626015379120-5e537de5310f?w=800',
            'features' => ['Local Guide', 'Hotel Pickup', 'Meals Included'],
            'rating' => 4.8,
            'badge' => 'Popular'
        ],
        [
            'title' => 'Manali Adventure',
            'description' => 'Discover the thrill of Manali with our adventure package including rafting and paragliding.',
            'duration' => '3 Days / 2 Nights',
            'duration_days' => 3,
            'price' => 7999,
            'location' => 'Manali',
            'category' => 'adventure',
            'image' => 'https://images.unsplash.com/photo-1593181629936-11c609b8db9f?w=800',
            'features' => ['Local Guide', 'All Meals', 'Adventure Activities'],
            'rating' => 4.7,
            'badge' => 'Best Seller'
        ],
        [
            'title' => 'Spiti Valley Trek',
            'description' => 'Experience the raw beauty of Spiti Valley with our guided trek through the rugged terrain.',
            'duration' => '5 Days / 4 Nights',
            'duration_days' => 5,
            'price' => 12999,
            'location' => 'Spiti Valley',
            'category' => 'trekking',
            'image' => 'https://images.unsplash.com/photo-1606209272507-8282af2b957b?w=800',
            'features' => ['Expert Guides', 'Camping Equipment', 'Meals'],
            'rating' => 4.9,
            'badge' => 'Adventure'
        ]
    ];
    
    foreach ($sampleTours as $tour) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $tour['title']));
        $features = $conn->real_escape_string(json_encode($tour['features']));
        $now = date('Y-m-d H:i:s');
        
        // Create a basic insert with minimal fields
        $sql = "INSERT INTO `tours` (
            `title`, `slug`, `description`, `duration`, `duration_days`, 
            `price`, `location`, `features`, `active`, `created_at`
        ) VALUES (
            '{$conn->real_escape_string($tour['title'])}',
            '{$conn->real_escape_string($slug)}',
            '{$conn->real_escape_string($tour['description'])}',
            '{$conn->real_escape_string($duration)}',
            " . (isset($tour['duration_days']) ? (int)$tour['duration_days'] : 3) . ",
            {$price},
            '{$location_escaped}',
            '[]', /* Empty JSON array for features */
            1,
            '$created_at'
        )";
        
        if ($conn->query($sql)) {
            echo "<p>Sample tour created: {$tour['title']}</p>";
            
            // Get the tour ID for itinerary items
            $tourId = $conn->insert_id;
            
            // Add sample itinerary items for this tour
            $days = $tour['duration_days'];
            for ($day = 1; $day <= $days; $day++) {
                $title = "Day $day: " . ($day == 1 ? "Arrival" : ($day == $days ? "Departure" : "Exploration"));
                $description = "Detailed itinerary for day $day of the {$tour['title']} tour.";
                $meals = json_encode(["Breakfast", "Lunch"]);
                
                $itinerarySql = "INSERT INTO `tour_itinerary` (
                    `tour_id`, `day_number`, `title`, `description`, `meals`, `accommodation`, `created_at`
                ) VALUES (
                    $tourId, $day, 
                    '{$conn->real_escape_string($title)}',
                    '{$conn->real_escape_string($description)}',
                    '{$conn->real_escape_string($meals)}',
                    'Hotel',
                    '$now'
                )";
                
                $conn->query($itinerarySql);
            }
            
            // Add sample reviews for this tour
            $reviewNames = ["Rahul Sharma", "Priya Patel", "Amit Singh"];
            $reviewComments = [
                "Amazing experience! Highly recommended.",
                "Had a great time with the family. The guides were very knowledgeable.",
                "Excellent tour package. Will definitely book again!"
            ];
            
            for ($i = 0; $i < 2; $i++) {
                $reviewSql = "INSERT INTO `reviews` (
                    `tour_id`, `name`, `email`, `rating`, `comment`, `approved`, `created_at`
                ) VALUES (
                    $tourId,
                    '{$conn->real_escape_string($reviewNames[$i])}',
                    'customer{$i}@example.com',
                    " . mt_rand(4, 5) . ",
                    '{$conn->real_escape_string($reviewComments[$i])}',
                    1,
                    '$now'
                )";
                
                $conn->query($reviewSql);
            }
        } else {
            echo "<p>Failed to create sample tour: " . $conn->error . "</p>";
        }
    }
    
    // Create sample vehicles
    echo "<p>Creating sample vehicles...</p>";
    
    $sampleVehicles = [
        [
            'name' => 'Swift Dzire',
            'description' => 'Comfortable sedan for small groups and couples',
            'seats' => 4,
            'bags' => 2,
            'price_per_day' => 1800,
            'image' => 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=800',
            'features' => ['AC', 'Music System', 'Experienced Driver']
        ],
        [
            'name' => 'Toyota Innova Crysta',
            'description' => 'Spacious SUV perfect for family trips',
            'seats' => 7,
            'bags' => 4,
            'price_per_day' => 3500,
            'image' => 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?w=800',
            'features' => ['AC', 'Music System', 'Charging Ports', 'Comfortable Seating']
        ],
        [
            'name' => 'Tempo Traveller',
            'description' => 'Perfect for large groups and extended trips',
            'seats' => 12,
            'bags' => 8,
            'price_per_day' => 4500,
            'image' => 'https://images.unsplash.com/photo-1513104487127-813ea879b8da?w=800',
            'features' => ['AC', 'Push-back Seats', 'LCD Screen', 'Ample Storage']
        ]
    ];
    
    foreach ($sampleVehicles as $vehicle) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $vehicle['name']));
        $features = $conn->real_escape_string(json_encode($vehicle['features']));
        $now = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO `vehicles` (
            `name`, `slug`, `description`, `seats`, `bags`, 
            `price_per_day`, `image`, `features`, `active`, `created_at`
        ) VALUES (
            '{$conn->real_escape_string($vehicle['name'])}',
            '{$conn->real_escape_string($slug)}',
            '{$conn->real_escape_string($vehicle['description'])}',
            {$vehicle['seats']},
            {$vehicle['bags']},
            {$vehicle['price_per_day']},
            '{$conn->real_escape_string($vehicle['image'])}',
            '$features',
            1,
            '$now'
        )";
        
        if ($conn->query($sql)) {
            echo "<p>Sample vehicle created: {$vehicle['name']}</p>";
        } else {
            echo "<p>Failed to create sample vehicle: " . $conn->error . "</p>";
        }
    }
    
    // Create sample bookings
    echo "<p>Creating sample bookings...</p>";
    
    // Get a tour ID
    $tourResult = $conn->query("SELECT id FROM tours ORDER BY id ASC LIMIT 1");
    $tourId = $tourResult->fetch_assoc()['id'] ?? 'NULL';
    
    // Get a vehicle ID
    $vehicleResult = $conn->query("SELECT id FROM vehicles ORDER BY id ASC LIMIT 1");
    $vehicleId = $vehicleResult->fetch_assoc()['id'] ?? 'NULL';
    
    $sampleBookings = [
        [
            'name' => 'Rajesh Kumar',
            'email' => 'rajesh@example.com',
            'phone' => '+918627873362 ',
            'tour_id' => $tourId,
            'vehicle_id' => 'NULL',
            'tour_package' => 'Shimla Adventure',
            'travel_date' => date('Y-m-d', strtotime('+10 days')),
            'end_date' => date('Y-m-d', strtotime('+12 days')),
            'guests' => 2,
            'message' => 'Looking forward to our trip!',
            'status' => 'confirmed'
        ],
        [
            'name' => 'Anita Sharma',
            'email' => 'anita@example.com',
            'phone' => '+91 9876543211',
            'tour_id' => 'NULL',
            'vehicle_id' => $vehicleId,
            'tour_package' => NULL,
            'travel_date' => date('Y-m-d', strtotime('+5 days')),
            'end_date' => date('Y-m-d', strtotime('+8 days')),
            'guests' => 4,
            'message' => 'We need child seats for 2 kids',
            'status' => 'pending'
        ]
    ];
    
    foreach ($sampleBookings as $booking) {
        $booking_ref = 'SIM' . mt_rand(10000, 99999);
        $created_at = date('Y-m-d H:i:s');
        $tour_package = $booking['tour_package'] ? "'" . $conn->real_escape_string($booking['tour_package']) . "'" : 'NULL';
        $end_date = $booking['end_date'] ? "'" . $booking['end_date'] . "'" : 'NULL';
        
        $sql = "INSERT INTO `bookings` (
            `booking_ref`, `name`, `email`, `phone`, `tour_id`, 
            `vehicle_id`, `tour_package`, `travel_date`, `end_date`, `guests`, 
            `message`, `status`, `created_at`
        ) VALUES (
            '{$conn->real_escape_string($booking_ref)}',
            '{$conn->real_escape_string($booking['name'])}',
            '{$conn->real_escape_string($booking['email'])}',
            '{$conn->real_escape_string($booking['phone'])}',
            {$booking['tour_id']},
            {$booking['vehicle_id']},
            $tour_package,
            '{$conn->real_escape_string($booking['travel_date'])}',
            $end_date,
            {$booking['guests']},
            '{$conn->real_escape_string($booking['message'])}',
            '{$conn->real_escape_string($booking['status'])}',
            '$created_at'
        )";
        
        if ($conn->query($sql)) {
            echo "<p>Sample booking created for: {$booking['name']}</p>";
        } else {
            echo "<p>Failed to create sample booking: " . $conn->error . "</p>";
        }
    }
    
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h2>Setup Complete!</h2>";
    echo "<p>Database and tables have been created successfully using MyISAM engine.</p>";
    echo "<p>Data has been imported from JSON files.</p>";
    echo "<p>Default admin user: <strong>admin</strong> / <strong>admin123</strong></p>";
    echo "<p><a href='../index.php' style='color: #155724;'>Go to Homepage</a> | <a href='../admin/index.php' style='color: #155724;'>Go to Admin Login</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<h2>Setup Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your MySQL server settings and try again.</p>";
    echo "<p>Try running this file directly at: <a href='/sim/install/db_setup_myisam.php'>db_setup_myisam.php</a></p>";
    echo "</div>";
}
