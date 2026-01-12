<?php
define('INCLUDED_FROM_ADMIN_PAGE', true);
define('SKIP_ANALYTICS', true);

require_once '../includes/auth.php';
require_once '../includes/db.php';

// Ensure user is logged in
requireLogin('index.php');

// Process filter parameters
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$filterDateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filterDateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$filterType = isset($_GET['type']) ? $_GET['type'] : '';

// Get export filename
$filename = "bookings-export-" . date('Y-m-d');
if ($filterStatus) {
    $filename .= "-" . $filterStatus;
}

// Set headers to download CSV file
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM (to ensure Excel handles UTF-8 correctly)
fprintf($output, "\xEF\xBB\xBF");

// CSV header
fputcsv($output, [
    'ID', 'Customer Name', 'Email', 'Phone', 'Service Type',
    'Service Name', 'Travel Date', 'Travelers', 'Status',
    'Created Date', 'Message'
]);

// Get all bookings
$bookings = getItems('bookings.json');

// Sort by most recent
usort($bookings, function($a, $b) {
    return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
});

// Apply filters
$filteredBookings = array_filter($bookings, function($booking) use ($filterStatus, $filterDateFrom, $filterDateTo, $filterType) {
    // Filter by status
    if ($filterStatus && ($booking['status'] ?? 'pending') !== $filterStatus) {
        return false;
    }
    
    // Filter by date range
    if ($filterDateFrom) {
        $bookingDate = strtotime($booking['created_at']);
        $fromDate = strtotime($filterDateFrom);
        if ($bookingDate < $fromDate) {
            return false;
        }
    }
    
    if ($filterDateTo) {
        $bookingDate = strtotime($booking['created_at']);
        $toDate = strtotime($filterDateTo . ' 23:59:59');
        if ($bookingDate > $toDate) {
            return false;
        }
    }
    
    // Filter by service type
    if ($filterType) {
        if ($filterType === 'tour' && !isset($booking['tour_id'])) {
            return false;
        } elseif ($filterType === 'vehicle' && !isset($booking['vehicle_id'])) {
            return false;
        } elseif ($filterType === 'custom' && !isset($booking['custom_request'])) {
            return false;
        }
    }
    
    return true;
});

// Write bookings to CSV
foreach ($filteredBookings as $booking) {
    // Determine service type and name
    $serviceType = 'Unknown';
    $serviceName = 'N/A';
    
    if (isset($booking['tour_id'])) {
        $serviceType = 'Tour';
        $tour = getItemById('tours.json', $booking['tour_id']);
        if ($tour) {
            $serviceName = $tour['title'];
        }
    } elseif (isset($booking['vehicle_id'])) {
        $serviceType = 'Vehicle';
        $vehicle = getItemById('vehicles.json', $booking['vehicle_id']);
        if ($vehicle) {
            $serviceName = $vehicle['name'];
        }
    } elseif (isset($booking['custom_request'])) {
        $serviceType = 'Custom';
        $serviceName = 'Custom Request';
    }
    
    // Clean message text - remove line breaks for CSV compatibility
    $message = isset($booking['message']) ? str_replace(["\r\n", "\n", "\r"], " ", $booking['message']) : '';
    
    // Write booking data row with proper encoding
    fputcsv($output, [
        $booking['id'],
        $booking['name'] ?? 'N/A',
        $booking['email'] ?? 'N/A',
        $booking['phone'] ?? 'N/A',
        $serviceType,
        $serviceName,
        $booking['travel_date'] ?? 'Not specified',
        $booking['travelers'] ?? 'N/A',
        ucfirst($booking['status'] ?? 'pending'),
        date('Y-m-d H:i', strtotime($booking['created_at'])),
        $message
    ]);
}

// Close output stream
fclose($output);
exit;
