<?php
// seed_tours.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db.php';

echo "<h2>Seeding Tours Table...</h2>";

$conn = getDbConnection();

if (!$conn) {
    die("Database connection failed.");
}

// Check if tours table is actually empty or just sparse
$countResult = $conn->query("SELECT COUNT(*) as count FROM tours");
$count = $countResult->fetch_assoc()['count'];
echo "<p>Current tour count: $count</p>";

// Sample Tours Data
$sampleTours = [
    [
        'title' => 'Shimla & Manali Explorer',
        'slug' => 'shimla-manali-explorer',
        'description' => 'Experience the best of Himachal with this 6-day tour covering the colonial charm of Shimla and the alpine beauty of Manali. Perfect for families and couples.',
        'duration' => '5 Nights / 6 Days',
        'duration_days' => 6,
        'price' => 18500,
        'location' => 'Shimla, Manali',
        'category' => 'nature',
        'image' => 'https://images.pexels.com/photos/1271619/pexels-photo-1271619.jpeg?auto=compress&cs=tinysrgb&w=800',
        'features' => ['3 Star Hotels', 'Breakfast & Dinner', 'Private Cab', 'Sightseeing'],
        'highlights' => ['Mall Road Shimla', 'Solang Valley', 'Rohtang Pass', 'Hadimba Temple'],
        'map_points' => [],
        'rating' => 4.8,
        'badge' => 'Bestseller',
        'active' => 1
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
        'image' => 'https://images.pexels.com/photos/1574843/pexels-photo-1574843.jpeg?auto=compress&cs=tinysrgb&w=800',
        'features' => ['Homestays/Hotels', 'Meals', 'SUV Transport', 'Guide'],
        'highlights' => ['Key Monastery', 'Chandratal Lake', 'Hikkim', 'Langza'],
        'map_points' => [],
        'rating' => 4.9,
        'badge' => 'Trending',
        'active' => 1
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
        'image' => 'https://images.pexels.com/photos/1024960/pexels-photo-1024960.jpeg?auto=compress&cs=tinysrgb&w=800',
        'features' => ['4 Star Hotel', 'Candlelight Dinner', 'Flower Decoration', 'Transfers'],
        'highlights' => ['Khajjiar Lake', 'Dainkund Peak', 'Kalatop Wildlife Sanctuary', 'St. John Church'],
        'map_points' => [],
        'rating' => 4.7,
        'badge' => 'Romantic',
        'active' => 1
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
        'image' => 'https://images.pexels.com/photos/3408354/pexels-photo-3408354.jpeg?auto=compress&cs=tinysrgb&w=800',
        'features' => ['Hotel Stay', 'Breakfast', 'Sightseeing', 'Guide'],
        'highlights' => ['Tsuglagkhang Complex', 'Bhagsu Waterfall', 'Norbulingka Institute', 'HPCA Stadium'],
        'map_points' => [],
        'rating' => 4.6,
        'badge' => 'Popular',
        'active' => 1
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
        'image' => 'https://images.pexels.com/photos/1365425/pexels-photo-1365425.jpeg?auto=compress&cs=tinysrgb&w=800',
        'features' => ['Camping', 'Trek Guide', 'Meals', 'Bonfire'],
        'highlights' => ['Parvati River', 'Kheerganga Trek', 'Hot Springs', 'Manikaran Sahib'],
        'map_points' => [],
        'rating' => 4.8,
        'badge' => 'Budget Friendly',
        'active' => 1
    ],
    [
        'title' => 'Kinnaur Valley Expedition',
        'slug' => 'kinnaur-valley-expedition',
        'description' => 'Explore the "Land of God" with its apple orchards, stunning valleys, and unique culture. A mesmerizing journey through Kinnaur.',
        'duration' => '5 Nights / 6 Days',
        'duration_days' => 6,
        'price' => 22000,
        'location' => 'Kinnaur, Kalpa, Sangla',
        'category' => 'nature',
        'image' => 'https://images.pexels.com/photos/417074/pexels-photo-417074.jpeg?auto=compress&cs=tinysrgb&w=800',
        'features' => ['Hotel/Homestay', 'Meals', 'Transport', 'Local Guide'],
        'highlights' => ['Chitkul (Last Village)', 'Kalpa', 'Suicide Point', 'Sangla Valley'],
        'map_points' => [],
        'rating' => 4.9,
        'badge' => 'Offbeat',
        'active' => 1
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
        'image' => 'https://images.pexels.com/photos/1271619/pexels-photo-1271619.jpeg?auto=compress&cs=tinysrgb&w=800',
        'features' => ['Family Suite', 'Breakfast & Dinner', 'Private Cab', 'Welcome Drink'],
        'highlights' => ['Solang Valley', 'Vashisht Temple', 'Manali Club House', 'Mall Road'],
        'map_points' => [],
        'rating' => 4.7,
        'badge' => 'Family Special',
        'active' => 1
    ]
];

foreach ($sampleTours as $tourData) {
    // Check if slug exists to avoid duplicates
    $checkSql = "SELECT id FROM tours WHERE slug = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param('s', $tourData['slug']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        // Insert new tour
        $tourId = saveTour($tourData);
        if ($tourId) {
            echo "<p style='color:green;'>✅ Inserted: {$tourData['title']} (ID: $tourId)</p>";
        } else {
             echo "<p style='color:red;'>❌ Failed to insert: {$tourData['title']} - " . $conn->error . "</p>";
        }
    } else {
        // Update existing tour with new CDN image URL
        $existingTour = $result->fetch_assoc();
        $existingId = $existingTour['id'];
        
        $tourId = saveTour($tourData, $existingId);
        if ($tourId) {
            echo "<p style='color:blue;'>🔄 Updated: {$tourData['title']} (ID: $existingId) - New CDN image applied</p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to update: {$tourData['title']}</p>";
        }
    }
    $stmt->close();
}


echo "<h3>Seeding Complete!</h3>";
?>
