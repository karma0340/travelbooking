<?php
$conn = new mysqli('localhost', 'root', '', 'codexmlt_shimla_airlines');
if ($conn->connect_error) { die($conn->connect_error); }

echo "--- TOURS GAPS ---\n";
$result = $conn->query("SELECT id, title, image FROM tours LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']} | Title: {$row['title']} | Image: {$row['image']}\n";
    
    // Check entity_images
    $img_res = $conn->query("SELECT * FROM entity_images WHERE entity_type = 'tour' AND entity_id = {$row['id']}");
    while ($img = $img_res->fetch_assoc()) {
        echo "  - Entity Image ID: {$img['id']} | URL: {$img['image_url']} | Primary: {$img['is_primary']}\n";
    }
}
?>
