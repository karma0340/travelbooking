<?php
// Start session
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (isset($_SESSION['admin_user'])) {
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
    
    // Return success response
    echo json_encode(['success' => true, 'message' => 'Session refreshed']);
} else {
    // Return error for not logged in
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
}
?>
