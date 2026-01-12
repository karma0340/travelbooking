<?php
/**
 * Production Configuration for Vercel Deployment
 * 
 * This file reads configuration from environment variables
 * which should be set in Vercel dashboard
 */

// Prevent direct access to this file
if (!defined('SECURE_ACCESS')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is forbidden');
}

// Check if we're running on Vercel (production)
$isProduction = isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']);

// Database configuration
$config = [
    // Database settings - use environment variables in production
    'db_host' => $isProduction ? ($_ENV['DB_HOST'] ?? getenv('DB_HOST')) : 'localhost',
    'db_port' => $isProduction ? (int)($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? 3306) : 3306,
    'db_name' => $isProduction ? ($_ENV['DB_NAME'] ?? getenv('DB_NAME')) : 'codexmlt_shimla_airlines',
    'db_user' => $isProduction ? ($_ENV['DB_USER'] ?? getenv('DB_USER')) : 'root',
    'db_pass' => $isProduction ? ($_ENV['DB_PASS'] ?? getenv('DB_PASS')) : '',
    'db_charset' => 'utf8mb4',
    'db_engine' => 'InnoDB',
    'db_version' => '5.6',
    
    // Site settings
    'site_url' => $isProduction ? ($_ENV['SITE_URL'] ?? getenv('SITE_URL')) : 'http://localhost',
    'site_name' => 'Shimla Air Lines',
    
    // Security settings
    'auth_timeout' => 1800, // 30 minutes
    'login_attempts' => 5,  // Maximum failed login attempts
    'lockout_time' => 900,  // Lockout time in seconds (15 minutes)
    
    // Contact information
    'contact_email' => 'travelinpeace605@gmail.com',
    'contact_phone' => '+91 8627873362, +91 7559775470',
    'contact_address' => 'Near ISBT, Tutikandi, Shimla, HP 171004'
];

return $config;
