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

require_once 'includes/db.php';
require_once 'includes/security.php';
require_once 'includes/seo-helper.php';

// SEO Optimization
$pageTitle = "Tours - Travel In Peace | Exclusive Himachal Packages";
$pageDescription = "Explore our exclusive Himachal tour packages including Shimla, Manali, Spiti Valley with customized itineraries for families, couples, and adventure seekers.";
$pageKeywords = generateSEOKeywords("himachal tour packages 2024-2025, shimla manali family tour, spiti valley road trip, adventure tourism himachal, budget himachal packages, himachal honeymoon packages 2025, shimla to manali taxi service");

// Set security headers
try {
    setSecurityHeaders();
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
}

$conn = getDbConnection();
if (!$conn) {
    die("Database connection failed.");
}

// Filter logic
$filters = [];
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $filters['category'] = $_GET['category'];
}
if (isset($_GET['location']) && !empty($_GET['location'])) {
    $filters['location'] = $_GET['location'];
}
$tours = getTours(null, $filters);
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
                        <h1 class="video-text">Destinations</h1>
                        <span class="video-text subtitle">Curated Journeys & Packages</span>
                        
                        <!-- Shared Video Source -->
                        <div class="video-container">
                            <video autoplay loop muted playsinline crossorigin="anonymous" id="sourceVideo">
                                <source src="https://www.w3schools.com/howto/rain.mp4" type="video/mp4">
                            </video>
                        </div>
                    </div>
                    
                    <div class="lead mb-5 mx-auto text-white" style="max-width: 800px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                        Discover our carefully curated selection of memorable journeys through the magnificent landscapes of Himachal Pradesh.
                    </div>
                    
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="#tours" class="btn btn-primary btn-lg rounded-pill shadow-lg px-5">View Packages</a>
                        <a href="#categories" class="btn btn-outline-light btn-lg rounded-pill px-5">Browse Categories</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/tour-sections.php'; ?>

<!-- Testimonials (Simplified) -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold">Traveler Reviews</h2>
            <div class="d-flex justify-content-center align-items-center gap-2 mt-2">
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <span class="text-muted fw-semibold">4.9/5 Average Rating</span>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Review 1 -->
            <div class="col-md-4" data-aos="fade-up">
                <div class="card border-0 shadow-sm p-4 h-100 review-card">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://ui-avatars.com/api/?name=Priya+S&background=random" class="rounded-circle me-3" width="50" height="50" alt="Priya Sharma reviewer">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">Priya Sharma</h6>
                            <div class="small text-warning">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted fst-italic">"Seamless experience from booking to the actual trip. The Shimla tour was perfectly paced!"</p>
                </div>
            </div>
             <!-- Review 2 -->
             <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm p-4 h-100 review-card">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://ui-avatars.com/api/?name=Rahul+V&background=random" class="rounded-circle me-3" width="50" height="50" alt="Rahul Verma reviewer">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">Rahul Verma</h6>
                            <div class="small text-warning">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted fst-italic">"Spiti Valley was a dream come true. The drivers were skilled and safe. Highly recommended!"</p>
                </div>
            </div>
             <!-- Review 3 -->
             <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm p-4 h-100 review-card">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://ui-avatars.com/api/?name=Anjali+P&background=random" class="rounded-circle me-3" width="50" height="50" alt="Anjali Patel reviewer">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">Anjali Patel</h6>
                            <div class="small text-warning">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted fst-italic">"Perfect honeymoon package. The hotels were premium and the candlelight dinner was a nice touch."</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 cta-section mt-5 mx-3 rounded-4 mb-5" data-aos="fade-up">
    <div class="container text-center">
        <h2 class="h1 fw-bold mb-3 text-white">Plan Your Customized Trip</h2>
        <p class="lead mb-4 text-white-50">Can't find what you're looking for? Let us create a bespoke itinerary for you.</p>
        <a href="contact.php#contact-form-section" class="btn btn-light btn-lg px-5 rounded-pill shadow-sm fw-bold text-primary">Contact Travel Experts</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- Video Mask Script -->
<script src="js/video-mask.js"></script>

<script>
function scrollSlider(direction) {
    const slider = document.getElementById('categorySlider');
    const scrollAmount = slider.clientWidth * 0.8; // Scroll 80% of width
    slider.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}
</script>
