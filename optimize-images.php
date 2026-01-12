<?php
/**
 * Image Optimization Script
 * This script helps optimize and convert images to WebP format
 * Run this script whenever you add new images
 */

// Directory to process
$dirs = ['images/tours', 'images/vehicles'];

// Make sure the directories exist
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Output message
echo "<h2>Image Optimization Utility</h2>";
echo "<p>This script will optimize your images and convert them to WebP format.</p>";

// Check if GD or Imagick is available
if (!extension_loaded('gd') && !extension_loaded('imagick')) {
    die("<p>Error: Neither GD nor Imagick extension is available. Please enable one of these extensions to use this script.</p>");
}

function optimizeImages($dir) {
    echo "<h3>Processing directory: $dir</h3>";
    $files = glob("$dir/*.{jpg,jpeg,png,gif}", GLOB_BRACE);
    
    foreach ($files as $file) {
        $info = pathinfo($file);
        $webpFile = $info['dirname'] . '/' . $info['filename'] . '.webp';
        
        echo "Converting: " . basename($file) . " to WebP... ";
        
        if (extension_loaded('imagick') && class_exists('Imagick')) {
            // Use Imagick if available (better quality)
            try {
                $image = new Imagick($file);
                $image->setImageFormat('webp');
                $image->setOption('webp:lossless', 'false');
                $image->setOption('webp:method', '6');
                $image->setImageCompressionQuality(80);
                $image->writeImage($webpFile);
                echo "Success (Imagick)<br>";
            } catch (Exception $e) {
                echo "Failed: " . $e->getMessage() . "<br>";
            }
        } elseif (extension_loaded('gd')) {
            // Use GD as fallback
            $image = null;
            switch ($info['extension']) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($file);
                    break;
                case 'png':
                    $image = imagecreatefrompng($file);
                    break;
                case 'gif':
                    $image = imagecreatefromgif($file);
                    break;
            }
            
            if ($image) {
                imagewebp($image, $webpFile, 80);
                imagedestroy($image);
                echo "Success (GD)<br>";
            } else {
                echo "Failed: Could not process image<br>";
            }
        }
    }
}

// Process each directory
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        optimizeImages($dir);
    } else {
        echo "<p>Directory not found: $dir</p>";
    }
}

echo "<p>Image optimization completed!</p>";
