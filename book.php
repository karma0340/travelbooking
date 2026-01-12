<?php
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
$pageTitle = "Book Now - Travel In Peace | Himachal Tour Booking";
$pageDescription = "Book your Himachal tour or vehicle rental online. Easy booking process with instant confirmation and secure payment options.";
$pageKeywords = generateSEOKeywords("book tour, online booking, himachal tour booking, vehicle rental booking, shimla tour booking, manali tour booking, travel booking, spiti valley booking, travel reservation");

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
    'shimla' => 'images/defaults/shimla-tour.jpg',
    'manali' => 'images/defaults/manali-tour.jpg',
    'spiti' => 'images/defaults/spiti-tour.jpg'
];

$defaultVehicleImages = [
    'desire' => 'images/defaults/sedan.jpg',
    'innova' => 'images/defaults/suv.jpg',
    'ertiga' => 'images/defaults/mpv.jpg',
    'tempo' => 'images/defaults/tempo.jpg',
    'sedan' => 'images/defaults/sedan.jpg'
];

// Generate CSRF token for forms
$csrfToken = generateCSRFToken();

// Set browser caching headers
header('Cache-Control: public, max-age=31536000'); // 1 year for static content
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
?>
<?php include 'includes/header.php'; ?>
    <!-- Page Specific Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Righteous&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet">
    
    <!-- Notifications CSS -->
    <link rel="stylesheet" href="css/notifications.css">

    <!-- Critical CSS inline for faster page load -->
    <style>
        /* Add critical CSS styles here */
        .preload * {
            transition: none !important;
        }

        .hero-section {
            min-height: 100vh;
            position: relative;
            color: white;
            overflow: hidden;
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1626015379120-5e537de5310f?q=80&w=1200&auto=format&fit=crop') no-repeat center/cover;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }

        .book-btn {
            background-color: #4F46E5;
        }

        /* Remove Tailwind dependency - add essential utility classes */
        .text-primary {
            color: #4F46E5 !important;
        }

        .bg-primary {
            background-color: #4F46E5 !important;
        }

        .text-light-50 {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        .opacity-10 {
            opacity: 0.1 !important;
        }

        .text-base-content {
            color: #333333 !important;
        }

        .bg-base-100 {
            background-color: #ffffff !important;
        }

        /* Theme support without Tailwind/DaisyUI */
        [data-theme="dark"] .bg-base-100 {
            background-color: #1f2937 !important;
        }

        [data-theme="dark"] .text-base-content {
            color: #e5e7eb !important;
        }

        [data-theme="dark"] .bg-light {
            background-color: #111827 !important;
        }

        [data-theme="dark"] .text-muted {
            color: #9ca3af !important;
        }

        [data-theme="dark"] .card {
            background-color: #1f2937 !important;
        }

        /* WhatsApp Button styling - improved with !important to ensure styles apply */
        .whatsapp-button {
            position: fixed !important;
            bottom: 30px !important;
            right: 30px !important;
            width: 60px !important;
            height: 60px !important;
            background-color: #25D366 !important;
            color: white !important;
            border-radius: 50% !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            font-size: 30px !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3) !important;
            z-index: 9999 !important;
            transition: all 0.3s !important;
            text-decoration: none !important;
        }

        .whatsapp-button:hover {
            transform: scale(1.1) !important;
            background-color: #128C7E !important;
            color: white !important;
            text-decoration: none !important;
        }

        /* WhatsApp Modal Styles */
        .whatsapp-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .whatsapp-modal.show {
            display: flex;
            opacity: 1;
        }

        .whatsapp-modal-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
            position: relative;
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .whatsapp-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 1rem;
        }

        .whatsapp-modal-header h3 {
            margin: 0;
            color: #25D366;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .whatsapp-modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
            transition: color 0.2s;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .whatsapp-modal-close:hover {
            color: #333;
        }

        .whatsapp-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .whatsapp-option {
            display: flex;
            align-items: center;
            padding: 1rem;
            background-color: #f9f9f9;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #333;
        }

        .whatsapp-option:hover {
            background-color: #e8f5e9;
            border-color: #25D366;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.2);
        }

        .whatsapp-option:active {
            transform: translateX(3px);
        }

        .whatsapp-option-icon {
            font-size: 2rem;
            color: #25D366;
            margin-right: 1rem;
            width: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .whatsapp-option-content {
            flex: 1;
            min-width: 0;
        }

        .whatsapp-option-label {
            font-weight: 600;
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .whatsapp-option-number {
            color: #25D366;
            font-weight: 700;
            font-size: 1.3rem;
            word-break: break-word;
        }

        .whatsapp-option-arrow {
            color: #25D366;
            font-size: 1.2rem;
            margin-left: 1rem;
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }

        .whatsapp-option:hover .whatsapp-option-arrow {
            transform: translateX(5px);
        }

        /* Back to Top Button styling */
        .back-to-top {
            position: fixed !important;
            bottom: 100px !important;
            /* Position above WhatsApp button */
            right: 30px !important;
            width: 50px !important;
            height: 50px !important;
            background-color: #4F46E5 !important;
            color: white !important;
            border-radius: 50% !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            font-size: 20px !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2) !important;
            z-index: 9998 !important;
            /* Lower than WhatsApp button */
            transition: all 0.3s !important;
            text-decoration: none !important;
        }

        .back-to-top:hover {
            transform: scale(1.1) !important;
            background-color: #3730A3 !important;
            color: white !important;
            text-decoration: none !important;
        }

        .cta-section {
            background-color: var(--daisyui-primary);
            /* DaisyUI primary color */
            color: var(--daisyui-primary-content);
            /* Text color for primary background */
            padding: 3rem 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            text-align: center;
            overflow: hidden;
            position: relative;
        }

        .cta-section h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--daisyui-primary-content);
        }

        .cta-section p {
            font-size: 1.125rem;
            margin-bottom: 2rem;
            color: var(--daisyui-primary-content);
        }

        .cta-section .btn {
            background-color: var(--daisyui-secondary);
            color: var(--daisyui-secondary-content);
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .cta-section .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            z-index: 0;
        }

        .cta-section .btn:focus {
            outline: none;
            box-shadow: 0 0 0 4px var(--daisyui-secondary-focus);
        }

        .cta-section .btn-secondary {
            background-color: var(--daisyui-accent);
            color: var(--daisyui-accent-content);
        }

        .cta-section .btn-secondary:hover {
            background-color: var(--daisyui-accent-focus);
        }

        /* About section paragraph styling - with higher specificity */
        /* section#about p {
    color: white !important;
}

[data-theme="dark"] section#about p {
    color: white !important;
} */

        /* Preserve muted text style */
        /* section#about p.text-muted {
    color: rgba(255, 255, 255, 0.7) !important;
}

[data-theme="dark"] section#about p.text-muted {
    color: rgba(255, 255, 255, 0.7) !important;
} */
        /* About section h6 styling - with higher specificity */
        /* section#about h6 {
    color: white !important;
}

[data-theme="dark"] section#about h6 {
    color: white !important;
} */
        /* Contact section h6 styling - with higher specificity */
        /* section#contact h6 {
    color: white !important;
}

[data-theme="dark"] section#contact h6 {
    color: white !important;
} */

        /* If you have muted text style, preserve it */
        /* section#contact h6.text-muted {
    color: rgba(255, 255, 255, 0.7) !important;
} */
        /* Contact section paragraph styling - with higher specificity */
        /* section#contact p {
    color: white !important;
}

[data-theme="dark"] section#contact p {
    color: white !important;
} */

        /* Preserve muted text style */
        /* section#contact p.text-muted {
    color: rgba(255, 255, 255, 0.7) !important;
}

[data-theme="dark"] section#contact p.text-muted {
    color: rgba(255, 255, 255, 0.7) !important;
} */

        /* About section h6 styling for light theme */
        [data-theme="light"] section#about h6 {
            color: black !important;
        }

        /* About section h6 styling for dark theme */
        [data-theme="dark"] section#about h6 {
            color: white !important;
        }

        /* Contact section h6 styling for light theme */
        [data-theme="light"] section#contact h6 {
            color: black !important;
        }

        /* Contact section h6 styling for dark theme */
        [data-theme="dark"] section#contact h6 {
            color: white !important;
        }

        /* Preserve muted text style for light theme */
        [data-theme="light"] section#about h6.text-muted,
        [data-theme="light"] section#contact h6.text-muted {
            color: rgba(0, 0, 0, 0.7) !important;
        }

        /* Preserve muted text style for dark theme */
        [data-theme="dark"] section#about h6.text-muted,
        [data-theme="dark"] section#contact h6.text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        /* About section paragraph styling for light theme */
        [data-theme="light"] section#about p {
            color: black !important;
        }

        /* About section paragraph styling for dark theme */
        [data-theme="dark"] section#about p {
            color: white !important;
        }

        /* Contact section paragraph styling for light theme */
        [data-theme="light"] section#contact p {
            color: black !important;
        }

        /* Contact section paragraph styling for dark theme */
        [data-theme="dark"] section#contact p {
            color: white !important;
        }

        /* Preserve muted text style for light theme */
        [data-theme="light"] section#about p.text-muted,
        [data-theme="light"] section#contact p.text-muted {
            color: rgba(0, 0, 0, 0.7) !important;
        }

        /* Preserve muted text style for dark theme */
        [data-theme="dark"] section#about p.text-muted,
        [data-theme="dark"] section#contact p.text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .contact-map-wrapper {
            margin-right: -15px;
            /* Adjust this value as needed */
        }

        /* Map container styles */
        .map-container {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            transition: all 0.3s ease;
            height: 400px;
            /* Fixed height */
            margin-bottom: 2rem;
            /* Space before contact info */
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .contact-map-wrapper {
                margin-right: 0;
                margin-bottom: 2rem;
            }
        }

        .map-container:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .map-container {
                height: 300px;
            }
        }

        /* Contact info styles */
        /* Updated Contact info styles */
        /* Update the contact info styles */
        .contact-info {
            padding: 0.5rem 0;
            /* Reduced from 1rem */
        }

        .contact-icon {
            width: 30px;
            height: 30px;
            /* Reduced from 40px */
            background-color: var(--bs-primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-right: 0.5rem;
            /* Added smaller margin */
        }

        .contact-item {
            height: auto;
            /* Changed from 100% */
            padding: 0.5rem;
            /* Reduced from 1rem */
            border-radius: 0.5rem;
            transition: transform 0.3s ease;
            margin-bottom: 0.5rem;
            /* Added smaller margin */
        }

        /* Adjust text sizes */
        .contact-item h6 {
            font-size: 0.9rem;
            /* Smaller heading */
            margin-bottom: 0.25rem;
            /* Reduced margin */
        }

        .contact-item p {
            font-size: 0.8rem;
            /* Smaller text */
            margin-bottom: 0;
            /* Remove bottom margin */
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .contact-info .row {
                margin-right: 0;
                margin-left: 0;
            }

            .contact-item {
                margin-bottom: 0.5rem;
                /* Reduced from 1rem */
            }
        }

        /* Add these styles to your existing CSS */
        .about-3d-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 400px;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .about-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 1rem;
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .about-3d-wrapper {
                min-height: 300px;
                margin-top: 2rem;
            }
        }
        .navbar-nav + .d-none.d-lg-flex {
    margin-right: 0rem;
}

.navbar-nav + .d-none.d-lg-flex .nav-link {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}

    /* Mobile optimizations for booking section */
    @media (max-width: 767px) {
        #booking-section.mt-5 {
            margin-top: 5rem !important;
        }
        
        #booking-section .container {
            padding-top: 2rem !important;
        }
        
        #booking-section .card {
            margin-bottom: 1rem !important;
            border-radius: 1rem;
        }
        
        #booking-section .card-body {
            padding: 1.5rem !important;
        }
        
        #booking-section h4 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-floating > label {
            padding-top: 0.75rem;
        }
        
        .form-floating > .form-control {
            padding: 0.75rem;
        }
        
        /* Fix iOS form input issues */
        input, select, textarea {
            font-size: 16px !important; /* Prevents iOS zoom on focus */
        }
    }
    
    /* General booking form improvements */
    #booking-form .form-control:focus, 
    #booking-form .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
        border-color: #4F46E5;
    }
    
    /* Fix navbar overlap with content */
    body {
        padding-top: 80px; /* Adjust based on navbar height */
    }
    
    @media (max-width: 991px) {
        body {
            padding-top: 70px; /* Smaller padding for mobile */
        }
    }

/* Updated navbar brand styles */
.navbar-branddecline .text-primary {
    font-family: 'Righteous', sans-serif !important;
    font-size: 1.6rem !important;
    font-weight: 400 !important;
    letter-spacing: 1px;
    background: linear-gradient(135deg, #4F46E5, #6366F1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.navbar-branddecline {
    padding: 0.5rem 0;
}

.navbar-branddecline i {
    font-size: 1.8rem;
    margin-right: 0.5rem !important;
    color: #4F46E5;
    filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.1));
}

/* Update footer brand to match */
.footer-brand .text-primary {
    font-family: 'Righteous', sans-serif !important;
    font-size: 1.6rem !important;
    font-weight: 400 !important;
}
    </style>




    <!-- About Us -->
    <section id="booking-section" class="py-5 position-relative" style="background-color: #f3f4f6;">
        <div class="container position-relative z-1 ">
            <div class="row g-0 shadow-lg rounded-4 overflow-hidden bg-white" style="min-height: 600px;">
                <!-- Left Side: Image & Info -->
                <div class="col-lg-5 d-none d-lg-block position-relative">
                    <img src="images/categories/nature.jpg" class="w-100 h-100 object-fit-cover absolute-full" alt="Himachal Landscape" 
                         onerror="this.src='https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?q=80&w=1000&auto=format&fit=crop'">
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(79, 70, 229, 0.4), rgba(67, 56, 202, 0.6));"></div>
                    
                    <div class="position-absolute top-0 start-0 w-100 h-100 p-5 d-flex flex-column text-white">
                        <div class="mb-auto">
                            <h3 class="display-6 fw-bold mb-2">Begin Your Journey</h3>
                            <p class="opacity-75">Travel In Peace - Where memories are made.</p>
                        </div>
                        
                        <div class="mb-5">
                            <h5 class="fw-bold mb-4">Why Book With Us?</h5>
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-white bg-opacity-25 p-2 me-3">
                                    <i class="fas fa-check text-white"></i>
                                </div>
                                <span>Customized Itineraries</span>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-white bg-opacity-25 p-2 me-3">
                                    <i class="fas fa-headset text-white"></i>
                                </div>
                                <span>24/7 Support on Trip</span>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-white bg-opacity-25 p-2 me-3">
                                    <i class="fas fa-rupee-sign text-white"></i>
                                </div>
                                <span>Best Price Guarantee</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-white bg-opacity-25 p-2 me-3">
                                    <i class="fas fa-car text-white"></i>
                                </div>
                                <span>Premium Fleet</span>
                            </div>
                        </div>
                        
                        <div class="mt-auto">
                            <p class="small opacity-50 mb-0">© 2026 Travel In Peace</p>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side: Booking Form -->
                <div class="col-lg-7 bg-white">
                    <div class="p-4 p-md-5">
                        <div class="d-lg-none mb-4 text-center">
                            <h2 class="fw-bold text-primary">Book Your Trip</h2>
                            <p class="text-muted">Fill in the details below</p>
                        </div>
                        
                        <h3 class="d-none d-lg-block fw-bold mb-2 text-dark">Plan Your Adventure</h3>
                        <p class="d-none d-lg-block text-muted mb-4">Complete the form to get a customized quote.</p>
                        
                        <form id="booking-form" method="POST" action="api/save-booking.php" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium small text-uppercase text-muted">Personal Details</label>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="nameInput" name="name" placeholder="John Doe" required>
                                        <label for="nameInput">Full Name</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium small text-uppercase text-muted d-none d-md-block">&nbsp;</label>
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="emailInput" name="email" placeholder="name@example.com" required>
                                        <label for="emailInput">Email Address</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="phoneInput" name="phone" placeholder="+91 98765 43210" required>
                                        <label for="phoneInput">Phone Number</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="tourSelect" name="tour_package" required>
                                            <option selected disabled value="">Select Destination</option>
                                            <option value="shimla-adventure">Shimla Adventure</option>
                                            <option value="manali-escape">Manali Escape</option>
                                            <option value="dharamshala-retreat">Dharamshala Retreat</option>
                                            <option value="spiti-valley-trek">Spiti Valley Trek</option>
                                            <option value="leh-ladakh-expedition">Leh Ladakh Expedition</option>
                                            <option value="custom-tour">Custom Tour</option>
                                        </select>
                                        <label for="tourSelect">Preferred Tour</label>
                                    </div>
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <label class="form-label fw-medium small text-uppercase text-muted">Trip Details</label>
                                </div>
                                
                                <div class="col-md-6 mt-2">
                                    <div class="form-floating">
                                        <input type="date" class="form-control" id="dateInput" name="travel_date" required min="<?php echo date('Y-m-d'); ?>">
                                        <label for="dateInput">Travel Date</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" id="guestsInput" name="guests" placeholder="2" min="1" max="20" required>
                                        <label for="guestsInput">No. of Guests</label>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" placeholder="Any special requests?" id="messageTextarea" name="message" style="height: 100px"></textarea>
                                        <label for="messageTextarea">Special Requests (Optional)</label>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="termsCheck" name="terms" required>
                                        <label class="form-check-label small text-muted" for="termsCheck">
                                            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal" class="text-primary text-decoration-none">Terms & Conditions</a>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12 pt-2">
                                    <button type="submit" id="submitBooking" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm transition-transform">
                                        Confirm Booking Request
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Alerts -->
                            <div class="mt-4">
                                <div class="alert alert-success d-none border-0 shadow-sm" id="bookingSuccess">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle fs-4 me-3 text-success"></i>
                                        <div>
                                            <h6 class="alert-heading fw-bold mb-0">Request Sent!</h6>
                                            <p class="mb-0 small">Sameer will contact you shortly.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-danger d-none border-0 shadow-sm" id="bookingError">
                                    <i class="fas fa-exclamation-circle me-2"></i> <span id="errorMessage">Something went wrong.</span>
                                </div>
                            </div>
                        </form>
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
                                <a href="#contact" class="btn book-btn text-white">Contact Us Today</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
    </section>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms & Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>By booking with Travel In Peace, you agree to the following terms:</p>
                    <ul>
                        <li>50% advance payment is required to confirm booking</li>
                        <li>Cancellation policy: 100% refund 7 days before travel date</li>
                        <li>75% refund for cancellations 3-7 days before travel</li>
                        <li>No refund for cancellations less than 3 days before travel</li>
                        <li>All travelers must carry valid ID proof</li>
                        <li>Travel In Peace reserves the right to change itinerary based on weather conditions</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="pt-5 pb-3">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-brand d-flex align-items-center mb-3">
                        <i class="fas fa-plane me-2" style="font-size: 1.8rem; color: #4F46E5; filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.1));"></i>
                        <span style="font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 1.5rem;">
                            <!-- <span class="text-primary">Tyagi</span>  -->
                            <span class="text-light">Travel In Peace</span>
                        </span>
                    </div>
                    <p class="text-light-50">Offering premium travel services across Himachal Pradesh and beyond. Your comfort is our priority.</p>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/travelinpeace605?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="text-white mb-4">Quick Links</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#about">About Us</a></li>
                        <li><a href="index.php#tours">Tours</a></li>
                        <li><a href="index.php#vehicles">Vehicles</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-white mb-4">Popular Destinations</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="https://www.google.com/maps/place/Shimla,+Himachal+Pradesh/"  target="_blank">Shimla</a></li>
                        <li><a href="https://www.google.com/maps/place/Manali,+Himachal+Pradesh/" target="_blank">Manali</a></li>
                        <li><a href="https://www.google.com/maps/place/Dharamshala,+Himachal+Pradesh/" target="_blank">Dharamshala</a></li>
                        <li><a href="https://www.google.com/maps/place/Dalhousie,+Himachal+Pradesh/" target="_blank">Dalhousie</a></li>
                        <li><a href="https://www.google.com/maps/place/Spiti+Valley,+Himachal+Pradesh/" target="_blank">Spiti Valley</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-white mb-4">Newsletter</h5>
                    <p class="text-light-50">Subscribe to get updates on new tours and offers</p>
                    <form class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your email">
                            <button class="btn btn-primary">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>

            <hr class="mt-4 mb-4 bg-light opacity-10">

            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-light-50">© 2026 Travel In Peace. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-light-50">
                        <a href="#" class="text-light-50">Terms & Conditions</a> |
                        <a href="#" class="text-light-50">Privacy Policy</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top"><i class="fas fa-arrow-up"></i></a>

    <!-- WhatsApp Button - opens modal to choose number -->
    <button id="whatsapp-btn" class="whatsapp-button" title="Contact us on WhatsApp" aria-label="Contact us on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </button>

    <!-- WhatsApp Modal - Shows both numbers -->
    <div id="whatsapp-modal" class="whatsapp-modal">
        <div class="whatsapp-modal-content">
            <div class="whatsapp-modal-header">
                <h3><i class="fab fa-whatsapp"></i> Choose a Number to Message</h3>
                <button class="whatsapp-modal-close" id="whatsapp-modal-close" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="whatsapp-options">
                <a href="https://wa.me/+917559775470?text=Hello, I want to know more about your services." class="whatsapp-option" target="_blank" rel="noopener noreferrer" title="Message this number on WhatsApp">
                    <div class="whatsapp-option-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div class="whatsapp-option-content">
                        <div class="whatsapp-option-label">Primary Contact</div>
                        <div class="whatsapp-option-number">+91 7559775470</div>
                    </div>
                    <div class="whatsapp-option-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <a href="https://wa.me/+918627873362?text=Hello, I want to know more about your services." class="whatsapp-option" target="_blank" rel="noopener noreferrer" title="Message this number on WhatsApp">
                    <div class="whatsapp-option-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div class="whatsapp-option-content">
                        <div class="whatsapp-option-label">Alternative Contact</div>
                        <div class="whatsapp-option-number">+91 8627873362</div>
                    </div>
                    <div class="whatsapp-option-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- Load critical scripts first, defer others -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- WhatsApp Modal functionality -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const whatsappBtn = document.getElementById('whatsapp-btn');
        const whatsappModal = document.getElementById('whatsapp-modal');
        const whatsappModalClose = document.getElementById('whatsapp-modal-close');

        // Open modal when button is clicked
        whatsappBtn.addEventListener('click', function(e) {
            e.preventDefault();
            whatsappModal.classList.add('show');
        });

        // Close modal when X button is clicked
        whatsappModalClose.addEventListener('click', function() {
            whatsappModal.classList.remove('show');
        });

        // Close modal when clicking outside the modal content
        whatsappModal.addEventListener('click', function(e) {
            if (e.target === whatsappModal) {
                whatsappModal.classList.remove('show');
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                whatsappModal.classList.remove('show');
            }
        });
    });
    </script>

    <!-- Defer non-critical scripts -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/ScrollTrigger.min.js"></script>
    <script defer src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Initialize animation library only after page is interactive -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AOS with delay
            setTimeout(function() {
                const aosScript = document.createElement('script');
                aosScript.onload = function() {
                    AOS.init({
                        once: true,
                        offset: 100,
                        duration: 800
                    });
                };
                aosScript.src = 'https://unpkg.com/aos@2.3.1/dist/aos.js';
                document.head.appendChild(aosScript);
            }, 1000);
        });
    </script>

    <!-- Custom scripts - load at the end -->
    <script defer src="js/responsive-helper.js"></script>
    <script defer src="js/weather-service.js"></script>
    <script defer src="js/three-scene.js"></script>
    <script defer src="js/animations.js"></script>
    <script defer src="js/theme-switcher.js"></script>
    <script defer src="js/main.js"></script>
    <script defer src="js/booking.js"></script>
</body>

</html>