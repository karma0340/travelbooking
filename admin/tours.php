<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

$user = $_SESSION['admin_user'];
$pageTitle = 'Manage Tours';
$activePage = 'tours';
$hideDefaultHeader = true;

// Message handling
$message = '';
$messageType = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $tourId = $_GET['delete'];
    if (deleteTour($tourId)) {
        $message = "Tour deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Failed to delete tour.";
        $messageType = "error";
    }
}

$tours = getTours();
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-3">
            <span class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white p-2.5 rounded-xl">
                <i class="fas fa-map-marked-alt"></i>
            </span>
            Manage Tours
        </h1>
        <p class="text-gray-500 text-sm mt-1">Create, edit and delete tour packages</p>
    </div>
    <a href="tour-edit.php" class="admin-btn admin-btn-primary">
        <i class="fas fa-plus"></i>
        <span>New Tour</span>
    </a>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?> mb-4">
    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <span><?= $message ?></span>
</div>
<?php endif; ?>

<!-- Tours Table Card -->
<div class="admin-card">
    <div class="overflow-x-auto">
        <?php if (empty($tours)): ?>
        <div class="admin-empty-state">
            <div class="icon">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <h3>No tours found</h3>
            <p>Get started by creating your first tour package</p>
            <a href="tour-edit.php" class="admin-btn admin-btn-primary">
                <i class="fas fa-plus"></i>
                <span>Create New Tour</span>
            </a>
        </div>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 60px;">Image</th>
                    <th>Title</th>
                    <th>Location</th>
                    <th>Duration</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tours as $tour): ?>
                <tr>
                    <td>
                        <div class="w-10 h-10 rounded-lg bg-cover bg-center border border-gray-200 shadow-sm" 
                             style="background-image: url('<?= !empty($tour['image']) ? htmlspecialchars($tour['image']) : '../images/tour-placeholder.jpg' ?>');"></div>
                    </td>
                    <td>
                        <span class="font-semibold text-gray-800"><?= htmlspecialchars($tour['title']) ?></span>
                    </td>
                    <td>
                        <span class="flex items-center gap-1.5 text-gray-600">
                            <i class="fas fa-map-marker-alt text-gray-400"></i>
                            <?= htmlspecialchars($tour['location']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="flex items-center gap-1.5 text-gray-600">
                            <i class="fas fa-clock text-gray-400"></i>
                            <?= htmlspecialchars($tour['duration']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="font-semibold text-gray-800">₹<?= number_format($tour['price']) ?></span>
                    </td>
                    <td>
                        <?php if ($tour['active']): ?>
                        <span class="admin-badge admin-badge-success">
                            <i class="fas fa-check-circle"></i> Active
                        </span>
                        <?php else: ?>
                        <span class="admin-badge admin-badge-neutral">
                            <i class="fas fa-pause-circle"></i> Inactive
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="admin-actions">
                            <a href="tour-edit.php?id=<?= $tour['id'] ?>" 
                               class="admin-action-btn edit tooltip" data-tip="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="tour-itinerary.php?tour_id=<?= $tour['id'] ?>" 
                               class="admin-action-btn view tooltip" data-tip="Itinerary">
                                <i class="fas fa-route"></i>
                            </a>
                            <button class="admin-action-btn delete delete-tour tooltip" 
                                    data-id="<?= $tour['id'] ?>" data-tip="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Modal -->
<dialog id="deleteModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Confirm Delete</h3>
        <p class="py-4">Are you sure you want to delete this tour? This action cannot be undone.</p>
        <div class="modal-action">
            <button class="btn btn-ghost" onclick="deleteModal.close()">Cancel</button>
            <a href="#" class="btn btn-error" id="confirmDelete">Delete</a>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-tour');
    const deleteModal = document.getElementById('deleteModal');
    const confirmDelete = document.getElementById('confirmDelete');
    
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            confirmDelete.href = `tours.php?delete=${btn.dataset.id}`;
            deleteModal.showModal();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>