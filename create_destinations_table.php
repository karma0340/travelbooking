<?php
/**
 * Create Destinations Table
 * Run this file once to create the destinations table in the database
 */

require_once 'includes/db.php';

$conn = getDbConnection();

// Create destinations table
$sql = "CREATE TABLE IF NOT EXISTS destinations (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    image VARCHAR(500) DEFAULT NULL,
    badge VARCHAR(100) DEFAULT NULL,
    category VARCHAR(100) DEFAULT 'general',
    rating DECIMAL(2,1) DEFAULT 4.5,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Destinations table created successfully!<br>";
    
    // Insert sample destinations
    $sampleDestinations = [
        [
            'title' => 'Shimla',
            'slug' => 'shimla',
            'description' => 'The Queen of Hills offers colonial charm, scenic beauty, and pleasant weather. Explore Mall Road, Ridge, and Jakhu Temple.',
            'image' => 'https://images.pexels.com/photos/3225531/pexels-photo-3225531.jpeg?auto=compress&cs=tinysrgb&w=800',
            'badge' => 'Popular',
            'category' => 'hill-station'
        ],
        [
            'title' => 'Manali',
            'slug' => 'manali',
            'description' => 'A paradise for adventure lovers and nature enthusiasts. Experience snow-capped mountains, Solang Valley, and Rohtang Pass.',
            'image' => 'https://images.pexels.com/photos/1271619/pexels-photo-1271619.jpeg?auto=compress&cs=tinysrgb&w=800',
            'badge' => 'Trending',
            'category' => 'adventure'
        ],
        [
            'title' => 'Spiti Valley',
            'slug' => 'spiti-valley',
            'description' => 'The cold desert mountain valley with ancient monasteries, stunning landscapes, and unique Tibetan culture.',
            'image' => 'https://images.pexels.com/photos/1562/italian-landscape-mountains-nature.jpg?auto=compress&cs=tinysrgb&w=800',
            'badge' => 'Adventure',
            'category' => 'offbeat'
        ],
        [
            'title' => 'Dharamshala',
            'slug' => 'dharamshala',
            'description' => 'Home to the Dalai Lama and Tibetan culture. Visit McLeodGanj, Bhagsu Waterfall, and peaceful monasteries.',
            'image' => 'https://images.pexels.com/photos/2166559/pexels-photo-2166559.jpeg?auto=compress&cs=tinysrgb&w=800',
            'badge' => 'Spiritual',
            'category' => 'spiritual'
        ],
        [
            'title' => 'Kasol',
            'slug' => 'kasol',
            'description' => 'The mini Israel of India in Parvati Valley. Perfect for trekking, camping, and experiencing hippie culture.',
            'image' => 'https://images.pexels.com/photos/1659438/pexels-photo-1659438.jpeg?auto=compress&cs=tinysrgb&w=800',
            'badge' => 'Offbeat',
            'category' => 'adventure'
        ],
        [
            'title' => 'Dalhousie',
            'slug' => 'dalhousie',
            'description' => 'A charming hill station with colonial architecture, pine forests, and the beautiful Khajjiar meadows.',
            'image' => 'https://images.pexels.com/photos/417074/pexels-photo-417074.jpeg?auto=compress&cs=tinysrgb&w=800',
            'badge' => 'Romantic',
            'category' => 'honeymoon'
        ]
    ];
    
    foreach ($sampleDestinations as $dest) {
        $stmt = $conn->prepare("INSERT INTO destinations (title, slug, description, image, badge, category) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $dest['title'], $dest['slug'], $dest['description'], $dest['image'], $dest['badge'], $dest['category']);
        
        if ($stmt->execute()) {
            echo "✅ Added destination: {$dest['title']}<br>";
        } else {
            echo "❌ Error adding {$dest['title']}: " . $stmt->error . "<br>";
        }
        $stmt->close();
    }
    
    echo "<br><strong>🎉 Setup complete! You can now manage destinations from the admin panel.</strong><br>";
    echo "<a href='admin/destinations.php'>Go to Destinations Management</a>";
    
} else {
    echo "❌ Error creating table: " . $conn->error;
}

$conn->close();
?>
