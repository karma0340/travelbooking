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

    <!-- Testimonials Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-7 mx-auto text-center" data-aos="fade-up">
                    <h2 class="section-title text-center">What Our Customers Say</h2>
                    <p class="text-muted">Hear from our happy travelers who have experienced the magic of Himachal Pradesh with us</p>
                    <?php
                    $stats = getReviewStats();
                    ?>
                    <div class="d-flex justify-content-center align-items-center gap-3 mt-3 flex-wrap">
                        <div class="d-flex align-items-center bg-white shadow-sm px-3 py-2 rounded-pill border">
                            <span class="fw-bold text-dark me-2 fs-5"><?php echo number_format($stats['average'] ?: 5.0, 1); ?>/5</span>
                            <div class="text-warning me-2">
                                <?php
                                $rating = $stats['average'] ?: 5;
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $rating) {
                                        echo '<i class="fas fa-star"></i>';
                                    } elseif ($i - 0.5 <= $rating) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <span class="text-muted small border-start ps-2">Based on <?php echo $stats['total']; ?> reviews</span>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <?php require_once 'includes/google-config.php'; ?>
                        <button class="btn btn-outline-primary rounded-pill px-4 me-2" data-bs-toggle="modal" data-bs-target="#addReviewModal">
                            <i class="fas fa-pen me-2"></i>Write a Review
                        </button>
                        <a href="reviews.php" class="btn btn-primary rounded-pill px-4">
                            View All Reviews <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <?php
                $reviews = getReviews(3, false);
                $displayReviews = $reviews;
                
                if (empty($displayReviews)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="p-5 bg-light rounded-3">
                            <i class="fas fa-comments text-muted mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                            <p class="text-muted mb-0">No reviews yet. Be the first to share your experience!</p>
                        </div>
                    </div>
                <?php endif;

                foreach ($displayReviews as $review):
                ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="testimonial-rating mb-3">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="testimonial-text">"<?php echo htmlspecialchars($review['review_text']); ?>"</p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="testimonial-avatar">
                                <?php if (!empty($review['google_picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($review['google_picture']); ?>" alt="<?php echo htmlspecialchars($review['name']); ?>" class="rounded-circle">
                                <?php elseif (!empty($review['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($review['image_path']); ?>" alt="<?php echo htmlspecialchars($review['name']); ?>" class="rounded-circle">
                                <?php else: ?>
                                    <div class="avatar-initials">
                                        <?php echo strtoupper(substr($review['name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0 d-flex align-items-center gap-2">
                                    <?php echo htmlspecialchars($review['name']); ?>
                                    <?php if (!empty($review['google_id'])): ?>
                                        <span class="badge bg-primary-subtle text-primary" style="font-size: 0.65rem;" title="Verified via Google">
                                            <i class="fab fa-google"></i>
                                        </span>
                                    <?php endif; ?>
                                </h6>
                                <small class="text-muted"><?php echo htmlspecialchars($review['location']); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

<!-- CTA -->
<section class="py-5 cta-section mt-5 mx-3 rounded-4 mb-5" data-aos="fade-up">
    <div class="container text-center">
        <h2 class="h1 fw-bold mb-3 text-white">Plan Your Customized Trip</h2>
        <p class="lead mb-4 text-white-50">Can't find what you're looking for? Let us create a bespoke itinerary for you.</p>
        <a href="https://wa.me/918627873362?text=I%20am%20interested%20in%20planning%20a%20customized%20trip" target="_blank" class="btn btn-light btn-lg px-5 rounded-pill shadow-sm fw-bold text-primary">
            <i class="fab fa-whatsapp me-2"></i>Contact Travel Experts
        </a>
    </div>
</section>

<?php
$extraScripts = <<<EOT
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
EOT;
?>
<!-- Review Modal -->
<?php require_once 'includes/simple-review-modal.php'; ?>
<?php include 'includes/footer.php'; ?>
