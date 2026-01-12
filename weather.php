<?php
// Weather page with embedded iframes for different locations in Himachal Pradesh

// Load required files
require_once 'includes/seo-helper.php';

// SEO Optimization
$pageTitle = "Weather Information - Travel In Peace | Himachal Weather Updates";
$pageDescription = "Get real-time weather updates for Shimla, Manali, Dharamshala, and other Himachal Pradesh destinations. Plan your trip with accurate weather forecasts.";
$pageKeywords = generateSEOKeywords("himachal weather, shimla weather, manali weather, dharamshala weather, weather forecast, himachal pradesh climate, spiti valley weather, weather updates");

// Weather locations and their corresponding iframe URLs from the live.txt file
$weatherLocations = [
    'shimla' => [
        'name' => 'Shimla',
        'url' => 'https://city.imd.gov.in/citywx/new/new_tour1.php?id=42083',
        'description' => 'The capital city of Himachal Pradesh, known for its pleasant climate and colonial architecture.',
        'best_season' => 'March to June, October to December',
        'avg_temp' => '5°C to 28°C'
    ],
    'manali' => [
        'name' => 'Manali',
        'url' => 'https://city.imd.gov.in/citywx/new/new_tour1.php?id=42065',
        'description' => 'A high-altitude resort town surrounded by mountains, popular for adventure sports.',
        'best_season' => 'March to June, October to November',
        'avg_temp' => '0°C to 25°C'
    ],
    'dharamshala' => [
        'name' => 'Dharamshala',
        'url' => 'https://city.imd.gov.in/citywx/new/new_tour1.php?id=42062',
        'description' => 'Home to the Dalai Lama and the Tibetan government-in-exile, with beautiful mountain views.',
        'best_season' => 'March to June, September to November',
        'avg_temp' => '5°C to 30°C'
    ],
    'keylong' => [
        'name' => 'Keylong',
        'url' => 'https://city.imd.gov.in/citywx/new/new_tour1.php?id=42063',
        'description' => 'The administrative center of Lahaul and Spiti, with cold desert climate.',
        'best_season' => 'May to October',
        'avg_temp' => '-15°C to 25°C'
    ],
    'kullu' => [
        'name' => 'Kullu',
        'url' => 'http://city.imd.gov.in/citywx/new/new_tour1.php?id=42081',
        'description' => 'A valley known for its ancient temples, majestic hills, and the Kullu Dussehra festival.',
        'best_season' => 'March to June, October to November',
        'avg_temp' => '3°C to 30°C'
    ],
    'sundarnagar' => [
        'name' => 'Sundarnagar',
        'url' => 'https://city.imd.gov.in/citywx/city_weather.php?id=42079',
        'description' => 'A beautiful town in Mandi district known for its lake and pleasant weather.',
        'best_season' => 'March to June, September to November',
        'avg_temp' => '8°C to 35°C'
    ],
    'kalpa' => [
        'name' => 'Kalpa',
        'url' => 'https://city.imd.gov.in/citywx/city_weather.php?id=42066',
        'description' => 'A small village in Kinnaur district with spectacular views of Kinner Kailash.',
        'best_season' => 'April to June, September to October',
        'avg_temp' => '-5°C to 25°C'
    ],
    'chamba' => [
        'name' => 'Chamba',
        'url' => 'https://city.imd.gov.in/citywx/new/new_tour1.php?id=8205',
        'description' => 'An ancient town with a rich history, beautiful temples, and palaces.',
        'best_season' => 'March to June, September to November',
        'avg_temp' => '5°C to 35°C'
    ],
    'hamirpur' => [
        'name' => 'Hamirpur',
        'url' => 'https://city.imd.gov.in/citywx/city_weather.php?id=11001',
        'description' => 'A district known for education institutions and moderate climate.',
        'best_season' => 'February to May, September to December',
        'avg_temp' => '10°C to 38°C'
    ],
    'una' => [
        'name' => 'Una',
        'url' => 'https://city.imd.gov.in/citywx/city_weather.php?id=42077',
        'description' => 'The gateway to Himachal Pradesh from Punjab, with warmer climate.',
        'best_season' => 'October to April',
        'avg_temp' => '12°C to 40°C'
    ],
    'bilaspur' => [
        'name' => 'Bilaspur',
        'url' => 'https://city.imd.gov.in/citywx/city_weather.php?id=42080',
        'description' => 'Known for the Govind Sagar Lake and pleasant climate throughout the year.',
        'best_season' => 'October to June',
        'avg_temp' => '10°C to 37°C'
    ],
    'solan' => [
        'name' => 'Solan',
        'url' => 'https://city.imd.gov.in/citywx/city_weather.php?id=',
        'description' => 'Known as the "Mushroom city of India" with many educational institutions.',
        'best_season' => 'March to June, September to November',
        'avg_temp' => '5°C to 32°C'
    ],
    'nahan' => [
        'name' => 'Nahan',
        'url' => 'https://city.imd.gov.in/citywx/city_weather.php?id=42104',
        'description' => 'A picturesque town in Sirmaur district with colonial architecture and gardens.',
        'best_season' => 'March to June, September to November',
        'avg_temp' => '8°C to 35°C'
    ]
];

// Get currently selected location, default to Shimla
$currentLocation = 'shimla';
if (isset($_GET['location']) && array_key_exists($_GET['location'], $weatherLocations)) {
    $currentLocation = $_GET['location'];
}

// Add custom head content for responsive iframes
$extraHeadContent = '
<style>
    .responsive-iframe-container {
        position: relative;
        overflow: hidden;
        padding-top: 80%; /* Aspect ratio */
        height: 0;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-radius: 10px;
        margin-bottom: 1.5rem;
    }
    .responsive-iframe-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }
    .location-list .list-group-item {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .location-list .list-group-item:hover {
        background-color: rgba(79, 70, 229, 0.1);
    }
    .location-list .list-group-item.active {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    .weather-info-card {
        transition: all 0.3s ease;
        border-radius: 10px;
        overflow: hidden;
    }
    .weather-info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>
';

// Include header
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header py-7 bg-primary">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center text-white">
                <h1 class="display-4 fw-bold">Weather Information</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Weather</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Weather Information -->
<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title text-center">Himachal Pradesh Weather</h2>
                <p class="text-muted">Stay updated with the latest weather forecasts for various destinations in Himachal Pradesh to plan your trip better</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <div class="list-group location-list mb-4 sticky-top" style="top: 100px; z-index: 1;">
                    <?php foreach ($weatherLocations as $id => $location): ?>
                    <a href="?location=<?= $id ?>" class="list-group-item list-group-item-action <?= $id === $currentLocation ? 'active' : '' ?>">
                        <?= $location['name'] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">Weather Tips</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-snowflake text-primary me-2"></i> Check weather before planning your trip</li>
                            <li class="mb-2"><i class="fas fa-umbrella text-primary me-2"></i> Always carry rain protection</li>
                            <li class="mb-2"><i class="fas fa-mountain text-primary me-2"></i> Mountain weather can change quickly</li>
                            <li class="mb-2"><i class="fas fa-car text-primary me-2"></i> Road conditions depend on weather</li>
                            <li class="mb-2"><i class="fas fa-sun text-primary me-2"></i> Carry sunscreen even in winter</li>
                            <li class="mb-2"><i class="fas fa-tshirt text-primary me-2"></i> Pack layers for temperature variations</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3">Best Seasons to Visit</h5>
                        <div class="season-info">
                            <p><strong>Spring (March-April):</strong><br>Moderate temperatures, blooming flowers</p>
                            <p><strong>Summer (May-June):</strong><br>Peak season, pleasant escape from heat</p>
                            <p><strong>Monsoon (July-Sept):</strong><br>Lush green landscapes, occasional landslides</p>
                            <p><strong>Autumn (Oct-Nov):</strong><br>Clear skies, golden hues, excellent views</p>
                            <p><strong>Winter (Dec-Feb):</strong><br>Snowfall in higher elevations, some passes closed</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3><?= $weatherLocations[$currentLocation]['name'] ?> Weather</h3>
                                <p class="text-muted mb-0"><?= $weatherLocations[$currentLocation]['description'] ?></p>
                            </div>
                            <div class="text-end">
                                <div class="mb-1"><i class="fas fa-thermometer-half text-primary me-1"></i> Average Temp: <?= $weatherLocations[$currentLocation]['avg_temp'] ?></div>
                                <div><i class="fas fa-calendar-alt text-primary me-1"></i> Best Season: <?= $weatherLocations[$currentLocation]['best_season'] ?></div>
                            </div>
                        </div>
                        
                        <div class="responsive-iframe-container">
                            <iframe src="<?= $weatherLocations[$currentLocation]['url'] ?>" frameborder="0" allowfullscreen></iframe>
                        </div>
                        
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i> Weather data provided by the Indian Meteorological Department (IMD).
                            If you're planning a trip to <?= $weatherLocations[$currentLocation]['name'] ?>, check this forecast regularly as mountain weather can change quickly.
                        </div>
                    </div>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100 weather-info-card">
                            <div class="card-body">
                                <h4>Planning Your Trip</h4>
                                <p>To make the most of your visit to <?= $weatherLocations[$currentLocation]['name'] ?>:</p>
                                <ul>
                                    <li>Check road conditions, especially during monsoon and winter</li>
                                    <li>Pack clothing appropriate for the season</li>
                                    <li>Keep buffer days in your itinerary for weather delays</li>
                                    <li>Book accommodations in advance during peak season</li>
                                </ul>
                                <div class="mt-3">
                                    <a href="tours.php" class="btn btn-outline-primary me-2">View Our Tours</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100 weather-info-card">
                            <div class="card-body">
                                <h4>Transportation Tips</h4>
                                <p>For traveling to and around <?= $weatherLocations[$currentLocation]['name'] ?>:</p>
                                <ul>
                                    <li>Choose vehicles suitable for the local terrain</li>
                                    <li>Check if your destination requires 4x4 capability</li>
                                    <li>Some areas may have limited transportation options during winter</li>
                                    <li>Consider booking our trusted vehicles for a worry-free journey</li>
                                </ul>
                                <div class="mt-3">
                                    <a href="vehicles.php" class="btn btn-outline-primary me-2">View Our Vehicles</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <div class="card border-0 shadow-sm p-4 cta-card">
                        <h4>Need Help Planning Your Trip?</h4>
                        <p class="mb-3">Our travel experts can help you create the perfect itinerary based on weather conditions and your preferences.</p>
                        <a href="index.php#contact" class="btn book-btn text-white mx-auto">Contact Us For Custom Tour</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
