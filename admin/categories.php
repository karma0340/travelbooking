<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

$user = $_SESSION['admin_user'];
$pageTitle = 'Tour Categories';
$activePage = 'categories';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $categoryId = (int)$_GET['id'];
    
    // Check if category has tours
    $tourCount = getCategoryTourCount($categoryId);
    if ($tourCount > 0) {
        $_SESSION['error_message'] = "Cannot delete category with $tourCount active tours. Please reassign or delete the tours first.";
    } else {
        if (deleteCategory($categoryId)) {
            $_SESSION['success_message'] = "Category deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete category.";
        }
    }
    header('Location: categories.php');
    exit;
}

// Get all categories
$categories = getCategories(false); // Get all including inactive

include 'includes/header.php';
?>

<!-- Categories List -->
<div class="card bg-base-100 shadow">
    <div class="card-body">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <h2 class="card-title text-gray-700">
                <i class="fas fa-th-large text-primary mr-2"></i> Tour Categories
            </h2>
            <a href="category-edit.php" class="btn btn-primary mt-2 md:mt-0">
                <i class="fas fa-plus mr-2"></i> Add New Category
            </a>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success mb-4">
                <i class="fas fa-check-circle mr-2"></i>
                <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php 
                echo $_SESSION['error_message']; 
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Categories Table -->
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Icon</th>
                        <th>Description</th>
                        <th>Tours</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                <p>No categories found. Create your first category!</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $category): 
                            $tourCount = getCategoryTourCount($category['id']);
                        ?>
                            <tr>
                                <td class="font-bold"><?php echo $category['display_order']; ?></td>
                                <td>
                                    <?php if (!empty($category['image'])): ?>
                                        <div class="avatar">
                                            <div class="w-12 h-12 rounded">
                                                <img src="../<?php echo htmlspecialchars($category['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($category['name']); ?>">
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-12 h-12 rounded bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="font-bold"><?php echo htmlspecialchars($category['name']); ?></div>
                                    <div class="text-sm opacity-50"><?php echo htmlspecialchars($category['slug']); ?></div>
                                </td>
                                <td>
                                    <div class="badge badge-<?php echo htmlspecialchars($category['color']); ?> badge-lg">
                                        <i class="fas <?php echo htmlspecialchars($category['icon']); ?> mr-1"></i>
                                    </div>
                                </td>
                                <td>
                                    <div class="max-w-xs truncate">
                                        <?php echo htmlspecialchars($category['description']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-outline"><?php echo $tourCount; ?> tours</span>
                                </td>
                                <td>
                                    <?php if ($category['active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="category-edit.php?id=<?php echo $category['id']; ?>" 
                                           class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', <?php echo $tourCount; ?>)" 
                                                class="btn btn-sm btn-error" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <?php if (!empty($categories)): ?>
            <div class="mt-4 text-sm text-gray-600">
                Total Categories: <strong><?php echo count($categories); ?></strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<dialog id="deleteModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Confirm Delete</h3>
        <p class="py-4" id="deleteMessage"></p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn">Cancel</button>
            </form>
            <a href="#" id="confirmDeleteBtn" class="btn btn-error">Delete</a>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
function confirmDelete(id, name, tourCount) {
    const modal = document.getElementById('deleteModal');
    const message = document.getElementById('deleteMessage');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    
    if (tourCount > 0) {
        message.innerHTML = `<strong>${name}</strong> has <strong>${tourCount} active tours</strong>. You must reassign or delete these tours before deleting this category.`;
        confirmBtn.classList.add('btn-disabled');
        confirmBtn.href = '#';
    } else {
        message.innerHTML = `Are you sure you want to delete the category <strong>${name}</strong>? This action cannot be undone.`;
        confirmBtn.classList.remove('btn-disabled');
        confirmBtn.href = `categories.php?action=delete&id=${id}`;
    }
    
    modal.showModal();
}
</script>

<?php include 'includes/footer.php'; ?>
