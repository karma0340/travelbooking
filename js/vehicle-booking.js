/**
 * Vehicle Booking Helper
 * This script adds enhanced functionality to vehicle booking modals
 */

// Initialize when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize event handlers for vehicle booking functionality
    initBookingHandlers();
});

/**
 * Initialize all booking-related functionality
 */
function initBookingHandlers() {
    // Set up "Book Now" button handlers
    setupBookButtons();
    
    // Handle vehicle booking form submission
    setupBookingFormSubmission();
}

/**
 * Set up event handlers for book buttons
 * This can be called again when new buttons are added dynamically
 */
function setupBookButtons() {
    // Target both directly targeted buttons and those with the dedicated class
    const bookButtons = document.querySelectorAll('[data-bs-target="#bookVehicleModal"], .book-vehicle-btn');
    bookButtons.forEach(button => {
        // Remove existing event listeners to prevent duplicates
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        newButton.addEventListener('click', function() {
            const vehicleId = this.getAttribute('data-vehicle-id');
            const vehicleName = this.getAttribute('data-vehicle-name');
            
            if (!vehicleId || !vehicleName) {
                console.error('Missing vehicle ID or name attributes on button');
                return;
            }
            
            // Reset form validation state if it was previously validated
            const form = document.getElementById('vehicle-booking-form');
            if (form) {
                form.classList.remove('was-validated');
                
                // Reset any inert attribute
                if (form.hasAttribute('inert')) {
                    form.removeAttribute('inert');
                }
            }
            
            // Update form fields
            const vehicleIdInput = document.getElementById('booking_vehicle_id');
            const modalTitle = document.getElementById('bookVehicleModalLabel');
            
            if (vehicleIdInput) vehicleIdInput.value = vehicleId;
            if (modalTitle) modalTitle.textContent = 'Book ' + vehicleName;
        });
    });
}

/**
 * Set up form submission handler
 */
function setupBookingFormSubmission() {
    const bookingForm = document.getElementById('vehicle-booking-form');
    if (bookingForm) {
        // Add validation styles
        bookingForm.classList.add('needs-validation');
        bookingForm.setAttribute('novalidate', '');
        
        // Remove any existing event listeners
        const newForm = bookingForm.cloneNode(true);
        bookingForm.parentNode.replaceChild(newForm, bookingForm);
        
        newForm.addEventListener('submit', handleBookingSubmit);
    }
}

/**
 * Handle booking form submission
 */
function handleBookingSubmit(e) {
    e.preventDefault();
    
    const bookingForm = e.target;
    
    // Add validation
    if (!bookingForm.checkValidity()) {
        e.stopPropagation();
        bookingForm.classList.add('was-validated');
        return;
    }
    
    // Collect form data
    const formData = new FormData(bookingForm);
    
    // Show loading state
    const submitBtn = document.getElementById('submitVehicleBooking');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
    
    // Send booking request
    fetch('api/save-booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Success message
            alert('Booking submitted successfully! Your booking reference is: ' + data.booking_ref);
            
            // Close the modal
            closeBookingModal();
            
            // Reset form
            bookingForm.classList.remove('was-validated');
            bookingForm.reset();
        } else {
            alert('Error: ' + (data.message || 'Unknown error occurred'));
        }
    })    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your booking. Please try again.');
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

/**
 * Close the booking modal with fallback methods
 */
function closeBookingModal() {
    // Use Bootstrap 5 method to hide modal
    const bookingModal = document.getElementById('bookVehicleModal');
    try {
        const modal = bootstrap.Modal.getInstance(bookingModal);
        if (modal) {
            modal.hide();
            return;
        }
    } catch (e) {
        console.warn('Error using Bootstrap modal instance:', e);
    }
    
    // Fallback if bootstrap Modal instance not found
    bookingModal.classList.remove('show');
    document.body.classList.remove('modal-open');
    
    // Remove any inert attribute that might have been added
    const form = document.getElementById('vehicle-booking-form');
    if (form && form.hasAttribute('inert')) {
        form.removeAttribute('inert');
    }
    
    // Remove backdrop
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) backdrop.parentNode.removeChild(backdrop);
}

/**
 * Set up vehicle detail modal functionality
 */
function setupDetailModal() {
    const detailButtons = document.querySelectorAll('[data-bs-target="#vehicleDetailModal"]');
    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const vehicleId = this.getAttribute('data-vehicle-id');
            const contentDiv = document.getElementById('vehicleDetailContent');
            
            // Show loading spinner
            contentDiv.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Fetch vehicle details
            fetch(`api/get-vehicle.php?id=${vehicleId}`)            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const vehicle = data.vehicle;
                    
                    // Build HTML content safely
                    let featuresHtml = '';
                    if (Array.isArray(vehicle.features)) {
                        featuresHtml = vehicle.features.map(feature => 
                            `<span class="feature-badge"><i class="fas fa-check me-1"></i> ${feature}</span>`
                        ).join('');
                    }
                    
                    // Create HTML content
                    const html = `
                        <div class="row">
                            <div class="col-md-6">
                                <img src="${vehicle.image || 'images/vehicle-placeholder.jpg'}" class="img-fluid rounded" alt="${vehicle.name}">
                            </div>
                            <div class="col-md-6">
                                <h4>${vehicle.name}</h4>
                                <div class="vehicle-specs my-3">
                                    <div class="spec-item">
                                        <i class="fas fa-users"></i>
                                        <span>${vehicle.seats} Seater</span>
                                    </div>
                                    <div class="spec-item">
                                        <i class="fas fa-suitcase"></i>
                                        <span>${vehicle.bags} Bags</span>
                                    </div>
                                    <div class="spec-item">
                                        <i class="fas fa-rupee-sign"></i>
                                        <span>₹${vehicle.price_per_day}/day</span>
                                    </div>
                                </div>
                                
                                <p>${vehicle.description}</p>
                                
                                <h5 class="mb-2">Features</h5>
                                <div class="vehicle-features mb-3">
                                    ${featuresHtml}
                                </div>
                                
                                <button class="btn btn-primary w-100 mt-3" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#bookVehicleModal" data-vehicle-id="${vehicle.id}" data-vehicle-name="${vehicle.name}">
                                    Book This Vehicle
                                </button>
                            </div>
                        </div>
                    `;
                    
                    contentDiv.innerHTML = html;
                    
                    // Rebind event handler for the newly added "Book This Vehicle" button
                    const detailBookBtn = contentDiv.querySelector('[data-bs-target="#bookVehicleModal"]');
                    if (detailBookBtn) {
                        detailBookBtn.addEventListener('click', function() {
                            const vehicleId = this.getAttribute('data-vehicle-id');
                            const vehicleName = this.getAttribute('data-vehicle-name');
                            
                            // Find and update form elements
                            const vehicleIdInput = document.getElementById('booking_vehicle_id');
                            const modalTitle = document.getElementById('bookVehicleModalLabel');
                            
                            if (vehicleIdInput) vehicleIdInput.value = vehicleId;
                            if (modalTitle) modalTitle.textContent = 'Book ' + vehicleName;
                        });
                    }
                    
                    // After adding new book buttons, refresh their event handlers
                    setupBookButtons();
                } else {
                    contentDiv.innerHTML = `<div class="alert alert-danger">Error: ${data.message || 'Unknown error'}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                contentDiv.innerHTML = `<div class="alert alert-danger">An error occurred while loading vehicle details</div>`;
            });
        });
    });
}

// Export functions that might be needed by other scripts
window.vehicleBooking = {
    initBookingHandlers,
    setupBookButtons,
    closeBookingModal,
    setupDetailModal
};

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initBookingHandlers();
    setupDetailModal();
});
