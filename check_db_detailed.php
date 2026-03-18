<?php
echo "Connecting to localhost...\n";
$conn1 = @new mysqli('localhost', 'root', '', '');
if ($conn1->connect_error) {
    echo "Localhost failed: " . $conn1->connect_error . "\n";
} else {
    echo "Localhost success\n";
}

echo "Connecting to 127.0.0.1...\n";
$conn2 = @new mysqli('127.0.0.1', 'root', '', '');
if ($conn2->connect_error) {
    echo "127.0.0.1 failed: " . $conn2->connect_error . "\n";
} else {
    echo "127.0.0.1 success\n";
}
?>
