<?php
require_once __DIR__ . '/seo-helper.php';

// Get current page for tracking
$currentPage = basename($_SERVER['SCRIPT_NAME']);

// Generate SEO data if not already set
$pageTitle = $pageTitle ?? 'Travel In Peace - Experience Himachal\'s Beauty';
$pageDescription = $pageDescription ?? 'Premium travel services in Himachal Pradesh - tours, vehicles, and personalized experiences.';
$pageKeywords = $pageKeywords ?? generateSEOKeywords($pageDescription);
$openGraphTags = $openGraphTags ?? generateOpenGraphTags($pageTitle, $pageDescription);
$twitterCardTags = $twitterCardTags ?? generateTwitterCardTags($pageTitle, $pageDescription);
$structuredData = $structuredData ?? generateStructuredData($pageTitle, $pageDescription, $currentPage);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4F46E5">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($pageKeywords); ?>">
    <meta name="author" content="Travel In Peace">
    <meta name="robots" content="index, follow">
    <meta name="language" content="English">
    
    <!-- Open Graph / Facebook -->
    <?php if (!empty($openGraphTags)): ?>
        <?php foreach ($openGraphTags as $property => $content): ?>
            <meta property="<?php echo htmlspecialchars($property); ?>" content="<?php echo htmlspecialchars($content); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Twitter -->
    <?php if (!empty($twitterCardTags)): ?>
        <?php foreach ($twitterCardTags as $name => $content): ?>
            <meta name="<?php echo htmlspecialchars($name); ?>" content="<?php echo htmlspecialchars($content); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>">
    
    <!-- JSON-LD Structured Data -->
    <?php if (!empty($structuredData)): ?>
    <script type="application/ld+json">
    <?php echo $structuredData; ?>
    </script>
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive-fixes.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
    :root {
        --primary-color: #4F46E5;
        --primary-color-dark: #4338CA;
        --text-color: #111827;
        --header-height: 80px;
        --transition-fast: 0.3s ease;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        scroll-behavior: smooth;
        scroll-padding-top: var(--header-height);
    }

    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    [data-theme="dark"] {
        --bs-body-bg: #111827;
        --bs-body-color: #F9FAFB;
    }

    /* ===========================
       HEADER / NAVBAR / LOGO
       =========================== */
    header#mainHeader {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 10000;
        height: var(--header-height);
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
        transition: all var(--transition-fast);
        border-bottom: 1px solid rgba(255, 255, 255, 0.18);
    }

    [data-theme="dark"] header#mainHeader {
        background: rgba(17, 24, 39, 0.85);
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    header#mainHeader.scrolled {
        height: 70px;
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
    }

    [data-theme="dark"] header#mainHeader.scrolled {
        background: rgba(17, 24, 39, 0.95);
    }

    .header-spacer {
        height: var(--header-height);
        transition: height var(--transition-fast);
    }

    header#mainHeader.scrolled + .header-spacer {
        height: 70px;
    }

    .navbar {
        height: 100%;
        padding: 0;
        background: transparent !important;
    }

    .navbar > .container {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        z-index: 10;
    }

    /* LOGO */
    .navbar-brand {
        display: flex;
        align-items: center;
        padding: 0;
        margin: 0;
        height: 100%;
    }

    .navbar-brand img {
        height: 75px;
        width: auto;
        max-width: 260px;
        object-fit: contain;
        transition: transform var(--transition-fast), height var(--transition-fast), filter var(--transition-fast);
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
    }

    header#mainHeader.scrolled .navbar-brand img {
        height: 62px;
        filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.25));
    }

    @media (max-width: 991.98px) {
        .navbar-brand img {
            height: 44px;
            max-width: 180px;
        }
        header#mainHeader.scrolled .navbar-brand img {
            height: 40px;
        }
    }

    @media (max-width: 575.98px) {
        .navbar-brand img {
            height: 40px;
            max-width: 160px;
        }
    }

    /* NAV LINKS (DESKTOP) */
    .navbar-nav {
        align-items: center;
        gap: 0.25rem;
    }

    .navbar-nav .nav-link {
        position: relative;
        font-weight: 500;
        font-size: 0.98rem;
        padding: 0.6rem 0.9rem;
        margin: 0 0.1rem;
        color: #111827 !important;
        border-radius: 0.5rem;
        transition: color var(--transition-fast), background-color var(--transition-fast), transform var(--transition-fast);
    }

    [data-theme="dark"] .navbar-nav .nav-link {
        color: #E5E7EB !important;
    }

    .navbar-nav .nav-link::after {
        content: '';
        position: absolute;
        left: 50%;
        bottom: 0.1rem;
        width: 0;
        height: 2px;
        border-radius: 999px;
        background-color: var(--primary-color);
        transform: translateX(-50%);
        transition: width var(--transition-fast);
    }

    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link:focus {
        color: var(--primary-color) !important;
        background-color: rgba(79, 70, 229, 0.08);
        transform: translateY(-1px);
    }

    .navbar-nav .nav-link:hover::after,
    .navbar-nav .nav-link.active::after {
        width: 60%;
    }

    [data-theme="dark"] .navbar-nav .nav-link:hover,
    [data-theme="dark"] .navbar-nav .nav-link:focus {
        color: #A5B4FC !important;
        background-color: rgba(79, 70, 229, 0.18);
    }

    /* DROPDOWN */
    .dropdown-menu {
        position: absolute !important;
        z-index: 10001 !important; /* Higher than header */
        border: none;
        box-shadow: 0 10px 40px rgba(15, 23, 42, 0.2);
        border-radius: 1rem;
        padding: 0.75rem;
        min-width: 14rem;
        margin-top: 0.5rem;
        animation: dropdownFadeIn 0.2s ease-out;
        background-color: #ffffff;
    }

    @keyframes dropdownFadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-menu-tours {
        min-width: 16rem;
    }
    
    /* Ensure dropdown parent has proper positioning */
    .nav-item.dropdown {
        position: static;
    }
    
    @media (min-width: 992px) {
        .nav-item.dropdown {
            position: relative;
        }
    }

    .dropdown-item {
        font-size: 0.95rem;
        padding: 0.65rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        font-weight: 500;
    }

    .dropdown-item i {
        font-size: 1.1rem;
        width: 24px;
        transition: transform 0.2s ease;
    }

    .dropdown-item:hover {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(99, 102, 241, 0.08) 100%);
        color: var(--primary-color);
        transform: translateX(4px);
        padding-left: 1.25rem;
    }

    .dropdown-item:hover i {
        transform: scale(1.15);
    }

    .dropdown-item.fw-semibold {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.08) 0%, rgba(99, 102, 241, 0.05) 100%);
        border: 1px solid rgba(79, 70, 229, 0.15);
    }

    .dropdown-item.fw-semibold:hover {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.15) 0%, rgba(99, 102, 241, 0.12) 100%);
        border-color: rgba(79, 70, 229, 0.25);
    }

    .dropdown-divider {
        margin: 0.5rem 0;
        border-color: rgba(79, 70, 229, 0.1);
    }

    [data-theme="dark"] .dropdown-menu {
        background-color: #1F2937;
        border: 1px solid #374151;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
    }

    [data-theme="dark"] .dropdown-item {
        color: #E5E7EB;
    }

    [data-theme="dark"] .dropdown-item:hover {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(79, 70, 229, 0.15) 100%);
        color: #A5B4FC;
    }

    [data-theme="dark"] .dropdown-item.fw-semibold {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(79, 70, 229, 0.1) 100%);
        border-color: rgba(99, 102, 241, 0.2);
    }

    [data-theme="dark"] .dropdown-item.fw-semibold:hover {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.25) 0%, rgba(79, 70, 229, 0.2) 100%);
        border-color: rgba(99, 102, 241, 0.3);
    }

    [data-theme="dark"] .dropdown-divider {
        border-color: rgba(99, 102, 241, 0.15);
    }

    /* THEME TOGGLE + MOBILE TOGGLER CONTAINER */
    .mobile-controls-container {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .navbar-toggler {
        border: none;
        padding: 0.5rem;
        background: transparent !important;
        transition: all var(--transition-fast);
        color: #111827;
        box-shadow: none !important;
    }

    [data-theme="dark"] .navbar-toggler {
        background: transparent !important;
        color: #F9FAFB;
    }

    .navbar-toggler:focus {
        box-shadow: none;
        outline: none;
    }

    .navbar-toggler:hover {
        transform: scale(1.1);
        color: var(--primary-color);
    }

    .navbar-toggler i {
        font-size: 1.1rem;
    }

    /* ===========================
       PROFESSIONAL THEME TOGGLE
       =========================== */
    .theme-switch-wrapper {
        display: flex;
        align-items: center;
    }

    .theme-switch {
        display: inline-block;
        height: 28px;
        position: relative;
        width: 50px;
    }

    .theme-switch input {
        display: none;
    }

    .slider {
        background-color: #E2E8F0;
        bottom: 0;
        cursor: pointer;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        transition: .4s;
        border-radius: 34px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 5px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    }

    .slider i {
        font-size: 12px;
        z-index: 1;
        transition: .4s;
    }

    .fa-sun { color: #F59E0B; }
    .fa-moon { color: #94A3B8; }

    .slider:before {
        background-color: #fff;
        bottom: 3px;
        content: "";
        height: 22px;
        left: 3px;
        position: absolute;
        transition: .4s;
        width: 22px;
        border-radius: 50%;
        z-index: 2;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    input:checked + .slider {
        background-color: #312E81;
    }

    input:checked + .slider:before {
        transform: translateX(22px);
        background-color: #F8FAFC;
    }

    [data-theme="dark"] .fa-sun { color: #475569; }
    [data-theme="dark"] .fa-moon { color: #F1F5F9; }

    .theme-switch:hover .slider:before {
        box-shadow: 0 0 8px rgba(79, 70, 229, 0.4);
    }

    /* CALL + BOOK BUTTONS */
    .navbar-end .btn,
    .nav-buttons .btn {
        font-weight: 500;
        letter-spacing: 0.02em;
        border-radius: 999px;
        padding: 0.45rem 1.2rem;
        text-transform: none;
        transition: background-color var(--transition-fast), color var(--transition-fast), box-shadow var(--transition-fast), transform var(--transition-fast);
    }

    .btn-primary {
        background-image: linear-gradient(to right, var(--primary-color), var(--primary-color-dark));
        border: none;
        color: #FFFFFF;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.45);
    }

    .btn-primary:hover {
        background-image: linear-gradient(to right, var(--primary-color-dark), var(--primary-color));
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(79, 70, 229, 0.55);
    }

    .btn-outline-primary {
        border-width: 2px;
        border-color: var(--primary-color);
        color: var(--primary-color);
        background-color: transparent;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
    }

    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        color: #FFFFFF;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.55);
    }

    @media (max-width: 1199.98px) {
        .navbar-end .btn {
            padding: 0.4rem 0.9rem;
            font-size: 0.85rem;
        }
    }

    /* NAV COLLAPSE (MOBILE) */
    /* NAV COLLAPSE (MOBILE) MOVED TO EXTERNAL CSS */
    @media (max-width: 991.98px) {
        .navbar-nav {
            margin-top: 1rem;
        }
    }

/* Dark Mode text adjustments for readability */
[data-theme="dark"] .text-muted {
    color: #9ca3af !important;
}

[data-theme="dark"] .card-text {
    color: #e5e7eb;
}

/* Navbar Fixes */
.navbar-collapse {
    max-height: 90vh;
    overflow-y: auto;
}

/* Desktop: Allow dropdowns to overflow */
@media (min-width: 992px) {
    .navbar-collapse {
        overflow: visible !important;
    }
}

@media (max-width: 991.98px) {
    .dropdown-menu {
        display: block; /* Always show in DOM for animation but hide visually */
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: all 0.3s ease;
        margin-top: 0;
        padding: 0;
        border: none;
        background: transparent;
        box-shadow: none;
        position: static !important; /* Fix for mobile menu positioning */
    }

    .dropdown-menu.show {
        max-height: 500px; /* Arbitrary large height */
        opacity: 1;
        padding: 0.5rem 0;
        margin-top: 0.5rem;
    }

    .dropdown-item {
        color: #1f2937; /* Ensure visible text on light mobile menu */
    }

    [data-theme="dark"] .dropdown-item {
        color: #e5e7eb;
    }
}


    </style>
    <script>
        // Remove preload class once page loads (Performance optimization)
        window.addEventListener('load', function() {
            document.documentElement.classList.remove('preload');
            document.body.classList.remove('preload');
        });
    </script>
</head>
<body class="min-h-screen bg-transparent preload">
<script>
// Theme switching functionality
document.addEventListener('DOMContentLoaded', function() {
    // Check for saved theme preference or use system preference
    const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Update toggle state
    const themeToggles = document.querySelectorAll('.theme-controller');
    themeToggles.forEach(toggle => {
        toggle.checked = savedTheme === 'dark';
    });
    
    // Add event listeners to all theme toggles
    themeToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const newTheme = this.checked ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update all toggles
            document.querySelectorAll('.theme-controller').forEach(t => t.checked = this.checked);
        });
    });
    
    // Scroll handling for header
    const header = document.querySelector('header#mainHeader');
    function handleScroll() {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }
    
    // Initial check
    handleScroll();
    
    // Listen for scroll events
    window.addEventListener('scroll', handleScroll);
    
    // Mobile menu closing logic is handled in main.js
    const menuToggle = document.getElementById('navbarNav'); 
    // We leave menuToggle definition if used elsewhere, or just remove the whole block.
    // Actually, checking if menuToggle is used:
    // It's used in the if check.
    
    // Removing the listener attachment block is sufficient.
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const headerHeight = document.querySelector('header#mainHeader').offsetHeight;
                const targetPosition = target.offsetTop - headerHeight - 20;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
});

// Google Analytics conversion tracking for phone calls
function gtag_report_conversion(url) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'conversion', {
            'send_to': 'AW-CONVERSION_ID/CONVERSION_LABEL',
            'event_callback': function() {
                if (typeof(url) != 'undefined') {
                    window.location = url;
                }
            }
        });
        return false;
    } else {
        if (typeof(url) != 'undefined') {
            window.location = url;
        }
        return true;
    }
}
</script>

<header id="mainHeader">
    <nav class="navbar navbar-expand-lg h-100">
        <div class="container h-100 px-3 px-lg-4">
            <!-- Logo -->
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="Travel In Peace Logo">
            </a>

            <!-- Theme toggle + Mobile menu button -->
            <div class="mobile-controls-container d-lg-none">
                <div class="theme-switch-wrapper me-2">
                    <label class="theme-switch" for="checkbox-mobile" title="Toggle Dark/Light Mode">
                        <input type="checkbox" id="checkbox-mobile" class="theme-controller" />
                        <div class="slider">
                            <i class="fas fa-sun"></i>
                            <i class="fas fa-moon"></i>
                        </div>
                    </label>
                </div>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarNav" aria-controls="navbarNav"
                        aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Navigation -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="tours.php" id="toursDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Tours
                        </a>
                        <ul class="dropdown-menu dropdown-menu-tours" aria-labelledby="toursDropdown">
                            <li>
                                <a class="dropdown-item" href="tours.php?category=adventure">
                                    <i class="fas fa-hiking me-2 text-primary"></i>
                                    <span>Adventure Tours</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="tours.php?category=family">
                                    <i class="fas fa-users me-2 text-success"></i>
                                    <span>Family Tours</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="tours.php?category=honeymoon">
                                    <i class="fas fa-heart me-2 text-danger"></i>
                                    <span>Honeymoon</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="tours.php?category=spiritual">
                                    <i class="fas fa-om me-2 text-warning"></i>
                                    <span>Spiritual</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="tours.php?category=group">
                                    <i class="fas fa-users-cog me-2 text-info"></i>
                                    <span>Group Tours</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="tours.php?category=nature">
                                    <i class="fas fa-leaf me-2 text-success"></i>
                                    <span>Nature</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-2"></li>
                            <li>
                                <a class="dropdown-item fw-semibold" href="tours.php">
                                    <i class="fas fa-th-large me-2 text-primary"></i>
                                    <span>View All Tours</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="vehicles.php">Vehicles</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>

                    <!-- MOBILE: Call + Book -->
                    <li class="nav-item d-lg-none nav-buttons-mobile">
                        <a href="tel:+918627873362" class="btn btn-outline-primary w-100 mb-3"
                           onclick="return gtag_report_conversion('tel:+918627873362');">
                            <i class="fas fa-phone-alt me-2"></i> Call +91 8627873362
                        </a>
                        <a href="book.php" class="btn btn-primary w-100">
                            <i class="fas fa-calendar-alt me-2"></i> Book Now
                        </a>
                    </li>
                </ul>

                <!-- DESKTOP: Theme + Call + Book -->
                <div class="navbar-end d-none d-lg-flex align-items-center ms-3">
                    <div class="theme-switch-wrapper me-3">
                        <label class="theme-switch" for="checkbox-desktop" title="Toggle Dark/Light Mode">
                            <input type="checkbox" id="checkbox-desktop" class="theme-controller" />
                            <div class="slider">
                                <i class="fas fa-sun"></i>
                                <i class="fas fa-moon"></i>
                            </div>
                        </label>
                    </div>

                    <button class="btn btn-outline-primary btn-sm me-3"
                            onclick="return gtag_report_conversion('tel:+918627873362');">
                        <i class="fas fa-phone-alt me-1"></i> Call Now
                    </button>

                    <a href="book.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-calendar-alt me-1"></i> Book Now
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>

<div class="header-spacer"></div>
