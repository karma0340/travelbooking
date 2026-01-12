<?php
require_once 'includes/db.php';

$conn = getDbConnection();
$result = $conn->query('SELECT id, title, image FROM tours LIMIT 10');

echo "Tours in database:\n";
echo "==================\n";
while($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . "\n";
    echo "Title: " . $row['title'] . "\n";
    echo "Image: " . $row['image'] . "\n";
    echo "---\n";
}
?>
