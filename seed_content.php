<?php
// seed_content.php
// Run this file to populate your database with Tours, Categories, and Vehicles

require_once 'includes/db.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo '<!DOCTYPE html>
<html>
<head>
    <title>Database Content Seeder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { padding: 40px; background: #f8f9fa; } .card { margin-bottom: 20px; }</style>
</head>
<body>
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Travel In Peace - Content Seeder</h3>
        </div>
        <div class="card-body">';

$conn = getDbConnection();

if (!$conn) {
    die('<div class="alert alert-danger">Database connection failed. Check your config.php</div>');
}

// 1. Seed Categories
echo '<h4>1. Seeding Tour Categories...</h4>';
$categories = [
    ['name' => 'Adventure', 'slug' => 'adventure', 'description' => 'Trekking, paragliding, and river rafting for thrill-seekers.', 'icon' => 'fa-hiking', 'color' => 'primary', 'image' => 'images/placeholder/adventure-tours.png'],
    ['name' => 'Family', 'slug' => 'family', 'description' => 'Comfortable itineraries with kid-friendly activities.', 'icon' => 'fa-users', 'color' => 'success', 'image' => 'images/placeholder/family-tours.png'],
    ['name' => 'Honeymoon', 'slug' => 'honeymoon', 'description' => 'Romantic getaways with luxury stays and special experiences.', 'icon' => 'fa-heart', 'color' => 'danger', 'image' => 'images/placeholder/honeymoon-tours.png'],
    ['name' => 'Spiritual', 'slug' => 'spiritual', 'description' => 'Discover inner peace at ancient Himalayan monasteries & temples.', 'icon' => 'fa-om', 'color' => 'warning', 'image' => 'images/placeholder/spiritual-tours.png'],
    ['name' => 'Group Tours', 'slug' => 'group', 'description' => 'Bonfires, camping, and unforgettable memories with friends.', 'icon' => 'fa-users-cog', 'color' => 'info', 'image' => 'images/placeholder/group-tours.png'],
    ['name' => 'Nature', 'slug' => 'nature', 'description' => 'Immerse yourself in lush valleys, forests, and untouched wilderness.', 'icon' => 'fa-leaf', 'color' => 'success', 'image' => 'images/placeholder/nature-tours.png']
];

// Check if table exists
$conn->query("CREATE TABLE IF NOT EXISTS `tour_categories` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

foreach ($categories as $i => $cat) {
    $slug = $cat['slug'];
    $check = $conn->query("SELECT id FROM tour_categories WHERE slug = '$slug'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO tour_categories (name, slug, description, icon, image, color, display_order, active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $order = $i + 1;
        $stmt->bind_param("ssssssi", $cat['name'], $cat['slug'], $cat['description'], $cat['icon'], $cat['image'], $cat['color'], $order);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success py-1'>Inserted Category: <strong>{$cat['name']}</strong></div>";
        } else {
            echo "<div class='alert alert-danger py-1'>Error inserting {$cat['name']}: " . $stmt->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-info py-1'>Category <strong>{$cat['name']}</strong> already exists.</div>";
    }
}

echo '<hr>';

// 2. Seed Tours
echo '<h4>2. Seeding Tours...</h4>';
$tours = [
    [
        'title' => 'Shimla & Manali Explorer',
        'slug' => 'shimla-manali-explorer',
        'description' => 'Experience the best of Himachal with this 6-day tour covering the colonial charm of Shimla and the alpine beauty of Manali. Perfect for families and couples.',
        'duration' => '5 Nights / 6 Days',
        'duration_days' => 6,
        'price' => 18500,
        'location' => 'Shimla, Manali',
        'category' => 'family',
        'image' => 'images/placeholder/shimla.jpg',
        'features' => json_encode(['3 Star Hotels', 'Breakfast & Dinner', 'Private Cab', 'Sightseeing']),
        'badge' => 'Bestseller'
    ],
    [
        'title' => 'Spiti Valley Adventure',
        'slug' => 'spiti-valley-adventure',
        'description' => 'A thrilling road trip through the rugged terrains of Spiti Valley. Visit ancient monasteries, the world\'s highest post office, and breathtaking landscapes.',
        'duration' => '8 Nights / 9 Days',
        'duration_days' => 9,
        'price' => 24999,
        'location' => 'Spiti Valley, Kaza',
        'category' => 'adventure',
        'image' => 'images/placeholder/spiti.jpg',
        'features' => json_encode(['Homestays', 'Meals', 'SUV Transport', 'Guide']),
        'badge' => 'Trending'
    ],
    [
        'title' => 'Romantic Dalhousie & Khajjiar',
        'slug' => 'romantic-dalhousie-khajjiar',
        'description' => 'Enjoy the "Mini Switzerland of India" with your better half. A peaceful retreat with pine forests, meadows, and luxurious stays.',
        'duration' => '3 Nights / 4 Days',
        'duration_days' => 4,
        'price' => 15000,
        'location' => 'Dalhousie, Khajjiar',
        'category' => 'honeymoon',
        'image' => 'images/placeholder/honeymoon-tours.png',
        'features' => json_encode(['4 Star Hotel', 'Candlelight Dinner', 'Flower Decoration', 'Transfers']),
        'badge' => 'Romantic'
    ],
    [
        'title' => 'Dharamshala & Mcleodganj Spiritual Tour',
        'slug' => 'dharamshala-mcleodganj-spiritual',
        'description' => 'Immerse yourself in peace and spirituality. Visit the residence of the Dalai Lama, serene monasteries, and waterfalls.',
        'duration' => '3 Nights / 4 Days',
        'duration_days' => 4,
        'price' => 13500,
        'location' => 'Dharamshala, Mcleodganj',
        'category' => 'spiritual',
        'image' => 'images/placeholder/spiritual-tours.png',
        'features' => json_encode(['Hotel Stay', 'Breakfast', 'Sightseeing', 'Guide']),
        'badge' => 'Popular'
    ],
    [
        'title' => 'Kasol & Kheerganga Trek',
        'slug' => 'kasol-kheerganga-trek',
        'description' => 'An exhilarating trek in Parvati Valley. Experience the hippie vibe of Kasol and the natural hot springs of Kheerganga.',
        'duration' => '2 Nights / 3 Days',
        'duration_days' => 3,
        'price' => 6500,
        'location' => 'Kasol, Kheerganga',
        'category' => 'adventure',
        'image' => 'images/placeholder/adventure-tours.png',
        'features' => json_encode(['Camping', 'Trek Guide', 'Meals', 'Bonfire']),
        'badge' => 'Budget Friendly'
    ],
    [
        'title' => 'Kinnaur Valley Expedition',
        'slug' => 'kinnaur-valley-expedition',
        'description' => 'Explore the "Land of God" with its apple orchards, stunning valleys, and unique culture. A mesmerizing journey through Kinnaur.',
        'duration' => '5 Nights / 6 Days',
        'duration_days' => 6,
        'price' => 22000,
        'location' => 'Kinnaur, Kalpa',
        'category' => 'nature',
        'image' => 'images/placeholder/nature-tours.png',
        'features' => json_encode(['Hotel/Homestay', 'Meals', 'Transport', 'Local Guide']),
        'badge' => 'Offbeat'
    ],
    [
        'title' => 'Manali Family Fun',
        'slug' => 'manali-family-fun',
        'description' => 'The ultimate family vacation package. Enjoy snow activities in Solang, shopping on Mall Road, and comfortable family suites.',
        'duration' => '4 Nights / 5 Days',
        'duration_days' => 5,
        'price' => 16000,
        'location' => 'Manali',
        'category' => 'family',
        'image' => 'images/placeholder/manali.jpg',
        'features' => json_encode(['Family Suite', 'Breakfast & Dinner', 'Private Cab', 'Welcome Drink']),
        'badge' => 'Family Special'
    ]
];

foreach ($tours as $tour) {
    $slug = $tour['slug'];
    $check = $conn->query("SELECT id FROM tours WHERE slug = '$slug'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO tours (title, slug, description, duration, duration_days, price, location, category, image, features, badge, active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
        $stmt->bind_param("sssidssssss", $tour['title'], $tour['slug'], $tour['description'], $tour['duration'], $tour['duration_days'], $tour['price'], $tour['location'], $tour['category'], $tour['image'], $tour['features'], $tour['badge']);
        if ($stmt->execute()) {
             echo "<div class='alert alert-success py-1'>Inserted Tour: <strong>{$tour['title']}</strong></div>";
        } else {
             echo "<div class='alert alert-danger py-1'>Error inserting {$tour['title']}: " . $stmt->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-info py-1'>Tour <strong>{$tour['title']}</strong> already exists.</div>";
    }
}

echo '<hr>';

// 3. Seed Vehicles
echo '<h4>3. Seeding Vehicles...</h4>';
$vehicles = [
    ['name' => 'Swift Dzire', 'slug' => 'swift-dzire', 'description' => 'Comfortable sedan perfect for small families or couples.', 'seats' => 4, 'bags' => 2, 'price_per_day' => 2500, 'image' => 'images/vehicles/sedan.jpg', 'features' => 'AC, Music System, Comfortable Seats'],
    ['name' => 'Toyota Innova', 'slug' => 'toyota-innova', 'description' => 'The most popular MPV for hill station travel. Spacious and powerful.', 'seats' => 6, 'bags' => 4, 'price_per_day' => 3500, 'image' => 'images/vehicles/innova.jpg', 'features' => 'AC, Captain Seats, Ample Luggage Space'],
    ['name' => 'Tempo Traveller', 'slug' => 'tempo-traveller', 'description' => 'Ideal for large groups. Travel together with comfort and ease.', 'seats' => 12, 'bags' => 8, 'price_per_day' => 5500, 'image' => 'images/vehicles/tempo.jpg', 'features' => 'AC, Pushback Seats, First Aid Kit']
];

foreach ($vehicles as $vehicle) {
    $slug = $vehicle['slug'];
    $check = $conn->query("SELECT id FROM vehicles WHERE slug = '$slug'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO vehicles (name, slug, description, seats, bags, price_per_day, image, features, active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
        $stmt->bind_param("sssiidss", $vehicle['name'], $vehicle['slug'], $vehicle['description'], $vehicle['seats'], $vehicle['bags'], $vehicle['price_per_day'], $vehicle['image'], $vehicle['features']);
        if ($stmt->execute()) {
             echo "<div class='alert alert-success py-1'>Inserted Vehicle: <strong>{$vehicle['name']}</strong></div>";
        } else {
             echo "<div class='alert alert-danger py-1'>Error inserting {$vehicle['name']}: " . $stmt->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-info py-1'>Vehicle <strong>{$vehicle['name']}</strong> already exists.</div>";
    }
}

echo '<br><a href="index.php" class="btn btn-primary btn-lg mt-3">Go to Homepage</a>';
echo '</div></div></div></body></html>';
?>
