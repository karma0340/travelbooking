<?php
// Define constant to prevent direct access
define('SETUP_AUTHORIZED', true);

function isLocalRequest() {
    $serverName = strtolower($_SERVER['SERVER_NAME']);
    $remoteAddr = $_SERVER['REMOTE_ADDR'];
    $localAddresses = ['127.0.0.1', '::1', 'localhost'];
    
    return (
        in_array($remoteAddr, $localAddresses) ||
        in_array($serverName, $localAddresses) ||
        strpos($serverName, '.local') !== false ||
        substr($remoteAddr, 0, 8) === '192.168.' ||
        substr($remoteAddr, 0, 7) === '172.16.'
    );
}

function validateSetupAccess() {
    $setupKey = $_GET['setup_key'] ?? '';
    $validSetupKey = 'shimla_setup_2026';
    $isLocal = isLocalRequest();
    
    if (!$isLocal && $setupKey !== $validSetupKey) {
        header('HTTP/1.1 403 Forbidden');
        echo "<h1>Access Denied</h1>";
        echo "<p>This setup script can only be accessed locally or with a valid setup key.</p>";
        exit;
    }
    
    return true;
}
