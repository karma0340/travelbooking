<?php
require_once 'includes/db.php';

echo "<h1>Setting up Tour Categories...</h1>";

$conn = getDbConnection();

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Read the SQL file
$sqlFile = 'database/tour_categories.sql';
if (!file_exists($sqlFile)) {
    die("SQL file not found at: $sqlFile");
}

$sql = file_get_contents($sqlFile);

// Execute multi-query
if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
        // Check if there are more result sets
    } while ($conn->next_result());
    
    echo "<p style='color: green;'><strong>Success!</strong> Tour categories table created and populated.</p>";
    echo "<p><a href='index.php'>Go to Home</a> | <a href='admin/categories.php'>Go to Admin Categories</a></p>";
} else {
    echo "<p style='color: red;'><strong>Error:</strong> " . $conn->error . "</p>";
}

$conn->close();
?>
