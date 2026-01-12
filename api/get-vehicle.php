<?php
/**
 * API endpoint for getting vehicle details
 */

// Set headers
header('Content-Type: application/json');

// Include database functions
require_once '../includes/db.php';

// Check if vehicle ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid vehicle ID'
    ]);
    exit;
}

// Get vehicle ID from request
$vehicleId = (int)$_GET['id'];

// Get vehicle data from database
try {
    $vehicle = getVehicleById($vehicleId);
    
    if (!$vehicle) {
        echo json_encode([
            'success' => false,
            'message' => 'Vehicle not found'
        ]);
        exit;
    }
    
    // Return vehicle data
    echo json_encode([
        'success' => true,
        'vehicle' => $vehicle
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving vehicle data: ' . $e->getMessage()
    ]);
}
