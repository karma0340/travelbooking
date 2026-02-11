<?php
/**
 * Image Upload Troubleshooting Script
 * Run this to check if everything is configured correctly
 */

echo "<h2>Image Upload System - Diagnostics</h2>";
echo "<style>body{font-family:sans-serif;padding:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";

// Check 1: Upload directory
echo "<h3>1. Upload Directory</h3>";
$uploadDir = '../uploads/images/';
if (file_exists($uploadDir)) {
    echo "<p class='success'>✓ Directory exists: $uploadDir</p>";
    
    if (is_writable($uploadDir)) {
        echo "<p class='success'>✓ Directory is writable</p>";
    } else {
        echo "<p class='error'>✗ Directory is NOT writable. Run: chmod 755 $uploadDir</p>";
    }
} else {
    echo "<p class='warning'>⚠ Directory doesn't exist. Creating...</p>";
    if (mkdir($uploadDir, 0755, true)) {
        echo "<p class='success'>✓ Directory created successfully</p>";
    } else {
        echo "<p class='error'>✗ Failed to create directory</p>";
    }
}

// Check 2: PHP GD Library
echo "<h3>2. PHP GD Library</h3>";
if (extension_loaded('gd')) {
    echo "<p class='success'>✓ GD library is loaded</p>";
    
    $gdInfo = gd_info();
    echo "<ul>";
    echo "<li>GD Version: " . $gdInfo['GD Version'] . "</li>";
    echo "<li>JPEG Support: " . ($gdInfo['JPEG Support'] ? '✓' : '✗') . "</li>";
    echo "<li>PNG Support: " . ($gdInfo['PNG Support'] ? '✓' : '✗') . "</li>";
    echo "<li>GIF Support: " . ($gdInfo['GIF Read Support'] && $gdInfo['GIF Create Support'] ? '✓' : '✗') . "</li>";
    echo "<li>WebP Support: " . (function_exists('imagewebp') ? '✓' : '✗') . "</li>";
    echo "</ul>";
} else {
    echo "<p class='error'>✗ GD library is NOT loaded. Install php-gd extension</p>";
}

// Check 3: PHP Upload Settings
echo "<h3>3. PHP Upload Settings</h3>";
echo "<ul>";
echo "<li>upload_max_filesize: " . ini_get('upload_max_filesize') . "</li>";
echo "<li>post_max_size: " . ini_get('post_max_size') . "</li>";
echo "<li>max_execution_time: " . ini_get('max_execution_time') . " seconds</li>";
echo "<li>memory_limit: " . ini_get('memory_limit') . "</li>";
echo "</ul>";

$maxUpload = ini_get('upload_max_filesize');
$maxPost = ini_get('post_max_size');
if (intval($maxUpload) < 10 || intval($maxPost) < 10) {
    echo "<p class='warning'>⚠ Consider increasing upload limits in php.ini</p>";
} else {
    echo "<p class='success'>✓ Upload limits are adequate</p>";
}

// Check 4: Database Connection
echo "<h3>4. Database Connection</h3>";
require_once '../includes/db.php';
try {
    $conn = getDbConnection();
    echo "<p class='success'>✓ Database connection successful</p>";
    
    // Check if entity_images table exists
    $result = $conn->query("SHOW TABLES LIKE 'entity_images'");
    if ($result->num_rows > 0) {
        echo "<p class='success'>✓ entity_images table exists</p>";
        
        // Check table structure
        $result = $conn->query("DESCRIBE entity_images");
        echo "<p>Table columns:</p><ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>{$row['Field']} ({$row['Type']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>✗ entity_images table does NOT exist. Run migrate-images.php</p>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "<p class='error'>✗ Database error: " . $e->getMessage() . "</p>";
}

// Check 5: ImageOptimizer Class
echo "<h3>5. ImageOptimizer Class</h3>";
if (file_exists('../includes/ImageOptimizer.php')) {
    echo "<p class='success'>✓ ImageOptimizer.php exists</p>";
    require_once '../includes/ImageOptimizer.php';
    
    if (class_exists('ImageOptimizer')) {
        echo "<p class='success'>✓ ImageOptimizer class loaded</p>";
    } else {
        echo "<p class='error'>✗ ImageOptimizer class not found</p>";
    }
} else {
    echo "<p class='error'>✗ ImageOptimizer.php file not found</p>";
}

// Check 6: Test Image Upload
echo "<h3>6. Test Upload</h3>";
echo "<form method='post' enctype='multipart/form-data'>";
echo "<input type='file' name='test_image' accept='image/*'>";
echo "<button type='submit' name='test_upload'>Test Upload</button>";
echo "</form>";

if (isset($_POST['test_upload']) && isset($_FILES['test_image'])) {
    echo "<h4>Upload Test Result:</h4>";
    $file = $_FILES['test_image'];
    
    echo "<ul>";
    echo "<li>File name: " . $file['name'] . "</li>";
    echo "<li>File type: " . $file['type'] . "</li>";
    echo "<li>File size: " . number_format($file['size'] / 1024, 2) . " KB</li>";
    echo "<li>Error code: " . $file['error'] . "</li>";
    echo "</ul>";
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        require_once '../includes/ImageOptimizer.php';
        $optimizer = new ImageOptimizer();
        $result = $optimizer->processUpload($file);
        
        if ($result['success']) {
            echo "<p class='success'>✓ Upload and optimization successful!</p>";
            echo "<pre>" . print_r($result, true) . "</pre>";
        } else {
            echo "<p class='error'>✗ Upload failed: " . $result['message'] . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Upload error code: " . $file['error'] . "</p>";
    }
}

echo "<hr>";
echo "<p><strong>Summary:</strong> Check all items above. All should show ✓ (green checkmarks).</p>";
echo "<p>If you see any ✗ (red X) or ⚠ (warnings), fix those issues first.</p>";
?>
