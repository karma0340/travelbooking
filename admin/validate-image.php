<?php
/**
 * Image File Validator
 * Tests if an uploaded file is a valid image
 */

echo "<h2>Image File Validator</h2>";
echo "<style>body{font-family:sans-serif;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

if (isset($_FILES['test_file']) && $_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['test_file'];
    
    echo "<h3>File Information:</h3>";
    echo "<ul>";
    echo "<li><strong>Name:</strong> " . htmlspecialchars($file['name']) . "</li>";
    echo "<li><strong>Size:</strong> " . number_format($file['size'] / 1024, 2) . " KB</li>";
    echo "<li><strong>MIME Type (reported):</strong> " . $file['type'] . "</li>";
    echo "<li><strong>Temp Path:</strong> " . $file['tmp_name'] . "</li>";
    echo "</ul>";
    
    // Test 1: getimagesize
    echo "<h3>Test 1: getimagesize()</h3>";
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo !== false) {
        echo "<p class='success'>✓ File is recognized as an image</p>";
        echo "<ul>";
        echo "<li><strong>Width:</strong> " . $imageInfo[0] . "px</li>";
        echo "<li><strong>Height:</strong> " . $imageInfo[1] . "px</li>";
        echo "<li><strong>Type:</strong> " . $imageInfo[2] . " (" . image_type_to_mime_type($imageInfo[2]) . ")</li>";
        echo "<li><strong>Actual MIME:</strong> " . $imageInfo['mime'] . "</li>";
        echo "</ul>";
        
        $actualMime = $imageInfo['mime'];
    } else {
        echo "<p class='error'>✗ Not recognized as a valid image</p>";
        echo "<p>This file is either corrupted or not a real image file.</p>";
        $actualMime = null;
    }
    
    // Test 2: Try to load with GD
    if ($actualMime) {
        echo "<h3>Test 2: GD Image Loading</h3>";
        
        $image = false;
        switch ($actualMime) {
            case 'image/jpeg':
            case 'image/jpg':
                echo "<p class='info'>Attempting to load as JPEG...</p>";
                $image = @imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                echo "<p class='info'>Attempting to load as PNG...</p>";
                $image = @imagecreatefrompng($file['tmp_name']);
                break;
            case 'image/gif':
                echo "<p class='info'>Attempting to load as GIF...</p>";
                $image = @imagecreatefromgif($file['tmp_name']);
                break;
            case 'image/webp':
                echo "<p class='info'>Attempting to load as WebP...</p>";
                if (function_exists('imagecreatefromwebp')) {
                    $image = @imagecreatefromwebp($file['tmp_name']);
                } else {
                    echo "<p class='error'>✗ WebP support not available</p>";
                }
                break;
        }
        
        if ($image !== false) {
            echo "<p class='success'>✓ Successfully loaded image with GD</p>";
            echo "<ul>";
            echo "<li><strong>Width:</strong> " . imagesx($image) . "px</li>";
            echo "<li><strong>Height:</strong> " . imagesy($image) . "px</li>";
            echo "</ul>";
            
            // Try to create a test output
            echo "<h3>Test 3: Image Processing</h3>";
            $testPath = '../uploads/images/test_' . time() . '.jpg';
            if (imagejpeg($image, $testPath, 80)) {
                echo "<p class='success'>✓ Successfully processed and saved test image</p>";
                echo "<p>Test file: <a href='$testPath' target='_blank'>$testPath</a></p>";
                echo "<img src='$testPath' style='max-width:300px; border:1px solid #ccc; margin:10px 0;'>";
            } else {
                echo "<p class='error'>✗ Failed to process image</p>";
            }
            
            imagedestroy($image);
        } else {
            echo "<p class='error'>✗ Failed to load image with GD</p>";
            echo "<p><strong>Possible reasons:</strong></p>";
            echo "<ul>";
            echo "<li>File is corrupted</li>";
            echo "<li>File extension doesn't match actual content</li>";
            echo "<li>File is not a real image (renamed file)</li>";
            echo "<li>Unsupported image variant</li>";
            echo "</ul>";
        }
    }
    
    // Test 3: File signature check
    echo "<h3>Test 4: File Signature (Magic Bytes)</h3>";
    $handle = fopen($file['tmp_name'], 'rb');
    $bytes = fread($handle, 8);
    fclose($handle);
    
    $hex = bin2hex($bytes);
    echo "<p><strong>First 8 bytes:</strong> " . strtoupper($hex) . "</p>";
    
    $signatures = [
        'FFD8FF' => 'JPEG',
        '89504E47' => 'PNG',
        '47494638' => 'GIF',
        '52494646' => 'WebP (RIFF)',
    ];
    
    $detected = 'Unknown';
    foreach ($signatures as $sig => $type) {
        if (strpos($hex, strtolower($sig)) === 0) {
            $detected = $type;
            break;
        }
    }
    
    echo "<p><strong>Detected format:</strong> $detected</p>";
    
    if ($detected === 'Unknown') {
        echo "<p class='error'>⚠ File signature doesn't match any known image format!</p>";
        echo "<p>This file may have been renamed or is not a real image.</p>";
    }
}
?>

<hr>
<h3>Upload a File to Test:</h3>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="test_file" accept="image/*" required>
    <button type="submit">Validate Image</button>
</form>

<hr>
<p><strong>Instructions:</strong></p>
<ol>
    <li>Upload the image file that's failing</li>
    <li>Check which test fails</li>
    <li>This will tell us exactly what's wrong with the file</li>
</ol>
