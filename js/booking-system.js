/**
 * Booking System for Shimla Air Lines
 * Handles client-side booking functionality with local storage
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize booking system
    const BookingSystem = {
        // Available tours data
        tours: {
            'shimla-adventure': {
                name: 'Shimla Adventure',
                price: 4500,
                duration: '2 Days',
                id: 'shimla-adventure'
            },
            'manali-escape': {
                name: 'Manali Escape',
                price: 5500,
                duration: '3 Days',
                id: 'manali-escape'
            },
            'spiti-valley': {
                name: 'Spiti Valley Trek',
                price: 12000,
                duration: '6 Days',
                id: 'spiti-valley'
            },
            'standard-tour': {
                name: 'Standard Tour Package',
                price: 3500,
                duration: '1 Day',
                id: 'standard-tour'
            }
        },
        
        // DOM elements
        elements: {},
        
        // Current booking data
        currentBooking: {
            tourId: 'standard-tour',
            date: '',
            guests: 2,
            name: '',
            email: '',
            phone: '',
            specialRequests: '',
            reference: ''
        },
        
        // Initialize booking system
        init: function() {
            this.cacheDOM();
            this.bindEvents();
            this.loadStoredBookings();
        },
        
        // Cache DOM elements
        cacheDOM: function() {
            // Booking modal elements
            this.elements.bookingModal = document.getElementById('bookingModal');
            this.elements.tourName = document.getElementById('selectedTourName');
            this.elements.tourPrice = document.getElementById('tourPrice');
            this.elements.tourDuration = document.getElementById('tourDuration');
            this.elements.tourDate = document.getElementById('tourDate');
            this.elements.tourGuests = document.getElementById('tourGuests');
            this.elements.bookingName = document.getElementById('bookingName');
            this.elements.bookingEmail = document.getElementById('bookingEmail');
            this.elements.bookingPhone = document.getElementById('bookingPhone');
            this.elements.bookingSpecialRequests = document.getElementById('bookingSpecialRequests');
            this.elements.termsCheck = document.getElementById('termsCheck');
            this.elements.submitBooking = document.getElementById('submitBooking');
            
            // Success modal elements
            this.elements.successModal = document.getElementById('successModal');
            this.elements.bookingReference = document.getElementById('bookingReference');
            
            // Book Now buttons
            this.elements.bookButtons = document.querySelectorAll('.book-btn');
            
            // Contact form
            this.elements.contactForm = document.getElementById('booking-form');
        },
        
        // Bind event listeners
        bindEvents: function() {
            const self = this;
            
            // Book now buttons
            this.elements.bookButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get tour ID from data attribute or use default
                    const tourId = this.getAttribute('data-tour-id') || 'standard-tour';
                    self.openBookingModal(tourId);
                });
            });
            
            // Submit booking button
            if (this.elements.submitBooking) {
                this.elements.submitBooking.addEventListener('click', function() {
                    self.validateAndSubmit();
                });
            }
            
            // Contact form submission
            if (this.elements.contactForm) {
                this.elements.contactForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(e.target);
                    const bookingData = {
                        name: formData.get('nameInput'),
                        email: formData.get('emailInput'),
                        phone: formData.get('phoneInput'),
                        tourId: formData.get('tourSelect') || 'standard-tour',
                        specialRequests: formData.get('messageTextarea'),
                        date: new Date().toISOString().split('T')[0],
                        guests: 2
                    };
                    self.saveBooking(bookingData);
                    
                    // Show success message
                    const alertElement = document.createElement('div');
                    alertElement.className = 'alert alert-success mt-3';
                    alertElement.innerHTML = 'Your booking request has been submitted successfully! Reference: ' + 
                        self.currentBooking.reference;
                    e.target.appendChild(alertElement);
                    
                    // Reset form
                    e.target.reset();
                    
                    // Remove alert after 5 seconds
                    setTimeout(() => {
                        alertElement.remove();
                    }, 5000);
                });
            }
            
            // Set minimum date for date picker to today
            if (this.elements.tourDate) {
                const today = new Date().toISOString().split('T')[0];
                this.elements.tourDate.setAttribute('min', today);
                this.elements.tourDate.value = today;
            }
        },
        
        // Open booking modal with tour information
        openBookingModal: function(tourId) {
            const tour = this.tours[tourId] || this.tours['standard-tour'];
            this.currentBooking.tourId = tour.id;
            
            // Update modal content
            this.elements.tourName.textContent = tour.name;
            this.elements.tourPrice.textContent = `From ₹${tour.price}`;
            this.elements.tourDuration.textContent = tour.duration;
            
            // Show modal
            const bookingModal = bootstrap.Modal.getOrCreateInstance(this.elements.bookingModal);
            bookingModal.show();
        },
        
        // Validate form and submit booking
        validateAndSubmit: function() {
            const bookingForm = document.getElementById('bookingForm');
            let isValid = true;
            
            // Simple validation
            if (!this.elements.bookingName.value.trim()) {
                this.elements.bookingName.classList.add('is-invalid');
                isValid = false;
            } else {
                this.elements.bookingName.classList.remove('is-invalid');
            }
            
            if (!this.elements.bookingEmail.value.trim() || 
                !this.validateEmail(this.elements.bookingEmail.value)) {
                this.elements.bookingEmail.classList.add('is-invalid');
                isValid = false;
            } else {
                this.elements.bookingEmail.classList.remove('is-invalid');
            }
            
            if (!this.elements.bookingPhone.value.trim()) {
                this.elements.bookingPhone.classList.add('is-invalid');
                isValid = false;
            } else {
                this.elements.bookingPhone.classList.remove('is-invalid');
            }
            
            if (!this.elements.termsCheck.checked) {
                this.elements.termsCheck.classList.add('is-invalid');
                isValid = false;
            } else {
                this.elements.termsCheck.classList.remove('is-invalid');
            }
            
            if (isValid) {
                // Gather booking data
                const bookingData = {
                    tourId: this.currentBooking.tourId,
                    name: this.elements.bookingName.value.trim(),
                    email: this.elements.bookingEmail.value.trim(),
                    phone: this.elements.bookingPhone.value.trim(),
                    date: this.elements.tourDate.value,
                    guests: parseInt(this.elements.tourGuests.value) || 1,
                    specialRequests: this.elements.bookingSpecialRequests.value.trim()
                };
                
                // Process booking
                this.saveBooking(bookingData);
                
                // Close booking modal
                const bookingModal = bootstrap.Modal.getInstance(this.elements.bookingModal);
                bookingModal.hide();
                
                // Show success modal
                setTimeout(() => {
                    const successModal = bootstrap.Modal.getOrCreateInstance(this.elements.successModal);
                    this.elements.bookingReference.textContent = this.currentBooking.reference;
                    successModal.show();
                }, 500);
            }
        },
        
        // Save booking to localStorage
        saveBooking: function(bookingData) {
            // Generate reference number
            const reference = 'SAL-' + Date.now().toString().slice(-6);
            
            // Update current booking
            this.currentBooking = {
                ...bookingData,
                reference: reference,
                timestamp: new Date().toISOString(),
                status: 'pending'
            };
            
            // Get existing bookings from localStorage
            let bookings = JSON.parse(localStorage.getItem('shimlaAirBookings')) || [];
            
            // Add new booking
            bookings.push(this.currentBooking);
            
            // Save to localStorage
            localStorage.setItem('shimlaAirBookings', JSON.stringify(bookings));
            
            console.log('Booking saved:', this.currentBooking);
        },
        
        // Load stored bookings from localStorage
        loadStoredBookings: function() {
            const bookings = JSON.parse(localStorage.getItem('shimlaAirBookings')) || [];
            console.log(`${bookings.length} bookings loaded from storage.`);
        },
        
        // Email validation helper
        validateEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email.toLowerCase());
        }
    };
    
    // Initialize the booking system
    BookingSystem.init();
    
    // Make booking system accessible globally for debugging
    window.BookingSystem = BookingSystem;
});
