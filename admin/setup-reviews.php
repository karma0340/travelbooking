<?php
/**
 * Setup Reviews Table
 */
define('SECURE_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db-connection.php';

// Check if run as admin or from CLI
if (php_sapi_name() !== 'cli') {
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        die("Unauthorized access.");
    }
}

$conn = getDbConnection();

$sql = file_get_contents(__DIR__ . '/../database/reviews.sql');

// Split SQL by semicolon, but be careful with multi-line statements if any
// This is a simple script so splitting by ; should work
$queries = explode(';', $sql);

echo "Starting database setup for reviews...\n";

foreach ($queries as $query) {
    if (trim($query) != '') {
        if ($conn->query($query)) {
            echo "Successfully executed: " . substr(trim($query), 0, 50) . "...\n";
        } else {
            echo "Error executing query: " . $conn->error . "\n";
        }
    }
}

echo "Setup completed.\n";
?>
