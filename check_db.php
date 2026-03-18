<?php
$conn = new mysqli('localhost', 'root', '', '');
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
} else {
    echo "Connected successfully";
}
?>
