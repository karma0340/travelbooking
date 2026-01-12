// GSAP animation for animated logo
window.addEventListener('DOMContentLoaded', function() {
    if (window.gsap) {
        // Animate logo icon
        gsap.fromTo('.logo-icon', {
            scale: 0.7,
            rotate: -30,
            opacity: 0.5
        }, {
            scale: 1,
            rotate: 0,
            opacity: 1,
            duration: 1.2,
            ease: 'elastic.out(1, 0.6)'
        });
        // Animate logo text
        gsap.fromTo('.brand-logo h2', {
            y: -40,
            opacity: 0
        }, {
            y: 0,
            opacity: 1,
            duration: 1.1,
            delay: 0.3,
            ease: 'power2.out'
        });
    }
});
