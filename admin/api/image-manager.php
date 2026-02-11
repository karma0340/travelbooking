<?php
session_start();
require_once '../../includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['admin_user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'upload':
        handleImageUpload();
        break;
    case 'add_url':
        handleImageUrl();
        break;
    case 'delete':
        handleImageDelete();
        break;
    case 'reorder':
        handleImageReorder();
        break;
    case 'set_primary':
        handleSetPrimary();
        break;
    case 'get_images':
        handleGetImages();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function handleImageUpload() {
    require_once '../includes/ImageOptimizer.php';
    
    $entityType = $_POST['entity_type'] ?? '';
    $entityId = (int)($_POST['entity_id'] ?? 0);
    
    if (!in_array($entityType, ['tour', 'vehicle', 'category'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid entity type']);
        return;
    }
    
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = isset($_FILES['image']) ? 
            'Upload error code: ' . $_FILES['image']['error'] : 
            'No file uploaded';
        echo json_encode(['success' => false, 'message' => $errorMsg]);
        error_log("Image upload failed: $errorMsg");
        return;
    }
    
    try {
        $file = $_FILES['image'];
        $originalSize = $file['size'];
        
        // Initialize optimizer
        $optimizer = new ImageOptimizer();
        
        // Process and optimize image
        $result = $optimizer->processUpload($file);
        
        if (!$result['success']) {
            error_log("ImageOptimizer failed: " . $result['message']);
            echo json_encode($result);
            return;
        }
    
    // Use the primary (large) image as the main URL
    $primaryImage = $result['primary_image'];
    $imageUrl = $primaryImage['path'];
    $compressedSize = $primaryImage['size'];
    
    // Get compression stats
    $stats = $optimizer->getCompressionStats($originalSize, $compressedSize);
    
    // Save to database
    $conn = getDbConnection();
    
    // Get current max display order
    $orderStmt = $conn->prepare("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM entity_images WHERE entity_type = ? AND entity_id = ?");
    $orderStmt->bind_param("si", $entityType, $entityId);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();
    $nextOrder = $orderResult->fetch_assoc()['next_order'];
    $orderStmt->close();
    
    // Insert main record
    $stmt = $conn->prepare("INSERT INTO entity_images (entity_type, entity_id, image_url, image_type, display_order) VALUES (?, ?, ?, 'upload', ?)");
    $stmt->bind_param("sisi", $entityType, $entityId, $imageUrl, $nextOrder);
    
    if ($stmt->execute()) {
        $imageId = $conn->insert_id;
        
        // Store metadata about all generated sizes
        $metadata = json_encode([
            'sizes' => $result['images'],
            'compression' => $stats
        ]);
        
        // Update with metadata
        $metaStmt = $conn->prepare("UPDATE entity_images SET image_metadata = ? WHERE id = ?");
        $metaStmt->bind_param("si", $metadata, $imageId);
        $metaStmt->execute();
        $metaStmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Image uploaded and optimized successfully',
            'image_id' => $imageId,
            'image_url' => $imageUrl,
            'stats' => $stats,
            'sizes' => array_keys($result['images'])
        ]);
    } else {
        // Delete uploaded files if database insert fails
        $optimizer->deleteImage($imageUrl);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
    } catch (Exception $e) {
        error_log("Image upload exception: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()]);
    }
}

function handleImageUrl() {
    $entityType = $_POST['entity_type'] ?? '';
    $entityId = (int)($_POST['entity_id'] ?? 0);
    $imageUrl = trim($_POST['image_url'] ?? '');
    
    if (!in_array($entityType, ['tour', 'vehicle', 'category'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid entity type']);
        return;
    }
    
    if (empty($imageUrl) || !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid URL']);
        return;
    }
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO entity_images (entity_type, entity_id, image_url, image_type, display_order) 
                            VALUES (?, ?, ?, 'url', (SELECT COALESCE(MAX(display_order), 0) + 1 FROM entity_images WHERE entity_type = ? AND entity_id = ?))");
    $stmt->bind_param("sissi", $entityType, $entityId, $imageUrl, $entityType, $entityId);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Image URL added successfully',
            'image_id' => $conn->insert_id,
            'image_url' => $imageUrl
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
}

function handleImageDelete() {
    $imageId = (int)($_POST['image_id'] ?? 0);
    
    if ($imageId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
        return;
    }
    
    $conn = getDbConnection();
    
    // Get image info before deleting
    $stmt = $conn->prepare("SELECT image_url, image_type FROM entity_images WHERE id = ?");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    $stmt->close();
    
    if (!$image) {
        echo json_encode(['success' => false, 'message' => 'Image not found']);
        $conn->close();
        return;
    }
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM entity_images WHERE id = ?");
    $stmt->bind_param("i", $imageId);
    
    if ($stmt->execute()) {
        // Delete physical file if it was an upload
        if ($image['image_type'] === 'upload' && file_exists('../../' . $image['image_url'])) {
            unlink('../../' . $image['image_url']);
        }
        
        echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
}

function handleImageReorder() {
    $imageIds = json_decode($_POST['image_ids'] ?? '[]', true);
    
    if (empty($imageIds) || !is_array($imageIds)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image IDs']);
        return;
    }
    
    $conn = getDbConnection();
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("UPDATE entity_images SET display_order = ? WHERE id = ?");
        
        foreach ($imageIds as $order => $imageId) {
            $stmt->bind_param("ii", $order, $imageId);
            $stmt->execute();
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Images reordered successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error reordering images: ' . $e->getMessage()]);
    }
    
    $stmt->close();
    $conn->close();
}

function handleSetPrimary() {
    $imageId = (int)($_POST['image_id'] ?? 0);
    $entityType = $_POST['entity_type'] ?? '';
    $entityId = (int)($_POST['entity_id'] ?? 0);
    
    if ($imageId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
        return;
    }
    
    $conn = getDbConnection();
    $conn->begin_transaction();
    
    try {
        // Unset all primary flags for this entity
        $stmt = $conn->prepare("UPDATE entity_images SET is_primary = 0 WHERE entity_type = ? AND entity_id = ?");
        $stmt->bind_param("si", $entityType, $entityId);
        $stmt->execute();
        $stmt->close();
        
        // Set new primary
        $stmt = $conn->prepare("UPDATE entity_images SET is_primary = 1 WHERE id = ?");
        $stmt->bind_param("i", $imageId);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Primary image set successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error setting primary image: ' . $e->getMessage()]);
    }
    
    $stmt->close();
    $conn->close();
}

function handleGetImages() {
    $entityType = $_GET['entity_type'] ?? '';
    $entityId = (int)($_GET['entity_id'] ?? 0);
    
    if (!in_array($entityType, ['tour', 'vehicle', 'category'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid entity type']);
        return;
    }
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM entity_images WHERE entity_type = ? AND entity_id = ? ORDER BY display_order ASC");
    $stmt->bind_param("si", $entityType, $entityId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $images = [];
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    
    echo json_encode(['success' => true, 'images' => $images]);
    
    $stmt->close();
    $conn->close();
}
?>
