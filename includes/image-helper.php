<?php
/**
 * Get images for an entity (tour, vehicle, category)
 * @param string $entityType - 'tour', 'vehicle', or 'category'
 * @param int $entityId - ID of the entity
 * @param bool $primaryOnly - If true, return only primary image
 * @return array|string - Array of images or single image URL if primaryOnly
 */
function getEntityImages($entityType, $entityId, $primaryOnly = false) {
    $conn = getDbConnection();
    
    if ($primaryOnly) {
        // Get only primary image
        $stmt = $conn->prepare("SELECT image_url, image_metadata FROM entity_images 
                                WHERE entity_type = ? AND entity_id = ? AND is_primary = 1 
                                LIMIT 1");
        $stmt->bind_param("si", $entityType, $entityId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return $row['image_url'];
        }
        
        // If no primary, get first image
        $stmt->close();
        $stmt = $conn->prepare("SELECT image_url FROM entity_images 
                                WHERE entity_type = ? AND entity_id = ? 
                                ORDER BY display_order ASC LIMIT 1");
        $stmt->bind_param("si", $entityType, $entityId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return $row['image_url'];
        }
        
        $stmt->close();
        return null;
    }
    
    // Get all images
    $stmt = $conn->prepare("SELECT * FROM entity_images 
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
    return $images;
}

/**
 * Get primary image for an entity with fallback
 * @param string $entityType - 'tour', 'vehicle', or 'category'
 * @param int $entityId - ID of the entity
 * @param string $fallback - Fallback image URL
 * @return string - Image URL
 */
function getPrimaryImage($entityType, $entityId, $fallback = 'https://images.unsplash.com/photo-1594322436404-5a0526db4d13?q=80&w=1129&auto=format&fit=crop') {
    $image = getEntityImages($entityType, $entityId, true);
    return $image ? $image : $fallback;
}
?>
