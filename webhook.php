<?php
/**
 * Webhook for handling tour booking requests
 * Saves data to a JSON file
 */

// Set the content type to JSON for all responses
header('Content-Type: application/json');

// Prepare the response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method. Only POST is allowed.';
    echo json_encode($response);
    exit;
}

// Validate required fields
$requiredFields = ['name', 'email', 'phone', 'tour_package', 'travel_date', 'guests', 'terms'];
$missing = [];

foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    $response['message'] = 'Missing required fields: ' . implode(', ', $missing);
    echo json_encode($response);
    exit;
}

// Validate email
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please provide a valid email address.';
    echo json_encode($response);
    exit;
}

// Create data directory if it doesn't exist
$dataDir = __DIR__ . '/data';
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// Booking data
$booking = [
    'id' => uniqid(),
    'name' => htmlspecialchars($_POST['name']),
    'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
    'phone' => htmlspecialchars($_POST['phone']),
    'tour_package' => htmlspecialchars($_POST['tour_package']),
    'travel_date' => htmlspecialchars($_POST['travel_date']),
    'guests' => (int)$_POST['guests'],
    'message' => htmlspecialchars($_POST['message'] ?? ''),
    'status' => 'pending',
    'created_at' => date('Y-m-d H:i:s')
];

// Path to bookings file
$bookingsFile = $dataDir . '/bookings.json';

// Load existing bookings
$bookings = [];
if (file_exists($bookingsFile)) {
    $bookingsContent = file_get_contents($bookingsFile);
    if ($bookingsContent) {
        $bookings = json_decode($bookingsContent, true) ?: [];
    }
}

// Add new booking
$bookings[] = $booking;

// Save all bookings back to file
if (file_put_contents($bookingsFile, json_encode($bookings, JSON_PRETTY_PRINT))) {
    $response['success'] = true;
    $response['message'] = 'Your booking has been submitted successfully!';
    $response['data'] = [
        'booking_id' => $booking['id']
    ];
} else {
    $response['message'] = 'Failed to save booking data. Please try again later.';
}

// Send the JSON response
echo json_encode($response);
exit;
