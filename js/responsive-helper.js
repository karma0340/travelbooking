/**
 * Responsive Helper Functions
 * Utility functions to help with responsive behavior across the site
 */

document.addEventListener('DOMContentLoaded', function() {
    // Fix for iOS viewport height issue (100vh)
    setViewportHeight();
    window.addEventListener('resize', setViewportHeight);
    window.addEventListener('orientationchange', setViewportHeight);
    
    // Detect touch devices and add appropriate class
    detectTouchDevice();
    
    // Prevent transitions during window resize
    handleResizeTransitions();
    
    // Handle nav collapse when clicking outside on mobile
    handleMobileNavCollapse();
    
    // Add smooth scrolling to anchor links
    addSmoothScrolling();
});

/**
 * Set the viewport height custom property to handle iOS viewport issues
 */
function setViewportHeight() {
    // First we get the viewport height and we multiply it by 1% to get a value for a vh unit
    const vh = window.innerHeight * 0.01;
    // Then we set the value in the --vh custom property to the root of the document
    document.documentElement.style.setProperty('--vh', `${vh}px`);
    
    // If on very small screens, reduce certain margins and padding
    if (window.innerWidth < 380) {
        document.documentElement.classList.add('very-small-screen');
    } else {
        document.documentElement.classList.remove('very-small-screen');
    }
}

/**
 * Detect touch devices and add appropriate class
 */
function detectTouchDevice() {
    const isTouchDevice = 'ontouchstart' in window || 
        navigator.maxTouchPoints > 0 ||
        navigator.msMaxTouchPoints > 0;
    
    if (isTouchDevice) {
        document.documentElement.classList.add('is-touch-device');
    } else {
        document.documentElement.classList.add('no-touch');
    }
    
    // Also detect mobile device
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        document.documentElement.classList.add('is-mobile-device');
    }
}

/**
 * Prevent CSS transitions during window resize for better performance
 */
function handleResizeTransitions() {
    let resizeTimer;
    
    window.addEventListener('resize', function() {
        document.body.classList.add('resize-animation-stopper');
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            document.body.classList.remove('resize-animation-stopper');
        }, 400);
    });
    
    // Also remove preload class after page load to enable transitions
    window.addEventListener('load', function() {
        // Remove the preload class after a small delay to prevent transition flicker
        setTimeout(function() {
            document.documentElement.classList.remove('preload');
            document.body.classList.remove('preload');
        }, 100);
    });
}

/**
 * Handle mobile nav collapse when clicking outside
 */
function handleMobileNavCollapse() {
    document.addEventListener('click', function(event) {
        const navbar = document.querySelector('.navbar-collapse.show');
        if (navbar && !navbar.contains(event.target) && !event.target.classList.contains('navbar-toggler')) {
            // Get the Bootstrap navbar toggler button
            const navbarToggler = document.querySelector('.navbar-toggler');
            if (navbarToggler) {
                navbarToggler.click();
            }
        }
    });
}

/**
 * Add smooth scrolling to all anchor links
 */
function addSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]:not(.carousel-control-prev):not(.carousel-control-next)').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            
            // Skip empty anchors or javascript: links
            if (targetId === '#' || targetId.startsWith('javascript:')) {
                return;
            }
            
            e.preventDefault();
            
            const target = document.querySelector(targetId);
            if (target) {
                // Get header height for offset
                const headerHeight = document.querySelector('header')?.offsetHeight || 80;
                
                window.scrollTo({
                    top: target.offsetTop - headerHeight,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                const navbarCollapse = document.querySelector('.navbar-collapse.show');
                if (navbarCollapse) {
                    const navbarToggler = document.querySelector('.navbar-toggler');
                    if (navbarToggler) {
                        navbarToggler.click();
                    }
                }
            }
        });
    });
}
