<!-- Simple Instant Review Modal -->
<div class="modal fade" id="addReviewModal" tabindex="-1" aria-labelledby="addReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold" id="addReviewModalLabel">
                    <i class="fas fa-star me-2"></i>Write a Review
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="instantReviewForm" class="needs-validation" novalidate>
                    <!-- Google Sign-In Button (Fallback) -->
                    <div id="googleButtonPlaceholder" class="mb-4 d-flex justify-content-center"></div>

                    <!-- Rating Stars -->
                    <div class="text-center mb-4">
                        <label class="form-label d-block fw-bold mb-2">How was your experience?</label>
                        <div class="rating-stars stars-lg">
                            <input type="radio" name="rating" id="star5" value="5" required checked>
                            <label for="star5" title="Amazing"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star4" value="4">
                            <label for="star4" title="Good"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star3" value="3">
                            <label for="star3" title="Average"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star2" value="2">
                            <label for="star2" title="Not good"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star1" value="1">
                            <label for="star1" title="Terrible"><i class="fas fa-star"></i></label>
                        </div>
                    </div>

                    <!-- Simple Inputs -->
                    <div class="mb-3">
                        <label for="reviewerName" class="form-label fw-bold">Your Name</label>
                        <input type="text" class="form-control bg-light border-0" id="reviewerName" name="name" placeholder="John Doe" required>
                    </div>

                    <div class="mb-3">
                        <label for="reviewText" class="form-label fw-bold">Your Review</label>
                        <textarea class="form-control bg-light border-0" id="reviewText" name="review_text" rows="4" placeholder="Share your experience..." required></textarea>
                    </div>
                    
<!-- Hidden fields for Google Data -->
                    <input type="hidden" name="google_pfp" id="googlePfp">
                    <input type="hidden" name="source" value="website">

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                        <i class="fas fa-paper-plane me-2"></i>Submit Review
                    </button>
                </form>
                
                <!-- Success Message -->
                <div id="reviewSuccessMessage" class="text-center d-none py-4">
                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="fw-bold text-success mb-2">Thank You!</h4>
                    <p class="text-muted">Your review has been submitted successfully.</p>
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4 mt-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Star Rating CSS */
.rating-stars {
    display: inline-flex;
    flex-direction: row-reverse;
    font-size: 1.5rem;
}
.rating-stars input {
    display: none;
}
.rating-stars label {
    color: #ddd;
    cursor: pointer;
    padding: 0 5px;
    transition: color 0.2s;
}
.rating-stars label:hover,
.rating-stars label:hover ~ label,
.rating-stars input:checked ~ label {
    color: #ffc107;
}
.stars-lg {
    font-size: 2.5rem;
}
/* Ensure Google One Tap is above the modal */
body > div[id*="credential_picker_container"] {
    z-index: 99999 !important;
}
</style>

<!-- Google One Tap Library -->
<script src="https://accounts.google.com/gsi/client" async defer></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const desktopForm = document.getElementById('instantReviewForm');
    const mobileForm = document.getElementById('mobileReviewForm');
    const modal = document.getElementById('addReviewModal');
    
    // Google One Tap Configuration
    const googleClientId = '<?php echo defined("GOOGLE_CLIENT_ID") ? GOOGLE_CLIENT_ID : ""; ?>';

    // Function to handle Google Sign-In response
    window.handleCredentialResponse = function(response) {
        const responsePayload = decodeJwtResponse(response.credential);
        
        // Auto-fill Modal Form
        if(document.getElementById('reviewerName')) {
             const nameField = document.getElementById('reviewerName');
             nameField.value = responsePayload.name;
             nameField.style.borderColor = '#198754';
        }
        if(document.getElementById('googlePfp')) {
            document.getElementById('googlePfp').value = responsePayload.picture;
        }

        // Auto-fill Mobile Form
        if(document.getElementById('m_reviewerName')) {
             const nameFieldMobile = document.getElementById('m_reviewerName');
             nameFieldMobile.value = responsePayload.name;
             nameFieldMobile.style.borderColor = '#198754';
        }
        if(document.getElementById('m_googlePfp')) {
            document.getElementById('m_googlePfp').value = responsePayload.picture;
        }
        
        // Hide button containers
        ['googleButtonPlaceholder', 'googleButtonPlaceholderMobile'].forEach(id => {
            const btn = document.getElementById(id);
            if(btn) btn.style.display = 'none';
        });
    }

    // Helper to decode JWT
    function decodeJwtResponse(token) {
        var base64Url = token.split('.')[1];
        var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        var jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
        return JSON.parse(jsonPayload);
    }

    // Initialize Google Button
    function renderGoogleButton(elementId) {
        const container = document.getElementById(elementId);
        if(container && container.innerHTML === '' && google && google.accounts) {
            google.accounts.id.renderButton(
                container,
                { theme: "outline", size: "large", text: "continue_with", shape: "pill", width: "250" }
            );
        }
    }

    // Initialize Global Google Service
    if(googleClientId) {
        const initGoogle = () => {
             google.accounts.id.initialize({
                client_id: googleClientId,
                callback: handleCredentialResponse,
                cancel_on_tap_outside: false,
                context: 'use',
                use_fedcm_for_prompt: false
            });
            // Try One Tap globally
            google.accounts.id.prompt();
            
            // Render Mobile Button Immediately (since it is inline)
            renderGoogleButton('googleButtonPlaceholderMobile');
        };

        // If scrip loaded, init. Else wait.
        if(typeof google !== 'undefined' && google.accounts) {
            initGoogle();
        } else {
            window.onload = initGoogle;
        }
    }

    // Modal Specific Logic (Render desktop button when opened)
    if(modal) {
        modal.addEventListener('shown.bs.modal', function () {
            renderGoogleButton('googleButtonPlaceholder');
        });
    }
    
    // Generic Submit Handler
    function handleFormSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const originalBtnText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        
        const formData = new FormData(form);
        
        fetch('api/save-review-local.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Determine which success message to show
                if(form.id === 'mobileReviewForm') {
                    form.classList.add('d-none');
                    document.getElementById('mobileReviewSuccess').classList.remove('d-none');
                    document.getElementById('mobileReviewSuccess').classList.add('d-block');
                } else {
                    form.classList.add('d-none');
                    document.getElementById('reviewSuccessMessage').classList.remove('d-none');
                }

                // Wait 2.5 seconds then seamlessly insert new review
                setTimeout(() => {
                    // Close Modals / Reset Forms
                    const bootstrapModal = bootstrap.Modal.getInstance(document.getElementById('addReviewModal'));
                    if(bootstrapModal) bootstrapModal.hide();
                    
                    if(form.id === 'mobileReviewForm') {
                         document.getElementById('mobileReviewSuccess').classList.add('d-none');
                         document.getElementById('mobileReviewSuccess').classList.remove('d-block');
                         form.classList.remove('d-none'); // Reset visibility for next time
                    } else {
                         document.getElementById('reviewSuccessMessage').classList.add('d-none');
                         form.classList.remove('d-none'); // Reset visibility
                    }
                    form.reset();
                    const btn = form.querySelector('button[type="submit"]');
                    btn.disabled = false;
                    btn.innerHTML = originalBtnText;

                    // Inject New Review into DOM if on reviews page
                    const container = document.getElementById('reviewsContainer');
                    if(container && data.html) {
                        // Check if we need to remove the "No reviews" placeholder
                        const noReviewsMsg = document.getElementById('noReviewsMessage');
                        if(noReviewsMsg) {
                            noReviewsMsg.remove();
                        }

                        // Create a temp div to parse the string
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.html;
                        const newReviewNode = tempDiv.firstElementChild;
                        
                        // Insert at top (PREPEND)
                        // If there are existing children, put before first. Else append.
                        if(container.firstChild) {
                            container.insertBefore(newReviewNode, container.firstChild);
                        } else {
                            container.appendChild(newReviewNode);
                        }
                        
                        // Trigger Flow/Fade In
                        setTimeout(() => {
                            newReviewNode.style.opacity = '1';
                        }, 100);
                        
                        // Smooth Scroll to it
                        newReviewNode.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } else if(data.html && !container) {
                         // If we are on Home or Tours page, we might just want to alert or do nothing
                         // For now, no redirect needed, just stays there.
                    }
                    
                }, 2500);

            } else {
                alert('Error: ' + data.message);
                btn.disabled = false;
                btn.innerHTML = originalBtnText;
            }
        })
        .catch(err => {
            alert('An error occurred. Please try again.');
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = originalBtnText;
        });
    }

    // Attach Listeners
    if(desktopForm) desktopForm.addEventListener('submit', handleFormSubmit);
    if(mobileForm) mobileForm.addEventListener('submit', handleFormSubmit);
});
</script>
