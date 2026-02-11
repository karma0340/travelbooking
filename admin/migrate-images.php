<?php
/**
 * Multi-Image Gallery System
 * Database migration to support multiple images for tours, vehicles, and categories
 */

require_once '../includes/db.php';

$conn = getDbConnection();

// Create images table
$sql = "CREATE TABLE IF NOT EXISTS `entity_images` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `entity_type` ENUM('tour', 'vehicle', 'category') NOT NULL,
    `entity_id` INT(11) NOT NULL,
    `image_url` VARCHAR(500) NOT NULL,
    `image_type` ENUM('url', 'upload') DEFAULT 'url',
    `image_metadata` TEXT NULL,
    `display_order` INT(11) DEFAULT 0,
    `is_primary` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `entity_lookup` (`entity_type`, `entity_id`),
    KEY `display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    echo "✓ Entity images table created successfully<br>";
} else {
    echo "✗ Error creating entity_images table: " . $conn->error . "<br>";
}

// Check if image_metadata column exists, add if missing
$result = $conn->query("SHOW COLUMNS FROM entity_images LIKE 'image_metadata'");
if ($result->num_rows == 0) {
    $alterSql = "ALTER TABLE entity_images ADD COLUMN image_metadata TEXT NULL AFTER image_type";
    if ($conn->query($alterSql)) {
        echo "✓ Added image_metadata column<br>";
    } else {
        echo "✗ Error adding image_metadata column: " . $conn->error . "<br>";
    }
} else {
    echo "✓ image_metadata column already exists<br>";
}

// Create uploads directory if it doesn't exist
$uploadDir = '../uploads/images';
if (!file_exists($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        echo "✓ Upload directory created: $uploadDir<br>";
    } else {
        echo "✗ Failed to create upload directory<br>";
    }
}

// Create .htaccess for uploads directory
$htaccessContent = "Options -Indexes\n<FilesMatch \"\\.(jpg|jpeg|png|gif|webp)$\">\n    Allow from all\n</FilesMatch>";
file_put_contents($uploadDir . '/.htaccess', $htaccessContent);

echo "<br><strong>Migration completed!</strong><br>";
echo "<a href='dashboard.php'>Go to Dashboard</a>";

$conn->close();
?>
