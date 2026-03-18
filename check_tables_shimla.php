<?php
$conn = new mysqli('localhost', 'root', '', 'shimla_airlines');
if ($conn->connect_error) {
    echo "shimla_airlines connection failed: " . $conn->connect_error;
} else {
    $result = $conn->query("SHOW TABLES");
    echo "Tables in shimla_airlines:\n";
    while ($row = $result->fetch_row()) {
        echo $row[0] . "\n";
    }
}
?>
