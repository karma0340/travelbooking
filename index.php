<?php
// Temporary Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session securely - move this before any output
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
$pageTitle = "Home - Travel In Peace | Himachal Pradesh Tours & Vehicles";
$pageDescription = "Discover Himachal's beauty with Travel In Peace - Premium tours, vehicle rentals, and personalized travel experiences in Shimla, Manali, Dharamshala, and more.";
$pageKeywords = generateSEOKeywords("best himachal tour packages 2025, shimla taxi service contact number, taxi near me shimla, car rental shimla with driver, shimla manali luxury tour 2024, spiti valley adventure packages, dharamshala dalhousie tour, himachal honeymoon trip 2025");

// Set security headers
try {
    setSecurityHeaders();
} catch (Exception $e) {
    // Log the error but don't display it
    error_log("Error setting security headers: " . $e->getMessage());
}

// Initialize database connection
$conn = getDbConnection();

// Check if database connection is working
if (!$conn) {
    die("Database connection failed. Please check your configuration.");
}

// Get featured tours and vehicles
$tours = getTours(5); // Get 5 featured tours
$vehicles = getVehicles(5); // Get 5 featured vehicles

// If no vehicles in database, log issue but don't create dummy data
if (empty($vehicles)) {
    logError("No vehicles found in the database. Tours count: " . count($tours));
}

// Define default image URLs for when images are missing
$defaultTourImages = [
    'shimla' => 'images/placeholder/shimla.jpg',
    'manali' => 'images/placeholder/manali.jpg',
    'spiti' => 'images/placeholder/spiti.jpg'
];

// Define default vehicle images
$defaultVehicleImages = [
    'sedan' => 'images/placeholder/vehicle-placeholder.jpg',
    'desire' => 'images/placeholder/vehicle-placeholder.jpg',
    'innova' => 'images/placeholder/vehicle-placeholder.jpg',
    'ertiga' => 'images/placeholder/vehicle-placeholder.jpg',
    'tempo' => 'images/placeholder/vehicle-placeholder.jpg'
];

// Generate CSRF token for forms
$csrfToken = generateCSRFToken();

// Include header after all processing is done
include 'includes/header.php';
?>
<!-- Hero Section -->
<section id="home" class="hero-section d-flex align-items-center justify-content-center">
    <!-- Enhanced overlay gradient -->
    <!-- Enhanced overlay gradient -->
    <div class="hero-overlay"></div>
    
    <div class="container" style="position: relative;">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9 text-center">
                <div data-aos="fade-up" data-aos-duration="1200">
                    <!-- Main Heading -->
                    <h1 class="hero-title">Discover Himachal's Magic</h1>
                    <h2 class="hero-subtitle">with Travel In Peace</h2>
                    
                    <!-- Description -->
                    <p class="lead mb-5">
                        Experience breathtaking views and unforgettable journeys through the magnificent landscapes of Himalayan foothills
                    </p>
                    
                    <!-- Buttons -->
                    <div class="d-flex justify-content-center gap-3 flex-wrap mb-5">
                        <a href="#contact" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-lg hover-lift">
                            <span>Plan Now</span>
                        </a>
                        <a href="#categories" class="btn btn-outline-light btn-lg px-5 py-3 rounded-pill hover-lift">
                            Explore<i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                    
                    <!-- Stats - Now Centered Below Content -->
                    <div class="hero-stats">
                        <div class="stat-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="fw-bold">10+</span>
                            <span class="small text-uppercase">Destinations</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-car"></i>
                            <span class="fw-bold">25+</span>
                            <span class="small text-uppercase">Premium Vehicles</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-users"></i>
                            <span class="fw-bold">5000+</span>
                            <span class="small text-uppercase">Travelers</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="hero-scroll-indicator">
        <a href="#categories" class="text-white">
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</section>

<?php include 'includes/tour-sections.php'; ?>



    <!-- Fixed Vehicle Booking Modal -->
    <div class="modal fade" id="bookVehicleModal" tabindex="-1" aria-labelledby="bookVehicleModalLabel">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookVehicleModalLabel">Book Vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="vehicle-booking-form" method="POST" action="api/save-booking.php">
                        <!-- Add CSRF token -->
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="vehicle_id" id="booking_vehicle_id">
                        <input type="hidden" name="vehicle_name" id="booking_vehicle_name">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required pattern="[A-Za-z\s]{2,50}">
                                <div class="invalid-feedback">Please enter a valid name (2-50 characters).</div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required pattern="[+\d\s()-]{7,15}">
                                <div class="invalid-feedback">Please enter a valid phone number.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="guests" class="form-label">Number of Passengers</label>
                                <select class="form-select" id="guests" name="guests" required>
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="travel_date" class="form-label">Pickup Date</label>
                                <input type="date" class="form-control" id="travel_date" name="travel_date" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">Return Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label">Special Requests</label>
                                <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="term_accept" required>
                                    <label class="form-check-label" for="term_accept">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="submitVehicleBooking">Submit Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicle Detail Modal -->
    <div class="modal fade" id="vehicleDetailModal" tabindex="-1" aria-labelledby="vehicleDetailModalLabel">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vehicleDetailModalLabel">Vehicle Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="vehicleDetailContent">
                    <!-- Content will be loaded dynamically -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary book-this-vehicle">Book Now</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicle Filter Modal -->
    <div class="modal fade" id="vehicleFilterModal" tabindex="-1" aria-labelledby="vehicleFilterModalLabel">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vehicleFilterModalLabel">Filter Vehicles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="filter-form">
                        <div class="mb-3">
                            <label class="form-label">Passenger Capacity</label>
                            <div class="d-flex gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="4" id="seats4">
                                    <label class="form-check-label" for="seats4">Up to 4</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="7" id="seats7">
                                    <label class="form-check-label" for="seats7">5-7</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="10" id="seats10">
                                    <label class="form-check-label" for="seats10">8+</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price-range" class="form-label">Price Range</label>
                            <input type="range" class="form-range" min="1000" max="10000" step="500" id="price-range">
                            <div class="d-flex justify-content-between">
                                <span>₹1,000</span>
                                <span id="price-value">₹5,000</span>
                                <span>₹10,000</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Features</label>
                            <div class="d-flex flex-wrap gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="AC" id="featureAC">
                                    <label class="form-check-label" for="featureAC">AC</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="Bluetooth" id="featureBluetooth">
                                    <label class="form-check-label" for="featureBluetooth">Bluetooth</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="GPS" id="featureGPS">
                                    <label class="form-check-label" for="featureGPS">GPS</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="applyFilter">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

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
                        <button type="button" class="btn btn-outline-primary rounded-pill px-4 me-2" data-bs-toggle="modal" data-bs-target="#addReviewModal">
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

    <!-- About Us -->
    <section id="about" class="py-5 py-lg-7 bg-light">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="section-title">About Travel In Peace</h2>
                    <p>With over a decade of experience in the travel industry, Travel In Peace has been the premier choice for tourists exploring the breathtaking landscapes of Himachal Pradesh.</p>
                    <p>Our team of experienced local guides and drivers ensures that every journey is safe, comfortable, and filled with unforgettable experiences.</p>
                    
                    <div class="about-features mt-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="about-icon">
                                        <i class="fas fa-map-marked-alt"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-1">Local Expertise</h6>
                                        <p class="small text-muted mb-0">Know every hidden gem</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="about-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-1">Safety First</h6>
                                        <p class="small text-muted mb-0">Well-maintained vehicles</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="about-icon">
                                        <i class="fas fa-hands-helping"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-1">24/7 Support</h6>
                                        <p class="small text-muted mb-0">Always there for you</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="about-icon">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-1">Personalized Tours</h6>
                                        <p class="small text-muted mb-0">Tailored to your needs</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline mt-5" id="about-timeline">
                        <!-- Timeline will be animated with GSAP -->
                    </div>
                </div>
<!-- Meet Our Leader Section -->
<div class="col-lg-6" data-aos="fade-left">
    <div class="text-center mb-4">
        <span class="text-primary fw-bold text-uppercase letter-spacing-1 d-block mb-1">Our Team</span>
        <h2 class="display-5 fw-bold mb-4">Meet Our Leader</h2>
    </div>
    
    <div class="leader-card p-4 p-md-5 text-center">
        <div class="leader-avatar-wrapper">
            <img src="images/nikhil_tyagi.jpg" alt="Nikhil Tyagi - Founder & CEO" class="leader-avatar">
        </div>
        
        <h3 class="leader-name">Nikhil Tyagi</h3>
        <p class="leader-title">Founder & CEO</p>
        
        <p class="leader-bio">
            With over 7 years of experience in tourism, Nikhil leads our team with passion and innovation, ensuring every journey becomes an unforgettable memory for our clients.
        </p>
        
        <div class="leader-socials">
            <a href="https://www.facebook.com/travelinpeace605" class="social-link facebook" title="Facebook" target="_blank">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://wa.me/+918627873362" class="social-link whatsapp" title="WhatsApp" target="_blank">
                <i class="fab fa-whatsapp"></i>
            </a>
            <a href="https://www.instagram.com/travelinpeace_/" class="social-link instagram" title="Instagram" target="_blank">
                <i class="fab fa-instagram"></i>
            </a>
        </div>
    </div>
</div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 cta-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="card border-0 shadow cta-card p-4 p-md-5" data-aos="fade-up">
                        <div class="row align-items-center">
                            <div class="col-lg-8 mb-4 mb-lg-0">
                                <h3>Ready to Explore Himachal Pradesh?</h3>
                                <p class="mb-0">Book your tour today and get special discounts on group bookings!</p>
                            </div>
                            <div class="col-lg-4 text-lg-end">
                                <a href="book.php" class="btn btn-premium rounded-pill px-4 py-2">Contact Us Today</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .contact-map-wrapper {
            margin-right: -15px;
        }

        .map-container {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            transition: all 0.3s ease;
            height: 400px;
            margin-bottom: 2rem;
        }

        .map-container:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 991px) {
            .contact-map-wrapper {
                margin-right: 0;
                margin-bottom: 2rem;
            }
        }

        @media (max-width: 768px) {
            .map-container {
                height: 300px;
            }
        }

        .contact-info {
            padding: 0.5rem 0;
        }

        .contact-icon {
            width: 30px;
            height: 30px;
            background-color: var(--bs-primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-right: 0.5rem;
        }

        .contact-item {
            height: auto;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: transform 0.3s ease;
            margin-bottom: 0.5rem;
        }

        .contact-item h6 {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .contact-item p {
            font-size: 0.8rem;
            margin-bottom: 0;
        }

        @media (max-width: 991px) {
            .contact-info .row {
                margin-right: 0;
                margin-left: 0;
            }
            .contact-item {
                margin-bottom: 0.5rem;
            }
        }

        @media (max-width: 768px) {
            .leader-avatar-wrapper {
                width: 140px;
                height: 140px;
            }
        }
    </style>

    <!-- Contact Section -->
    <section id="contact" class="py-5 py-lg-7 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-7 mx-auto text-center" data-aos="fade-up">
                    <h2 class="section-title text-center">Contact Us</h2>
                    <p class="text-muted">Reach out to us for bookings, inquiries, or customized tours</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="contact-map-wrapper">
                        <div class="w-full h-96 map-container">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3472.0673758453463!2d77.17031077529827!3d31.098990067939283!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzHCsDA1JzU2LjQiTiA3N8KwMTAnMTAuMiJF!5e1!3m2!1sen!2sin!4v1639998273714!5m2!1sen!2sin"
                                width="100%"
                                height="100%"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                        
                        
                        
<!-- Contact Info Section with adjusted spacing -->
<div class="contact-info mt-4 mb-4"> <!-- Added bottom margin -->
    <div class="row g-4">
        <div class="col-lg-4 col-md-12 mb-3"> <!-- Changed column sizes -->
            <div class="contact-item d-flex align-items-start"> <!-- Changed to align-items-start -->
                <div class="contact-icon me-3"> <!-- Added margin-end -->
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div>
                    <h6 class="mb-1">Address</h6>
                    <p class="small mb-0 text-wrap">Near ISBT, Tutikandi, Shimla, HP 171004</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 mb-3"> <!-- Changed column sizes -->
            <div class="contact-item d-flex align-items-start">
                <div class="contact-icon me-3">
                    <i class="fas fa-phone"></i>
                </div>
                <div>
                    <h6 class="mb-1">Phone</h6>
                    <p class="small mb-0">+91 8627873362<br>+91 7559775470</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 mb-3"> <!-- Changed column sizes -->
            <div class="contact-item d-flex align-items-start">
                <div class="contact-icon me-3">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <h6 class="mb-1">Email</h6>
                    <p class="small mb-0">travelinpeace605@gmail.com</p>
                </div>
            </div>
        </div>
    </div>
</div>
                    </div>
                </div>
                
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4 p-lg-5">
                            <h4 class="mb-4">Book Your Tour</h4>
                            <form id="booking-form" method="POST" action="api/save-booking.php">
                                <!-- Add CSRF token -->
                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="nameInput" name="name" placeholder="Your Name" required pattern="[A-Za-z\s]{2,50}">
                                            <label for="nameInput">Your Name</label>
                                            <div class="invalid-feedback">Please enter a valid name (2-50 characters).</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" id="emailInput" name="email" placeholder="Your Email" required>
                                            <label for="emailInput">Your Email</label>
                                            <div class="invalid-feedback">Please enter a valid email address.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="tel" class="form-control" id="phoneInput" name="phone" placeholder="Phone Number" required pattern="[+\d\s()-]{7,15}">
                                            <label for="phoneInput">Phone Number</label>
                                            <div class="invalid-feedback">Please enter a valid phone number.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="tourSelect" name="tour_package" required>
                                                <option selected disabled value="">Select Tour Package</option>
                                                <option value="shimla-adventure">Shimla Adventure</option>
                                                <option value="manali-escape">Manali Escape</option>
                                                <option value="dharamshala-retreat">Dharamshala Retreat</option>
                                                <option value="spiti-valley-trek">Spiti Valley Trek</option>
                                                <option value="leh-ladakh-expedition">Leh Ladakh Expedition</option>
                                                <option value="custom-tour">Custom Tour</option>
                                            </select>
                                            <label for="tourSelect">Tour Package</label>
                                            <div class="invalid-feedback">Please select a tour package.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="date" class="form-control" id="dateInput" name="travel_date" required min="<?php echo date('Y-m-d'); ?>">
                                            <label for="dateInput">Travel Date</label>
                                            <div class="invalid-feedback">Please select a valid future date.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="number" class="form-control" id="guestsInput" name="guests" placeholder="Number of Guests" min="1" max="20" required>
                                            <label for="guestsInput">Number of Guests</label>
                                            <div class="invalid-feedback">Please enter a valid number (1-20).</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" placeholder="Your Message" id="messageTextarea" name="message" style="height: 120px"></textarea>
                                            <label for="messageTextarea">Special Requests</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="termsCheck" name="terms" required>
                                            <label class="form-check-label" for="termsCheck">
                                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" id="submitBooking" class="btn btn-premium w-100 py-3 rounded-pill">Submit Request</button>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <div class="alert alert-success d-none" id="bookingSuccess">
                                            Your booking request has been submitted successfully. We'll contact you shortly!
                                        </div>
                                        <div class="alert alert-danger d-none" id="bookingError">
                                            There was an error processing your request. Please try again.
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms & Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>By booking with Travel In Peace, you agree to the following terms:</p>
                    <ul>
                        <li>Cancellation policy: 100% refund 7 days before travel date</li>
                        <li>75% refund for cancellations 3-7 days before travel</li>
                        <li>No refund for cancellations less than 3 days before travel</li>
                        <li>All travelers must carry valid ID proof</li>
                        <li>Shimla Air Lines reserves the right to change itinerary based on weather conditions</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

<?php
$extraScripts = '
    <script src="js/vehicle-booking.js"></script>
    <script src="js/video-mask.js"></script>
    <script>
        window.gm_authFailure = function() {
            console.log("Google Maps API authentication error");
            const mapContainers = document.querySelectorAll(".map-container");
            mapContainers.forEach(container => {
                container.innerHTML = "<div class=\"p-4 bg-light text-center\">Map could not be loaded. Please try again later.</div>";
            });
        };
    </script>
    <script>
    function scrollSlider(direction) {
        const slider = document.getElementById("categorySlider");
        if (slider) {
            const scrollAmount = slider.clientWidth * 0.8;
            slider.scrollBy({
                left: direction * scrollAmount,
                behavior: "smooth"
            });
        }
    }
    </script>
';
?>
<!-- Review Modal -->
<?php require_once 'includes/simple-review-modal.php'; ?>

<?php include 'includes/footer.php'; ?>



</content>
