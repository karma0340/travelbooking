<?php
/**
 * Application Configuration
 * 
 * SECURITY NOTICE: This file contains sensitive configuration information
 * and should not be accessible directly from the web.
 */

// Prevent direct access to this file
if (!defined('SECURE_ACCESS')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is forbidden');
}

// Database configuration
return [
    // Database settings - Fixed port to 3306 (default MySQL port)
    'db_host' => 'localhost', 
    'db_port' => 3306,             // Default MySQL port is 3306, not 3306
    'db_name' => 'codexmlt_shimla_airlines',
    'db_user' => 'root',           // Default XAMPP user
    'db_pass' => '',               // Default XAMPP password (blank)
    'db_charset' => 'utf8mb4',     // Character set
    'db_engine' => 'InnoDB',       // Storage engine
    'db_version' => '5.6',         // MySQL version for compatibility
    
    // Site settings
    'site_url' => 'https://travelinpeace.in', // Live domain
    'site_name' => 'Travel In Peace',
    'site_domain' => 'travelinpeace.in', // Live domain
    'site_protocol' => 'https', // HTTPS for live site
    
    // Security settings
    'auth_timeout' => 1800, // 30 minutes
    'login_attempts' => 5,  // Maximum failed login attempts
    'lockout_time' => 900,  // Lockout time in seconds (15 minutes)
    
    // Contact information
    'contact_email' => 'travelinpeace605@gmail.com',
    'contact_phone' => '+91 8627873362, +91 7559775470',
    'contact_address' => 'Near ISBT, Tutikandi, Shimla, HP 171004'
];
