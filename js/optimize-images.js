/**
 * Client-side image optimization helper
 * Helps with loading optimized images and implementing lazy loading
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize lazy loading for all images
    initLazyLoading();
    
    // Optimize existing images
    optimizeImageSizes();
    
    // Detect WebP support
    checkWebpSupport();
});

/**
 * Initialize lazy loading for images
 */
function initLazyLoading() {
    // If browser supports native lazy loading, nothing to do
    if ('loading' in HTMLImageElement.prototype) {
        console.log('Browser supports native lazy loading');
        return;
    }
    
    // For browsers that don't support native lazy loading
    // Use Intersection Observer API to implement lazy loading
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    
    if (!('IntersectionObserver' in window)) {
        // If no Intersection Observer support, load all images immediately
        lazyImages.forEach(img => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
            }
        });
        return;
    }
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                }
                observer.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => {
        if (!img.src && img.dataset.src) {
            // If image doesn't have src but has data-src, observe it
            imageObserver.observe(img);
        }
    });
}

/**
 * Check if browser supports WebP format
 */
function checkWebpSupport() {
    const webpTest = new Image();
    webpTest.onload = function() {
        // WebP is supported
        document.documentElement.classList.add('webp-support');
    };
    webpTest.onerror = function() {
        // WebP is not supported
        document.documentElement.classList.add('no-webp-support');
    };
    webpTest.src = 'data:image/webp;base64,UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAgA0JaQAA3AA/vz0AAA=';
}

/**
 * Optimize image sizes based on viewport
 */
function optimizeImageSizes() {
    const heroBackground = document.querySelector('.hero-section');
    if (heroBackground) {
        // Use smaller image for mobile devices
        if (window.innerWidth < 768) {
            const currentBg = window.getComputedStyle(heroBackground).backgroundImage;
            if (currentBg.includes('w=2070')) {
                const optimizedBg = currentBg.replace('w=2070', 'w=800');
                heroBackground.style.backgroundImage = optimizedBg;
            }
        }
    }
    
    // Resize images in cards based on container size
    const tourImages = document.querySelectorAll('.tour-card img, .vehicle-card img');
    tourImages.forEach(img => {
        const container = img.parentElement;
        if (container) {
            const containerWidth = container.offsetWidth;
            // Set appropriate size that's a bit larger than container for quality
            if (img.src.includes('unsplash.com') && img.src.includes('w=')) {
                const newWidth = Math.ceil(containerWidth * 1.5 / 100) * 100; // Round to nearest 100
                const optimizedSrc = img.src.replace(/w=\d+/, `w=${newWidth}`);
                img.src = optimizedSrc;
            }
        }
    });
}

/**
 * Get optimal image size based on display/element width
 * @param {Number} width - Element width
 */
function getOptimalImageSize(width) {
    // Use standard breakpoints
    if (width <= 576) return 600;
    if (width <= 768) return 800;
    if (width <= 992) return 1200;
    if (width <= 1400) return 1600;
    return 2000; // Default for large screens
}

// Resize handling for responsive images
window.addEventListener('resize', debounce(function() {
    optimizeImageSizes();
}, 300));

// Debounce function to limit execution rate
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}
