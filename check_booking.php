<?php
// Check latest booking
require_once __DIR__ . '/includes/db.php';
$conn = getDbConnection();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result = $conn->query("SELECT * FROM bookings ORDER BY id DESC LIMIT 1");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Latest Booking:\n";
    print_r($row);
} else {
    echo "No bookings found or error: " . $conn->error;
}
?>
