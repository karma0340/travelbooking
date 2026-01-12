<?php
require_once 'includes/db.php';
require_once 'includes/seo-helper.php';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// SEO Optimization
$pageTitle = "About Us - Travel In Peace | Himachal Travel Experts";
$pageDescription = "Learn about Travel In Peace - Himachal's premier travel service provider offering tours, vehicles, and personalized experiences since 2020.";
$pageKeywords = generateSEOKeywords("about travel in peace, himachal travel agency, shimla tour operator, manali travel contact, travel agency phone, email contact, travel in peace story, himachal tourism company");

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center justify-content-center" style="min-height: 60vh;">
    <!-- Enhanced overlay gradient - Same as Index -->
    <div class="hero-overlay"></div>
    
    <div class="container" style="position: relative;">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <div data-aos="fade-up">
                    <!-- Text with video mask -->
                    <div class="video-text-container">
                        <h1 class="video-text">About Us</h1>
                        <span class="video-text subtitle">Our Journey & Vision</span>
                        
                        <!-- Video Source (Hidden, used by Canvas) - Shared ID -->
                        <div class="video-container">
                            <video autoplay loop muted playsinline crossorigin="anonymous" id="sourceVideo">
                                <source src="https://www.w3schools.com/howto/rain.mp4" type="video/mp4">
                            </video>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="lead mb-5 mx-auto text-white" style="position: relative; max-width: 800px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                        Discover the story behind Himachal's premier travel experience provider, where every journey becomes an unforgettable memory.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Story Section with Timeline -->
<section id="story" class="py-5 my-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6">
                <div class="story-content pe-lg-4" data-aos="fade-right">
                    <span class="text-primary fw-bold text-uppercase letter-spacing-1">Our History</span>
                    <h2 class="display-5 fw-bold mb-4">Crafting Memories Since 2020</h2>
                    <p class="lead text-muted mb-4">
                        Founded in the heart of Shimla, Travel In Peace has been the trusted name in Himachal tourism for over a decade.
                    </p>
                    <p class="mb-4 text-muted">
                        What started as a small vision to showcase the breathtaking beauty of Himachal Pradesh has grown into a premier travel service provider. Our journey began with a simple mission: to provide authentic, comfortable, and memorable travel experiences that capture the essence of Himalayan hospitality.
                    </p>
                    <div class="p-4 bg-light rounded-3 border-start border-4 border-primary">
                        <p class="mb-0 fst-italic">"We don't just organize tours; we curate experiences that stay with you forever."</p>
                        <div class="mt-2 fw-bold text-primary">- Founder</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <!-- Timeline -->
                <div class="timeline ps-4" data-aos="fade-left">
                    <div class="timeline-item">
                        <div class="timeline-dot bg-primary"></div>
                        <div class="timeline-content">
                            <span class="badge bg-primary mb-2">2020</span>
                            <h5 class="fw-bold">The Beginning</h5>
                            <p class="mb-0 text-muted small">Started with just 2 vehicles and a dream to showcase Himachal's beauty to the world.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot bg-success"></div>
                        <div class="timeline-content">
                            <span class="badge bg-success mb-2">2023</span>
                            <h5 class="fw-bold">Expansion</h5>
                            <p class="mb-0 text-muted small">Grew our fleet to 15+ vehicles and expanded tour packages across all of Himachal Pradesh.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot bg-info"></div>
                        <div class="timeline-content">
                            <span class="badge bg-info mb-2">2026</span>
                            <h5 class="fw-bold">Digital Transformation</h5>
                            <p class="mb-0 text-muted small">Launched our new premium digital platform to serve 5000+ happy customers globally.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="text-primary fw-bold text-uppercase letter-spacing-1">Why Choose Us</span>
            <h2 class="display-5 fw-bold">Our Core Values</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">The principles that guide everything we do to ensure your perfect journey.</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm hover-lift value-card text-center p-4">
                    <div class="value-icon mb-4">
                        <div class="icon-circle text-primary mx-auto" style="width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; background-color: rgba(79, 70, 229, 0.1);">
                            <i class="fas fa-heart"></i>
                        </div>
                    </div>
                    <h4 class="h5 fw-bold mb-3">Customer First</h4>
                    <p class="text-muted small mb-0">Your satisfaction and safety are our top priorities. We ensure every journey is comfortable.</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm hover-lift value-card text-center p-4">
                    <div class="value-icon mb-4">
                        <div class="icon-circle text-success mx-auto" style="width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; background-color: rgba(37, 211, 102, 0.1);">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <h4 class="h5 fw-bold mb-3">Excellence</h4>
                    <p class="text-muted small mb-0">We strive for excellence in everything we do, from our vehicles to our premium customer service.</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm hover-lift value-card text-center p-4">
                    <div class="value-icon mb-4">
                        <div class="icon-circle text-info mx-auto" style="width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; background-color: rgba(13, 202, 240, 0.1);">
                            <i class="fas fa-handshake"></i>
                        </div>
                    </div>
                    <h4 class="h5 fw-bold mb-3">Integrity</h4>
                    <p class="text-muted small mb-0">We believe in transparent pricing, honest communication, and building long-term trust.</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                <div class="card h-100 border-0 shadow-sm hover-lift value-card text-center p-4">
                    <div class="value-icon mb-4">
                        <div class="icon-circle text-warning mx-auto" style="width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; background-color: rgba(255, 193, 7, 0.1);">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                    </div>
                    <h4 class="h5 fw-bold mb-3">Innovation</h4>
                    <p class="text-muted small mb-0">We continuously improve our services and adopt new technologies for better experiences.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="text-primary fw-bold text-uppercase letter-spacing-1">Our Team</span>
            <h2 class="display-5 fw-bold">Meet Our Leader</h2>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-6 col-xl-5">
                <div class="card border-0 shadow-lg team-card text-center p-5" data-aos="zoom-in">
                    <div class="team-avatar mb-4 mx-auto" style="width: 150px; height: 150px;">
                        <div class="avatar-circle rounded-circle p-1 bg-gradient-primary w-100 h-100">
                            <img src="images/nikhil_tyagi.jpg" alt="Founder" class="rounded-circle w-100 h-100 object-fit-cover bg-white">
                        </div>
                    </div>
                    <h3 class="h3 fw-bold mb-1">Nikhil Tyagi</h3>
                    <p class="text-primary fw-semibold mb-3">Founder & CEO</p>
                    <p class="text-muted mb-4">
                        With over 20 years of experience in tourism, Nikhil leads our team with passion and innovation, 
                        ensuring every journey becomes an unforgettable memory for our clients.
                    </p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="#" class="btn btn-outline-primary btn-sm rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-success btn-sm rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="btn btn-outline-danger btn-sm rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Banner -->
<section class="py-5 cta-section mt-5 mx-3 rounded-4" data-aos="fade-up">
    <div class="container">
        <div class="row align-items-center justify-content-center text-center">
            <div class="col-lg-8">
                <h2 class="h1 fw-bold mb-3 text-white">Ready to Start Your Journey?</h2>
                <p class="lead mb-4 text-white-50">Join thousands of satisfied travelers who have discovered the magic of the Himalayas.</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="tours.php" class="btn btn-light btn-lg px-5 rounded-pill shadow-sm fw-bold text-primary">Explore Tours</a>
                    <a href="contact.php#contact-form-section" class="btn btn-outline-light btn-lg px-5 rounded-pill fw-bold">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<!-- External Scripts -->
<script src="js/video-mask.js"></script>
