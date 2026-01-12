/**
 * Success Animation Component
 * Displays an animated checkmark for successful form submissions
 */

function createSuccessAnimation(container, message = "Success!") {
    // Create SVG animation
    const successHtml = `
    <div class="booking-success-animation">
        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
            <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
            <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
        </svg>
        <h3 class="mt-4">${message}</h3>
    </div>`;
    
    // Insert into container
    container.innerHTML = successHtml;
    
    // Return control to reset animation if needed
    return {
        reset: function() {
            container.innerHTML = '';
        }
    };
}

// Make available globally
window.createSuccessAnimation = createSuccessAnimation;
