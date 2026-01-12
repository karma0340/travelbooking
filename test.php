<?php
// Test SEO function
require_once 'includes/seo-helper.php';

echo "✅ SEO Helper Test:<br>";
echo "generateSEOKeywords function exists: " . (function_exists('generateSEOKeywords') ? 'YES' : 'NO') . "<br>";

// Test the function
$testKeywords = generateSEOKeywords("shimla trip, himachal tour");
echo "Test keywords: " . $testKeywords . "<br>";

echo "<br>✅ XAMPP is working!<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current directory: " . __DIR__;
?>
