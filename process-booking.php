<?php
// Process booking requests and save to database/file
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Validate required fields
$requiredFields = ['name', 'email', 'phone', 'tour_package', 'travel_date', 'guests', 'terms'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    $response['message'] = 'Missing required fields: ' . implode(', ', $missingFields);
    echo json_encode($response);
    exit;
}

// Validate email
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email address';
    echo json_encode($response);
    exit;
}

// Sanitize inputs
$name = htmlspecialchars($_POST['name']);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$phone = htmlspecialchars($_POST['phone']);
$tourPackage = htmlspecialchars($_POST['tour_package']);
$travelDate = htmlspecialchars($_POST['travel_date']);
$guests = (int)$_POST['guests'];
$message = htmlspecialchars($_POST['message'] ?? '');

// Create booking data array
$booking = [
    'id' => uniqid(),
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'tour_package' => $tourPackage,
    'travel_date' => $travelDate,
    'guests' => $guests,
    'message' => $message,
    'status' => 'pending',
    'created_at' => date('Y-m-d H:i:s')
];

// Ensure data directory exists
$dataDir = __DIR__ . '/data';
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// Path to bookings file
$bookingsFile = $dataDir . '/bookings.json';

// Load existing bookings
$bookings = [];
if (file_exists($bookingsFile)) {
    $bookingsData = file_get_contents($bookingsFile);
    if ($bookingsData) {
        $bookings = json_decode($bookingsData, true) ?: [];
    }
}

// Add new booking
$bookings[] = $booking;

// Save bookings back to file
if (file_put_contents($bookingsFile, json_encode($bookings, JSON_PRETTY_PRINT))) {
    $response['success'] = true;
    $response['message'] = 'Booking submitted successfully';
    
    // Send confirmation email if mail function is available
    if (function_exists('mail')) {
        $subject = 'Booking Confirmation - Travel In Peace';
        $headers = "From: bookings@shimlaairlines.com\r\n";
        $headers .= "Reply-To: bookings@shimlaairlines.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $mailBody = "
        <html>
        <head>
            <title>Booking Confirmation - Travel In Peace</title>
        </head>
        <body>
            <h2>Thank you for your booking!</h2>
            <p>Dear $name,</p>
            <p>We have received your booking request for the following:</p>
            <ul>
                <li><strong>Tour Package:</strong> $tourPackage</li>
                <li><strong>Travel Date:</strong> $travelDate</li>
                <li><strong>Number of Guests:</strong> $guests</li>
            </ul>
            <p>Our team will contact you shortly at $phone to confirm your booking details.</p>
            <p>Thank you for choosing Travel In Peace!</p>
        </body>
        </html>
        ";
        
        mail($email, $subject, $mailBody, $headers);
        
        // Also send notification to admin
        mail('admin@shimlaairlines.com', 'New Booking Request', "New booking from $name for $tourPackage on $travelDate", $headers);
    }
} else {
    $response['message'] = 'Failed to save booking data';
}

echo json_encode($response);
exit;
