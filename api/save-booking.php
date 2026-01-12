<?php
// Prevent any output before JSON
ob_start();

// Disable error display for production/AJAX
ini_set('display_errors', 0);
error_reporting(E_ALL);

// This API endpoint handles saving booking form submissions
header('Content-Type: application/json; charset=utf-8');

// Include required files
require_once '../includes/db.php';
require_once '../includes/security.php';

// Add this near the top of your file after the initial includes
// to ensure all PHP errors are caught and converted to JSON responses:

set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    // Log the error
    error_log("PHP Error in save-booking.php: $message in $file on line $line");
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Unknown error occurred'
];

// Log API requests for debugging
function logApiRequest($message, $data = []) {
    $logFile = '../logs/api_' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    
    // Create logs directory if it doesn't exist
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Format log message
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $logMessage = "[$timestamp] [$ip] $message: " . json_encode($data) . PHP_EOL;
    
    // Write to log file
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

try {
    // Ensure this is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Verify CSRF token to prevent cross-site request forgery
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        logApiRequest('Missing CSRF token', [
            'post_has_token' => isset($_POST['csrf_token']),
            'session_has_token' => isset($_SESSION['csrf_token'])
        ]);
        throw new Exception('Security validation failed. Please refresh the page and try again.');
    }
    
    if (!isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        logApiRequest('CSRF token mismatch', [
            'session_token' => $_SESSION['csrf_token'] ?? 'not set',
            'post_token' => $_POST['csrf_token']
        ]);
        throw new Exception('Security validation failed. Please refresh the page and try again.');
    }

    // Sanitize and validate input data
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = filter_var(sanitizeInput($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $tourId = isset($_POST['tour_id']) ? filter_var($_POST['tour_id'], FILTER_VALIDATE_INT) : null;
    $vehicleId = isset($_POST['vehicle_id']) ? filter_var($_POST['vehicle_id'], FILTER_VALIDATE_INT) : null;
    $tourPackage = sanitizeInput($_POST['tour_package'] ?? null);
    // If tour_id is not set or not valid, set to null to avoid FK errors
    if (empty($tourId) || $tourId <= 0) {
        $tourId = null;
    }
    $travelDate = sanitizeInput($_POST['travel_date'] ?? '');
    $endDate = sanitizeInput($_POST['end_date'] ?? '');
    $guests = isset($_POST['guests']) ? filter_var($_POST['guests'], FILTER_VALIDATE_INT) : 1;
    $message = sanitizeInput($_POST['message'] ?? '');

    // Validate email specifically
    if ($email === false) {
        throw new Exception('Please provide a valid email address');
    }

    // Log sanitized data
    logApiRequest('Sanitized booking data', [
        'name' => $name,
        'email' => $email,
        'tour_id' => $tourId,
        'vehicle_id' => $vehicleId,
        'tour_package' => $tourPackage,
        'travel_date' => $travelDate
    ]);

    // Validation
    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($phone)) $errors[] = 'Phone is required';
    if (empty($travelDate)) $errors[] = 'Travel date is required';
    
    // At least one of tour_id, vehicle_id or tour_package must be provided
    if (empty($tourId) && empty($vehicleId) && empty($tourPackage)) {
        $errors[] = 'Please select a tour or vehicle to book';
    }

    if (!empty($errors)) {
        throw new Exception('Please correct the following errors: ' . implode(', ', $errors));
    }

    // Create booking data array
    $bookingData = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'tour_id' => $tourId,
        'vehicle_id' => $vehicleId,
        'tour_package' => $tourPackage,
        'travel_date' => $travelDate,
        'end_date' => $endDate,
        'guests' => $guests,
        'message' => $message,
        'status' => 'pending',
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Save booking to database
    logApiRequest('Calling saveBooking function');
    $result = saveBooking($bookingData);
    logApiRequest('saveBooking result', is_array($result) ? $result : ['result' => $result]);
    
    if (is_array($result) && isset($result['success']) && $result['success']) {
        // Generate a new CSRF token after successful submission
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // --- EMAIL NOTIFICATION LOGIC ---
        require_once __DIR__ . '/../includes/email-helper.php';

        $adminEmail = 'travelinpeace605@gmail.com'; // Admin notification email
        $subject = 'New Booking Request: ' . $result['booking_ref'];
        
        $emailBody = "New Booking Request\n\n";
        $emailBody .= "Reference: " . $result['booking_ref'] . "\n";
        $emailBody .= "Name: " . $name . "\n";
        $emailBody .= "Email: " . $email . "\n";
        $emailBody .= "Phone: " . $phone . "\n";
        $emailBody .= "Travel Date: " . $travelDate . "\n";
        if (!empty($endDate)) $emailBody .= "End Date: " . $endDate . "\n";
        $emailBody .= "Guests: " . $guests . "\n";
        if (!empty($tourPackage)) $emailBody .= "Tour Package: " . $tourPackage . "\n";
        if (!empty($message)) $emailBody .= "Message: " . $message . "\n";
        
        // Send email to admin using helper
        $mailSent = sendEmail($adminEmail, $subject, $emailBody, $email);
        
        if ($mailSent) {
            logApiRequest('Email notification sent/logged successfully');
        } else {
            logApiRequest('Email notification failed');
        }
        
        // --- WHATSAPP LINK GENERATION ---
        // Create a pre-filled WhatsApp message link for the USER to click
        $waMessage = "New Booking Request Details:%0a";
        $waMessage .= "Ref: " . $result['booking_ref'] . "%0a";
        $waMessage .= "Name: " . $name . "%0a";
        $waMessage .= "Tour: " . ($tourPackage ?? 'Custom') . "%0a";
        $waMessage .= "Date: " . $travelDate;
        
        // Use the primary admin WhatsApp number
        $adminWaNumber = "917559775470"; 
        $whatsappLink = "https://wa.me/" . $adminWaNumber . "?text=" . $waMessage;
        
        echo json_encode([
            'success' => true,
            'message' => 'Your booking has been submitted successfully! Reference: ' . $result['booking_ref'],
            'booking_ref' => $result['booking_ref'],
            'id' => $result['id'],
            'new_token' => $_SESSION['csrf_token'],
            'whatsapp_link' => $whatsappLink // Return the link to the frontend
        ]);
    } else {
        // Generate a new CSRF token for next attempt
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        $errorMessage = is_array($result) && isset($result['message']) ? 
            $result['message'] : 'Failed to save booking. Please try again.';
        
        echo json_encode([
            'success' => false,
            'message' => $errorMessage,
            'new_token' => $_SESSION['csrf_token']
        ]);
    }
} catch (Exception $e) {
    // Generate a new CSRF token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    logApiRequest('Error', ['message' => $e->getMessage()]);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'new_token' => $_SESSION['csrf_token']
    ]);
} catch (Throwable $e) {
    // Log the error server-side
    error_log($e->getMessage());
    
    // Return a JSON error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}
?>
