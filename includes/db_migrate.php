<?php
require_once 'includes/db.php';
$conn = getDbConnection();

// Check if column exists
$result = $conn->query("SHOW COLUMNS FROM customer_reviews LIKE 'google_id'");
if ($result->num_rows == 0) {
    echo "Adding google_id column...\n";
    if ($conn->query("ALTER TABLE customer_reviews ADD COLUMN google_id VARCHAR(255) UNIQUE AFTER image_path")) {
        echo "Success: Column google_id added.\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
} else {
    echo "Column google_id already exists.\n";
}

$conn->close();
?>
