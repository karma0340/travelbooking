<?php
/**
 * Review-related database functions
 */

if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

require_once __DIR__ . '/db-connection.php';

/**
 * Ensure reviews table exists
 */
function ensureReviewsTableExists() {
    $conn = getDbConnection();
    $sql = "CREATE TABLE IF NOT EXISTS `customer_reviews` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `email` varchar(100) DEFAULT NULL,
      `location` varchar(100) DEFAULT NULL,
      `rating` int(1) NOT NULL DEFAULT 5,
      `review_text` text NOT NULL,
      `image_path` varchar(255) DEFAULT NULL,
      `google_id` varchar(255) DEFAULT NULL,
      `google_picture` varchar(500) DEFAULT NULL,
      `status` enum('pending','approved','rejected') DEFAULT 'approved',
      `is_featured` tinyint(1) DEFAULT 0,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `google_id` (`google_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    try {
        if (!$conn->query($sql)) {
            logError("Error creating reviews table: " . $conn->error);
        }
        
        // Ensure columns exist (structure updates)
        $columnsToCheck = [
            'location' => "ADD `location` varchar(100) DEFAULT NULL AFTER `email`",
            'review_text' => "ADD `review_text` text NOT NULL AFTER `rating`",
            'image_path' => "ADD `image_path` varchar(255) DEFAULT NULL AFTER `review_text`",
            'google_id' => "ADD `google_id` varchar(255) DEFAULT NULL AFTER `image_path`",
            'google_picture' => "ADD `google_picture` varchar(500) DEFAULT NULL AFTER `google_id`",
            'status' => "ADD `status` enum('pending','approved','rejected') DEFAULT 'approved' AFTER `google_picture`",
            'is_featured' => "ADD `is_featured` tinyint(1) DEFAULT 0 AFTER `status`"
        ];
        
        foreach ($columnsToCheck as $colName => $alterQuery) {
            $check = $conn->query("SHOW COLUMNS FROM `reviews` LIKE '$colName'");
            if ($check && $check->num_rows == 0) {
                if (!$conn->query("ALTER TABLE `reviews` $alterQuery")) {
                    logError("Error adding column $colName: " . $conn->error);
                } else {
                    logError("Added missing '$colName' column to reviews table");
                }
            }
        }
        
        // Dummy data insertion disabled as per user request
        /*
        // Check if we need to insert dummy data (if table was just created and is empty)
        $check = $conn->query("SELECT COUNT(*) as count FROM customer_reviews");
        if ($check) {
            $row = $check->fetch_assoc();
            if ($row['count'] == 0) {
                // Prepare dummy data insertion
                $dummySql = "INSERT INTO `reviews` (`name`, `location`, `rating`, `review_text`, `image_path`, `status`, `is_featured`) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($dummySql);
                
                if ($stmt) {
                    $reviews = [
                        ['Priya Sharma', 'Delhi', 5, 'Our trip to Shimla was unforgettable. The tour was well-organized and the guide was very knowledgeable. Highly recommend!', 'https://randomuser.me/api/portraits/women/45.jpg', 'approved', 1],
                        ['Rahul Verma', 'Mumbai', 5, 'The Spiti Valley trek was a life-changing experience. The guides were excellent and the arrangements were perfect. Will definitely book with Travel In Peace again!', 'https://randomuser.me/api/portraits/men/32.jpg', 'approved', 1],
                        ['Anita Gupta', 'Bangalore', 4, 'We booked the Innova Crysta for our family trip to Manali. The vehicle was in excellent condition and the driver was very professional and friendly.', 'https://randomuser.me/api/portraits/women/68.jpg', 'approved', 1]
                    ];
                    
                    foreach ($reviews as $review) {
                        $stmt->bind_param("ssissii", $review[0], $review[1], $review[2], $review[3], $review[4], $review[5], $review[6]);
                        $stmt->execute();
                    }
                }
            }
            }
        }
        */
    } catch (Exception $e) {
        logError("Exception ensuring reviews table: " . $e->getMessage());
    }
}

/**
 * Get approved reviews
 */
function getReviews($limit = 6, $featuredOnly = false) {
    ensureReviewsTableExists();
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM customer_reviews WHERE status = 'approved'";
    if ($featuredOnly) {
        $sql .= " AND is_featured = 1";
    }
    $sql .= " ORDER BY created_at DESC LIMIT ?";
    
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) return [];
        
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reviews = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $reviews[] = $row;
            }
        }
        return $reviews;
    } catch (Exception $e) {
        error_log("Error fetching reviews: " . $e->getMessage());
        return [];
    }
}

/**
 * Get review statistics
 */
function getReviewStats() {
    ensureReviewsTableExists();
    $conn = getDbConnection();
    
    $sql = "SELECT COUNT(*) as total, AVG(rating) as average FROM customer_reviews WHERE status = 'approved'";
    
    try {
        $result = $conn->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return [
                'total' => (int)$row['total'],
                'average' => round((float)$row['average'], 1)
            ];
        }
        return ['total' => 0, 'average' => 5.0];
    } catch (Exception $e) {
        return ['total' => 0, 'average' => 5.0];
    }
}

/**
 * Save a new review
 */
function saveGeneralReview($data) {
    ensureReviewsTableExists();
    $conn = getDbConnection();
    
    $sql = "INSERT INTO customer_reviews (name, email, location, rating, review_text, image_path, google_id, google_picture, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved')";
    
    // Check for duplicate submission (same google_id and text within last 5 minutes)
    if (!empty($data['google_id'])) {
        $checkSql = "SELECT id FROM customer_reviews WHERE google_id = ? AND review_text = ? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        $checkStmt = $conn->prepare($checkSql);
        if ($checkStmt) {
            $checkStmt->bind_param("ss", $data['google_id'], $data['review_text']);
            $checkStmt->execute();
            $checkStmt->store_result();
            if ($checkStmt->num_rows > 0) {
                // Duplicate found, treat as success
                return true; 
            }
            $checkStmt->close();
        }
    }
    
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $msg = "Error preparing saveGeneralReview: " . $conn->error;
            logError($msg);
            file_put_contents(__DIR__ . '/../debug_log.txt', date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
            return false;
        }
        
        $stmt->bind_param("sssissss", 
            $data['name'], 
            $data['email'], 
            $data['location'], 
            $data['rating'], 
            $data['review_text'],
            $data['image_path'],
            $data['google_id'],
            $data['google_picture']
        );
        
        $result = $stmt->execute();
        if (!$result) {
            $msg = "Error executing saveGeneralReview: " . $stmt->error;
            logError($msg);
            file_put_contents(__DIR__ . '/../debug_log.txt', date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
        }
        return $result;
    } catch (Exception $e) {
        $msg = "Exception saving review: " . $e->getMessage();
        logError($msg);
        file_put_contents(__DIR__ . '/../debug_log.txt', date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
        return false;
    }
}

/**
 * Get all reviews for admin
 */
function getAllReviews($limit = 50) {
    ensureReviewsTableExists();
    $conn = getDbConnection();
    $sql = "SELECT * FROM customer_reviews ORDER BY created_at DESC LIMIT ?";
    
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Exception $e) {
        error_log("Error fetching all reviews: " . $e->getMessage());
        return [];
    }
}

/**
 * Update review status
 */
function updateGeneralReviewStatus($id, $status) {
    $conn = getDbConnection();
    $sql = "UPDATE customer_reviews SET status = ? WHERE id = ?";
    
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error updating review status: " . $e->getMessage());
        return false;
    }
}

/**
 * Toggle featured status
 */
function toggleReviewFeatured($id) {
    $conn = getDbConnection();
    $sql = "UPDATE customer_reviews SET is_featured = NOT is_featured WHERE id = ?";
    
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error toggling review featured: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete review
 */
function deleteReview($id) {
    $conn = getDbConnection();
    $sql = "DELETE FROM customer_reviews WHERE id = ?";
    
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error deleting review: " . $e->getMessage());
        return false;
    }
}
?>
