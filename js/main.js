/**
 * Main JavaScript file for the site
 * Contains functionality for the entire site
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log('Main.js initialized');

    // Initialize smooth scrolling
    initSmoothScrolling();

    // Initialize back-to-top button
    initBackToTop();

    // Initialize mobile menu
    initMobileMenu();

    // Fix for theme toggle
    initThemeToggle();

    // Initialize header scroll effect
    initHeaderScroll();

    // Initialize AOS Animation
    initAOS();
});

/**
 * Initialize smooth scrolling for anchor links
 */
function initSmoothScrolling() {
    // Get all anchor links that point to section IDs
    const anchors = document.querySelectorAll('a[href^="#"]:not([href="#"])');

    // Add click handler to each anchor
    anchors.forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            try {
                // Prevent default anchor behavior
                e.preventDefault();

                // Get the target section ID
                const targetId = this.getAttribute('href');

                // Skip invalid selectors
                if (!targetId || targetId === '#') return;

                // Get the target element
                const targetElement = document.querySelector(targetId);

                // Scroll to the target element if it exists
                if (targetElement) {
                    // Scroll smoothly
                    window.scrollTo({
                        top: targetElement.offsetTop - 80, // Offset for fixed header
                        behavior: 'smooth'
                    });

                    // Update URL hash without scrolling
                    history.pushState(null, null, targetId);
                }
            } catch (error) {
                console.warn('Error in smooth scrolling:', error);
            }
        });
    });
}

/**
 * Initialize back-to-top button
 */
function initBackToTop() {
    const backToTopButton = document.querySelector('.back-to-top');

    if (backToTopButton) {
        // Show/hide based on scroll position
        window.addEventListener('scroll', function () {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('active');
            } else {
                backToTopButton.classList.remove('active');
            }
        });

        // Scroll to top when clicked
        backToTopButton.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

/**
 * Initialize mobile menu
 */
function initMobileMenu() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');

    if (navbarToggler && navbarCollapse) {
        // Close menu when clicking nav links on mobile
        const navLinks = navbarCollapse.querySelectorAll('.nav-link');

        navLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                // Ignore if this is a dropdown toggle
                if (this.classList.contains('dropdown-toggle')) {
                    return;
                }

                // Check if navbar is expanded
                if (navbarCollapse.classList.contains('show')) {
                    // Use Bootstrap's collapse API if available
                    if (window.bootstrap && bootstrap.Collapse) {
                        const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse); // use getInstance to avoid re-initializing
                        if (bsCollapse) {
                            bsCollapse.hide();
                        } else {
                            // If no instance, create one then hide (rare case)
                            new bootstrap.Collapse(navbarCollapse).hide();
                        }
                    } else {
                        // Fallback to manual toggling
                        navbarCollapse.classList.remove('show');
                        const toggler = document.querySelector('.navbar-toggler');
                        if (toggler) toggler.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        });
    }
}

/**
 * Initialize theme toggle
 */
function initThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');

    if (themeToggle) {
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme');
        const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)').matches;

        // Set initial theme based on preference
        if (savedTheme === 'dark' || (!savedTheme && prefersDarkScheme)) {
            document.documentElement.setAttribute('data-theme', 'dark');
            themeToggle.checked = true;
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            themeToggle.checked = false;
        }

        // Handle theme toggle
        themeToggle.addEventListener('change', function () {
            if (this.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            }
        });
    }
}

/**
 * Initialize header scroll effect - adds blur background when scrolling
 */
function initHeaderScroll() {
    const header = document.querySelector('header');

    if (!header) return;

    // Add scroll event listener with throttling for performance
    let scrollTimer = null;

    window.addEventListener('scroll', function () {
        if (scrollTimer) {
            window.cancelAnimationFrame(scrollTimer);
        }

        scrollTimer = window.requestAnimationFrame(function () {
            const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

            // Add scrolled class when scroll position is greater than 50px
            if (scrollPosition > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }, { passive: true });

    // Check initial scroll position
    const initialScrollPosition = window.pageYOffset || document.documentElement.scrollTop;
    if (initialScrollPosition > 50) {
        header.classList.add('scrolled');
    }
}

/**
 * Initialize AOS (Animate On Scroll)
 */
function initAOS() {
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 50,
            delay: 100
        });
    } else {
        // Fallback if AOS is not loaded: make elements visible
        document.querySelectorAll('[data-aos]').forEach(el => {
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
    }
}
