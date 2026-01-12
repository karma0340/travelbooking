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

// Set security headers
try {
    setSecurityHeaders();
} catch (Exception $e) {
    error_log("Error setting security headers: " . $e->getMessage());
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// SEO Optimization
$pageTitle = "Contact Us - Travel In Peace | Himachal Travel Agency";
$pageDescription = "Get in touch with Travel In Peace for your Himachal travel needs. Contact us for tours, vehicle rentals, and customized travel packages.";
$pageKeywords = generateSEOKeywords("contact travel in peace, himachal travel agency contact, shimla tour operator, manali travel contact, travel agency phone, email contact, travel in peace address, shimla travel office, manali travel office");

// Set browser caching headers
header('Cache-Control: public, max-age=31536000');
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));

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
                        <h1 class="video-text">Contact Us</h1>
                        <span class="video-text subtitle">We're Here to Help</span>
                        
                        <!-- Shared Video Source -->
                        <div class="video-container">
                            <video autoplay loop muted playsinline crossorigin="anonymous" id="sourceVideo">
                                <source src="https://www.w3schools.com/howto/rain.mp4" type="video/mp4">
                            </video>
                        </div>
                    </div>
                    
                    <div class="lead mb-5 mx-auto text-white" style="max-width: 800px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                        Get in touch with us for your Himachal travel needs. We're here to make your journey memorable.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact-form-section" class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="text-primary fw-bold text-uppercase letter-spacing-1">Get In Touch</span>
            <h2 class="display-5 fw-bold">Send Us a Message</h2>
            <p class="text-muted">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        </div>
        
        <div class="row g-4">
            <!-- Contact Form -->
            <div class="col-lg-8" data-aos="fade-right">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <form id="contactForm" method="POST" action="includes/contact-handler.php">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-semibold">Your Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                                <div class="col-md-6">
                                    <label for="subject" class="form-label fw-semibold">Subject *</label>
                                    <select class="form-select" id="subject" name="subject" required>
                                        <option value="">Select Subject</option>
                                        <option value="tour-booking">Tour Booking</option>
                                        <option value="vehicle-rental">Vehicle Rental</option>
                                        <option value="general-inquiry">General Inquiry</option>
                                        <option value="feedback">Feedback</option>
                                        <option value="complaint">Complaint</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label fw-semibold">Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="#" class="text-primary">Terms and Conditions</a>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5">
                                        <i class="fas fa-paper-plane me-2"></i> Send Message
                                    </button>
                                    <div id="formStatus" class="mt-3"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-4" data-aos="fade-left">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">Contact Information</h4>
                        
                        <div class="contact-info-item d-flex align-items-start mb-4">
                            <div class="contact-icon me-3">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Address</h6>
                                <p class="text-muted mb-0 small">Near ISBT, Tutikandi<br>Shimla, Himachal Pradesh 171004</p>
                            </div>
                        </div>
                        
                        <div class="contact-info-item d-flex align-items-start mb-4">
                            <div class="contact-icon me-3">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Phone</h6>
                                <p class="text-muted mb-0 small">
                                    <a href="tel:+917559775470" class="text-muted text-decoration-none">+91 7559775470</a><br>
                                    <a href="tel:+918627873362" class="text-muted text-decoration-none">+91 8627873362</a>
                                </p>
                            </div>
                        </div>
                        
                        <div class="contact-info-item d-flex align-items-start mb-4">
                            <div class="contact-icon me-3">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Email</h6>
                                <p class="text-muted mb-0 small">
                                    <a href="mailto:travelinpeace605@gmail.com" class="text-muted text-decoration-none">travelinpeace605@gmail.com</a>
                                </p>
                            </div>
                        </div>
                        
                        <div class="contact-info-item d-flex align-items-start">
                            <div class="contact-icon me-3">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Business Hours</h6>
                                <p class="text-muted mb-0 small">Mon - Sat: 9:00 AM - 6:00 PM<br>Sunday: Closed</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">Follow Us</h4>
                        <div class="d-flex flex-wrap gap-2 social-links-container">
                            <a href="#" class="btn btn-outline-primary rounded-pill social-icon-btn">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="btn btn-outline-primary rounded-pill social-icon-btn">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="btn btn-outline-primary rounded-pill social-icon-btn">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="btn btn-outline-primary rounded-pill social-icon-btn">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="text-primary fw-bold text-uppercase letter-spacing-1">Location</span>
            <h2 class="display-5 fw-bold">Find Us</h2>
            <p class="text-muted">Visit our office in Shimla or contact us for any travel assistance</p>
        </div>
        
        <div class="row">
            <div class="col-12" data-aos="fade-up">
                <div class="map-container rounded-4 overflow-hidden shadow-sm">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3429.243952504936!2d77.17068281513686!3d31.10444398144085!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390578e9f2910903%3A0x6e9a0e637841c646!2sShimla%2C%20Himachal%20Pradesh!5e0!3m2!1sen!2sin!4v1625642531386!5m2!1sen!2sin"
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="text-primary fw-bold text-uppercase letter-spacing-1">FAQ</span>
            <h2 class="display-5 fw-bold">Frequently Asked Questions</h2>
            <p class="text-muted">Find answers to common questions about our services</p>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto" data-aos="fade-up">
                <div class="accordion accordion-flush" id="faqAccordion">
                    <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I book a tour or vehicle?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                You can book through our website by visiting the booking page, or contact us directly via phone or email. Our team will assist you with the booking process.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                What payment methods do you accept?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                We accept cash, bank transfers, UPI, and major credit/debit cards. Payment can be made online or at our office.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Do you provide customized tour packages?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Yes! We specialize in creating customized tour packages based on your preferences, budget, and travel dates. Contact us to discuss your requirements.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                What is your cancellation policy?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Cancellations made 7 days before the trip receive a full refund. Cancellations within 3-7 days receive 50% refund. No refund for cancellations within 3 days of departure.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 cta-section mt-5 mx-3 rounded-4 mb-5" data-aos="fade-up">
    <div class="container text-center">
        <h2 class="h1 fw-bold mb-3 text-white">Ready to Start Your Journey?</h2>
        <p class="lead mb-4 text-white-50">Contact us today and let us help you plan the perfect Himachal adventure</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="#contact-form-section" class="btn btn-light btn-lg px-5 rounded-pill shadow-sm fw-bold text-primary">Book Now</a>
            <a href="tel:+918627873362" class="btn btn-outline-light btn-lg px-5 rounded-pill">Call Us</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- Video Mask Script -->
<script src="js/video-mask.js"></script>

<style>
.contact-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #EDE9FE 0%, #DDD6FE 100%);
    color: #4F46E5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.25rem;
    box-shadow: 0 4px 12px rgba(79,70,229,0.15);
}

.form-control, .form-select {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid #E5E7EB;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #4F46E5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.social-icon-btn {
    width: 45px;
    height: 45px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.accordion-button:not(.collapsed) {
    background-color: #F3F4F6;
    color: #4F46E5;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: transparent;
}
</style>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const formStatus = document.getElementById('formStatus');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
    
    // Simulate form submission (replace with actual AJAX call)
    setTimeout(function() {
        formStatus.innerHTML = '<div class="alert alert-success rounded-pill"><i class="fas fa-check-circle me-2"></i>Thank you for your message! We will get back to you soon.</div>';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Send Message';
        document.getElementById('contactForm').reset();
    }, 2000);
});
</script>
