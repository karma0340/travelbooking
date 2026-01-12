<?php
session_start();
require_once '../../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_user'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileError = $file['error'];

// Check for errors
if ($fileError !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Upload failed']);
    exit;
}

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$fileType = mime_content_type($fileTmpName);
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type']);
    exit;
}

// Create base directories if they don't exist
$baseDir = '../../images';
$vehiclesDir = $baseDir . '/vehicles';
$placeholderDir = $baseDir . '/placeholder';

// Ensure directories exist with proper permissions
foreach ([$baseDir, $vehiclesDir, $placeholderDir] as $dir) {
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0755, true)) {
            echo json_encode(['success' => false, 'error' => "Failed to create directory: $dir"]);
            exit;
        }
    }
}

// Generate clean filename
$extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$cleanName = preg_replace('/[^a-z0-9]+/', '-', strtolower(pathinfo($fileName, PATHINFO_FILENAME)));
$newFileName = 'vehicle-' . substr($cleanName, 0, 30) . '-' . substr(uniqid(), -4) . '.' . $extension;
$uploadPath = $vehiclesDir . '/' . $newFileName;

// Delete any previous image with the same name pattern (not needed for now as we use unique names)
/*
$pattern = $vehiclesDir . '/vehicle-' . $cleanName . '-*';
array_map('unlink', glob($pattern));
*/

// Move uploaded file
if (move_uploaded_file($fileTmpName, $uploadPath)) {
    // Return just the relative URL for database storage
    $relativeUrl = '/images/vehicles/' . $newFileName;
    echo json_encode([
        'success' => true, 
        'url' => $relativeUrl, // This will be stored in database
        'fullUrl' => 'http://' . $_SERVER['HTTP_HOST'] . $relativeUrl // For preview only
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
    exit;
}
