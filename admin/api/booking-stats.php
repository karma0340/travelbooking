<?php
define('INCLUDED_FROM_ADMIN_PAGE', true);
define('AJAX_REQUEST', true);

require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once './api-helpers.php'; // Add this line to include the helper functions

// Ensure user is logged in and has proper permissions via API token
$apiToken = isset($_SERVER['HTTP_X_API_TOKEN']) ? $_SERVER['HTTP_X_API_TOKEN'] : '';
$validToken = hash('sha256', 'shimla_admin_' . date('Y-m-d'));

// Simple validation - in production use a better authentication mechanism
if (empty($apiToken) || $apiToken !== $validToken) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Return JSON content type
header('Content-Type: application/json');

// Process request based on action parameter
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($action) {
        case 'monthly_stats':
            echo json_encode(getMonthlyBookingStats());
            break;
            
        case 'status_distribution':
            echo json_encode(getBookingStatusDistribution());
            break;
            
        case 'service_distribution':
            echo json_encode(getServiceTypeDistribution());
            break;
            
        case 'recent_activity':
            echo json_encode(getRecentBookingActivity());
            break;
            
        default:
            // Default - return summary stats
            echo json_encode(getBookingSummary());
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}

/**
 * Get summary of booking statistics
 */
function getBookingSummary() {
    $bookings = getItems('bookings.json');
    $today = date('Y-m-d');
    $currentMonth = date('Y-m');
    
    // Initialize counters
    $totalBookings = count($bookings);
    $todayBookings = 0;
    $pendingBookings = 0;
    $confirmedBookings = 0;
    $completedBookings = 0;
    $cancelledBookings = 0;
    $currentMonthBookings = 0;
    
    // Calculate statistics
    foreach ($bookings as $booking) {
        $bookingDate = date('Y-m-d', strtotime($booking['created_at']));
        $bookingMonth = date('Y-m', strtotime($booking['created_at']));
        
        if ($bookingDate === $today) {
            $todayBookings++;
        }
        
        if ($bookingMonth === $currentMonth) {
            $currentMonthBookings++;
        }
        
        // Count by status
        switch ($booking['status'] ?? 'pending') {
            case 'pending': $pendingBookings++; break;
            case 'confirmed': $confirmedBookings++; break;
            case 'completed': $completedBookings++; break;
            case 'cancelled': $cancelledBookings++; break;
        }
    }
    
    // Calculate month-over-month growth
    $lastMonth = date('Y-m', strtotime('first day of last month'));
    $lastMonthBookings = 0;
    
    foreach ($bookings as $booking) {
        $bookingMonth = date('Y-m', strtotime($booking['created_at']));
        if ($bookingMonth === $lastMonth) {
            $lastMonthBookings++;
        }
    }
    
    // Calculate growth rate (avoid division by zero)
    $growthRate = $lastMonthBookings > 0 
        ? (($currentMonthBookings - $lastMonthBookings) / $lastMonthBookings) * 100
        : ($currentMonthBookings > 0 ? 100 : 0);
    
    return [
        'total_bookings' => $totalBookings,
        'today_bookings' => $todayBookings,
        'pending_bookings' => $pendingBookings,
        'confirmed_bookings' => $confirmedBookings,
        'completed_bookings' => $completedBookings,
        'cancelled_bookings' => $cancelledBookings,
        'current_month_bookings' => $currentMonthBookings,
        'last_month_bookings' => $lastMonthBookings,
        'growth_rate' => round($growthRate, 2),
        'growth_trend' => $growthRate >= 0 ? 'positive' : 'negative'
    ];
}

/**
 * Get monthly booking statistics for chart
 */
function getMonthlyBookingStats() {
    $bookings = getItems('bookings.json');
    $monthlyData = [];
    
    // Initialize data for last 12 months
    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $monthlyData[$month] = [
            'label' => date('M Y', strtotime("-$i months")),
            'total' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];
    }
    
    // Populate data
    foreach ($bookings as $booking) {
        $bookingMonth = date('Y-m', strtotime($booking['created_at']));
        
        // Only include bookings from the last 12 months
        if (isset($monthlyData[$bookingMonth])) {
            $monthlyData[$bookingMonth]['total']++;
            
            if (($booking['status'] ?? '') === 'completed') {
                $monthlyData[$bookingMonth]['completed']++;
            } else if (($booking['status'] ?? '') === 'cancelled') {
                $monthlyData[$bookingMonth]['cancelled']++;
            }
        }
    }
    
    // Convert to format suitable for charts
    $chartData = [
        'labels' => array_column($monthlyData, 'label'),
        'datasets' => [
            [
                'label' => 'Total Bookings',
                'data' => array_column($monthlyData, 'total'),
                'backgroundColor' => 'rgba(78, 115, 223, 0.8)'
            ],
            [
                'label' => 'Completed Bookings',
                'data' => array_column($monthlyData, 'completed'),
                'backgroundColor' => 'rgba(28, 200, 138, 0.8)'
            ],
            [
                'label' => 'Cancelled Bookings',
                'data' => array_column($monthlyData, 'cancelled'),
                'backgroundColor' => 'rgba(231, 74, 59, 0.8)'
            ]
        ]
    ];
    
    return $chartData;
}

/**
 * Get booking status distribution
 */
function getBookingStatusDistribution() {
    $bookings = getItems('bookings.json');
    
    // Initialize counters
    $statusCounts = [
        'pending' => 0,
        'confirmed' => 0,
        'completed' => 0,
        'cancelled' => 0
    ];
    
    // Count by status
    foreach ($bookings as $booking) {
        $status = $booking['status'] ?? 'pending';
        if (isset($statusCounts[$status])) {
            $statusCounts[$status]++;
        }
    }
    
    // Format for chart
    return [
        'labels' => ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
        'data' => array_values($statusCounts),
        'backgroundColor' => [
            'rgba(246, 194, 62, 0.8)',  // Pending - yellow
            'rgba(78, 115, 223, 0.8)',   // Confirmed - blue
            'rgba(28, 200, 138, 0.8)',   // Completed - green
            'rgba(231, 74, 59, 0.8)'     // Cancelled - red
        ]
    ];
}

/**
 * Get service type distribution
 */
function getServiceTypeDistribution() {
    $bookings = getItems('bookings.json');
    
    // Initialize counters
    $serviceCounts = [
        'tour' => 0,
        'vehicle' => 0,
        'custom' => 0
    ];
    
    // Count by service type
    foreach ($bookings as $booking) {
        if (isset($booking['tour_id'])) {
            $serviceCounts['tour']++;
        } elseif (isset($booking['vehicle_id'])) {
            $serviceCounts['vehicle']++;
        } elseif (isset($booking['custom_request'])) {
            $serviceCounts['custom']++;
        }
    }
    
    // Format for chart
    return [
        'labels' => ['Tour Packages', 'Vehicle Rentals', 'Custom Requests'],
        'data' => array_values($serviceCounts),
        'backgroundColor' => [
            'rgba(78, 115, 223, 0.8)',  // Tour - blue
            'rgba(54, 185, 204, 0.8)',  // Vehicle - cyan
            'rgba(246, 194, 62, 0.8)'   // Custom - yellow
        ]
    ];
}

/**
 * Get recent booking activity
 */
function getRecentBookingActivity() {
    $bookings = getItems('bookings.json');
    
    // Sort by most recent
    usort($bookings, function($a, $b) {
        return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
    });
    
    // Get only the last 10 bookings
    $recentBookings = array_slice($bookings, 0, 10);
    
    $result = [];
    foreach ($recentBookings as $booking) {
        // Get service name
        $serviceName = 'Unknown Service';
        $serviceType = 'unknown';
        
        if (isset($booking['tour_id'])) {
            $tour = getItemById('tours.json', $booking['tour_id']);
            $serviceName = $tour ? $tour['title'] : 'Unknown Tour';
            $serviceType = 'tour';
        } elseif (isset($booking['vehicle_id'])) {
            $vehicle = getItemById('vehicles.json', $booking['vehicle_id']);
            $serviceName = $vehicle ? $vehicle['name'] : 'Unknown Vehicle';
            $serviceType = 'vehicle';
        } elseif (isset($booking['custom_request'])) {
            $serviceName = 'Custom Request';
            $serviceType = 'custom';
        }
        
        $result[] = [
            'id' => $booking['id'],
            'customer' => $booking['name'] ?? 'Unknown',
            'service_name' => $serviceName,
            'service_type' => $serviceType,
            'status' => $booking['status'] ?? 'pending',
            'created_at' => $booking['created_at'],
            'created_at_formatted' => date('M j, Y g:i A', strtotime($booking['created_at'])),
            'travel_date' => $booking['travel_date'] ?? 'Not specified',
        ];
    }
    
    return $result;
}
