<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

$user = $_SESSION['admin_user'];
$pageTitle = 'Manage Reviews';
$activePage = 'reviews';
$hideDefaultHeader = true;

// Message handling
$message = '';
$messageType = '';

// Handle status updates
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'];
    if (in_array($status, ['approved', 'pending', 'rejected'])) {
        if (updateGeneralReviewStatus($id, $status)) {
            $message = "Review status updated to " . ucfirst($status);
            $messageType = "success";
        }
    }
}

// Handle featured toggle
if (isset($_GET['toggle_featured']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if (toggleReviewFeatured($id)) {
        $message = "Featured status updated.";
        $messageType = "success";
    }
}

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    if (deleteReview($id)) {
        $message = "Review deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Failed to delete review.";
        $messageType = "error";
    }
}

$reviews = getAllReviews();
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-3">
            <span class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white p-2.5 rounded-xl">
                <i class="fas fa-star"></i>
            </span>
            Manage Reviews
        </h1>
        <p class="text-gray-500 text-sm mt-1">Approve, reject or feature customer testimonials</p>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?> mb-4">
    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <span><?= $message ?></span>
</div>
<?php endif; ?>

<div class="admin-card">
    <div class="overflow-x-auto">
        <?php if (empty($reviews)): ?>
        <div class="admin-empty-state">
            <div class="icon">
                <i class="fas fa-comment-slash"></i>
            </div>
            <h3>No reviews found</h3>
            <p>Customer reviews will appear here when submitted.</p>
        </div>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Review</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <?php if (!empty($review['google_picture'])): ?>
                                <img src="<?= htmlspecialchars($review['google_picture']) ?>" alt="<?= htmlspecialchars($review['name']) ?>" class="w-10 h-10 rounded-full">
                            <?php elseif (!empty($review['image_path'])): ?>
                                <img src="<?= htmlspecialchars($review['image_path']) ?>" alt="<?= htmlspecialchars($review['name']) ?>" class="w-10 h-10 rounded-full">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                    <?= strtoupper(substr($review['name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <div class="font-semibold text-gray-800 flex items-center gap-2">
                                    <?= htmlspecialchars($review['name']) ?>
                                    <?php if (!empty($review['google_id'])): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800" title="Authenticated via Google">
                                            <i class="fab fa-google mr-1"></i> Verified
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-xs text-gray-500"><?= htmlspecialchars($review['location']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="max-w-xs truncate text-sm text-gray-600" title="<?= htmlspecialchars($review['review_text']) ?>">
                            <?= htmlspecialchars($review['review_text']) ?>
                        </div>
                        <div class="text-[10px] text-gray-400 mt-1">
                            <?= ($review['created_at'] && strtotime($review['created_at']) > 0) ? date('M d, Y', strtotime($review['created_at'])) : 'N/A' ?>
                        </div>
                    </td>
                    <td>
                        <div class="text-yellow-500 text-sm">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $review['rating'] ? '' : 'text-gray-200' ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($review['status'] === 'approved'): ?>
                        <span class="admin-badge admin-badge-success">Approved</span>
                        <?php elseif ($review['status'] === 'rejected'): ?>
                        <span class="admin-badge admin-badge-error">Rejected</span>
                        <?php else: ?>
                        <span class="admin-badge admin-badge-warning">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="reviews.php?toggle_featured=1&id=<?= $review['id'] ?>" class="text-xl hover:scale-110 transition-transform inline-block">
                            <i class="fas fa-heart <?= $review['is_featured'] ? 'text-red-500' : 'text-gray-200' ?>"></i>
                        </a>
                    </td>
                    <td>
                        <div class="admin-actions gap-2">
                            <?php if ($review['status'] !== 'approved'): ?>
                            <a href="reviews.php?status=approved&id=<?= $review['id'] ?>" 
                               class="admin-action-btn view tooltip hover:bg-indigo-50" title="Approve">
                                <i class="fas fa-check text-lg"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($review['status'] !== 'rejected'): ?>
                            <a href="reviews.php?status=rejected&id=<?= $review['id'] ?>" 
                               class="admin-action-btn edit tooltip hover:bg-yellow-50" title="Reject">
                                <i class="fas fa-times text-lg"></i>
                            </a>
                            <?php endif; ?>

                            <a href="reviews.php?delete=<?= $review['id'] ?>" 
                               class="admin-action-btn delete tooltip hover:bg-red-50" 
                               title="Delete"
                               onclick="return confirm('Are you sure you want to delete this review? This action cannot be undone.');">
                                <i class="fas fa-trash text-lg"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
// Initialize tooltips if needed, though mostly handled by CSS title/data-tip
</script>

<?php include 'includes/footer.php'; ?>
