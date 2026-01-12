<?php
require_once 'includes/db.php';

$conn = getDbConnection();

// Update tour images to use local placeholders
$updates = [
    "UPDATE tours SET image = 'images/placeholder/manali.jpg' WHERE title LIKE '%Manali%'",
    "UPDATE tours SET image = 'images/placeholder/spiti.jpg' WHERE title LIKE '%Spiti%' OR title LIKE '%Kinnaur%'",
    "UPDATE tours SET image = 'images/placeholder/shimla.jpg' WHERE title LIKE '%Shimla%'",
    "UPDATE tours SET image = 'images/placeholder/honeymoon-tours.png' WHERE title LIKE '%Dalhousie%' OR title LIKE '%Romantic%'",
    "UPDATE tours SET image = 'images/placeholder/adventure-tours.png' WHERE title LIKE '%Kasol%' OR title LIKE '%Trek%' OR title LIKE '%Adventure%'",
    "UPDATE tours SET image = 'images/placeholder/spiritual-tours.png' WHERE title LIKE '%Dharamshala%' OR title LIKE '%Spiritual%' OR title LIKE '%Mcleodganj%'",
    "UPDATE tours SET image = 'images/placeholder/family-tours.png' WHERE title LIKE '%Family%'"
];

echo "<h2>Updating Tour Images</h2>";
echo "<pre>";

foreach ($updates as $sql) {
    if ($conn->query($sql)) {
        echo "✓ " . $sql . "\n";
        echo "  Affected rows: " . $conn->affected_rows . "\n\n";
    } else {
        echo "✗ Error: " . $conn->error . "\n\n";
    }
}

// Show updated tours
echo "\n<h3>Updated Tours:</h3>\n";
$result = $conn->query('SELECT id, title, image FROM tours ORDER BY id LIMIT 10');
while($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | " . $row['title'] . "\n";
    echo "Image: " . $row['image'] . "\n";
    echo "---\n";
}

echo "</pre>";
echo "<p><a href='index.php'>Go to Home Page</a></p>";
?>
