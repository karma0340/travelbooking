<?php
/**
 * Save Review API
 */
define('SECURE_ACCESS', true);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// CSRF Protection
$token = $_POST['csrf_token'] ?? '';
if (!verifyCSRFToken($token)) {
    echo json_encode(['success' => false, 'message' => 'Security token mismatch']);
    exit;
}

// Get Google OAuth data
$google_id = trim($_POST['google_id'] ?? '');
$google_picture = trim($_POST['google_picture'] ?? '');
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$location = trim($_POST['location'] ?? '');
$rating = intval($_POST['rating'] ?? 5);
$review_text = trim($_POST['review_text'] ?? '');

// Debug logging removed

// Require Google authentication
if (empty($google_id)) {
    echo json_encode(['success' => false, 'message' => 'Please sign in with Google to submit a review']);
    exit;
}

// Only require review text
if (empty($review_text)) {
    echo json_encode(['success' => false, 'message' => 'Please write your review']);
    exit;
}

// Prepare data for saving
$reviewData = [
    'name' => $name,
    'email' => $email,
    'location' => $location,
    'rating' => $rating,
    'review_text' => $review_text,
    'image_path' => null, // We could handle image upload here if needed
    'google_id' => $google_id ?: null,
    'google_picture' => $google_picture ?: null
];

$result = saveGeneralReview($reviewData);

if ($result) {
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for your review! It has been published.'
    ]);
} else {
    // Get the last database error for debugging
    global $conn;
    $error_details = '';
    if (isset($conn) && $conn) {
        $error_details = mysqli_error($conn);
    }
    
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to save review. Please try again later.',
        'debug' => $error_details // Remove this in production
    ]);
}
?>
