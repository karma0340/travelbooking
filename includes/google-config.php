<?php
/**
 * Google OAuth Configuration
 * 
 * SETUP INSTRUCTIONS:
 * 1. Go to https://console.cloud.google.com/
 * 2. Create a new project or select existing one
 * 3. Enable Google+ API
 * 4. Go to Credentials > Create Credentials > OAuth 2.0 Client ID
 * 5. Add authorized JavaScript origins: http://localhost, http://yourdomain.com
 * 6. Add authorized redirect URIs (if needed)
 * 7. Copy the Client ID and paste below
 */

define('GOOGLE_CLIENT_ID', '285613694375-lm466vgcmk7ce7jl0hq05s1pot0is247.apps.googleusercontent.com');

// Example: '123456789-abcdefghijklmnop.apps.googleusercontent.com'
// Replace YOUR_GOOGLE_CLIENT_ID_HERE with your actual Google Client ID

/**
 * Get Google Client ID
 */
function getGoogleClientId() {
    return GOOGLE_CLIENT_ID;
}

/**
 * Check if Google OAuth is configured
 */
function isGoogleOAuthConfigured() {
    return GOOGLE_CLIENT_ID !== 'YOUR_GOOGLE_CLIENT_ID_HERE' && !empty(GOOGLE_CLIENT_ID);
}

// Verified Google Maps CID
define('GOOGLE_CID_HEX', '0x9e74c45bbb85ab6');
define('GOOGLE_FID_HEX', '0x68f59e6452ed2cc5');
define('GOOGLE_CID_DEC', '713622928347388598');

// Google Places API Configuration (REQUIRED for fetching reviews)
// Get your key here: https://console.cloud.google.com/google/maps-apis/credentials
define('GOOGLE_API_KEY', 'AIzaSyAJOxdIfz3c-6gbIPxFJt0mlNFGrmWyhNE'); 

// You need the Place ID to fetch reviews via API. 
// Use the "Place ID Finder" or check the URL of your business in Google Maps developer tools
define('GOOGLE_PLACE_ID_API', 'ChIJU1VVVUGF4TgRK8cCLLORNOs'); // Verified Place ID for Travel In Peace

function getReviewUrl() {
    // This specific URL format forces the review box to open
    $placeId = 'ChIJU1VVVUGF4TgRK8cCLLORNOs'; // Found via manual search
    return "https://search.google.com/local/writereview?placeid=" . $placeId;
}
?>
