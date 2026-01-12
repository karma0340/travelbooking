<?php
// This is a debug script to verify that password verification works
$users_file = __DIR__ . '/data/users.json';

echo "Checking password verification with users.json file...<br>";

if (file_exists($users_file)) {
    $users = json_decode(file_get_contents($users_file), true);
    echo "Users file found. " . count($users) . " users loaded.<br>";
    
    // Try to verify admin password
    foreach ($users as $user) {
        if ($user['username'] === 'admin') {
            echo "Found admin user.<br>";
            echo "Password hash: " . $user['password_hash'] . "<br>";
            
            // Try standard password
            $test_password = 'admin123';
            $result = password_verify($test_password, $user['password_hash']);
            
            echo "Testing password 'admin123': " . ($result ? "SUCCESS" : "FAILED") . "<br>";
            
            // If it failed, let's create a new hash
            if (!$result) {
                echo "Creating a new hash for 'admin123': " . password_hash('admin123', PASSWORD_DEFAULT) . "<br>";
                echo "You can replace the hash in your users.json file with this new one.";
            }
        }
    }
} else {
    echo "Users file not found at: $users_file";
}
?>
