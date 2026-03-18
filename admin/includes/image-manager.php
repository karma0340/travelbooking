<!-- Multi-Image Manager Component -->
<div class="card bg-base-100 shadow-sm mt-6">
    <div class="card-body">
        <h3 class="card-title text-lg mb-4">
            <i class="fas fa-images text-primary mr-2"></i>
            Image Gallery
        </h3>
        
        <!-- Upload Methods Tabs -->
        <div class="tabs tabs-boxed mb-4">
            <a class="tab tab-active" data-tab="upload">
                <i class="fas fa-upload mr-1"></i> Upload File
            </a>
            <a class="tab" data-tab="url">
                <i class="fas fa-link mr-1"></i> Add URL
            </a>
        </div>
        
        <!-- Upload File Tab -->
        <div class="tab-content" id="upload-tab">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Choose images to upload (Max 5MB each)</span>
                </label>
                <input type="file" 
                       class="file-input file-input-bordered w-full" 
                       id="image-file-input"
                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                       multiple>
                <label class="label">
                    <span class="label-text-alt">Supported: JPG, PNG, GIF, WebP</span>
                </label>
            </div>
            <button type="button" class="btn btn-primary mt-2" id="upload-images-btn">
                <i class="fas fa-cloud-upload-alt mr-2"></i>Upload Images
            </button>
        </div>
        
        <!-- Add URL Tab -->
        <div class="tab-content hidden" id="url-tab">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Image URL</span>
                </label>
                <div class="join w-full">
                    <input type="url" 
                           class="input input-bordered join-item flex-1" 
                           id="image-url-input"
                           placeholder="https://example.com/image.jpg">
                    <button type="button" class="btn btn-primary join-item" id="add-url-btn">
                        <i class="fas fa-plus mr-1"></i>Add
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Image Gallery Display -->
        <div class="divider">Current Images</div>
        <div id="image-gallery" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Images will be loaded here dynamically -->
        </div>
        
        <!-- Empty State -->
        <div id="empty-gallery" class="text-center py-8 text-gray-400">
            <i class="fas fa-images fa-3x mb-2"></i>
            <p>No images yet. Upload or add URLs to get started.</p>
        </div>
    </div>
</div>

<!-- Image Gallery Styles -->
<style>
.image-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 0.5rem;
    overflow: hidden;
    border: 2px solid #e5e7eb;
    transition: all 0.3s;
}

.image-item:hover {
    border-color: #4f46e5;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-item .image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s;
}

.image-item:hover .image-overlay {
    opacity: 1;
}

.image-item .primary-badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    background: #10b981;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.image-item .order-badge {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: #4f46e5;
    color: white;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
}
</style>

<!-- Image Manager JavaScript -->
<script>
const ImageManager = {
    entityType: '<?php echo $entityType ?? "tour"; ?>',
    entityId: <?php echo $entityId ?? 0; ?>,
    
    init() {
        this.bindEvents();
        if (this.entityId > 0) {
            this.loadImages();
        }
    },
    
    bindEvents() {
        // Tab switching
        document.querySelectorAll('.tabs .tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = tab.dataset.tab;
                this.switchTab(tabName);
            });
        });
        
        // Upload button
        document.getElementById('upload-images-btn')?.addEventListener('click', () => {
            this.uploadImages();
        });
        
        // Add URL button
        document.getElementById('add-url-btn')?.addEventListener('click', () => {
            this.addImageUrl();
        });
        
        // Enter key on URL input
        document.getElementById('image-url-input')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.addImageUrl();
            }
        });
    },
    
    switchTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('tab-active'));
        document.querySelector(`.tabs .tab[data-tab="${tabName}"]`).classList.add('tab-active');
        
        // Update tab content
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        document.getElementById(`${tabName}-tab`).classList.remove('hidden');
    },
    
    async uploadImages() {
        const fileInput = document.getElementById('image-file-input');
        const files = fileInput.files;
        
        if (files.length === 0) {
            alert('Please select at least one image');
            return;
        }
        
        const btn = document.getElementById('upload-images-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner"></span> Uploading...';
        
        let successCount = 0;
        let totalSaved = 0;
        
        for (let file of files) {
            const formData = new FormData();
            formData.append('action', 'upload');
            formData.append('entity_type', this.entityType);
            formData.append('entity_id', this.entityId);
            formData.append('image', file);
            
            try {
                const response = await fetch('api/image-manager.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    successCount++;
                    if (result.stats) {
                        console.log(`✓ ${file.name}: ${result.stats.original_size} → ${result.stats.compressed_size} (Saved ${result.stats.saved_percent})`);
                    }
                } else {
                    alert(`Error uploading ${file.name}: ${result.message}`);
                }
            } catch (error) {
                console.error('Upload error:', error);
                alert(`Error uploading ${file.name}`);
            }
        }
        
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-cloud-upload-alt mr-2"></i>Upload Images';
        fileInput.value = '';
        
        if (successCount > 0) {
            alert(`✓ Successfully uploaded and optimized ${successCount} image(s)!\n\nImages have been compressed and converted to WebP format for faster loading.`);
        }
        
        this.loadImages();
    },
    
    async addImageUrl() {
        const urlInput = document.getElementById('image-url-input');
        const imageUrl = urlInput.value.trim();
        
        if (!imageUrl) {
            alert('Please enter an image URL');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'add_url');
        formData.append('entity_type', this.entityType);
        formData.append('entity_id', this.entityId);
        formData.append('image_url', imageUrl);
        
        try {
            const response = await fetch('api/image-manager.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                urlInput.value = '';
                this.loadImages();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to add image URL');
        }
    },
    
    async loadImages() {
        try {
            const response = await fetch(`api/image-manager.php?action=get_images&entity_type=${this.entityType}&entity_id=${this.entityId}`);
            const result = await response.json();
            
            if (result.success) {
                this.renderImages(result.images);
            }
        } catch (error) {
            console.error('Error loading images:', error);
        }
    },
    
    renderImages(images) {
        const gallery = document.getElementById('image-gallery');
        const emptyState = document.getElementById('empty-gallery');
        
        if (images.length === 0) {
            gallery.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }
        
        emptyState.classList.add('hidden');
        
        gallery.innerHTML = images.map((img, index) => `
            <div class="image-item" data-image-id="${img.id}">
                <img src="${img.image_url.startsWith('http') ? img.image_url : '../' + img.image_url}" 
                     alt="Image ${index + 1}"
                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1594322436404-5a0526db4d13?q=80&w=1129&auto=format&fit=crop'">
                ${img.is_primary ? '<div class="primary-badge">Primary</div>' : ''}
                <div class="order-badge">${index + 1}</div>
                <div class="image-overlay">
                    ${!img.is_primary ? `<button class="btn btn-sm btn-success" onclick="ImageManager.setPrimary(${img.id})">
                        <i class="fas fa-star"></i>
                    </button>` : ''}
                    <button class="btn btn-sm btn-error" onclick="ImageManager.deleteImage(${img.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');

        // Sync primary image to external input (if exists in parent form)
        const primaryImg = images.find(img => img.is_primary) || images[0];
        const externalImageInput = document.getElementById('image');
        if (externalImageInput) {
            if (primaryImg) {
                externalImageInput.value = primaryImg.image_url;
            } else if (images.length === 0) {
                // DON'T clear it automatically as it might be a manual URL the user entered
                // but if we want strictly gallery-based, we'd clear it.
                // Leave it for now.
            }
        }
    },
    
    async deleteImage(imageId) {
        if (!confirm('Are you sure you want to delete this image?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('image_id', imageId);
        
        try {
            const response = await fetch('api/image-manager.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.loadImages();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to delete image');
        }
    },
    
    async setPrimary(imageId) {
        const formData = new FormData();
        formData.append('action', 'set_primary');
        formData.append('image_id', imageId);
        formData.append('entity_type', this.entityType);
        formData.append('entity_id', this.entityId);
        
        try {
            const response = await fetch('api/image-manager.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.loadImages();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to set primary image');
        }
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    ImageManager.init();
});
</script>
