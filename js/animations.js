// GSAP animations and effects

function initAnimations() {
    // Hero section text animations (now handled by AOS)
    
    // Initialize GSAP ScrollTrigger
    gsap.registerPlugin(ScrollTrigger);
    
    // Detect if device is mobile or touch device
    const isMobile = window.innerWidth <= 991 || 'ontouchstart' in window;
    
    // Animate navbar on scroll with smoother settings
    gsap.to('.navbar', {
        scrollTrigger: {
            trigger: 'body',
            start: "top top",
            end: "50px",
            scrub: 0.3
        },
        padding: isMobile ? "8px 0" : "10px 0",
        boxShadow: "0 5px 10px rgba(0,0,0,0.1)",
        ease: "power2.out"
    });
    
    // Parallax effect for tour card images - lighter effect on mobile
    gsap.utils.toArray('.tour-card .card-img-top').forEach(image => {
        gsap.to(image, {
            backgroundPositionY: isMobile ? "20%" : "30%",
            ease: "none",
            scrollTrigger: {
                trigger: image,
                scrub: true,
                start: "top bottom",
                end: "bottom top"
            }
        });
    });
    
    // Animate statistics in hero section with responsive durations
    const counterElements = document.querySelectorAll('.hero-stats .fw-bold');
    counterElements.forEach(counter => {
        const target = parseInt(counter.textContent, 10);
        counter.textContent = '0';
        gsap.to({val: 0}, {
            val: target,
            duration: isMobile ? 1.5 : 2,
            onUpdate: function() {
                counter.textContent = Math.round(this.targets()[0].val) + '+';
            },
            scrollTrigger: {
                trigger: '.hero-stats',
                start: 'top 85%',
                toggleActions: 'play none none none'
            }
        });
    });
    
    // Optimize timeline animations for better performance on mobile
    const timelineItems = document.querySelectorAll('.timeline-item');
    timelineItems.forEach((item, index) => {
        gsap.from(item, {
            scrollTrigger: {
                trigger: item,
                start: "top 90%",
                toggleActions: "play none none none"
            },
            opacity: 0,
            x: isMobile ? -25 : -50,
            duration: isMobile ? 0.4 : 0.6,
            delay: isMobile ? index * 0.2 : index * 0.3,
            ease: "power2.out"
        });
    });
    
    // Infinite subtle float animation for CTA button - smoother on mobile
    gsap.to('.cta-card .book-btn', {
        y: isMobile ? -3 : -5,
        duration: 1.5,
        repeat: -1,
        yoyo: true,
        ease: "sine.inOut"
    });
    
    // Animate testimonial cards on scroll with staggered timing
    const testimonialCards = document.querySelectorAll('.testimonial-carousel .card');
    testimonialCards.forEach((card, index) => {
        gsap.from(card, {
            scrollTrigger: {
                trigger: card,
                start: "top 90%",
                toggleActions: "play none none none"
            },
            y: isMobile ? 30 : 50,
            opacity: 0,
            duration: isMobile ? 0.6 : 0.8,
            delay: index * (isMobile ? 0.15 : 0.2),
            ease: "power2.out"
        });
    });
    
    // Optimize icon animations for better performance
    gsap.utils.toArray('.about-icon, .contact-icon').forEach(icon => {
        gsap.to(icon, {
            scale: isMobile ? 1.05 : 1.1,
            duration: 0.8,
            repeat: -1,
            yoyo: true,
            ease: "sine.inOut",
            scrollTrigger: {
                trigger: icon,
                start: "top 90%",
                toggleActions: "play none none none"
            }
        });
    });
    
    // Make scroll animations responsive with IntersectionObserver
    const animateOnScroll = document.querySelectorAll('.animate-on-scroll');
    
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -50px 0px'
        });
        
        animateOnScroll.forEach(element => {
            observer.observe(element);
        });
    } else {
        // Fallback for browsers that don't support IntersectionObserver
        animateOnScroll.forEach(element => {
            element.classList.add('in-view');
        });
    }
}
