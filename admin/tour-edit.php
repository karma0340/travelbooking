<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

// Get user data
$user = $_SESSION['admin_user'];

// Initialize variables
$tourId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$tour = [];
$errors = [];
$success = false;

// Page title and active menu item
$pageTitle = $tourId ? 'Edit Tour' : 'Add New Tour';
$activePage = 'tours';

// Load tour data if editing
if ($tourId) {
    try {
        $tour = getTourById($tourId);
        if (!$tour) {
            $_SESSION['error_message'] = "Tour not found. It may have been deleted.";
            header('Location: tours.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error loading tour: " . $e->getMessage();
        header('Location: tours.php');
        exit;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $tour = [
        'title' => trim($_POST['title'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'duration' => trim($_POST['duration'] ?? ''),
        'duration_days' => (int)($_POST['duration_days'] ?? 3),
        'price' => floatval($_POST['price'] ?? 0),
        'location' => trim($_POST['location'] ?? ''),
        'category' => trim($_POST['category'] ?? 'general'),
        'image' => trim($_POST['image'] ?? ''),
        'features' => $_POST['features'] ? explode(',', $_POST['features']) : [],
        'rating' => floatval($_POST['rating'] ?? 4.5),
        'badge' => trim($_POST['badge'] ?? ''),
        'active' => isset($_POST['active']) ? 1 : 0
    ];
    
    // Validate form data
    if (empty($tour['title'])) {
        $errors[] = 'Title is required';
    }
    
    if (empty($tour['description'])) {
        $errors[] = 'Description is required';
    }
    
    if (empty($tour['location'])) {
        $errors[] = 'Location is required';
    }
    
    // Price validation - only if provided
    if (!empty($tour['price']) && (!is_numeric($tour['price']) || $tour['price'] < 0)) {
        $errors[] = 'Price must be a positive number';
    }
    
    // If no errors, save the tour
    if (empty($errors)) {
        $result = saveTour($tour, $tourId);
        
        if ($result) {
            $success = true;
            $tourId = $result;
            
            // Redirect to tour list after successful save
            header('Location: tours.php?success=1');
            exit;
        } else {
            $errors[] = 'Failed to save tour. Please try again.';
        }
    }
}

// Set error and success messages
if (!empty($errors)) {
    $errorMessage = implode('<br>', $errors);
}

if ($success) {
    $successMessage = 'Tour has been saved successfully!';
}

// Include header
include 'includes/header.php';
?>

<!-- Tour Form Card -->
<div class="card bg-base-100 shadow">
    <div class="card-body">
        <!-- Form Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <h2 class="card-title text-gray-700">
                <i class="fas fa-map-marked-alt text-primary mr-2"></i> <?php echo $tourId ? 'Edit Tour' : 'Add New Tour'; ?>
            </h2>
            <a href="tours.php" class="btn btn-outline btn-sm mt-2 md:mt-0">
                <i class="fas fa-arrow-left mr-1"></i> Back to Tours
            </a>
        </div>
        
        <!-- Tour Form -->
        <form method="post" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-4">
                    <!-- Tour Title -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Tour Title *</span>
                        </label>
                        <input type="text" class="input input-bordered w-full" id="title" name="title" 
                               value="<?php echo htmlspecialchars($tour['title'] ?? ''); ?>" required>
                    </div>
                    
                    <!-- Slug -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Slug</span>
                        </label>
                        <input type="text" class="input input-bordered w-full" id="slug" name="slug" 
                               value="<?php echo htmlspecialchars($tour['slug'] ?? ''); ?>">
                        <label class="label">
                            <span class="label-text-alt">Leave empty to auto-generate from title</span>
                        </label>
                    </div>
                    
                    <!-- Description -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Description *</span>
                        </label>
                        <textarea class="textarea textarea-bordered h-32" id="description" name="description" 
                                  required><?php echo htmlspecialchars($tour['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Image URL -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Image URL</span>
                        </label>
                        <input type="text" class="input input-bordered w-full" id="image" name="image" 
                               value="<?php echo htmlspecialchars($tour['image'] ?? ''); ?>">
                        <label class="label">
                            <span class="label-text-alt">Enter a URL for the tour image</span>
                        </label>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Duration -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Duration</span>
                            </label>
                            <input type="text" class="input input-bordered w-full" id="duration" name="duration" 
                                   value="<?php echo htmlspecialchars($tour['duration'] ?? '3 Days / 2 Nights'); ?>">
                            <label class="label">
                                <span class="label-text-alt">E.g., "3 Days / 2 Nights"</span>
                            </label>
                        </div>
                        
                        <!-- Duration Days -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Duration in Days</span>
                            </label>
                            <input type="number" class="input input-bordered w-full" id="duration_days" name="duration_days" 
                                   value="<?php echo htmlspecialchars($tour['duration_days'] ?? 3); ?>" min="1">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Price -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Price (₹)</span>
                            </label>
                            <input type="number" class="input input-bordered w-full" id="price" name="price" 
                                   value="<?php echo htmlspecialchars($tour['price'] ?? ''); ?>" step="0.01" min="0">
                        </div>
                        
                        <!-- Rating -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Rating</span>
                            </label>
                            <input type="number" class="input input-bordered w-full" id="rating" name="rating" 
                                   value="<?php echo htmlspecialchars($tour['rating'] ?? 4.5); ?>" step="0.1" min="1" max="5">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Location -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Location *</span>
                            </label>
                            <input type="text" class="input input-bordered w-full" id="location" name="location" 
                                   value="<?php echo htmlspecialchars($tour['location'] ?? ''); ?>" required>
                        </div>
                        
                        <!-- Category -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Category</span>
                            </label>
                            <select class="select select-bordered w-full" id="category" name="category">
                                <option value="general" <?php echo (isset($tour['category']) && $tour['category'] === 'general') ? 'selected' : ''; ?>>General</option>
                                <option value="adventure" <?php echo (isset($tour['category']) && $tour['category'] === 'adventure') ? 'selected' : ''; ?>>Adventure</option>
                                <option value="family" <?php echo (isset($tour['category']) && $tour['category'] === 'family') ? 'selected' : ''; ?>>Family</option>
                                <option value="honeymoon" <?php echo (isset($tour['category']) && $tour['category'] === 'honeymoon') ? 'selected' : ''; ?>>Honeymoon</option>
                                <option value="luxury" <?php echo (isset($tour['category']) && $tour['category'] === 'luxury') ? 'selected' : ''; ?>>Luxury</option>
                                <option value="budget" <?php echo (isset($tour['category']) && $tour['category'] === 'budget') ? 'selected' : ''; ?>>Budget</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Badge -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Badge</span>
                        </label>
                        <input type="text" class="input input-bordered w-full" id="badge" name="badge" 
                               value="<?php echo htmlspecialchars($tour['badge'] ?? ''); ?>">
                        <label class="label">
                            <span class="label-text-alt">E.g., "Popular", "New", "Best Seller"</span>
                        </label>
                    </div>
                    
                    <!-- Active Status -->
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start">
                            <input type="checkbox" class="checkbox checkbox-primary mr-2" id="active" name="active" 
                                   <?php echo (!isset($tour['active']) || $tour['active']) ? 'checked' : ''; ?>>
                            <span class="label-text">Active</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Features Section -->
            <div class="form-control mt-6">
                <label class="label">
                    <span class="label-text font-medium">Features</span>
                </label>
                <div class="flex gap-2 mb-2">
                    <input type="text" class="input input-bordered flex-1" id="feature-input" placeholder="Add a feature">
                    <button type="button" class="btn" id="add-feature">Add</button>
                </div>
                <div id="features-container" class="flex flex-wrap gap-2 mb-4">
                    <?php if (isset($tour['features']) && is_array($tour['features'])): ?>
                        <?php foreach ($tour['features'] as $feature): ?>
                            <div class="badge badge-lg bg-base-200 gap-1">
                                <?php echo htmlspecialchars($feature); ?>
                                <button type="button" class="remove-feature btn btn-xs btn-circle btn-ghost">×</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="features" id="features" value="<?php echo isset($tour['features']) ? htmlspecialchars(implode(',', $tour['features'])) : ''; ?>">
            </div>
            
            <!-- Form Buttons -->
            <div class="flex justify-end mt-8 gap-2">
                <a href="tours.php" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> Save Tour
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Custom JavaScript for Features Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from title
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    
    titleInput.addEventListener('input', function() {
        if (!slugInput.value) {
            slugInput.value = this.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
        }
    });

    // Features functionality
    const featureInput = document.getElementById('feature-input');
    const featuresContainer = document.getElementById('features-container');
    const featuresHiddenInput = document.getElementById('features');
    const addFeatureButton = document.getElementById('add-feature');
    
    // Add feature
    function addFeature() {
        const feature = featureInput.value.trim();
        if (!feature) return;
        
        // Create feature tag
        const featureTag = document.createElement('div');
        featureTag.className = 'badge badge-lg bg-base-200 gap-1';
        featureTag.innerHTML = `
            ${feature}
            <button type="button" class="remove-feature btn btn-xs btn-circle btn-ghost">×</button>
        `;
        
        featuresContainer.appendChild(featureTag);
        featureInput.value = '';
        
        // Update hidden input
        updateFeaturesHiddenInput();
    }
    
    // Add feature on button click
    addFeatureButton.addEventListener('click', addFeature);
    
    // Add feature on Enter key
    featureInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addFeature();
        }
    });
    
    // Remove feature
    featuresContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-feature')) {
            e.target.parentElement.remove();
            updateFeaturesHiddenInput();
        }
    });
    
    // Update hidden input with features
    function updateFeaturesHiddenInput() {
        const features = [];
        featuresContainer.querySelectorAll('.badge').forEach(tag => {
            // Get text content excluding the × button
            let featureText = tag.textContent.trim();
            featureText = featureText.replace('×', '').trim();
            features.push(featureText);
        });
        
        featuresHiddenInput.value = features.join(',');
    }
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
