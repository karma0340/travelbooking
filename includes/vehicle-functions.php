<?php
/**
 * Vehicle Functions
 * CRUD operations for vehicles
 */

// Ensure db-connection is loaded
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/db-connection.php';
}

/**
 * Get vehicles with optional limit - Enhanced with better error handling
 * 
 * @param int|null $limit Maximum number of vehicles to return
 * @return array Array of vehicle data
 */
function getVehicles($limit = null) {
    try {
        $conn = getDbConnection();
        
        // Check if vehicles table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'vehicles'");
        if ($tableCheck && $tableCheck->num_rows === 0) {
            error_log("Vehicles table doesn't exist in the database. Please run the setup script.");
            return [];
        }
        
        // Try a direct query first to check if any vehicles exist
        $countQuery = "SELECT COUNT(*) as count FROM `vehicles` WHERE `active` = 1";
        $countResult = $conn->query($countQuery);
        $vehicleCount = 0;
        
        if ($countResult && $row = $countResult->fetch_assoc()) {
            $vehicleCount = $row['count'];
        }
        
        // If we have no vehicles, return empty array early
        if ($vehicleCount == 0) {
            return [];
        }
        
        $sql = "SELECT * FROM `vehicles` WHERE `active` = 1 ORDER BY `id` DESC";
        
        // Add limit if specified
        if ($limit && is_numeric($limit)) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("SQL Prepare Error in getVehicles(): " . $conn->error);
            return [];
        }
        
        // Execute and check for errors
        if (!$stmt->execute()) {
            error_log("SQL Execute Error in getVehicles(): " . $stmt->error);
            $stmt->close();
            return [];
        }
        
        $result = $stmt->get_result();
        $vehicles = [];
        
        while ($vehicle = $result->fetch_assoc()) {
            // Parse JSON features field - handle error if it's not valid JSON
            try {
                $vehicle['features'] = json_decode($vehicle['features'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $vehicle['features'] = [];
                }
            } catch (Exception $e) {
                $vehicle['features'] = [];
            }
            
            $vehicles[] = $vehicle;
        }
        
        $stmt->close();
        return $vehicles;
    } catch (Exception $e) {
        error_log("Exception in getVehicles(): " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single vehicle by ID
 * 
 * @param int $id Vehicle ID
 * @return array|null Vehicle data or null if not found
 */
function getVehicleById($id) {
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM `vehicles` WHERE `id` = ? AND `active` = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }
    
    $vehicle = $result->fetch_assoc();
    
    // Parse JSON fields stored as TEXT
    $vehicle['features'] = json_decode($vehicle['features'], true) ?: [];
    
    $stmt->close();
    return $vehicle;
}

/**
 * Create or update a vehicle
 * 
 * @param array $vehicleData Vehicle data
 * @param int|null $vehicleId Vehicle ID for updates, null for new vehicle
 * @return int|false The vehicle ID or false on failure
 */
function saveVehicle($vehicleData, $vehicleId = null) {
    $conn = getDbConnection();
    
    // Convert features to JSON
    $features = isset($vehicleData['features']) ? json_encode($vehicleData['features']) : json_encode([]);
    
    // Create slug from name if not provided
    if (empty($vehicleData['slug'])) {
        $vehicleData['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $vehicleData['name']));
    }
    
    if ($vehicleId) {
        // Update existing vehicle
        $sql = "UPDATE `vehicles` SET
            `name` = ?,
            `slug` = ?,
            `description` = ?,
            `seats` = ?,
            `bags` = ?,
            `price_per_day` = ?,
            `image` = ?,
            `features` = ?,
            `active` = ?,
            `updated_at` = NOW()
        WHERE `id` = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sssiiissii',
            $vehicleData['name'],
            $vehicleData['slug'],
            $vehicleData['description'],
            $vehicleData['seats'],
            $vehicleData['bags'],
            $vehicleData['price_per_day'],
            $vehicleData['image'],
            $features,
            $vehicleData['active'],
            $vehicleId
        );
    } else {
        // Insert new vehicle
        $sql = "INSERT INTO `vehicles` (
            `name`, `slug`, `description`, `seats`, `bags`,
            `price_per_day`, `image`, `features`, `active`, `created_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sssiiissi',
            $vehicleData['name'],
            $vehicleData['slug'],
            $vehicleData['description'],
            $vehicleData['seats'],
            $vehicleData['bags'],
            $vehicleData['price_per_day'],
            $vehicleData['image'],
            $features,
            $vehicleData['active']
        );
    }
    
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    
    $newId = $vehicleId ?: $stmt->insert_id;
    $stmt->close();
    return $newId;
}

/**
 * Delete a vehicle by ID
 * 
 * @param int $vehicleId Vehicle ID
 * @return bool Success or failure
 */
function deleteVehicle($vehicleId) {
    $conn = getDbConnection();
    
    // Delete the vehicle
    $sql = "DELETE FROM `vehicles` WHERE `id` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $vehicleId);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}
?>
