<?php
/**
 * Tour Functions
 * CRUD operations for tours and tour itineraries
 */

// Ensure db-connection is loaded
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/db-connection.php';
}

/**
 * Get tours with optional filters and limit
 * 
 * @param int|null $limit Maximum number of tours to return
 * @param array $filters Optional filters like category, location
 * @return array Array of tour data
 */
function getTours($limit = null, $filters = []) {
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM `tours` WHERE `active` = 1";
    $params = [];
    
    // Apply filters if provided
    if (!empty($filters)) {
        if (isset($filters['category']) && $filters['category']) {
            $sql .= " AND `category` = ?";
            $params[] = $filters['category'];
        }
        
        if (isset($filters['location']) && $filters['location']) {
            $sql .= " AND `location` = ?";
            $params[] = $filters['location'];
        }
        
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $sql .= " AND `price` >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $sql .= " AND `price` <= ?";
            $params[] = $filters['max_price'];
        }
    }
    
    // Apply ordering and limit
    $sql .= " ORDER BY `id` DESC";
    
    if ($limit && is_numeric($limit)) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    // Prepare statement
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [];
    }
    
    // Bind parameters if any
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    // Execute statement
    $stmt->execute();
    $result = $stmt->get_result();
    $tours = [];
    
    while ($tour = $result->fetch_assoc()) {
        // Parse JSON fields stored as TEXT
        $tour['features'] = json_decode($tour['features'], true) ?: [];
        if (isset($tour['highlights'])) {
            $tour['highlights'] = json_decode($tour['highlights'], true) ?: [];
        }
        if (isset($tour['map_points'])) {
            $tour['map_points'] = json_decode($tour['map_points'], true) ?: [];
        }
        $tours[] = $tour;
    }
    
    $stmt->close();
    return $tours;
}

/**
 * Get a single tour by ID
 * 
 * @param int $id Tour ID
 * @return array|null Tour data or null if not found
 */
function getTourById($id) {
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM `tours` WHERE `id` = ? AND `active` = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }
    
    $tour = $result->fetch_assoc();
    
    // Parse JSON fields stored as TEXT
    $tour['features'] = json_decode($tour['features'], true) ?: [];
    if (isset($tour['highlights'])) {
        $tour['highlights'] = json_decode($tour['highlights'], true) ?: [];
    }
    if (isset($tour['map_points'])) {
        $tour['map_points'] = json_decode($tour['map_points'], true) ?: [];
    }
    
    $stmt->close();
    return $tour;
}

/**
 * Get a single tour by slug
 * 
 * @param string $slug Tour slug
 * @return array|null Tour data or null if not found
 */
function getTourBySlug($slug) {
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM `tours` WHERE `slug` = ? AND `active` = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }
    
    $tour = $result->fetch_assoc();
    
    // Parse JSON fields stored as TEXT
    $tour['features'] = json_decode($tour['features'], true) ?: [];
    if (isset($tour['highlights'])) {
        $tour['highlights'] = json_decode($tour['highlights'], true) ?: [];
    }
    if (isset($tour['map_points'])) {
        $tour['map_points'] = json_decode($tour['map_points'], true) ?: [];
    }
    
    $stmt->close();
    return $tour;
}

/**
 * Create or update a tour
 * 
 * @param array $tourData Tour data
 * @param int|null $tourId Tour ID for updates, null for new tour
 * @return int|false The tour ID or false on failure
 */
function saveTour($tourData, $tourId = null) {
    $conn = getDbConnection();
    
    // Convert certain fields to JSON
    $features = isset($tourData['features']) ? json_encode($tourData['features']) : json_encode([]);
    $highlights = isset($tourData['highlights']) ? json_encode($tourData['highlights']) : null;
    $mapPoints = isset($tourData['map_points']) ? json_encode($tourData['map_points']) : null;
    
    // Create slug from title if not provided
    if (empty($tourData['slug'])) {
        $tourData['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $tourData['title']));
    }
    
    if ($tourId) {
        // Update existing tour
        $sql = "UPDATE `tours` SET
            `title` = ?,
            `slug` = ?,
            `description` = ?,
            `duration` = ?,
            `duration_days` = ?,
            `price` = ?,
            `location` = ?,
            `category` = ?,
            `image` = ?,
            `features` = ?,
            `highlights` = ?,
            `map_points` = ?,
            `rating` = ?,
            `badge` = ?,
            `active` = ?,
            `updated_at` = NOW()
        WHERE `id` = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssidssssssdsis',
            $tourData['title'],
            $tourData['slug'],
            $tourData['description'],
            $tourData['duration'],
            $tourData['duration_days'],
            $tourData['price'],
            $tourData['location'],
            $tourData['category'],
            $tourData['image'],
            $features,
            $highlights,
            $mapPoints,
            $tourData['rating'],
            $tourData['badge'],
            $tourData['active'],
            $tourId
        );
        
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
        
        $stmt->close();
        return $tourId;
    } else {
        // Insert new tour
        $sql = "INSERT INTO `tours` (
            `title`, `slug`, `description`, `duration`, `duration_days`,
            `price`, `location`, `category`, `image`, `features`,
            `highlights`, `map_points`, `rating`, `badge`, `active`, `created_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssidssssssdsi',
            $tourData['title'],
            $tourData['slug'],
            $tourData['description'],
            $tourData['duration'],
            $tourData['duration_days'],
            $tourData['price'],
            $tourData['location'],
            $tourData['category'],
            $tourData['image'],
            $features,
            $highlights,
            $mapPoints,
            $tourData['rating'],
            $tourData['badge'],
            $tourData['active']
        );
        
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
        
        $newTourId = $stmt->insert_id;
        $stmt->close();
        return $newTourId;
    }
}

/**
 * Delete a tour by ID
 * 
 * @param int $tourId Tour ID
 * @return bool Success or failure
 */
function deleteTour($tourId) {
    $conn = getDbConnection();
    
    // First delete related records (itinerary, reviews)
    $conn->query("DELETE FROM `tour_itinerary` WHERE `tour_id` = $tourId");
    $conn->query("DELETE FROM `reviews` WHERE `tour_id` = $tourId");
    
    // Then delete the tour
    $sql = "DELETE FROM `tours` WHERE `id` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $tourId);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Get tour itinerary by tour ID
 * 
 * @param int $tourId Tour ID
 * @return array Array of itinerary day items
 */
function getTourItinerary($tourId) {
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM `tour_itinerary` WHERE `tour_id` = ? ORDER BY `day_number` ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $tourId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $itinerary = [];
    
    while ($day = $result->fetch_assoc()) {
        // Parse JSON fields stored as TEXT
        $day['meals'] = json_decode($day['meals'], true) ?: [];
        $itinerary[] = $day;
    }
    
    $stmt->close();
    
    // If no itinerary found, generate a default one
    if (empty($itinerary)) {
        $tour = getTourById($tourId);
        $days = $tour ? $tour['duration_days'] : 3;
        
        // Generate default itinerary
        for ($i = 1; $i <= $days; $i++) {
            $itinerary[] = [
                'day_number' => $i,
                'title' => "Day $i: " . ($i == 1 ? "Arrival" : ($i == $days ? "Departure" : "Exploration")),
                'description' => "Default itinerary for day $i.",
                'meals' => ['Breakfast', 'Lunch'],
                'accommodation' => $i < $days ? 'Hotel' : ''
            ];
        }
    }
    
    return $itinerary;
}

/**
 * Save tour itinerary day
 * 
 * @param array $dayData Itinerary day data
 * @param int|null $dayId Day ID for updates, null for new day
 * @return int|false The day ID or false on failure
 */
function saveItineraryDay($dayData, $dayId = null) {
    $conn = getDbConnection();
    
    // Convert meals to JSON
    $meals = isset($dayData['meals']) ? json_encode($dayData['meals']) : json_encode([]);
    
    if ($dayId) {
        // Update existing day
        $sql = "UPDATE `tour_itinerary` SET
            `tour_id` = ?,
            `day_number` = ?,
            `title` = ?,
            `description` = ?,
            `meals` = ?,
            `accommodation` = ?,
            `updated_at` = NOW()
        WHERE `id` = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'iissisi',
            $dayData['tour_id'],
            $dayData['day_number'],
            $dayData['title'],
            $dayData['description'],
            $meals,
            $dayData['accommodation'],
            $dayId
        );
    } else {
        // Insert new day
        $sql = "INSERT INTO `tour_itinerary` (
            `tour_id`, `day_number`, `title`, `description`, 
            `meals`, `accommodation`, `created_at`
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'iissss',
            $dayData['tour_id'],
            $dayData['day_number'],
            $dayData['title'],
            $dayData['description'],
            $meals,
            $dayData['accommodation']
        );
    }
    
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    
    $newId = $dayId ?: $stmt->insert_id;
    $stmt->close();
    return $newId;
}

/**
 * Get related tours based on category and excluding current tour
 * 
 * @param string $category Tour category
 * @param int $currentTourId Current tour ID to exclude
 * @param int $limit Maximum number of tours to return
 * @return array Array of related tours
 */
function getRelatedTours($category, $currentTourId, $limit = 3) {
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM `tours` 
            WHERE `category` = ? 
            AND `id` != ? 
            AND `active` = 1 
            ORDER BY RAND() 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sii', $category, $currentTourId, $limit);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $relatedTours = [];
    
    while ($tour = $result->fetch_assoc()) {
        // Parse JSON fields stored as TEXT
        $tour['features'] = json_decode($tour['features'], true) ?: [];
        if (isset($tour['highlights'])) {
            $tour['highlights'] = json_decode($tour['highlights'], true) ?: [];
        }
        if (isset($tour['map_points'])) {
            $tour['map_points'] = json_decode($tour['map_points'], true) ?: [];
        }
        $relatedTours[] = $tour;
    }
    
    $stmt->close();
    return $relatedTours;
}

/**
 * Get reviews for a tour
 * 
 * @param int $tourId Tour ID
 * @param bool $approvedOnly Get only approved reviews
 * @return array Array of reviews
 */
function getTourReviews($tourId, $approvedOnly = true) {
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM `reviews` WHERE `tour_id` = ?";
    
    if ($approvedOnly) {
        $sql .= " AND `approved` = 1";
    }
    
    $sql .= " ORDER BY `created_at` DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $tourId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $reviews = [];
    
    while ($review = $result->fetch_assoc()) {
        $reviews[] = $review;
    }
    
    $stmt->close();
    return $reviews;
}

/**
 * Save a review
 * 
 * @param array $reviewData Review data
 * @return bool Success or failure
 */
function saveReview($reviewData) {
    $conn = getDbConnection();
    
    $sql = "INSERT INTO `reviews` (
        `tour_id`, `name`, `email`, `rating`, 
        `comment`, `approved`, `created_at`
    ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    
    // Default to not approved
    $approved = isset($reviewData['approved']) ? $reviewData['approved'] : 0;
    
    $stmt->bind_param(
        'issisi',
        $reviewData['tour_id'],
        $reviewData['name'],
        $reviewData['email'],
        $reviewData['rating'],
        $reviewData['comment'],
        $approved
    );
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Approve or reject a review
 * 
 * @param int $reviewId Review ID
 * @param bool $approved Approval status
 * @return bool Success or failure
 */
function updateReviewStatus($reviewId, $approved) {
    $conn = getDbConnection();
    
    $sql = "UPDATE `reviews` SET `approved` = ? WHERE `id` = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $approved, $reviewId);
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}
?>
