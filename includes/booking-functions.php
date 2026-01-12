<?php
/**
 * Booking Functions
 * CRUD operations for bookings and dashboard statistics
 */

// Ensure db-connection is loaded
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/db-connection.php';
}

/**
 * Get bookings with optional filters
 * 
 * @param array $filters Optional filters
 * @return array Array of booking data
 */
function getBookings($filters = []) {
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM `bookings`";
    $params = [];
    $types = '';
    
    // Apply filters if provided
    if (!empty($filters)) {
        $conditions = [];
        
        if (isset($filters['status']) && $filters['status']) {
            $conditions[] = "`status` = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        
        if (isset($filters['search']) && $filters['search']) {
            $searchTerm = "%" . $filters['search'] . "%";
            $conditions[] = "(`name` LIKE ? OR `email` LIKE ? OR `phone` LIKE ? OR `booking_ref` LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ssss';
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
    }
    
    $sql .= " ORDER BY `created_at` DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = [];
    
    while ($booking = $result->fetch_assoc()) {
        $bookings[] = $booking;
    }
    
    $stmt->close();
    return $bookings;
}

/**
 * Get a booking by ID
 * 
 * @param int $id Booking ID
 * @return array|null Booking data or null if not found
 */
function getBookingById($id) {
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM `bookings` WHERE `id` = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }
    
    $booking = $result->fetch_assoc();
    $stmt->close();
    
    return $booking;
}

/**
 * Save a booking
 * 
 * @param array $bookingData Booking data
 * @return array Array with success status and ID or error message
 */
function saveBooking($bookingData) {
    $conn = getDbConnection();
    
    // Generate booking reference
    if (empty($bookingData['booking_ref'])) {
        $bookingData['booking_ref'] = 'SIM' . rand(10000, 99999);
    }
    
    // Set defaults
    if (empty($bookingData['status'])) {
        $bookingData['status'] = 'pending';
    }
    if (empty($bookingData['created_at'])) {
        $bookingData['created_at'] = date('Y-m-d H:i:s');
    }
    if (!isset($bookingData['tour_id']) || empty($bookingData['tour_id']) || $bookingData['tour_id'] == 0) {
        $bookingData['tour_id'] = null;
    }
    if (!isset($bookingData['vehicle_id']) || empty($bookingData['vehicle_id']) || $bookingData['vehicle_id'] == 0) {
        $bookingData['vehicle_id'] = null;
    }
    if (empty($bookingData['end_date'])) {
        $bookingData['end_date'] = null;
    }
    
    // Prepare SQL
    $sql = "INSERT INTO `bookings` (
        `booking_ref`, `name`, `email`, `phone`, 
        `tour_id`, `vehicle_id`, `tour_package`, `travel_date`, 
        `end_date`, `guests`, `status`, `created_at`
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // Use null for tour_id and vehicle_id if not set, and bind as 'i' (integer)
    $stmt->bind_param(
        'ssssiisssiss',
        $bookingData['booking_ref'],
        $bookingData['name'],
        $bookingData['email'],
        $bookingData['phone'],
        $bookingData['tour_id'], // may be null
        $bookingData['vehicle_id'], // may be null
        $bookingData['tour_package'],
        $bookingData['travel_date'],
        $bookingData['end_date'],
        $bookingData['guests'],
        $bookingData['status'],
        $bookingData['created_at']
    );
    
    if (!$stmt->execute()) {
        $errorMsg = "Error saving booking: " . $stmt->error;
        error_log($errorMsg);
        if ($stmt) { @$stmt->close(); $stmt = null; }
        return ['success' => false, 'message' => $errorMsg];
    }
    
    $newId = $stmt->insert_id;
    if ($stmt) { @$stmt->close(); $stmt = null; }
    
    return [
        'success' => true,
        'id' => $newId,
        'booking_ref' => $bookingData['booking_ref']
    ];
    // Ensure $stmt is not used after close to prevent 'mysqli_stmt object is already closed' errors.
}

/**
 * Update booking status
 * 
 * @param int $bookingId Booking ID
 * @param string $status New status
 * @param string $updatedBy Username of person making the update
 * @return bool Success or failure
 */
function updateBookingStatus($bookingId, $status, $updatedBy = null) {
    $conn = getDbConnection();
    
    $sql = "UPDATE `bookings` SET
        `status` = ?,
        `updated_at` = NOW(),
        `updated_by` = ?
    WHERE `id` = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $status, $updatedBy, $bookingId);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Delete a booking
 * 
 * @param int $bookingId Booking ID
 * @return bool Success or failure
 */
function deleteBooking($bookingId) {
    $conn = getDbConnection();
    
    $sql = "DELETE FROM `bookings` WHERE `id` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $bookingId);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Get dashboard statistics
 * 
 * @return array Dashboard statistics
 */
function getDashboardStats() {
    $conn = getDbConnection();
    
    // Get total bookings
    $totalBookings = 0;
    $result = $conn->query("SELECT COUNT(*) as count FROM `bookings`");
    if ($result && $row = $result->fetch_assoc()) {
        $totalBookings = $row['count'];
    }
    
    // Get booking counts by status
    $bookingStatus = [
        'pending' => 0,
        'confirmed' => 0,
        'cancelled' => 0,
        'completed' => 0
    ];
    
    $result = $conn->query("SELECT `status`, COUNT(*) as count FROM `bookings` GROUP BY `status`");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $bookingStatus[$row['status']] = $row['count'];
        }
    }
    
    // Get total tours
    $totalTours = 0;
    $result = $conn->query("SELECT COUNT(*) as count FROM `tours` WHERE `active` = 1");
    if ($result && $row = $result->fetch_assoc()) {
        $totalTours = $row['count'];
    }
    
    // Get total vehicles
    $totalVehicles = 0;
    $result = $conn->query("SELECT COUNT(*) as count FROM `vehicles` WHERE `active` = 1");
    if ($result && $row = $result->fetch_assoc()) {
        $totalVehicles = $row['count'];
    }
    
    // Get recent activity
    $recentActivity = [];
    $result = $conn->query("
        SELECT b.*, t.title as tour_name, v.name as vehicle_name
        FROM `bookings` b
        LEFT JOIN `tours` t ON b.tour_id = t.id
        LEFT JOIN `vehicles` v ON b.vehicle_id = v.id
        ORDER BY b.created_at DESC
        LIMIT 5
    ");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recentActivity[] = $row;
        }
    }
    
    return [
        'total_bookings' => $totalBookings,
        'booking_status' => $bookingStatus,
        'total_tours' => $totalTours,
        'total_vehicles' => $totalVehicles,
        'recent_activity' => $recentActivity
    ];
}
?>
