<?php
session_start();
require_once 'check-login.php';
require_once '../includes/db.php';

// Page title
$pageTitle = 'Edit Vehicle';
$activePage = 'vehicles';

// Initialize variables
$error = '';
$success = '';
$vehicleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$vehicle = [
    'id' => 0,
    'name' => '',
    'slug' => '',
    'description' => '',
    'seats' => 4,
    'bags' => 2,
    'price_per_day' => 1000,
    'image' => '',
    'features' => [],
    'active' => 1
];

// If editing existing vehicle, get data
if ($vehicleId) {
    $vehicleData = getVehicleById($vehicleId);
    if ($vehicleData) {
        $vehicle = $vehicleData;
    } else {
        $error = "Vehicle not found!";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $vehicle['name'] = $_POST['name'] ?? '';
    $vehicle['slug'] = $_POST['slug'] ?? '';
    $vehicle['description'] = $_POST['description'] ?? '';
    $vehicle['seats'] = (int)($_POST['seats'] ?? 4);
    $vehicle['bags'] = (int)($_POST['bags'] ?? 2);
    $vehicle['price_per_day'] = (float)($_POST['price_per_day'] ?? 1000);
    $vehicle['image'] = $_POST['image'] ?? '';
    $vehicle['features'] = isset($_POST['features']) ? explode("\n", $_POST['features']) : [];
    $vehicle['active'] = isset($_POST['active']) ? 1 : 0;

    // Validate required fields
    if (empty($vehicle['name'])) {
        $error = "Vehicle name is required!";
    } else {
        // Generate slug if empty
        if (empty($vehicle['slug'])) {
            $vehicle['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $vehicle['name']));
        }

        // Save vehicle
        $result = saveVehicle($vehicle, $vehicleId);
        if ($result) {
            $success = "Vehicle saved successfully!";
            if (!$vehicleId) {
                // If new vehicle, redirect to edit page with ID
                header("Location: vehicle-edit.php?id={$result}&success=" . urlencode($success));
                exit;
            }
            // Update vehicle ID for existing vehicle
            $vehicleId = $result;
        } else {
            $error = "Error saving vehicle!";
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold"><?php echo $vehicleId ? 'Edit' : 'Add New'; ?> Vehicle</h1>
        <a href="vehicles.php" class="btn btn-ghost btn-sm">
            <i class="fas fa-arrow-left mr-2"></i> Back to Vehicles
        </a>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alert alert-error mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span><?php echo $error; ?></span>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($success) || isset($_GET['success'])): ?>
    <div class="alert alert-success mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span><?php echo $success ?: $_GET['success']; ?></span>
    </div>
    <?php endif; ?>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <form method="post" action="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label" for="name">
                                <span class="label-text">Vehicle Name</span>
                                <span class="label-text-alt text-error">*</span>
                            </label>
                            <input type="text" class="input input-bordered w-full" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($vehicle['name']); ?>" required>
                        </div>
                        
                        <div class="form-control">
                            <label class="label" for="slug">
                                <span class="label-text">URL Slug</span>
                            </label>
                            <input type="text" class="input input-bordered w-full" id="slug" name="slug" 
                                   value="<?php echo htmlspecialchars($vehicle['slug']); ?>" 
                                   placeholder="Leave empty to auto-generate">
                            <label class="label">
                                <span class="label-text-alt">URL-friendly version of name (e.g., 'innova-crysta')</span>
                            </label>
                        </div>
                        
                        <div class="form-control">
                            <label class="label" for="seats">
                                <span class="label-text">Number of Seats</span>
                            </label>
                            <input type="number" class="input input-bordered w-full" id="seats" name="seats" 
                                   value="<?php echo $vehicle['seats']; ?>" min="1" max="50">
                        </div>
                        
                        <div class="form-control">
                            <label class="label" for="bags">
                                <span class="label-text">Luggage Capacity</span>
                            </label>
                            <input type="number" class="input input-bordered w-full" id="bags" name="bags" 
                                   value="<?php echo $vehicle['bags']; ?>" min="0" max="20">
                            <label class="label">
                                <span class="label-text-alt">Number of bags that can fit</span>
                            </label>
                        </div>
                        
                        <div class="form-control">
                            <label class="label" for="price_per_day">
                                <span class="label-text">Price Per Day (₹)</span>
                            </label>
                            <input type="number" class="input input-bordered w-full" id="price_per_day" 
                                   name="price_per_day" value="<?php echo $vehicle['price_per_day']; ?>" 
                                   min="1" step="1">
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label" for="description">
                                <span class="label-text">Description</span>
                            </label>
                            <textarea class="textarea textarea-bordered h-24" id="description" 
                                      name="description"><?php echo htmlspecialchars($vehicle['description']); ?></textarea>
                        </div>
                          <div class="form-control">
                            <label class="label">
                                <span class="label-text">Vehicle Image</span>
                            </label>
                            <div class="space-y-4">
                                <!-- Image Preview -->
                                <div class="relative w-full h-48 rounded-lg bg-base-200 overflow-hidden mb-4" id="imagePreview">
                                    <img src="<?php echo !empty($vehicle['image']) ? htmlspecialchars($vehicle['image']) : '/images/placeholder/vehicle-placeholder.jpg'; ?>" 
                                         alt="Vehicle preview" 
                                         class="w-full h-full object-cover"
                                         onerror="this.onerror=null; this.src='/images/placeholder/vehicle-placeholder.jpg'; this.parentElement.classList.add('error');">
                                    <div class="absolute inset-0 flex items-center justify-center bg-base-200 opacity-0 transition-opacity duration-300" id="imageLoading">
                                        <div class="loading loading-spinner loading-lg"></div>
                                    </div>
                                </div>
                                <!-- Tabs for upload options -->
                                <div class="tabs tabs-lifted">
                                    <a class="tab tab-active" data-tab="url">Enter URL</a>
                                    <a class="tab" data-tab="upload">Upload Image</a>
                                </div>

                                <div class="tab-panels mt-4">
                                    <!-- URL Input Panel -->
                                    <div id="url-panel" class="tab-panel active bg-base-100 border border-base-300 rounded-box p-6">
                                        <div class="form-control">                                            <input type="text" class="input input-bordered w-full" id="image" name="image" 
                                                   value="<?php echo htmlspecialchars($vehicle['image']); ?>" 
                                                   pattern="^(https?:\/\/.+)|^(\/images\/.+)"
                                                   placeholder="https://example.com/image.jpg"
                                                   title="Please enter either a valid URL (starting with http:// or https://) or a local image path (starting with /images/)">
                                            <label class="label">
                                                <span class="label-text-alt">Enter a direct link to an image (must start with http:// or https://)</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- File Upload Panel -->
                                    <div id="upload-panel" class="tab-panel hidden bg-base-100 border border-base-300 rounded-box p-6">
                                        <div class="form-control">
                                            <input type="file" class="file-input file-input-bordered w-full" 
                                                   id="image_upload" name="image_upload" accept="image/*">
                                            <label class="label">
                                                <span class="label-text-alt">Select an image file to upload (JPG, PNG, GIF)</span>
                                            </label>
                                            <div class="mt-4" id="upload-status"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const fileInput = document.getElementById('image_upload');
                                const urlInput = document.getElementById('image');
                                const uploadStatus = document.getElementById('upload-status');                                
                                const previewContainer = document.getElementById('imagePreview');
                                const imageLoading = document.getElementById('imageLoading');
                                const tabs = document.querySelectorAll('.tab');
                                const panels = document.querySelectorAll('.tab-panel');

                                // Tab switching
                                tabs.forEach(tab => {
                                    tab.addEventListener('click', (e) => {
                                        e.preventDefault();
                                        const target = tab.getAttribute('data-tab');
                                        
                                        // Update tabs
                                        tabs.forEach(t => t.classList.remove('tab-active'));
                                        tab.classList.add('tab-active');
                                        
                                        // Update panels
                                        panels.forEach(panel => panel.classList.add('hidden'));
                                        document.getElementById(`${target}-panel`).classList.remove('hidden');
                                    });
                                });

                                // URL input validation
                                urlInput.addEventListener('input', function() {
                                    if (this.validity.patternMismatch) {
                                        this.setCustomValidity('Please enter a valid URL starting with http:// or https://');
                                    } else {
                                        this.setCustomValidity('');
                                    }
                                });

                                function showStatus(message, type = 'info') {
                                    uploadStatus.innerHTML = `
                                        <div class="alert alert-${type} shadow-lg">
                                            <span>${message}</span>
                                        </div>
                                    `;
                                }                                function updateImagePreview(url) {
                                    if (previewContainer) {
                                        // Show loading state
                                        imageLoading.style.opacity = '1';
                                        
                                        // Create new image element
                                        const img = new Image();
                                        img.className = 'w-full h-full object-cover';
                                        img.alt = 'Vehicle preview';
                                        
                                        img.onload = function() {
                                            // Hide loading state
                                            imageLoading.style.opacity = '0';
                                            previewContainer.classList.remove('error');
                                            // Replace existing image
                                            const existingImg = previewContainer.querySelector('img');
                                            if (existingImg) {
                                                existingImg.remove();
                                            }
                                            previewContainer.insertBefore(img, previewContainer.firstChild);
                                        };
                                        
                                        img.onerror = function() {
                                            // Hide loading state
                                            imageLoading.style.opacity = '0';
                                            previewContainer.classList.add('error');
                                            this.src = '/images/placeholder/vehicle-placeholder.jpg';
                                        };
                                        
                                        // Use relative URL for database storage
                                        if (url.startsWith('http')) {
                                            // Extract relative path if it's a full URL
                                            try {
                                                const urlObj = new URL(url);
                                                url = urlObj.pathname;
                                            } catch (e) {
                                                console.error('Invalid URL:', e);
                                            }
                                        }
                                        
                                        img.src = url;
                                        // Update the hidden input with relative URL
                                        urlInput.value = url;
                                    }
                                }

                                // Handle file upload
                                fileInput.addEventListener('change', function(e) {
                                    const file = e.target.files[0];
                                    if (file) {
                                        // Validate file type
                                        if (!file.type.match('image.*')) {
                                            showStatus('Please select an image file (JPG, PNG, or GIF)', 'error');
                                            return;
                                        }

                                        showStatus(`<div class="loading loading-spinner loading-sm"></div> Uploading ${file.name}...`);
                                        
                                        const formData = new FormData();
                                        formData.append('image', file);

                                        fetch('api/upload-image.php', {
                                            method: 'POST',
                                            body: formData
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                urlInput.value = data.url;
                                                showStatus('Image uploaded successfully!', 'success');
                                                updateImagePreview(data.url);
                                            } else {
                                                showStatus('Error uploading image: ' + data.error, 'error');
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error:', error);
                                            showStatus('Error uploading image. Please try again.', 'error');
                                        });
                                    }
                                });
                            });
                        </script>
                        
                        <div class="form-control">
                            <label class="label" for="features">
                                <span class="label-text">Features</span>
                            </label>
                            <textarea class="textarea textarea-bordered h-24" id="features" name="features" 
                                      placeholder="Enter one feature per line"><?php echo htmlspecialchars(implode("\n", $vehicle['features'])); ?></textarea>
                            <label class="label">
                                <span class="label-text-alt">Enter one feature per line (e.g., 'AC', 'GPS', etc.)</span>
                            </label>
                        </div>
                        
                        <div class="form-control">
                            <label class="label cursor-pointer">
                                <span class="label-text">Active</span>
                                <input type="checkbox" class="toggle toggle-primary" id="active" name="active" 
                                       <?php echo $vehicle['active'] ? 'checked' : ''; ?>>
                            </label>
                            <label class="label">
                                <span class="label-text-alt">Inactive vehicles won't be displayed on the website</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-center gap-4 mt-8">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i> Save Vehicle
                    </button>
                    <a href="vehicles.php" class="btn btn-ghost">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
