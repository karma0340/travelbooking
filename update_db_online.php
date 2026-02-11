<?php
// Database Updater for Hostinger
// Upload and run this to fix missing columns

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('SECURE_ACCESS', true);
$config = require __DIR__ . '/includes/config.php';

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "<h1>Database Auto-Fixer</h1>";

// 1. Check & Fix `tours` table
echo "<h2>Checking 'tours' table...</h2>";

$columns = [
    "features" => "ALTER TABLE `tours` ADD COLUMN `features` TEXT AFTER `image`",
    "highlights" => "ALTER TABLE `tours` ADD COLUMN `highlights` TEXT AFTER `features`",
    "map_points" => "ALTER TABLE `tours` ADD COLUMN `map_points` TEXT AFTER `highlights`",
    "rating" => "ALTER TABLE `tours` ADD COLUMN `rating` DECIMAL(3,1) DEFAULT 5.0 AFTER `price`",
    "badge" => "ALTER TABLE `tours` ADD COLUMN `badge` VARCHAR(50) DEFAULT NULL AFTER `rating`",
    "slug" => "ALTER TABLE `tours` ADD COLUMN `slug` VARCHAR(255) DEFAULT NULL AFTER `title`"
];

$existing_columns = [];
$result = $conn->query("DESCRIBE `tours`");
while ($row = $result->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
}

foreach ($columns as $col => $sql) {
    if (!in_array($col, $existing_columns)) {
        echo "<p style='color:blue'>Adding missing column: <strong>$col</strong>...</p>";
        if ($conn->query($sql)) {
            echo "<p style='color:green'>Success!</p>";
        } else {
            echo "<p style='color:red'>Error: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:green'>Column '$col' exists.</p>";
    }
}

// 2. Check & Fix `customer_reviews` table
echo "<h2>Checking 'customer_reviews' table...</h2>";

// Allow NULL for google_id
$result = $conn->query("DESCRIBE `customer_reviews` `google_id`");
if ($result->num_rows == 0) {
     $conn->query("ALTER TABLE `customer_reviews` ADD COLUMN `google_id` VARCHAR(255) DEFAULT NULL, ADD UNIQUE KEY `google_id` (`google_id`)");
     echo "<p style='color:green'>Added google_id column.</p>";
} else {
    echo "<p style='color:green'>google_id exists.</p>";
}

// 3. Check for `tour_itinerary` table
echo "<h2>Checking 'tour_itinerary' table...</h2>";
$result = $conn->query("SHOW TABLES LIKE 'tour_itinerary'");
if ($result->num_rows == 0) {
    echo "<p style='color:blue'>Creating missing table: tour_itinerary...</p>";
    $sql = "CREATE TABLE `tour_itinerary` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `tour_id` int(11) NOT NULL,
        `day_number` int(11) NOT NULL,
        `title` varchar(255) NOT NULL,
        `description` text,
        `meals` varchar(255) DEFAULT NULL,
        `accommodation` varchar(255) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `tour_id` (`tour_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
      
    if ($conn->query($sql)) {
        echo "<p style='color:green'>Table created!</p>";
    } else {
         echo "<p style='color:red'>Error: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:green'>Table tour_itinerary exists.</p>";
}

echo "<h3>Done! Go check your website now.</h3>";
?>
