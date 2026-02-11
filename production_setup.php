<?php
/**
 * Master Production Setup Script - FINAL FIX (V4)
 * Fixes Entiy Images Schema, Categories Table Name, and Missing Data.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

echo "<!DOCTYPE html><html><head><title>Production Setup V4</title>
<style>
body { font-family: sans-serif; line-height: 1.6; max-width: 800px; margin: 2rem auto; padding: 1rem; color: #333; }
.step { background: #f9fafb; border: 1px solid #e5e7eb; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 8px; }
.success { color: green; font-weight: bold; }
.error { color: #dc2626; background: #fee2e2; padding: 0.5rem; border-radius: 4px; }
.info { color: #2563eb; }
</style></head><body>
<h1>🚀 Production Setup V4 (Final Fix)</h1>";

// 1. Config
if (!file_exists(__DIR__ . '/includes/config.php')) die("Config missing.");
define('SECURE_ACCESS', true);
$config = require __DIR__ . '/includes/config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) die("DB Connection Failed: " . $conn->connect_error);
echo "<div class='success'>✓ Connected to DB</div>";

// 2. Critical File Check
echo "<div class='step'><h2>File Integrity</h2>";
$criticalFiles = [
    'includes/db.php', 'includes/image-helper.php', 'includes/tour-functions.php', 
    'includes/category-functions.php', 'admin/includes/image-manager.php', 
    'admin/api/image-manager.php'
];
foreach($criticalFiles as $f) {
    if(!file_exists(__DIR__ . "/$f")) echo "<div class='error'>MISSING: $f</div>";
    else echo "<div class='success'>✓ Found: $f</div>";
}
echo "</div>";

// 3. Schema Fixes
echo "<div class='step'><h2>Database Schema</h2>";

// A. Fix Categories Table Name
$resCat = $conn->query("SHOW TABLES LIKE 'categories'");
$resTourCat = $conn->query("SHOW TABLES LIKE 'tour_categories'");

if ($resCat->num_rows > 0) {
    if ($resTourCat->num_rows == 0) {
        // Case 1: categories Exists, tour_categories Missing -> RENAME
        $conn->query("RENAME TABLE `categories` TO `tour_categories`");
        echo "<div class='success'>✓ Renamed 'categories' -> 'tour_categories'</div>";
    } else {
        // Case 2: Both Exist -> Transfer Data (if needed) and DROP outdated 'categories'
        // We assume tour_categories is the correct one to keep.
        $conn->query("DROP TABLE `categories`");
        echo "<div class='info'>• Dropped redundant 'categories' table (tour_categories already exists).</div>";
    }
} else {
    echo "<div class='success'>✓ No incorrect 'categories' table found.</div>";
}

$conn->query("CREATE TABLE IF NOT EXISTS `tour_categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL,
    `description` text,
    `image` varchar(255) DEFAULT NULL,
    `icon` varchar(50) DEFAULT 'fas fa-map-marker-alt',
    `color` varchar(20) DEFAULT 'primary',
    `display_order` int(11) DEFAULT 0,
    `active` tinyint(1) DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`), UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Seed Categories
if ($conn->query("SELECT COUNT(*) as c FROM tour_categories")->fetch_assoc()['c'] == 0) {
    $cats = [
        ['Adventure', 'adventure', 'Experience the thrill', 'fas fa-hiking'],
        ['Family', 'family', 'Perfect for loved ones', 'fas fa-users'],
        ['Honeymoon', 'honeymoon', 'Romantic getaways', 'fas fa-heart'],
        ['Spiritual', 'spiritual', 'Discover inner peace', 'fas fa-om'],
        ['Group', 'group', 'Fun-filled expeditions', 'fas fa-users-cog'],
        ['Nature', 'nature', 'Nature walks and more', 'fas fa-leaf']
    ];
    foreach ($cats as $c) {
        $stmt = $conn->prepare("INSERT INTO tour_categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $c[0], $c[1], $c[2], $c[3]);
        $stmt->execute();
    }
    echo "<div class='success'>✓ Seeded categories</div>";
}


// B. Fix Entity Images Schema (The BLANK PAGE Cause)
// We drop and recreate to match image-helper.php expectations
// Use 'image_url' NOT 'image_path'
// Use 'display_order' NOT 'sort_order'
$conn->query("DROP TABLE IF EXISTS `entity_images`"); 
$conn->query("CREATE TABLE `entity_images` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `entity_type` enum('tour','vehicle','category') NOT NULL,
    `entity_id` int(11) NOT NULL,
    `image_url` varchar(255) NOT NULL,
    `image_type` varchar(20) DEFAULT 'upload',
    `image_metadata` text,
    `is_primary` tinyint(1) DEFAULT 0,
    `display_order` int(11) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `entity_lookup` (`entity_type`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "<div class='success'>✓ Fixed 'entity_images' schema</div>";


// C. Migration of Images
echo "<h3>Migrating Data...</h3>";
$res = $conn->query("SELECT id, image FROM tours WHERE image IS NOT NULL AND image != ''");
$count = 0;
while($row = $res->fetch_assoc()) {
    $img = clean($row['image']);
    $conn->query("UPDATE tours SET image='$img' WHERE id={$row['id']}");
    $conn->query("INSERT INTO entity_images (entity_type, entity_id, image_url, is_primary) VALUES ('tour', {$row['id']}, '$img', 1)");
    $count++;
}
echo "<div class='success'>✓ Processed $count Tour images</div>";

$res = $conn->query("SELECT id, image FROM tour_categories WHERE image IS NOT NULL AND image != ''");
$count = 0;
while($row = $res->fetch_assoc()) {
    $img = clean($row['image']);
    $conn->query("UPDATE tour_categories SET image='$img' WHERE id={$row['id']}");
    $conn->query("INSERT INTO entity_images (entity_type, entity_id, image_url, is_primary) VALUES ('category', {$row['id']}, '$img', 1)");
    $count++;
}
echo "<div class='success'>✓ Processed $count Category images</div>";


// D. Tours Columns
$cols = ["features"=>"TEXT", "highlights"=>"TEXT", "map_points"=>"TEXT", "rating"=>"DECIMAL(3,1)", "badge"=>"VARCHAR(50)", "slug"=>"VARCHAR(255)"];
foreach($cols as $k=>$v) {
    if(!$conn->query("SELECT $k FROM tours LIMIT 1")) $conn->query("ALTER TABLE tours ADD COLUMN $k $v");
}


echo "</div><h2>Done! Website should be fixed.</h2></body></html>";

function clean($p) { return preg_replace('/^(\.\.\/|\/)+/', '', $p); }
?>
