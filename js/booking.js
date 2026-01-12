/**
 * Booking System JavaScript
 * Handles booking form submission, validation, and feedback
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log('Booking system initialized');
    // Initialize all booking-related components
    initializeBookingForms();
    initializeVehicleBooking();
    setupNotificationSystem();
});

/**
 * Initialize all booking forms on the page
 */
function initializeBookingForms() {
    console.log('Setting up main booking form');

    // Main contact/booking form
    const mainBookingForm = document.getElementById('booking-form');
    if (mainBookingForm) {
        mainBookingForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Validate form
            if (!validateBookingForm(this)) {
                showNotification('Please fill in all required fields correctly.', 'error');
                return;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBooking');
            if (!submitBtn) return;

            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

            // Collect form data
            const formData = new FormData(this);

            // Send AJAX request
            fetch('api/save-booking.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Server error: ${response.status}`);
                    }
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Invalid JSON response:', text);
                            throw new Error('The server returned an invalid response');
                        }
                    });
                })
                .then(data => {
                    // Reset button
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;

                    console.log('Booking response:', data);

                    // If we got a new token, update our form
                    if (data.new_token) {
                        const csrfInputs = document.querySelectorAll('input[name="csrf_token"]');
                        csrfInputs.forEach(input => {
                            input.value = data.new_token;
                        });
                    }
                    // Handle response
                    if (data.success) {                    // Success feedback
                        const successEl = document.getElementById('bookingSuccess');
                        const errorEl = document.getElementById('bookingError');
                        const debugEl = document.getElementById('bookingDebug');

                        if (successEl) {
                            successEl.classList.remove('d-none');
                        }

                        if (errorEl) errorEl.classList.add('d-none');
                        if (debugEl) debugEl.classList.add('d-none');

                        mainBookingForm.reset();

                        // Show success notification
                        // Check if WhatsApp link is available
                        if (data.whatsapp_link) {
                            // Create a custom modal or alert for immediate action
                            const waConfirm = confirm("Booking Successful! \n\nWould you like to send these details to us on WhatsApp for faster confirmation?");
                            if (waConfirm) {
                                window.open(data.whatsapp_link, '_blank');
                            }
                        }

                        showNotification(data.message || 'Your booking has been submitted successfully!');

                        // Scroll to success message
                        if (successEl) {
                            successEl.scrollIntoView({ behavior: 'smooth' });
                        }
                    } else {
                        // Error feedback
                        const successEl = document.getElementById('bookingSuccess');
                        const errorEl = document.getElementById('bookingError');
                        const errorMessage = document.getElementById('errorMessage');
                        const debugEl = document.getElementById('bookingDebug');
                        const debugMessage = document.getElementById('debugMessage');

                        if (successEl) successEl.classList.add('d-none');
                        if (errorEl) {
                            errorEl.classList.remove('d-none');
                        }

                        // Set error message
                        if (errorMessage) {
                            errorMessage.textContent = data.message || 'There was an error processing your request. Please try again.';
                        }

                        // Show debug information if available
                        if (debugEl && debugMessage && data.debug) {
                            debugMessage.textContent = data.debug;
                            debugEl.classList.remove('d-none');
                        } else if (debugEl) {
                            debugEl.classList.add('d-none');
                        }

                        // Show error notification
                        showNotification(data.message || 'There was an error processing your request.', 'error');
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    // Show error notification
                    const errorAlert = document.getElementById('bookingError');
                    const errorMessage = document.getElementById('errorMessage');
                    const successAlert = document.getElementById('bookingSuccess');

                    if (successAlert) successAlert.classList.add('d-none');

                    if (errorAlert) {
                        errorAlert.classList.remove('d-none');
                        if (errorMessage) {
                            errorMessage.textContent = 'Server error. Please try again or contact support.';
                        }
                    }

                    // Show notification
                    showNotification('Server error. Please try again or contact support.', 'error');

                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
        });
    }
}

/**
 * Initialize vehicle booking modal and form
 */
function initializeVehicleBooking() {
    console.log('Setting up vehicle booking');

    // Vehicle booking form
    const vehicleBookingForm = document.getElementById('vehicle-booking-form');
    const vehicleBookingBtns = document.querySelectorAll('.book-vehicle-btn');

    // Set up booking button click handlers
    if (vehicleBookingBtns.length > 0) {
        vehicleBookingBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const vehicleId = this.getAttribute('data-vehicle-id');
                const vehicleName = this.getAttribute('data-vehicle-name');

                console.log('Booking vehicle:', vehicleId, vehicleName);

                // Set modal title
                const modalTitle = document.getElementById('bookVehicleModalLabel');
                if (modalTitle) {
                    modalTitle.textContent = `Book ${vehicleName || 'Vehicle'}`;
                }

                // Set vehicle ID in form
                const vehicleIdInput = document.getElementById('booking_vehicle_id');
                if (vehicleIdInput) {
                    vehicleIdInput.value = vehicleId;
                }
            });
        });
    }

    // Set up form submission
    if (vehicleBookingForm) {
        vehicleBookingForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Validate form
            if (!validateBookingForm(this)) {
                showNotification('Please fill in all required fields correctly.', 'error');
                return;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitVehicleBooking');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

            // Collect form data
            const formData = new FormData(this);

            // Send AJAX request
            fetch('api/save-booking.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Network response error: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Reset button
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;

                    // Handle response
                    if (data.success) {
                        // Success feedback
                        showNotification(data.message || 'Your booking has been submitted successfully!');

                        // Reset form
                        vehicleBookingForm.reset();

                        // Close modal after delay
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('bookVehicleModal'));
                            if (modal) modal.hide();
                        }, 2000);
                    } else {
                        // Error feedback
                        showNotification(data.message || 'There was an error processing your request.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;

                    // Error feedback
                    showNotification('Network error. Please check your connection and try again.', 'error');
                });
        });
    }

    // Handle "Book Now" buttons in tour cards
    const tourBookBtns = document.querySelectorAll('.tour-card .book-btn');
    if (tourBookBtns.length > 0) {
        tourBookBtns.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const tourId = this.getAttribute('data-tour-id');
                const tourTitle = this.closest('.card').querySelector('.card-title').textContent;

                // Scroll to booking form
                const bookingForm = document.getElementById('booking-form');
                if (bookingForm) {
                    // Populate tour package dropdown if it exists
                    const tourSelect = document.getElementById('tourSelect');
                    if (tourSelect) {
                        // Find the option that matches this tour
                        Array.from(tourSelect.options).forEach(option => {
                            if (option.value === tourId) {
                                option.selected = true;
                            }
                        });
                    }

                    // Scroll to form
                    bookingForm.scrollIntoView({ behavior: 'smooth' });

                    // Focus first input
                    setTimeout(() => {
                        const firstInput = bookingForm.querySelector('input:not([type="hidden"])');
                        if (firstInput) firstInput.focus();
                    }, 800);
                }
            });
        });
    }
}

/**
 * Set up notification system
 */
function setupNotificationSystem() {
    // Create global notification function
    window.showNotification = function (message, type = 'success', duration = 5000) {
        // Create notification container if it doesn't exist
        let container = document.getElementById('notification-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-container';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} notification-toast`;
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Add to container
        container.appendChild(notification);

        // Auto dismiss
        setTimeout(() => {
            notification.remove();
        }, duration);

        // Close button handler
        const closeBtn = notification.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                notification.remove();
            });
        }
    };
}

/**
 * Validate a booking form
 * 
 * @param {HTMLFormElement} form The form to validate
 * @return {boolean} Whether the form is valid
 */
function validateBookingForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let valid = true;

    // Hide all error messages initially
    const errorMessages = form.querySelectorAll('.error-message');
    errorMessages.forEach(msg => {
        msg.style.display = 'none';
    });

    // Reset validation state
    requiredFields.forEach(field => {
        field.classList.remove('input-error');
    });

    // Check each required field
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            const errorId = field.id + '-error';
            const errorMsg = document.getElementById(errorId);
            if (errorMsg) {
                errorMsg.style.display = 'block';
            }
            valid = false;
        }

        // Additional email validation
        if (field.type === 'email' && field.value.trim()) {
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailPattern.test(field.value.trim())) {
                field.classList.add('is-invalid');
                const errorMsg = document.getElementById('email-error');
                if (errorMsg) {
                    errorMsg.style.display = 'block';
                }
                valid = false;
            }
        }        // Additional phone validation
        if (field.name === 'phone' && field.value.trim()) {
            const phonePattern = /^[0-9+\s\-()]{7,15}$/;
            if (!phonePattern.test(field.value.trim())) {
                field.classList.add('is-invalid');
                const errorMsg = document.getElementById('phone-error');
                if (errorMsg) {
                    errorMsg.style.display = 'block';
                }
                valid = false;
            }
        }

        // Date validation
        if (field.type === 'date' && field.value) {
            const selectedDate = new Date(field.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (selectedDate < today) {
                field.classList.add('is-invalid');
                valid = false;
            }
        }

        // Name validation
        if (field.name === 'name' && field.value.trim()) {
            // Name should be at least 2 characters
            if (field.value.trim().length < 2) {
                field.classList.add('is-invalid');
                valid = false;
            }
        }

        // Terms checkbox validation
        if (field.type === 'checkbox' && field.name === 'terms') {
            if (!field.checked) {
                field.classList.add('is-invalid');
                valid = false;
            }
        }
    });

    return valid;
}
