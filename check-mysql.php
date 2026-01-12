<?php
/**
 * MySQL Server Status Check
 * This script helps diagnose issues with MySQL connections
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load configuration early so we have access to database settings
$config = require_once __DIR__ . '/includes/config.php';

echo "<h1>MySQL Server Check</h1>";

// Check if XAMPP MySQL service is running
$isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

echo "<h2>1. XAMPP Service Status Check</h2>";
if ($isWindows) {
    // Windows method to check MySQL service
    $output = [];
    exec('sc query mysql', $output, $returnVar);
    
    if ($returnVar === 0) {
        echo "<pre>" . implode("\n", $output) . "</pre>";
        
        // Check if the output indicates the service is running
        $isRunning = false;
        foreach ($output as $line) {
            if (strpos($line, 'RUNNING') !== false) {
                $isRunning = true;
                break;
            }
        }
        
        if ($isRunning) {
            echo "<p style='color: green;'>✓ MySQL service appears to be running.</p>";
        } else {
            echo "<p style='color: red;'>✗ MySQL service is not running.</p>";
            echo "<p><strong>Solution:</strong> Open XAMPP Control Panel and start MySQL.</p>";
            echo "<img src='https://www.apachefriends.org/images/xampp-panel.jpg' style='max-width: 500px; border: 1px solid #ccc;' alt='XAMPP Control Panel'>";
        }
    } else {
        echo "<p>Unable to query service status. Checking port directly.</p>";
    }
} else {
    // Linux/Mac method
    exec('ps aux | grep mysql | grep -v grep', $output, $returnVar);
    
    if (!empty($output)) {
        echo "<pre>" . implode("\n", $output) . "</pre>";
        echo "<p style='color: green;'>✓ MySQL process appears to be running.</p>";
    } else {
        echo "<p style='color: red;'>✗ MySQL process does not appear to be running.</p>";
        echo "<p><strong>Solution:</strong> Start MySQL from XAMPP Control Panel or command line.</p>";
    }
}

echo "<h2>2. Port Availability Check</h2>";

// Check if the MySQL port is open
$port = $config['db_port'];
$serverAddress = $config['db_host'] === 'localhost' ? '127.0.0.1' : $config['db_host'];
$socket = @fsockopen($serverAddress, $port, $errno, $errstr, 5);

if ($socket) {
    echo "<p style='color: green;'>✓ Port $port is open and accepting connections.</p>";
    fclose($socket);
} else {
    echo "<p style='color: red;'>✗ Port $port is closed or blocked. Error: $errstr ($errno)</p>";
    echo "<p><strong>Solutions:</strong></p>";
    echo "<ol>
            <li>Make sure MySQL is running in XAMPP Control Panel</li>
            <li>Check if another application is using port $port</li>
            <li>Check your firewall settings to ensure it's not blocking this port</li>
            <li>MySQL might be configured to use a different port - check your my.ini file</li>
          </ol>";
}

echo "<h2>3. Config Check</h2>";
echo "<p>Host: " . htmlspecialchars($config['db_host']) . "</p>";
echo "<p>Port: " . htmlspecialchars($config['db_port']) . "</p>";
echo "<p>User: " . htmlspecialchars($config['db_user']) . "</p>";
echo "<p>Database: " . htmlspecialchars($config['db_name']) . "</p>";

echo "<h2>4. Manual Connection Test</h2>";

// Try connecting multiple ways
echo "<h3>Attempt 1: Using config values</h3>";
try {
    $conn = @new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], '', $config['db_port']);
    if ($conn->connect_error) {
        echo "<p style='color: red;'>✗ Failed: " . htmlspecialchars($conn->connect_error) . "</p>";
    } else {
        echo "<p style='color: green;'>✓ Connected successfully!</p>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h3>Attempt 2: Using IP address</h3>";
try {
    $conn = @new mysqli('127.0.0.1', $config['db_user'], $config['db_pass'], '', $config['db_port']);
    if ($conn->connect_error) {
        echo "<p style='color: red;'>✗ Failed: " . htmlspecialchars($conn->connect_error) . "</p>";
    } else {
        echo "<p style='color: green;'>✓ Connected successfully!</p>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>5. XAMPP Configuration Check</h2>";

// Look for my.ini location
$possiblePaths = [
    'C:/xampp/mysql/bin/my.ini',
    'C:/xampp/mysql/my.ini',
    '/Applications/XAMPP/xamppfiles/etc/my.cnf',
    '/opt/lampp/etc/my.cnf'
];

$iniFound = false;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        echo "<p>Found MySQL configuration at: " . htmlspecialchars($path) . "</p>";
        $iniFound = true;
        
        // Try to read the port from the config
        $iniContent = file_get_contents($path);
        if (preg_match('/port\s*=\s*(\d+)/i', $iniContent, $matches)) {
            $configuredPort = $matches[1];
            echo "<p>MySQL is configured to use port: " . htmlspecialchars($configuredPort) . "</p>";
            
            if ($configuredPort != $config['db_port']) {
                echo "<p style='color: orange;'>⚠️ MySQL is using a port ($configuredPort) that doesn't match your config.php ({$config['db_port']}). You may need to update your config.php.</p>";
            }
        }
        break;
    }
}

if (!$iniFound) {
    echo "<p>Could not find MySQL configuration file. Please check your XAMPP installation.</p>";
}

echo "<h2>Troubleshooting Steps</h2>";
echo "<ol>
        <li><strong>Start MySQL Server:</strong> Open XAMPP Control Panel and click the 'Start' button next to MySQL.</li>
        <li><strong>Check Logs:</strong> Look at XAMPP logs (in XAMPP Control Panel, click 'Logs' > 'MySQL') for any errors.</li>
        <li><strong>Restart XAMPP:</strong> Try restarting all XAMPP services.</li>
        <li><strong>Port Conflict:</strong> If another application is using port {$config['db_port']}, either:
            <ul>
                <li>Stop that application, or</li>
                <li>Configure MySQL to use a different port in my.ini</li>
            </ul>
        </li>
        <li><strong>Antivirus/Firewall:</strong> Temporarily disable to check if it's blocking connections.</li>
        <li><strong>Windows Services:</strong> Check if MySQL service is actually running in Windows Services.</li>
     </ol>";

echo "<p><a href='install/db_setup.php' class='button' style='display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Try Database Setup Again</a></p>";
?>
