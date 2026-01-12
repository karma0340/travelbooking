<?php

/**
 * SEO Helper Functions
 * Provides SEO optimization functions for the website
 */

/**
 * Generate SEO-optimized title
 */
function generateSEOTitle($pageTitle, $siteName = 'Travel In Peace') {
    if (empty($pageTitle)) {
        return $siteName . ' - Experience Himachal\'s Beauty';
    }
    return $pageTitle . ' - ' . $siteName;
}

/**
 * Generate SEO-optimized meta description
 */
function generateSEODescription($content, $maxLength = 160) {
    $content = strip_tags($content);
    $content = preg_replace('/\s+/', ' ', $content);
    return substr($content, 0, $maxLength);
}

/**
 * Generate SEO keywords from content
 */
function generateSEOKeywords($content) {
    $keywords = [
        'travel in peace', 'himachal pradesh', 'shimla tours', 'manali tours',
        'dharamshala tours', 'vehicle rental', 'taxi service', 'travel agency',
        'himachal tourism', 'kullu manali', 'spiti valley', 'kinnaur',
        'lahaul spiti', 'himachal travel packages', 'shimla taxi service',
        'manali taxi', 'dharamshala taxi', 'himachal tour packages'
    ];
    
    // Add page-specific keywords
    if (!empty($content)) {
        $content = strtolower(strip_tags($content));
        $additionalKeywords = [];
        
        // Extract potential keywords from content
        $locations = ['shimla', 'manali', 'dharamshala', 'kullu', 'spiti', 'kinnaur', 'lahaul'];
        foreach ($locations as $location) {
            if (strpos($content, $location) !== false) {
                $additionalKeywords[] = $location . ' tours';
                $additionalKeywords[] = $location . ' taxi';
                $additionalKeywords[] = $location . ' travel';
            }
        }
        
        $keywords = array_merge($keywords, $additionalKeywords);
    }
    
    return implode(', ', array_unique($keywords));
}

/**
 * Generate structured data for TravelAgency
 */
function generateStructuredData($pageTitle, $pageDescription, $currentPage = '') {
    $config = include 'config.php';
    $baseUrl = $config['site_url'];
    
    $structuredData = [
        "@context" => "https://schema.org",
        "@type" => "TravelAgency",
        "name" => "Travel In Peace",
        "description" => $pageDescription,
        "url" => $baseUrl,
        "logo" => $baseUrl . "/images/logo.png",
        "image" => $baseUrl . "/images/logo.png",
        "telephone" => "+91 7559775470",
        "email" => "travelinpeace605@gmail.com",
        "address" => [
            "@type" => "PostalAddress",
            "addressLocality" => "Shimla",
            "addressRegion" => "Himachal Pradesh",
            "addressCountry" => "India"
        ],
        "sameAs" => [
            "https://www.instagram.com/travelinpeace605",
            "https://www.facebook.com/travelinpeace",
            "https://twitter.com/travelinpeace"
        ],
        "priceRange" => "$$",
        "openingHours" => "Mo-Su 00:00-23:59",
        "serviceType" => "Tour Package Booking, Vehicle Rental, Travel Services"
    ];
    
    // Add page-specific structured data
    if ($currentPage === 'vehicles') {
        $structuredData["@type"] = "LocalBusiness";
        $structuredData["serviceType"] = "Vehicle Rental, Taxi Service";
    } elseif ($currentPage === 'tours') {
        $structuredData["@type"] = "TouristTrip";
        $structuredData["serviceType"] = "Tour Packages, Travel Services";
    }
    
    return json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

/**
 * Generate breadcrumb structured data
 */
function generateBreadcrumbData($breadcrumbs) {
    $breadcrumbList = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => []
    ];
    
    foreach ($breadcrumbs as $index => $breadcrumb) {
        $breadcrumbList["itemListElement"][] = [
            "@type" => "ListItem",
            "position" => $index + 1,
            "name" => $breadcrumb["name"],
            "item" => $breadcrumb["url"]
        ];
    }
    
    return json_encode($breadcrumbList, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

/**
 * Generate Open Graph meta tags
 */
function generateOpenGraphTags($pageTitle, $pageDescription, $imageUrl = null) {
    $config = include 'config.php';
    $baseUrl = $config['site_url'];
    $currentUrl = $baseUrl . $_SERVER['REQUEST_URI'];
    
    return [
        'og:type' => 'website',
        'og:title' => $pageTitle,
        'og:description' => $pageDescription,
        'og:image' => $imageUrl ?: $baseUrl . '/images/logo.png',
        'og:url' => $currentUrl,
        'og:site_name' => 'Travel In Peace',
        'og:locale' => 'en_US'
    ];
}

/**
 * Generate Twitter Card meta tags
 */
function generateTwitterCardTags($pageTitle, $pageDescription, $imageUrl = null) {
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    
    return [
        'twitter:card' => 'summary_large_image',
        'twitter:title' => $pageTitle,
        'twitter:description' => $pageDescription,
        'twitter:image' => $imageUrl ?: $baseUrl . '/images/logo.png',
        'twitter:site' => '@travelinpeace'
    ];
}

?>
