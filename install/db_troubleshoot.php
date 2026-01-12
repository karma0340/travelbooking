<?php
/**
 * Database Troubleshooting Script
 * This script helps diagnose issues with the MySQL setup
 */

// Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>MySQL Troubleshooting</h1>";

// Get configuration
$config = require_once __DIR__ . '/../includes/config.php';

echo "<style>
    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
    h1, h2, h3 { color: #333; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow: auto; }
    .success { color: green; }
    .error { color: red; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    th { background-color: #4CAF50; color: white; }
</style>";

try {
    // Connect to MySQL server
    echo "<h2>Connection Test</h2>";
    
    $conn = new mysqli(
        $config['db_host'],
        $config['db_user'],
        $config['db_pass'],
        '',
        $config['db_port']
    );
    
    if ($conn->connect_error) {
        throw new Exception("Failed to connect to MySQL: " . $conn->connect_error);
    }
    
    echo "<p class='success'>Successfully connected to MySQL server!</p>";
    
    // MySQL server info
    echo "<h2>MySQL Server Information</h2>";
    echo "<pre>";
    echo "MySQL Server Version: " . $conn->server_info . "\n";
    echo "MySQL Client Version: " . $conn->client_info . "\n";
    
    $result = $conn->query("SHOW VARIABLES LIKE 'character_set_%'");
    if ($result) {
        echo "\nCharacter Set Variables:\n";
        while ($row = $result->fetch_assoc()) {
            echo $row['Variable_name'] . ": " . $row['Value'] . "\n";
        }
    }
    
    $result = $conn->query("SHOW VARIABLES LIKE 'collation_%'");
    if ($result) {
        echo "\nCollation Variables:\n";
        while ($row = $result->fetch_assoc()) {
            echo $row['Variable_name'] . ": " . $row['Value'] . "\n";
        }
    }
    
    // Storage engines
    $result = $conn->query("SHOW ENGINES");
    if ($result) {
        echo "\nAvailable Storage Engines:\n";
        echo "Engine\tSupport\tComment\n";
        echo "------\t-------\t-------\n";
        while ($row = $result->fetch_assoc()) {
            echo $row['Engine'] . "\t" . $row['Support'] . "\t" . $row['Comment'] . "\n";
        }
    }
    echo "</pre>";
    
    // Check if database exists
    echo "<h2>Database Check</h2>";
    $dbName = $config['db_name'];
    $result = $conn->query("SHOW DATABASES LIKE '$dbName'");
    
    if ($result->num_rows > 0) {
        echo "<p class='success'>Database '$dbName' exists!</p>";
        
        // Select the database
        if (!$conn->select_db($dbName)) {
            throw new Exception("Error selecting database: " . $conn->error);
        }
        
        // Check tables
        echo "<h2>Tables in $dbName</h2>";
        $result = $conn->query("SHOW TABLES");
        
        if ($result->num_rows > 0) {
            echo "<ul>";
            while ($row = $result->fetch_row()) {
                echo "<li>{$row[0]} - <a href='?table={$row[0]}'>View Structure</a></li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='error'>No tables found in database '$dbName'.</p>";
        }
        
        // Show table structure if requested
        if (isset($_GET['table'])) {
            $table = $conn->real_escape_string($_GET['table']);
            echo "<h3>Structure of table '$table'</h3>";
            
            // Get table structure
            $result = $conn->query("DESCRIBE `$table`");
            
            if ($result) {
                echo "<table>";
                echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
                
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['Field']}</td>";
                    echo "<td>{$row['Type']}</td>";
                    echo "<td>{$row['Null']}</td>";
                    echo "<td>{$row['Key']}</td>";
                    echo "<td>{$row['Default']}</td>";
                    echo "<td>{$row['Extra']}</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p class='error'>Error getting table structure: " . $conn->error . "</p>";
            }
            
            // Show first 10 rows of data
            echo "<h3>Sample data from '$table' (first 10 rows)</h3>";
            $result = $conn->query("SELECT * FROM `$table` LIMIT 10");
            
            if ($result) {
                if ($result->num_rows > 0) {
                    echo "<table>";
                    
                    // Table header
                    $fields = $result->fetch_fields();
                    echo "<tr>";
                    foreach ($fields as $field) {
                        echo "<th>{$field->name}</th>";
                    }
                    echo "</tr>";
                    
                    // Reset pointer
                    $result->data_seek(0);
                    
                    // Table data
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td>" . htmlspecialchars($value) . "</td>";
                        }
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                } else {
                    echo "<p>No data in table.</p>";
                }
            } else {
                echo "<p class='error'>Error fetching data: " . $conn->error . "</p>";
            }
        }
        
        // Test creating a temporary table
        echo "<h2>Table Creation Test</h2>";
        $testTable = "test_table_" . time();
        $sql = "CREATE TABLE `$testTable` (
            `id` INT PRIMARY KEY,
            `name` VARCHAR(50)
        ) ENGINE=InnoDB";
        
        if ($conn->query($sql)) {
            echo "<p class='success'>Successfully created test table!</p>";
            
            // Insert test data
            if ($conn->query("INSERT INTO `$testTable` VALUES (1, 'Test')")) {
                echo "<p class='success'>Successfully inserted data into test table!</p>";
            } else {
                echo "<p class='error'>Failed to insert data: " . $conn->error . "</p>";
            }
            
            // Clean up
            $conn->query("DROP TABLE `$testTable`");
        } else {
            echo "<p class='error'>Failed to create test table: " . $conn->error . "</p>";
        }
    } else {
        echo "<p class='error'>Database '$dbName' does not exist.</p>";
    }
    
    echo "<p><a href='db_setup.php'>Run Database Setup Script</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red;'>";
    echo "<h3>Error</h3>";
    echo "<p>{$e->getMessage()}</p>";
    echo "</div>";
}
