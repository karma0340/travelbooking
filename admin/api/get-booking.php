<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['admin_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
    exit;
}

$bookingId = (int)$_GET['id'];

try {
    $booking = getBookingById($bookingId);
    
    if (!$booking) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }
    
    // Fetch related vehicle details if applicable
    if (!empty($booking['vehicle_id'])) {
        $vehicle = getVehicleById($booking['vehicle_id']);
        if ($vehicle) {
            $booking['vehicle'] = $vehicle;
        }
    }
    
    // Fetch related tour details if applicable
    if (!empty($booking['tour_id'])) {
        $tour = getTourById($booking['tour_id']);
        if ($tour) {
            $booking['tour'] = $tour;
        }
    }
    
    echo json_encode(['success' => true, 'booking' => $booking]);

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
