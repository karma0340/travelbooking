<?php
require_once 'includes/db.php';
require_once 'includes/security.php'; // Required for CSRF token in modal
require_once 'includes/review-functions.php';

// Sync Google Reviews (Simple throttling: once per hour per session to avoid API limits)
if (!isset($_SESSION['last_review_sync']) || time() - $_SESSION['last_review_sync'] > 3600) {
    require_once 'includes/sync-reviews.php';
    syncGoogleReviews(); // Fire and forget
    $_SESSION['last_review_sync'] = time();
}

// Fetch all approved reviews
$reviews = getReviews(100, false); // Fetch up to 100 reviews for now
$stats = getReviewStats();

$pageTitle = "Customer Reviews - Travel In Peace";
$activePage = "reviews";

// Set security headers
try {
    setSecurityHeaders();
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center justify-content-center" style="min-height: 40vh;">
    <div class="hero-overlay"></div>
    <div class="container text-center text-white" style="position: relative; z-index: 10;">
        <h1 class="display-4 fw-bold mb-3">Customer Reviews</h1>
        <p class="lead mb-0">What our travelers say about their experiences with us</p>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        
        <!-- Stats Summary -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-8 text-center">
                <div class="card border-0 shadow-sm p-4">
                    <h2 class="h4 mb-3">Overall Rating</h2>
                    <div class="display-3 fw-bold text-primary mb-2"><?php echo number_format($stats['average'] ?: 5.0, 1); ?>/5</div>
                    <div class="text-warning fs-3 mb-2">
                        <?php
                        $rating = $stats['average'] ?: 5;
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $rating) {
                                echo '<i class="fas fa-star"></i>';
                            } elseif ($i - 0.5 <= $rating) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                    <p class="text-muted mb-4">Based on <?php echo $stats['total']; ?> verified reviews</p>
                    <?php 
                    // Ensure config is loaded
                    require_once 'includes/google-config.php';
                    ?>
                    
                    <!-- Desktop Button (Hidden on Mobile) -->
                    <button class="btn btn-primary rounded-pill px-4 shadow-sm d-none d-md-inline-block" data-bs-toggle="modal" data-bs-target="#addReviewModal">
                        <i class="fas fa-pen me-2"></i>Write a Review
                    </button>
                    
                    <!-- Mobile Embedded Form (Visible only on Mobile) -->
                    <div class="d-block d-md-none mt-4 text-start bg-white rounded-3">
                        <hr class="my-4">
                        <h5 class="fw-bold mb-3 text-center">Write a Review</h5>
                        <form id="mobileReviewForm" class="needs-validation" novalidate>
                            <!-- Google Sign-In Button (Mobile) -->
                            <div id="googleButtonPlaceholderMobile" class="mb-3 d-flex justify-content-center"></div>

                            <div class="text-center mb-3">
                                <div class="rating-stars stars-lg justify-content-center d-flex flex-row-reverse">
                                    <input type="radio" name="rating" id="m_star5" value="5" required checked>
                                    <label for="m_star5"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="m_star4" value="4">
                                    <label for="m_star4"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="m_star3" value="3">
                                    <label for="m_star3"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="m_star2" value="2">
                                    <label for="m_star2"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="m_star1" value="1">
                                    <label for="m_star1"><i class="fas fa-star"></i></label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <input type="text" class="form-control bg-light border-0" id="m_reviewerName" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control bg-light border-0" name="review_text" rows="3" placeholder="Share your experience..." required></textarea>
                            </div>
                            <input type="hidden" name="google_pfp" id="m_googlePfp">
                            <input type="hidden" name="source" value="website">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm">Submit Review</button>
                        </form>
                         <!-- Success Message Mobile -->
                        <div id="mobileReviewSuccess" class="text-center d-none py-3">
                            <i class="fas fa-check-circle text-success fs-1 mb-2"></i>
                            <p class="text-success fw-bold">Review Submitted!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Grid -->
        <div class="row g-4" id="reviewsContainer">
            <?php if (empty($reviews)): ?>
                <div id="noReviewsMessage" class="col-12 text-center py-5">
                    <div class="text-muted mb-3"><i class="fas fa-comments fa-4x opacity-25"></i></div>
                    <h3>No reviews yet</h3>
                    <p class="text-muted">Be the first to share your experience with us!</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="testimonial-avatar me-3">
                                        <?php 
                                            $avatarUrl = !empty($review['google_picture']) ? $review['google_picture'] : $review['image_path'];
                                            // Specific check for broken absolute/relative paths
                                            if ($avatarUrl === 'assets/images/default-avatar.png') $avatarUrl = '';
                                        ?>
                                        
                                        <?php if (!empty($avatarUrl)): ?>
                                            <img src="<?php echo htmlspecialchars($avatarUrl); ?>" 
                                                 alt="<?php echo htmlspecialchars($review['name']); ?>" 
                                                 class="rounded-circle avatar-img" 
                                                 style="width: 50px; height: 50px; object-fit: cover; display: block;"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="avatar-initials bg-primary text-white rounded-circle align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px; font-size: 1.2rem; display: none;">
                                                <?php echo strtoupper(substr($review['name'], 0, 1)); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="avatar-initials bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px; font-size: 1.2rem;">
                                                <?php echo strtoupper(substr($review['name'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                                            <?php echo htmlspecialchars($review['name']); ?>
                                            <?php if (!empty($review['google_id'])): ?>
                                                <span class="badge bg-primary-subtle text-primary" style="font-size: 0.7rem;" title="Verified via Google">
                                                    <i class="fab fa-google"></i>
                                                </span>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($review['location']); ?></small>
                                    </div>
                                </div>
                                <?php if ($review['is_featured']): ?>
                                <span class="badge bg-primary-subtle text-primary rounded-pill"><i class="fas fa-heart me-1"></i> Featured</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3 text-warning">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? '' : 'text-muted opacity-25'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            
                            <p class="mb-0 text-muted review-text">
                                <i class="fas fa-quote-left me-2 opacity-50"></i>
                                <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                <i class="fas fa-quote-right ms-2 opacity-50"></i>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Pagination (Hidden if fewer than 100 reviews) -->
        <?php if ($stats['total'] > 100): ?>
        <div class="text-center mt-5">
            <button class="btn btn-outline-primary rounded-pill px-4">Load More Reviews</button>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Review Modal -->
<?php require_once 'includes/simple-review-modal.php'; ?>

<?php
include 'includes/footer.php';
?>
<!-- Force Bootstrap Load for this page -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
