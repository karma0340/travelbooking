<?php
/**
 * Database Functions Aggregator
 * 
 * This file includes all modular database function files for backward compatibility.
 * Files that require 'db.php' will continue to work without modification.
 * 
 * The database layer has been modularized into:
 * - db-connection.php: Database connection and error logging
 * - tour-functions.php: Tour CRUD operations
 * - vehicle-functions.php: Vehicle CRUD operations
 * - booking-functions.php: Booking CRUD operations and dashboard stats
 * - user-functions.php: User authentication and management
 */

// Define secure access constant only if not already defined
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

// Include all modular database files
require_once __DIR__ . '/db-connection.php';
require_once __DIR__ . '/tour-functions.php';
require_once __DIR__ . '/vehicle-functions.php';
require_once __DIR__ . '/booking-functions.php';
require_once __DIR__ . '/user-functions.php';
require_once __DIR__ . '/category-functions.php';
?>
