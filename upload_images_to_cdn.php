<?php
/**
 * Upload Images to ImgBB CDN
 * This script uploads local placeholder images to ImgBB and returns permanent URLs
 */

// ImgBB API Key (Free tier: unlimited uploads, permanent hosting)
// Get your free API key from: https://api.imgbb.com/
$apiKey = 'YOUR_IMGBB_API_KEY'; // Replace with your actual API key

// Directory containing images
$imageDir = __DIR__ . '/images/placeholder/';

// Images to upload
$imagesToUpload = [
    'adventure-tours.png',
    'family-tours.png',
    'group-tours.png',
    'honeymoon-tours.png',
    'manali.jpg',
    'nature-tours.png',
    'shimla.jpg',
    'spiritual-tours.png',
    'spiti.jpg',
    'vehicle-placeholder.jpg'
];

// Store uploaded URLs
$uploadedUrls = [];

echo "<h2>Uploading Images to ImgBB CDN</h2>";
echo "<p>This may take a few minutes...</p>";

foreach ($imagesToUpload as $imageName) {
    $imagePath = $imageDir . $imageName;
    
    if (!file_exists($imagePath)) {
        echo "<p style='color:red;'>❌ File not found: $imageName</p>";
        continue;
    }
    
    echo "<p>📤 Uploading: <strong>$imageName</strong>... ";
    
    // Read image and encode to base64
    $imageData = base64_encode(file_get_contents($imagePath));
    
    // Prepare API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'key' => $apiKey,
        'image' => $imageData,
        'name' => pathinfo($imageName, PATHINFO_FILENAME)
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $result = json_decode($response, true);
        
        if (isset($result['data']['url'])) {
            $url = $result['data']['url'];
            $uploadedUrls[$imageName] = $url;
            echo "✅ <span style='color:green;'>Success!</span><br>";
            echo "&nbsp;&nbsp;&nbsp;URL: <a href='$url' target='_blank'>$url</a></p>";
        } else {
            echo "❌ <span style='color:red;'>Failed - Invalid response</span></p>";
        }
    } else {
        echo "❌ <span style='color:red;'>Failed - HTTP $httpCode</span></p>";
        if ($httpCode == 400) {
            echo "<p style='color:orange;'>⚠️ Please set your ImgBB API key in this file (line 9)</p>";
        }
    }
    
    // Small delay to avoid rate limiting
    usleep(500000); // 0.5 seconds
}

echo "<hr>";
echo "<h3>Upload Summary</h3>";
echo "<p>Successfully uploaded: <strong>" . count($uploadedUrls) . "/" . count($imagesToUpload) . "</strong> images</p>";

if (!empty($uploadedUrls)) {
    echo "<h3>Generated URLs (Copy these to your seed file)</h3>";
    echo "<pre style='background:#f5f5f5; padding:15px; border-radius:5px; overflow-x:auto;'>";
    echo "// Image URLs for seed_tours.php\n";
    echo "\$cdnImages = [\n";
    foreach ($uploadedUrls as $name => $url) {
        $key = str_replace(['.jpg', '.png', '-tours'], '', $name);
        echo "    '$key' => '$url',\n";
    }
    echo "];\n";
    echo "</pre>";
    
    // Save to a JSON file for reference
    $jsonFile = __DIR__ . '/cdn_image_urls.json';
    file_put_contents($jsonFile, json_encode($uploadedUrls, JSON_PRETTY_PRINT));
    echo "<p>✅ URLs also saved to: <code>cdn_image_urls.json</code></p>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Copy the generated URLs from above</li>";
echo "<li>Update <code>seed_tours.php</code> with these CDN URLs</li>";
echo "<li>Run <code>seed_tours.php</code> to update the database</li>";
echo "<li>Optional: Delete local images from <code>images/placeholder/</code> to save space</li>";
echo "</ol>";
?>
