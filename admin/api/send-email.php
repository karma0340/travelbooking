<?php
/**
 * Send Email API Endpoint
 * 
 * Handles sending emails to booking customers from the admin panel
 */
// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Set JSON content type for response
header('Content-Type: application/json');

// Include database functions
require_once '../../includes/db.php';

/**
 * Logs email sending activity to the database
 * @param array $logData Array containing email log data
 * @return bool Success status of the logging operation
 */
function logEmailSent($logData) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO email_log (booking_id, user_id, email_to, subject, sent_at) 
                           VALUES (:booking_id, :user_id, :email_to, :subject, :sent_at)");
    
    return $stmt->execute([
        ':booking_id' => $logData['booking_id'],
        ':user_id' => $logData['user_id'],
        ':email_to' => $logData['email_to'],
        ':subject' => $logData['subject'],
        ':sent_at' => $logData['sent_at']
    ]);
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Validate required fields
if (empty($_POST['booking_id']) || empty($_POST['email_to']) || empty($_POST['subject']) || empty($_POST['message'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields'
    ]);
    exit;
}

// Sanitize inputs
$bookingId = filter_var($_POST['booking_id'], FILTER_VALIDATE_INT);
$emailTo = filter_var($_POST['email_to'], FILTER_VALIDATE_EMAIL);
$subject = htmlspecialchars($_POST['subject']);
$message = htmlspecialchars($_POST['message']);
$includeDetails = isset($_POST['include_details']) && $_POST['include_details'] === 'on';

// Validate booking ID and email
if (!$bookingId || !$emailTo) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid booking ID or email address'
    ]);
    exit;
}

try {
    // Get booking details if needed
    $bookingDetails = "";
    if ($includeDetails) {
        $booking = getBookingById($bookingId);
        
        if ($booking) {
            $bookingDetails = "\n\n--- BOOKING DETAILS ---\n";
            $bookingDetails .= "Reference: " . $booking['booking_ref'] . "\n";
            $bookingDetails .= "Name: " . $booking['name'] . "\n";
            $bookingDetails .= "Travel Date: " . date('d M Y', strtotime($booking['travel_date'])) . "\n";
            if (!empty($booking['end_date'])) {
                $bookingDetails .= "Return Date: " . date('d M Y', strtotime($booking['end_date'])) . "\n";
            }
            $bookingDetails .= "Guests: " . $booking['guests'] . "\n";
            $bookingDetails .= "Status: " . ucfirst($booking['status']) . "\n";
        }
    }
    
    // Compose email
    $emailBody = $message . $bookingDetails;
    $emailBody .= "\n\n--\n Travel In Peace\nPhone: +91 8627873362 / +91 7559775470\nEmail: travelinpeace605@gmail.com";
    
    // Set email headers
    $headers = "From: Travel In Peace <travelinpeace605@gmail.com>\r\n";
    $headers .= "Reply-To: travelinpeace605@gmail.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Send email
    $mailSent = mail($emailTo, $subject, $emailBody, $headers);
    
    if ($mailSent) {
        // Log email in database
        $logData = [
            'booking_id' => $bookingId,
            'user_id' => $_SESSION['user_id'],
            'email_to' => $emailTo,
            'subject' => $subject,
            'sent_at' => date('Y-m-d H:i:s')
        ];
        
        // Save to email_log table if it exists
        if (function_exists('logEmailSent')) {
            logEmailSent($logData);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Email sent successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send email'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
