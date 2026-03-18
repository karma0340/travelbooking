<?php
$conn = new mysqli('localhost', 'root', '', 'codexmlt_shimla_airlines');
if ($conn->connect_error) { die($conn->connect_error); }

$result = $conn->query("SELECT COUNT(*) FROM entity_images");
$count = $result->fetch_row()[0];
echo "Total rows in entity_images: $count\n";

if ($count > 0) {
    $result = $conn->query("SELECT * FROM entity_images LIMIT 10");
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
}
?>
