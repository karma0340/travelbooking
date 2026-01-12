/**
 * Vehicle Filter JS
 * Handles filtering functionality for the vehicle listing page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize vehicle filter functionality
    initVehicleFilter();
});

/**
 * Initialize vehicle filter functionality
 */
function initVehicleFilter() {
    // Handle price range slider visual update
    setupPriceRangeSlider();
    
    // Handle filter form submission
    setupFilterFormSubmission();
}

/**
 * Set up price range slider
 */
function setupPriceRangeSlider() {
    const priceRange = document.getElementById('price-range');
    const priceValue = document.getElementById('price-value');
    
    if (priceRange && priceValue) {
        // Set initial value
        priceValue.textContent = `₹${priceRange.value}`;
        
        // Update value on slider change
        priceRange.addEventListener('input', function() {
            priceValue.textContent = `₹${this.value}`;
        });
    }
}

/**
 * Set up filter form submission
 */
function setupFilterFormSubmission() {
    const filterForm = document.getElementById('filter-form');
    const applyFilterBtn = document.getElementById('applyFilter');
    const resetFilterBtn = document.getElementById('resetFilter');
    
    if (filterForm && applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            // Collect filter values
            const filters = getFilterValues();
            
            // Apply filters to vehicles
            filterVehicles(filters);
            
            // Close modal
            const filterModal = bootstrap.Modal.getInstance(document.getElementById('vehicleFilterModal'));
            if (filterModal) {
                filterModal.hide();
            }
        });
    }
    
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
            // Reset the form
            if (filterForm) {
                filterForm.reset();
                
                // Reset price value display
                const priceRange = document.getElementById('price-range');
                const priceValue = document.getElementById('price-value');
                if (priceRange && priceValue) {
                    priceValue.textContent = `₹${priceRange.value}`;
                }
            }
        });
    }
}

/**
 * Get filter values from form
 * @returns {Object} Filter values
 */
function getFilterValues() {
    const filters = {
        seats: [],
        maxPrice: document.getElementById('price-range')?.value || 10000,
        features: []
    };
    
    // Collect seat filters
    document.querySelectorAll('#filter-form input[type="checkbox"][id^="seats"]').forEach(checkbox => {
        if (checkbox.checked) {
            filters.seats.push(parseInt(checkbox.value, 10));
        }
    });
    
    // Collect feature filters
    document.querySelectorAll('#filter-form input[type="checkbox"][id^="feature"]').forEach(checkbox => {
        if (checkbox.checked) {
            filters.features.push(checkbox.value);
        }
    });
    
    return filters;
}

/**
 * Filter vehicles based on selected criteria
 * @param {Object} filters Filter values
 */
function filterVehicles(filters) {
    const vehicleCards = document.querySelectorAll('.vehicle-card');
    let matchCount = 0;
    
    vehicleCards.forEach(card => {
        const cardContainer = card.closest('.col-md-6');
        if (!cardContainer) return;
        
        // Get vehicle data from card
        const seats = parseInt(card.getAttribute('data-seats') || '0', 10);
        const price = parseInt(card.getAttribute('data-price') || '0', 10);
        const features = (card.getAttribute('data-features') || '').split(',');
        
        // Check if vehicle matches the filters
        let matchesSeats = true;
        let matchesPrice = true;
        let matchesFeatures = true;
        
        // Filter by seats
        if (filters.seats.length > 0) {
            if (filters.seats.includes(4) && seats <= 4) {
                // Up to 4 seats
                matchesSeats = true;
            } else if (filters.seats.includes(7) && seats > 4 && seats <= 7) {
                // 5-7 seats
                matchesSeats = true;
            } else if (filters.seats.includes(10) && seats > 7) {
                // 8+ seats
                matchesSeats = true;
            } else {
                matchesSeats = false;
            }
        }
        
        // Filter by price
        if (price > filters.maxPrice) {
            matchesPrice = false;
        }
        
        // Filter by features
        if (filters.features.length > 0) {
            matchesFeatures = filters.features.every(feature => 
                features.map(f => f.trim().toLowerCase()).includes(feature.toLowerCase())
            );
        }
        
        // Show/hide the card based on filter matches
        if (matchesSeats && matchesPrice && matchesFeatures) {
            cardContainer.style.display = '';
            matchCount++;
        } else {
            cardContainer.style.display = 'none';
        }
    });
    
    // Display message if no matches found
    const noResultsMessage = document.getElementById('no-filter-results');
    if (noResultsMessage) {
        if (matchCount === 0) {
            noResultsMessage.classList.remove('d-none');
        } else {
            noResultsMessage.classList.add('d-none');
        }
    }
    
    // Show clear filters button if any filter is applied
    const hasFilters = filters.seats.length > 0 || 
                      filters.maxPrice < 10000 || 
                      filters.features.length > 0;
                      
    toggleClearFiltersButton(hasFilters);
    
    // Add a nice animation to the filtered results
    animateFilteredResults();
}

/**
 * Show or hide clear filters button based on whether filters are applied
 * @param {boolean} show Whether to show the button
 */
function toggleClearFiltersButton(show) {
    let clearBtn = document.getElementById('clear-filters');
    
    if (!clearBtn && show) {
        // Create the clear filters button if it doesn't exist
        clearBtn = document.createElement('button');
        clearBtn.id = 'clear-filters';
        clearBtn.className = 'btn btn-outline-secondary mt-3';
        clearBtn.innerHTML = '<i class="fas fa-times me-2"></i> Clear Filters';
        clearBtn.addEventListener('click', clearFilters);
        
        // Add it to the page
        const filterSection = document.querySelector('.filter-section');
        if (filterSection) {
            filterSection.appendChild(clearBtn);
        }
    } else if (clearBtn) {
        clearBtn.style.display = show ? 'block' : 'none';
    }
}

/**
 * Clear all filters and show all vehicles
 */
function clearFilters() {
    // Reset form inputs
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        filterForm.reset();
        
        // Reset price value display
        const priceRange = document.getElementById('price-range');
        const priceValue = document.getElementById('price-value');
        if (priceRange && priceValue) {
            priceValue.textContent = `₹${priceRange.value}`;
        }
    }
    
    // Show all vehicles
    document.querySelectorAll('.vehicle-card').forEach(card => {
        const cardContainer = card.closest('.col-md-6');
        if (cardContainer) {
            cardContainer.style.display = '';
        }
    });
    
    // Hide no results message
    const noResultsMessage = document.getElementById('no-filter-results');
    if (noResultsMessage) {
        noResultsMessage.classList.add('d-none');
    }
    
    // Hide clear filters button
    toggleClearFiltersButton(false);
    
    // Add animation
    animateFilteredResults();
}

/**
 * Add animation to filtered results
 */
function animateFilteredResults() {
    const visibleCards = document.querySelectorAll('.vehicle-card:not([style*="display: none"])');
    
    visibleCards.forEach((card, index) => {
        const cardContainer = card.closest('.col-md-6');
        if (cardContainer) {
            // Add animation with staggered delay
            cardContainer.style.opacity = '0';
            cardContainer.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                cardContainer.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                cardContainer.style.opacity = '1';
                cardContainer.style.transform = 'translateY(0)';
            }, index * 100);
        }
    });
}
