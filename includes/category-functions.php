<?php
/**
 * Tour Category Functions
 * Handles all database operations for tour categories
 */

/**
 * Get all tour categories
 * @param bool $activeOnly - If true, only return active categories
 * @return array - Array of category records
 */
function getCategories($activeOnly = true) {
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM tour_categories";
    if ($activeOnly) {
        $sql .= " WHERE active = 1";
    }
    $sql .= " ORDER BY display_order ASC, name ASC";
    
    $result = $conn->query($sql);
    $categories = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

/**
 * Get a single category by ID
 * @param int $id - Category ID
 * @return array|null - Category record or null if not found
 */
function getCategoryById($id) {
    $conn = getDbConnection();
    $id = (int)$id;
    
    $sql = "SELECT * FROM tour_categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get a category by slug
 * @param string $slug - Category slug
 * @return array|null - Category record or null if not found
 */
function getCategoryBySlug($slug) {
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM tour_categories WHERE slug = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Save a category (create or update)
 * @param array $data - Category data
 * @param int|null $id - Category ID for update, null for insert
 * @return int|bool - Category ID on success, false on failure
 */
function saveCategory($data, $id = null) {
    $conn = getDbConnection();
    
    // Generate slug if not provided
    if (empty($data['slug']) && !empty($data['name'])) {
        $data['slug'] = generateSlug($data['name']);
    }
    
    // Set default values
    $data['display_order'] = isset($data['display_order']) ? (int)$data['display_order'] : 0;
    $data['active'] = isset($data['active']) ? 1 : 0;
    $data['color'] = $data['color'] ?? 'primary';
    
    if ($id) {
        // Update existing category
        $sql = "UPDATE tour_categories SET 
                name = ?, 
                slug = ?, 
                description = ?, 
                icon = ?, 
                image = ?, 
                color = ?, 
                display_order = ?, 
                active = ?
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssiis",
            $data['name'],
            $data['slug'],
            $data['description'],
            $data['icon'],
            $data['image'],
            $data['color'],
            $data['display_order'],
            $data['active'],
            $id
        );
        
        if ($stmt->execute()) {
            return $id;
        }
    } else {
        // Insert new category
        $sql = "INSERT INTO tour_categories 
                (name, slug, description, icon, image, color, display_order, active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssii",
            $data['name'],
            $data['slug'],
            $data['description'],
            $data['icon'],
            $data['image'],
            $data['color'],
            $data['display_order'],
            $data['active']
        );
        
        if ($stmt->execute()) {
            return $conn->insert_id;
        }
    }
    
    return false;
}

/**
 * Delete a category
 * @param int $id - Category ID
 * @return bool - True on success, false on failure
 */
function deleteCategory($id) {
    $conn = getDbConnection();
    $id = (int)$id;
    
    $sql = "DELETE FROM tour_categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    return $stmt->execute();
}

/**
 * Generate a URL-friendly slug from text
 * @param string $text - Text to convert
 * @return string - Slug
 */
function generateSlug($text) {
    // Convert to lowercase
    $text = strtolower($text);
    // Replace spaces and special characters with hyphens
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    // Remove leading/trailing hyphens
    $text = trim($text, '-');
    return $text;
}

/**
 * Get count of tours in a category
 * @param int $categoryId - Category ID
 * @return int - Number of tours
 */
function getCategoryTourCount($categoryId) {
    $conn = getDbConnection();
    $categoryId = (int)$categoryId;
    
    // Get category slug
    $category = getCategoryById($categoryId);
    if (!$category) {
        return 0;
    }
    
    $sql = "SELECT COUNT(*) as count FROM tours WHERE category = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category['slug']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return (int)$row['count'];
}
?>
