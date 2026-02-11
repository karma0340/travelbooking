<?php
/**
 * Auto-Rotating Image Carousel Component
 * Displays multiple images with auto-rotation (2-5 seconds interval)
 * 
 * Usage:
 * $entityType = 'tour'; // or 'vehicle', 'category'
 * $entityId = 123;
 * $interval = 3000; // milliseconds (optional, default 3000)
 * include 'includes/image-carousel.php';
 */

// Default values
$interval = $interval ?? 3000; // 3 seconds default
$carouselId = 'carousel-' . $entityType . '-' . $entityId;

// Fetch images from database
require_once __DIR__ . '/db-connection.php';
$conn = getDbConnection();
$stmt = $conn->prepare("SELECT image_url, is_primary FROM entity_images 
                        WHERE entity_type = ? AND entity_id = ? 
                        ORDER BY is_primary DESC, display_order ASC");
$stmt->bind_param("si", $entityType, $entityId);
$stmt->execute();
$result = $stmt->get_result();

$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}

$stmt->close();

// If no images in gallery, fall back to single image field
if (empty($images) && !empty($fallbackImage)) {
    $images = [['image_url' => $fallbackImage, 'is_primary' => 1]];
}
?>

<?php if (!empty($images)): ?>
<div class="image-carousel-container" id="<?php echo $carouselId; ?>" 
     data-interval="<?php echo $interval; ?>" 
     data-image-count="<?php echo count($images); ?>">
    
    <!-- Main Image Display -->
    <div class="carousel-images" style="position: relative; width: 100%; height: 100%; overflow: hidden;">
        <?php foreach ($images as $index => $image): 
            // Parse metadata for responsive images
            $metadata = !empty($image['image_metadata']) ? json_decode($image['image_metadata'], true) : null;
            $sizes = $metadata['sizes'] ?? [];
            
            // Build srcset for responsive images
            $srcset = [];
            if (!empty($sizes)) {
                foreach ($sizes as $sizeName => $sizeData) {
                    $srcset[] = $sizeData['path'] . ' ' . $sizeData['width'] . 'w';
                }
            }
            $srcsetAttr = !empty($srcset) ? 'srcset="' . implode(', ', $srcset) . '"' : '';
            $sizesAttr = 'sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"';
        ?>
        <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>" 
             style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: <?php echo $index === 0 ? '1' : '0'; ?>; transition: opacity 0.5s ease-in-out;">
            <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                 <?php echo $srcsetAttr; ?>
                 <?php echo !empty($srcset) ? $sizesAttr : ''; ?>
                 alt="Image <?php echo $index + 1; ?>"
                 style="width: 100%; height: 100%; object-fit: cover;"
                 loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Navigation Dots (only show if multiple images) -->
    <?php if (count($images) > 1): ?>
    <div class="carousel-dots" style="position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 10;">
        <?php foreach ($images as $index => $image): ?>
        <button class="carousel-dot <?php echo $index === 0 ? 'active' : ''; ?>" 
                data-index="<?php echo $index; ?>"
                style="width: 10px; height: 10px; border-radius: 50%; border: 2px solid white; background: <?php echo $index === 0 ? 'white' : 'transparent'; ?>; cursor: pointer; transition: all 0.3s;"
                aria-label="Go to image <?php echo $index + 1; ?>"></button>
        <?php endforeach; ?>
    </div>
    
    <!-- Navigation Arrows (only show if multiple images) -->
    <button class="carousel-prev" 
            style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; transition: background 0.3s;"
            aria-label="Previous image">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="carousel-next" 
            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; transition: background 0.3s;"
            aria-label="Next image">
        <i class="fas fa-chevron-right"></i>
    </button>
    <?php endif; ?>
    
    <!-- Image Counter -->
    <?php if (count($images) > 1): ?>
    <div class="carousel-counter" 
         style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.6); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; z-index: 10;">
        <span class="current-slide">1</span> / <?php echo count($images); ?>
    </div>
    <?php endif; ?>
</div>

<style>
.carousel-prev:hover, .carousel-next:hover {
    background: rgba(0,0,0,0.8) !important;
}

.carousel-dot:hover {
    background: rgba(255,255,255,0.7) !important;
}
</style>

<script>
(function() {
    const carousel = document.getElementById('<?php echo $carouselId; ?>');
    if (!carousel) return;
    
    const imageCount = parseInt(carousel.dataset.imageCount);
    if (imageCount <= 1) return; // No need for carousel with single image
    
    const interval = parseInt(carousel.dataset.interval);
    const slides = carousel.querySelectorAll('.carousel-slide');
    const dots = carousel.querySelectorAll('.carousel-dot');
    const prevBtn = carousel.querySelector('.carousel-prev');
    const nextBtn = carousel.querySelector('.carousel-next');
    const counter = carousel.querySelector('.current-slide');
    
    let currentIndex = 0;
    let autoPlayInterval;
    
    function showSlide(index) {
        // Wrap around
        if (index >= imageCount) index = 0;
        if (index < 0) index = imageCount - 1;
        
        currentIndex = index;
        
        // Update slides
        slides.forEach((slide, i) => {
            slide.style.opacity = i === index ? '1' : '0';
            slide.classList.toggle('active', i === index);
        });
        
        // Update dots
        dots.forEach((dot, i) => {
            dot.style.background = i === index ? 'white' : 'transparent';
            dot.classList.toggle('active', i === index);
        });
        
        // Update counter
        if (counter) {
            counter.textContent = index + 1;
        }
    }
    
    function nextSlide() {
        showSlide(currentIndex + 1);
    }
    
    function prevSlide() {
        showSlide(currentIndex - 1);
    }
    
    function startAutoPlay() {
        stopAutoPlay();
        autoPlayInterval = setInterval(nextSlide, interval);
    }
    
    function stopAutoPlay() {
        if (autoPlayInterval) {
            clearInterval(autoPlayInterval);
        }
    }
    
    // Event listeners
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevSlide();
            stopAutoPlay();
            startAutoPlay(); // Restart auto-play after manual interaction
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
            stopAutoPlay();
            startAutoPlay();
        });
    }
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            stopAutoPlay();
            startAutoPlay();
        });
    });
    
    // Pause on hover
    carousel.addEventListener('mouseenter', stopAutoPlay);
    carousel.addEventListener('mouseleave', startAutoPlay);
    
    // Start auto-play
    startAutoPlay();
})();
</script>
<?php endif; ?>
