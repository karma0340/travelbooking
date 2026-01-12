<?php
// Start session securely
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
session_start();

// Turn off error display for production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Load required files
require_once 'includes/db.php';
require_once 'includes/security.php';
require_once 'includes/seo-helper.php';

// SEO Optimization
$pageTitle = "Vehicles - Travel In Peace | Himachal Taxi & Car Rental";
$pageDescription = "Browse our fleet of well-maintained vehicles for comfortable travel across Himachal Pradesh.";
$pageKeywords = generateSEOKeywords("taxi service shimla, car rental shimla, himachal taxi service, shimla manali taxi, taxi service manali, car rental manali, taxi service dharamshala, vehicle rental himachal, taxi booking shimla");

// Set security headers
try {
    setSecurityHeaders();
} catch (Exception $e) {
    error_log("Error setting security headers: " . $e->getMessage());
}

// Initialize database connection
$conn = getDbConnection();

if (!$conn) {
    die("Database connection failed. Please check your configuration.");
}

// Get featured vehicles
$vehicles = getVehicles(6); // Get 6 vehicles

// If no vehicles in database, log issue
if (empty($vehicles)) {
    logError("No vehicles found in the database.");
}

// Generate CSRF token for forms
$csrfToken = generateCSRFToken();

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center justify-content-center" style="min-height: 60vh;">
    <div class="hero-overlay"></div>
    <div class="container" style="position: relative;">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <div data-aos="fade-up">
                    <div class="video-text-container">
                        <h1 class="video-text">Our Fleet</h1>
                        <span class="video-text subtitle">Premium Vehicles for Your Journey</span>
                        
                        <!-- Shared Video Source -->
                        <div class="video-container">
                            <video autoplay loop muted playsinline crossorigin="anonymous" id="sourceVideo">
                                <source src="https://www.w3schools.com/howto/rain.mp4" type="video/mp4">
                            </video>
                        </div>
                    </div>
                    
                    <div class="lead mb-5 mx-auto text-white" style="max-width: 800px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                        Travel in comfort and style with our well-maintained fleet of vehicles across Himachal Pradesh
                    </div>
                    
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="#vehicles" class="btn btn-primary btn-lg rounded-pill shadow-lg px-5">View Fleet</a>
                        <a href="book.php" class="btn btn-outline-light btn-lg rounded-pill px-5">Book Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Vehicles Section -->
<section id="vehicles" class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5 align-items-end">
            <div class="col-lg-8" data-aos="fade-right">
                <span class="text-primary fw-bold text-uppercase letter-spacing-1">Our Fleet</span>
                <h2 class="display-5 fw-bold">Premium Vehicles</h2>
                <p class="text-muted mb-0" style="max-width: 600px;">Choose from our wide range of well-maintained vehicles for safe and comfortable travel throughout Himachal Pradesh</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php if (empty($vehicles)): ?>
            <div class="col-12 text-center">
                <div class="alert alert-info py-4 rounded-3 shadow-sm border-0">
                    <i class="fas fa-info-circle me-2 fs-5 align-middle"></i> No vehicles are currently available. Please check back soon!
                </div>
            </div>
            <?php else: ?>
                <?php 
                $i = 0;
                foreach ($vehicles as $vehicle): 
                    // Get image from vehicle or use placeholder
                    $imageUrl = !empty($vehicle['image']) && filter_var($vehicle['image'], FILTER_VALIDATE_URL) 
                        ? $vehicle['image'] 
                        : 'images/placeholder/vehicle-placeholder.jpg';
                    $i++;
                ?>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo ($i % 3) * 100; ?>">
                    <div class="card h-100 border-0 shadow-sm hover-lift vehicle-card overflow-hidden">
                        <div class="vehicle-image position-relative" style="height: 220px; overflow: hidden;">
                            <img src="<?php echo htmlspecialchars($imageUrl); ?>" class="w-100 h-100 object-fit-cover transition-transform" alt="<?php echo htmlspecialchars($vehicle['name']); ?>" loading="lazy">
                            <?php if (!empty($vehicle['badge'])): ?>
                            <span class="position-absolute top-0 start-0 m-3 badge bg-primary shadow-sm rounded-pill px-3 py-2"><?php echo htmlspecialchars($vehicle['badge']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <h3 class="h5 fw-bold mb-2 text-dark"><?php echo htmlspecialchars($vehicle['name']); ?></h3>
                            <p class="text-muted small mb-3 flex-grow-1"><?php echo htmlspecialchars($vehicle['description']); ?></p>
                            
                            <!-- Specs -->
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-light text-secondary border fw-normal">
                                    <i class="fas fa-users text-primary me-1"></i> <?php echo htmlspecialchars($vehicle['seats']); ?> Seats
                                </span>
                                <span class="badge bg-light text-secondary border fw-normal">
                                    <i class="fas fa-cog text-success me-1"></i> <?php echo htmlspecialchars($vehicle['transmission']); ?>
                                </span>
                                <span class="badge bg-light text-secondary border fw-normal">
                                    <i class="fas fa-suitcase text-info me-1"></i> <?php echo htmlspecialchars($vehicle['luggage']); ?> Bags
                                </span>
                            </div>
                            
                            <!-- Amenities -->
                            <div class="mb-3">
                                <?php 
                                $amenityCount = 0;
                                foreach ($vehicle['amenities'] as $amenity): 
                                    if($amenityCount++ >= 3) break;
                                ?>
                                <span class="text-muted small me-2"><i class="fas fa-check text-success me-1"></i> <?php echo htmlspecialchars($amenity); ?></span>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="d-flex align-items-center justify-content-between mt-auto pt-3 border-top">
                                <div>
                                    <small class="text-muted d-block">Starting from</small>
                                    <span class="h5 fw-bold text-primary mb-0">₹<?php echo number_format($vehicle['price_per_day']); ?></span>
                                    <small class="text-muted">/day</small>
                                </div>
                                <a href="book.php?vehicle=<?php echo urlencode($vehicle['name']); ?>" class="btn btn-primary rounded-pill px-4">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="text-primary fw-bold text-uppercase letter-spacing-1">Why Choose Us</span>
            <h2 class="display-5 fw-bold">Premium Service</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up">
                <div class="card border-0 shadow-sm h-100 text-center p-4 hover-lift">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-shield-alt text-primary"></i>
                    </div>
                    <h4 class="fw-bold">Safety First</h4>
                    <p class="text-muted mb-0">All vehicles undergo regular maintenance and safety checks to ensure your journey is safe and secure.</p>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm h-100 text-center p-4 hover-lift">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-user-tie text-primary"></i>
                    </div>
                    <h4 class="fw-bold">Professional Drivers</h4>
                    <p class="text-muted mb-0">Experienced and courteous drivers who know the local routes and ensure a comfortable journey.</p>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm h-100 text-center p-4 hover-lift">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-clock text-primary"></i>
                    </div>
                    <h4 class="fw-bold">24/7 Service</h4>
                    <p class="text-muted mb-0">Round-the-clock availability for your convenience, whether it's an early morning pickup or late-night drop.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 cta-section mt-5 mx-3 rounded-4 mb-5" data-aos="fade-up">
    <div class="container text-center">
        <h2 class="h1 fw-bold mb-3 text-white">Ready to Travel in Comfort?</h2>
        <p class="lead mb-4 text-white-50">Book your preferred vehicle today and enjoy a hassle-free travel experience across Himachal Pradesh</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="book.php" class="btn btn-light btn-lg px-5 rounded-pill shadow-sm fw-bold text-primary">Book Your Vehicle</a>
            <a href="book.php" class="btn btn-outline-light btn-lg px-5 rounded-pill">Get Quote</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- Video Mask Script -->
<script src="js/video-mask.js"></script>

<style>
.feature-icon {
    font-size: 2.5rem; 
    width: 80px; 
    height: 80px;
    display: flex; 
    align-items: center; 
    justify-content: center;
    background: linear-gradient(135deg, #EDE9FE 0%, #DDD6FE 100%);
    border-radius: 50%;
    box-shadow: 0 4px 12px rgba(79,70,229,0.15);
}

.vehicle-card .vehicle-image img {
    transition: transform 0.5s ease;
}

.vehicle-card:hover .vehicle-image img {
    transform: scale(1.1);
}
</style>
