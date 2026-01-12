<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

$user = $_SESSION['admin_user'];
$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$category = [];
$errors = [];
$success = false;

$pageTitle = $categoryId ? 'Edit Category' : 'Add New Category';
$activePage = 'categories';

// Load category data if editing
if ($categoryId) {
    $category = getCategoryById($categoryId);
    if (!$category) {
        $_SESSION['error_message'] = "Category not found.";
        header('Location: categories.php');
        exit;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $category = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'icon' => trim($_POST['icon'] ?? ''),
        'image' => trim($_POST['image'] ?? ''),
        'color' => trim($_POST['color'] ?? 'primary'),
        'display_order' => (int)($_POST['display_order'] ?? 0),
        'active' => isset($_POST['active']) ? 1 : 0
    ];
    
    // Validate form data
    if (empty($category['name'])) {
        $errors[] = 'Category name is required';
    }
    
    if (empty($category['description'])) {
        $errors[] = 'Description is required';
    }
    
    if (empty($category['icon'])) {
        $errors[] = 'Icon is required';
    }
    
    // If no errors, save the category
    if (empty($errors)) {
        $result = saveCategory($category, $categoryId);
        
        if ($result) {
            $success = true;
            $categoryId = $result;
            $_SESSION['success_message'] = 'Category saved successfully!';
            header('Location: categories.php');
            exit;
        } else {
            $errors[] = 'Failed to save category. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<!-- Category Form Card -->
<div class="card bg-base-100 shadow">
    <div class="card-body">
        <!-- Form Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <h2 class="card-title text-gray-700">
                <i class="fas fa-th-large text-primary mr-2"></i> 
                <?php echo $categoryId ? 'Edit Category' : 'Add New Category'; ?>
            </h2>
            <a href="categories.php" class="btn btn-outline btn-sm mt-2 md:mt-0">
                <i class="fas fa-arrow-left mr-1"></i> Back to Categories
            </a>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <div>
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Category Form -->
        <form method="post" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-4">
                    <!-- Category Name -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Category Name *</span>
                        </label>
                        <input type="text" class="input input-bordered w-full" name="name" 
                               value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>" required>
                    </div>
                    
                    <!-- Slug -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Slug</span>
                        </label>
                        <input type="text" class="input input-bordered w-full" id="slug" name="slug" 
                               value="<?php echo htmlspecialchars($category['slug'] ?? ''); ?>">
                        <label class="label">
                            <span class="label-text-alt">Leave empty to auto-generate from name</span>
                        </label>
                    </div>
                    
                    <!-- Description -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Description *</span>
                        </label>
                        <textarea class="textarea textarea-bordered h-24" name="description" 
                                  required><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Image URL -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Image URL</span>
                        </label>
                        <input type="text" class="input input-bordered w-full" name="image" 
                               value="<?php echo htmlspecialchars($category['image'] ?? ''); ?>">
                        <label class="label">
                            <span class="label-text-alt">Enter a URL or path to the category image</span>
                        </label>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="space-y-4">
                    <!-- Icon -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Font Awesome Icon *</span>
                        </label>
                        <input type="text" class="input input-bordered w-full" name="icon" 
                               value="<?php echo htmlspecialchars($category['icon'] ?? ''); ?>" 
                               placeholder="fa-hiking" required>
                        <label class="label">
                            <span class="label-text-alt">
                                E.g., "fa-hiking", "fa-users", "fa-heart". 
                                <a href="https://fontawesome.com/icons" target="_blank" class="link link-primary">Browse icons</a>
                            </span>
                        </label>
                    </div>
                    
                    <!-- Color -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Badge Color</span>
                        </label>
                        <select class="select select-bordered w-full" name="color">
                            <option value="primary" <?php echo (isset($category['color']) && $category['color'] === 'primary') ? 'selected' : ''; ?>>Primary (Blue)</option>
                            <option value="success" <?php echo (isset($category['color']) && $category['color'] === 'success') ? 'selected' : ''; ?>>Success (Green)</option>
                            <option value="danger" <?php echo (isset($category['color']) && $category['color'] === 'danger') ? 'selected' : ''; ?>>Danger (Red)</option>
                            <option value="warning" <?php echo (isset($category['color']) && $category['color'] === 'warning') ? 'selected' : ''; ?>>Warning (Yellow)</option>
                            <option value="info" <?php echo (isset($category['color']) && $category['color'] === 'info') ? 'selected' : ''; ?>>Info (Cyan)</option>
                        </select>
                    </div>
                    
                    <!-- Display Order -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Display Order</span>
                        </label>
                        <input type="number" class="input input-bordered w-full" name="display_order" 
                               value="<?php echo htmlspecialchars($category['display_order'] ?? 0); ?>" min="0">
                        <label class="label">
                            <span class="label-text-alt">Lower numbers appear first</span>
                        </label>
                    </div>
                    
                    <!-- Active Status -->
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start">
                            <input type="checkbox" class="checkbox checkbox-primary mr-2" name="active" 
                                   <?php echo (!isset($category['active']) || $category['active']) ? 'checked' : ''; ?>>
                            <span class="label-text">Active</span>
                        </label>
                    </div>
                    
                    <!-- Preview -->
                    <?php if (!empty($category['image'])): ?>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Current Image</span>
                            </label>
                            <div class="avatar">
                                <div class="w-32 rounded">
                                    <img src="../<?php echo htmlspecialchars($category['image']); ?>" 
                                         alt="Category Image">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Form Buttons -->
            <div class="flex justify-end mt-8 gap-2">
                <a href="categories.php" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Auto-generate slug from name -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.querySelector('input[name="name"]');
    const slugInput = document.getElementById('slug');
    
    nameInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.dataset.autoGenerated) {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            slugInput.value = slug;
            slugInput.dataset.autoGenerated = 'true';
        }
    });
    
    slugInput.addEventListener('input', function() {
        if (this.value) {
            delete this.dataset.autoGenerated;
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
