<?php
/**
 * Database Connection Test
 * This script helps diagnose database connection issues
 */

// Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Test</h1>";

// Get configuration
$config = require_once __DIR__ . '/includes/config.php';

echo "<h2>Configuration</h2>";
echo "<ul>";
echo "<li>Host: " . htmlspecialchars($config['db_host']) . "</li>";
echo "<li>Database: " . htmlspecialchars($config['db_name']) . "</li>";
echo "<li>User: " . htmlspecialchars($config['db_user']) . "</li>";
echo "</ul>";

echo "<h2>Connection Tests</h2>";

// Test 1: Connect with TCP/IP (127.0.0.1)
echo "<h3>Test 1: Connect using 127.0.0.1</h3>";
try {
    $conn = @new mysqli('127.0.0.1', $config['db_user'], $config['db_pass']);
    if ($conn->connect_error) {
        echo "<p style='color: red;'>Failed: " . htmlspecialchars($conn->connect_error) . "</p>";
    } else {
        echo "<p style='color: green;'>Success! Connected to MySQL server.</p>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 2: Connect with socket (localhost)
echo "<h3>Test 2: Connect using localhost</h3>";
try {
    $conn = @new mysqli('localhost', $config['db_user'], $config['db_pass']);
    if ($conn->connect_error) {
        echo "<p style='color: red;'>Failed: " . htmlspecialchars($conn->connect_error) . "</p>";
    } else {
        echo "<p style='color: green;'>Success! Connected to MySQL server.</p>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// MySQL Server Status Check
echo "<h3>Test 3: Check MySQL Service Status</h3>";

if (function_exists('exec')) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        exec('sc query mysql', $output, $returnVal);
        
        if ($returnVal === 0) {
            echo "<p>MySQL Service Status:</p>";
            echo "<pre>" . implode("\n", $output) . "</pre>";
        } else {
            echo "<p>Could not query MySQL service status. Try checking XAMPP Control Panel.</p>";
        }
    } else {
        // Linux/Unix
        exec('systemctl status mysql', $output, $returnVal);
        
        if ($returnVal === 0) {
            echo "<p>MySQL Service Status:</p>";
            echo "<pre>" . implode("\n", $output) . "</pre>";
        } else {
            echo "<p>Could not query MySQL service status.</p>";
        }
    }
} else {
    echo "<p>Cannot check MySQL service status (exec function is disabled).</p>";
}

echo "<h2>Troubleshooting Steps</h2>";
echo "<ol>";
echo "<li>Make sure MySQL service is running in XAMPP Control Panel</li>";
echo "<li>Check if MySQL is running on the default port (3306)</li>";
echo "<li>Verify the username and password in your config file</li>";
echo "<li>Make sure the firewall is not blocking MySQL connections</li>";
echo "<li>Try restarting the MySQL service</li>";
echo "</ol>";

echo "<a href='install/db_setup.php'>Run Database Setup Script</a>";
