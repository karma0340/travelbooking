<?php
/**
 * API Helper Functions
 * Provides utility functions for API endpoints to work with JSON data files
 */

// Define the data directory path
define('DATA_DIR', __DIR__ . '/../data/');

/**
 * Get all items from a JSON data file
 * 
 * @param string $filename Name of the JSON file (e.g., 'bookings.json')
 * @return array Array of items or empty array if file doesn't exist
 */
function getItems($filename) {
    $filePath = DATA_DIR . $filename;
    
    // Check if file exists
    if (!file_exists($filePath)) {
        // Try to fall back to database if available
        if (function_exists('getDbConnection')) {
            $tableName = pathinfo($filename, PATHINFO_FILENAME);
            return getItemsFromDatabase($tableName);
        }
        return [];
    }
    
    // Read and decode the JSON file
    $jsonData = file_get_contents($filePath);
    $items = json_decode($jsonData, true);
    
    // Return array or empty array if decode failed
    return is_array($items) ? $items : [];
}

/**
 * Get a specific item by ID from a JSON data file
 * 
 * @param string $filename Name of the JSON file (e.g., 'tours.json')
 * @param int $id The ID to look for
 * @return array|null Item data or null if not found
 */
function getItemById($filename, $id) {
    $items = getItems($filename);
    
    // Search for the item with the given ID
    foreach ($items as $item) {
        if (isset($item['id']) && $item['id'] == $id) {
            return $item;
        }
    }
    
    // Try to fall back to database if available
    if (function_exists('getDbConnection')) {
        $tableName = pathinfo($filename, PATHINFO_FILENAME);
        return getItemFromDatabase($tableName, $id);
    }
    
    return null;
}

/**
 * Fallback: Get items from the database
 * 
 * @param string $tableName The table name
 * @return array Array of items or empty array
 */
function getItemsFromDatabase($tableName) {
    $conn = getDbConnection();
    $items = [];
    
    // Sanitize table name
    $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
    
    try {
        $result = $conn->query("SELECT * FROM `$tableName`");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Database fallback error: " . $e->getMessage());
    }
    
    return $items;
}

/**
 * Fallback: Get a specific item from the database
 * 
 * @param string $tableName The table name
 * @param int $id Item ID
 * @return array|null Item data or null if not found
 */
function getItemFromDatabase($tableName, $id) {
    $conn = getDbConnection();
    
    // Sanitize table name
    $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
    
    try {
        $stmt = $conn->prepare("SELECT * FROM `$tableName` WHERE `id` = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    } catch (Exception $e) {
        error_log("Database fallback error: " . $e->getMessage());
    }
    
    return null;
}
