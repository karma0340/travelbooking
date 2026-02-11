<?php
/**
 * Sync Google Reviews via Places API
 * 
 * Fetches reviews from Google and stores them locally.
 * Limitations: Standard API returns only 5 'most relevant' reviews.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/google-config.php';

function syncGoogleReviews() {
    // 1. Check Prerequisities
    if (GOOGLE_API_KEY === 'YOUR_API_KEY_HERE' || GOOGLE_PLACE_ID_API === 'YOUR_PLACE_ID_HERE') {
        return ['success' => false, 'message' => 'API Key or Place ID not configured'];
    }

    // 2. Fetch from Google API
    $finalPlaceId = GOOGLE_PLACE_ID_API;
    
    // Now fetch reviews using the actual Place ID
    $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id=" . $finalPlaceId . "&fields=reviews,rating,user_ratings_total,name&key=" . GOOGLE_API_KEY;

    // Initialize CURL for the second request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return ['success' => false, 'message' => 'Failed to connect to Google API'];
    }

    $data = json_decode($response, true);

    if (!isset($data['result']['reviews'])) {
        return ['success' => false, 'message' => 'No reviews found in API response'];
    }

    // 3. Save to Database
    $conn = getDbConnection();
    $count = 0;

    foreach ($data['result']['reviews'] as $review) {
        // Prepare data
        $authorName = $review['author_name'];
        $rating = $review['rating'];
        $text = $review['text'];
        $photoUrl = isset($review['profile_photo_url']) ? $review['profile_photo_url'] : '';
        $authorUrl = isset($review['author_url']) ? $review['author_url'] : ''; // Use as unique ID
        $time = $review['time']; // Unix timestamp

        // Check if exists
        $stmt = $conn->prepare("SELECT id FROM customer_reviews WHERE google_id = ?");
        $stmt->bind_param("s", $authorUrl);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            // Insert New
            $insert = $conn->prepare("INSERT INTO customer_reviews (name, rating, review_text, image_path, google_id, created_at, status) VALUES (?, ?, ?, ?, ?, FROM_UNIXTIME(?), 'approved')");
            $insert->bind_param("sisssi", $authorName, $rating, $text, $photoUrl, $authorUrl, $time);
            if ($insert->execute()) {
                $count++;
            }
            $insert->close();
        } else {
            // Optional: Update existing (e.g. if they changed their review)
            // For now, we skip to save resources
        }
        $stmt->close();
    }

    return ['success' => true, 'message' => "Synced $count new reviews", 'count' => $count];
}

// If run directly (e.g. via cron or browser for testing)
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header('Content-Type: application/json');
    echo json_encode(syncGoogleReviews());
}
?>
