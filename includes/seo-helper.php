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
        // Primary High-Volume
        'travel in peace', 'taxi service', 'cab booking', 'taxi booking online', 'book taxi online', 
        'online cab booking', 'taxi near me', 'cab near me', 'local taxi service',
        
        // High-Intent / Money Keywords
        'book taxi now', 'instant cab booking', 'affordable taxi service', 'cheap cab booking',
        'best taxi service near me', '24 hour taxi service', 'taxi booking app', 'hire taxi near me',
        
        // Himachal / Route Specific (Ranking Gold)
        'best himachal tour packages 2025', 'shimla taxi service', 'manali tour package from chandigarh',
        'chandigarh to manali tour package', 'chandigarh to manali taxi package', 'chandigarh to manali cab fare',
        'delhi to manali tour package', 'delhi to manali taxi booking', 'chandigarh to shimla taxi',
        'one way taxi chandigarh to shimla', 'himachal tour package by cab', 'shimla manali taxi package',
        
        // Legacy/Generic
        'shimla taxi booking', 'cab service shimla', 'taxi near me shimla',
        'shimla to chandigarh taxi', 'shimla to manali taxi', 'outstation taxi shimla',
        'local taxi shimla', 'airport taxi shimla', 'shimla airport transfer',
        'spiti valley tour package', 'dharamshala tour packages',
        'dalhousie tour package', 'kinnaur spiti tour', 'himachal honeymoon packages',
        'himachal family packages', 'trekking in himachal pradesh', 'adventure tourism himachal',
        'car rental shimla', 'luxury car hire shimla', 'innova rental shimla',
        'tempo traveller shimla', 'one way taxi shimla', 'affordable taxi shimla',
        'reliable taxi shimla', 'himachal tourism packages', 'kullu manali tour'
    ];
    
    // Add page-specific keywords
    if (!empty($content)) {
        $content = strtolower(strip_tags($content));
        $additionalKeywords = [];
        
        // Extract potential keywords from content
        $locations = ['shimla', 'manali', 'dharamshala', 'kullu', 'spiti', 'kinnaur', 'lahaul', 'dalhousie', 'kasauli', 'chandigarh', 'delhi', 'punjab', 'ludhiana'];
        foreach ($locations as $location) {
            if (strpos($content, $location) !== false) {
                $additionalKeywords[] = $location . ' tour packages 2025';
                $additionalKeywords[] = $location . ' taxi service';
                $additionalKeywords[] = 'best travel agency in ' . $location;
                $additionalKeywords[] = 'taxi near me in ' . $location;
                $additionalKeywords[] = 'cab booking in ' . $location;
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
    
    // Base Schema for the Organization
    $structuredData = [
        "@context" => "https://schema.org",
        "@type" => "TravelAgency",
        "name" => "Travel In Peace",
        "alternateName" => "Travel In Peace Shimla",
        "description" => $pageDescription,
        "url" => $baseUrl,
        "logo" => $baseUrl . "/images/logo.png",
        "image" => $baseUrl . "/images/logo.png",
        "telephone" => "+91 8627873362",
        "email" => "travelinpeace605@gmail.com",
        "address" => [
            "@type" => "PostalAddress",
            "addressLocality" => "Shimla",
            "addressRegion" => "Himachal Pradesh",
            "addressCountry" => "IN"
        ],
        "areaServed" => [
            "Shimla",
            "Manali",
            "Dharamshala",
            "Dalhousie",
            "Spiti Valley",
            "Kinnaur",
            "Chandigarh",
            "Delhi",
            "Punjab",
            "Ludhiana",
            "Kullu"
        ],
        "hasOfferCatalog" => [
            "@type" => "OfferCatalog",
            "name" => "Taxi & Tour Services",
            "itemListElement" => [
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "Service",
                        "name" => "Chandigarh to Manali Tour Package"
                    ]
                ],
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "Service",
                        "name" => "Shimla Local Taxi Service"
                    ]
                ],
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "Service",
                        "name" => "Outstation Cab Booking"
                    ]
                ]
            ]
        ],
        "sameAs" => [
            "https://www.instagram.com/travelinpeace_/",
            "https://www.facebook.com/travelinpeace605"
        ],
        "priceRange" => "₹₹",
        "openingHoursSpecification" => [
            [
                "@type" => "OpeningHoursSpecification",
                "dayOfWeek" => ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
                "opens" => "00:00",
                "closes" => "23:59"
            ]
        ]
    ];
    
    // Page-Specific Schema Overrides
    if ($currentPage === 'vehicles.php' || strpos($pageTitle, 'Car') !== false) {
        // Add specific TaxiService/AutoRental schema
        $structuredData["@type"] = ["TravelAgency", "TaxiService", "AutoRental"];
        $structuredData["serviceType"] = "Car Rental, Taxi Service, Outstation Cabs, Sightseeing Taxi";
        $structuredData["hasOfferCatalog"] = [
            "@type" => "OfferCatalog",
            "name" => "Taxi Services",
            "itemListElement" => [
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "Service",
                        "name" => "Shimla Local Sightseeing Taxi"
                    ]
                ],
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "Service",
                        "name" => "Shimla to Manali Taxi"
                    ]
                ],
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "Service",
                        "name" => "Shimla to Chandigarh Taxi"
                    ]
                ]
            ]
        ];
    } elseif ($currentPage === 'tours.php' || strpos($pageTitle, 'Tour') !== false) {
        $structuredData["@type"] = ["TravelAgency"];
        $structuredData["serviceType"] = "Holiday Packages, Honeymoon Packages, Adventure Tours";
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
        'twitter:site' => '@travelinpeace_'
    ];
}

?>
