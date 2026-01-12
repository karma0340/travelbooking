<?php
// Include authentication check
require_once 'check-login.php';

// Include database functions
require_once '../includes/db.php';

// Page title
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// Get dashboard statistics
try {
    $stats = getDashboardStats();
    
    // Check that $stats is actually an array first
    if (!is_array($stats)) {
        $stats = [];
        $errorMessage = "Dashboard statistics data is not in the expected format.";
    }
    
    // Ensure all required data points exist in the array
    if (!isset($stats['total_bookings'])) $stats['total_bookings'] = 0;
    if (!isset($stats['booking_status'])) $stats['booking_status'] = [];
    if (!isset($stats['booking_status']['pending'])) $stats['booking_status']['pending'] = 0;
    if (!isset($stats['booking_status']['confirmed'])) $stats['booking_status']['confirmed'] = 0;
    if (!isset($stats['booking_status']['cancelled'])) $stats['booking_status']['cancelled'] = 0;
    if (!isset($stats['booking_status']['completed'])) $stats['booking_status']['completed'] = 0;
    if (!isset($stats['total_tours'])) $stats['total_tours'] = 0;
    if (!isset($stats['total_vehicles'])) $stats['total_vehicles'] = 0;
    if (!isset($stats['recent_activity'])) $stats['recent_activity'] = [];
} catch (Exception $e) {
    $errorMessage = "Error loading dashboard stats: " . $e->getMessage();
    $stats = [
        'total_bookings' => 0,
        'booking_status' => [
            'pending' => 0,
            'confirmed' => 0,
            'cancelled' => 0,
            'completed' => 0
        ],
        'total_tours' => 0,
        'total_vehicles' => 0,
        'recent_activity' => []
    ];
}

// Helper function to get status color for badges
function getStatusColorClass($status) {
    switch (strtolower($status)) {
        case 'pending': return 'badge-warning';
        case 'confirmed': return 'badge-primary';
        case 'cancelled': return 'badge-error';
        case 'completed': return 'badge-success';
        default: return 'badge-neutral';
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Stats Cards with Gradient Backgrounds -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
    <!-- Total Bookings -->
    <div class="card bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="card-body p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-sm font-medium uppercase opacity-90 tracking-wide">Total Bookings</h2>
                    <p class="text-4xl font-bold mt-2"><?php echo number_format($stats['total_bookings']); ?></p>
                    <div class="flex items-center mt-3 text-sm opacity-80">
                        <i class="fas fa-arrow-up mr-1"></i>
                        <span>All time</span>
                    </div>
                </div>
                <div class="bg-white/20 p-4 rounded-2xl">
                    <i class="fas fa-calendar-check fa-2x"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-white/20">
                <a href="bookings.php" class="text-sm flex items-center hover:underline opacity-90 hover:opacity-100">
                    View all bookings <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Total Tours -->
    <div class="card bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="card-body p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-sm font-medium uppercase opacity-90 tracking-wide">Total Tours</h2>
                    <p class="text-4xl font-bold mt-2"><?php echo number_format($stats['total_tours']); ?></p>
                    <div class="flex items-center mt-3 text-sm opacity-80">
                        <i class="fas fa-route mr-1"></i>
                        <span>Active packages</span>
                    </div>
                </div>
                <div class="bg-white/20 p-4 rounded-2xl">
                    <i class="fas fa-map-marked-alt fa-2x"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-white/20">
                <a href="tours.php" class="text-sm flex items-center hover:underline opacity-90 hover:opacity-100">
                    Manage tours <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Total Vehicles -->
    <div class="card bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="card-body p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-sm font-medium uppercase opacity-90 tracking-wide">Total Vehicles</h2>
                    <p class="text-4xl font-bold mt-2"><?php echo number_format($stats['total_vehicles']); ?></p>
                    <div class="flex items-center mt-3 text-sm opacity-80">
                        <i class="fas fa-car-side mr-1"></i>
                        <span>In fleet</span>
                    </div>
                </div>
                <div class="bg-white/20 p-4 rounded-2xl">
                    <i class="fas fa-car fa-2x"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-white/20">
                <a href="vehicles.php" class="text-sm flex items-center hover:underline opacity-90 hover:opacity-100">
                    Manage vehicles <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Pending Bookings -->
    <div class="card bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="card-body p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-sm font-medium uppercase opacity-90 tracking-wide">Pending</h2>
                    <p class="text-4xl font-bold mt-2"><?php echo number_format($stats['booking_status']['pending'] ?? 0); ?></p>
                    <div class="flex items-center mt-3 text-sm opacity-80">
                        <i class="fas fa-clock mr-1"></i>
                        <span>Awaiting action</span>
                    </div>
                </div>
                <div class="bg-white/20 p-4 rounded-2xl">
                    <i class="fas fa-hourglass-half fa-2x"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-white/20">
                <a href="bookings.php?status=pending" class="text-sm flex items-center hover:underline opacity-90 hover:opacity-100">
                    View pending <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-6">
    <!-- Recent Bookings Card -->
    <div class="xl:col-span-2">
        <div class="card bg-base-100 shadow-lg border border-gray-100">
            <div class="card-body">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="card-title text-gray-800 flex items-center">
                        <span class="bg-primary/10 p-2 rounded-lg mr-3">
                            <i class="fas fa-calendar-alt text-primary"></i>
                        </span>
                        Recent Bookings
                    </h2>
                    <a href="bookings.php" class="btn btn-sm btn-primary gap-2">
                        <span>View All</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <?php if (empty($stats['recent_activity'])): ?>
                <div class="text-center py-12">
                    <div class="bg-gray-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-calendar-times fa-2x text-gray-400"></i>
                    </div>
                    <p class="text-gray-600 font-medium mb-2">No recent bookings</p>
                    <p class="text-gray-400 text-sm mb-4">Start by creating your first booking</p>
                    <a href="bookings.php" class="btn btn-primary btn-sm gap-2">
                        <i class="fas fa-list"></i>
                        View All Bookings
                    </a>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="table w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-gray-600 font-semibold">Reference</th>
                                <th class="text-gray-600 font-semibold">Customer</th>
                                <th class="text-gray-600 font-semibold">Type</th>
                                <th class="text-gray-600 font-semibold">Travel Date</th>
                                <th class="text-gray-600 font-semibold">Status</th>
                                <th class="text-right text-gray-600 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recent_activity'] as $booking): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td>
                                    <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($booking['booking_ref']); ?></span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-primary/10 text-primary rounded-full w-8 h-8">
                                                <span class="text-xs font-bold"><?php echo strtoupper(substr($booking['name'], 0, 2)); ?></span>
                                            </div>
                                        </div>
                                        <span class="font-medium"><?php echo htmlspecialchars($booking['name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($booking['tour_id'])): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">
                                        <i class="fas fa-map-marker-alt"></i> Tour
                                    </span>
                                    <?php elseif (!empty($booking['vehicle_id'])): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-cyan-100 text-cyan-700 rounded-full text-xs font-medium">
                                        <i class="fas fa-car"></i> Vehicle
                                    </span>
                                    <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                                        <i class="fas fa-box"></i> Package
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                        <?php echo date('d M Y', strtotime($booking['travel_date'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo getStatusColorClass($booking['status']); ?> badge-sm gap-1">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-ghost text-primary hover:bg-primary/10 gap-1">
                                        <span>Details</span>
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Links & Activity -->
    <div class="xl:col-span-1 flex flex-col gap-4 md:gap-6">
        <!-- Quick Actions Card -->
        <div class="card bg-base-100 shadow-lg border border-gray-100">
            <div class="card-body">
                <h2 class="card-title text-gray-800 mb-4 flex items-center">
                    <span class="bg-primary/10 p-2 rounded-lg mr-3">
                        <i class="fas fa-bolt text-primary"></i>
                    </span>
                    Quick Actions
                </h2>
                
                <div class="space-y-3">
                    <a href="tour-edit.php" class="flex items-center p-3 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl hover:shadow-md transition-all group">
                        <div class="bg-indigo-500 text-white p-3 rounded-xl mr-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Add New Tour</p>
                            <p class="text-xs text-gray-500">Create a new tour package</p>
                        </div>
                        <i class="fas fa-chevron-right ml-auto text-gray-400 group-hover:text-indigo-500"></i>
                    </a>
                    
                    <a href="vehicle-edit.php" class="flex items-center p-3 bg-gradient-to-r from-cyan-50 to-blue-50 rounded-xl hover:shadow-md transition-all group">
                        <div class="bg-cyan-500 text-white p-3 rounded-xl mr-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-car-alt"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Add Vehicle</p>
                            <p class="text-xs text-gray-500">Add to your fleet</p>
                        </div>
                        <i class="fas fa-chevron-right ml-auto text-gray-400 group-hover:text-cyan-500"></i>
                    </a>
                    
                    <a href="bookings.php?status=pending" class="flex items-center p-3 bg-gradient-to-r from-amber-50 to-orange-50 rounded-xl hover:shadow-md transition-all group">
                        <div class="bg-amber-500 text-white p-3 rounded-xl mr-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Pending Bookings</p>
                            <p class="text-xs text-gray-500">Review awaiting bookings</p>
                        </div>
                        <i class="fas fa-chevron-right ml-auto text-gray-400 group-hover:text-amber-500"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Booking Status Summary -->
        <div class="card bg-base-100 shadow-lg border border-gray-100">
            <div class="card-body">
                <h2 class="card-title text-gray-800 mb-4 flex items-center">
                    <span class="bg-primary/10 p-2 rounded-lg mr-3">
                        <i class="fas fa-chart-pie text-primary"></i>
                    </span>
                    Booking Overview
                </h2>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-amber-50 rounded-xl">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-amber-500 rounded-full mr-3"></div>
                            <span class="text-gray-700">Pending</span>
                        </div>
                        <span class="text-2xl font-bold text-amber-600"><?php echo $stats['booking_status']['pending'] ?? 0; ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-indigo-50 rounded-xl">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-indigo-500 rounded-full mr-3"></div>
                            <span class="text-gray-700">Confirmed</span>
                        </div>
                        <span class="text-2xl font-bold text-indigo-600"><?php echo $stats['booking_status']['confirmed'] ?? 0; ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-xl">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-emerald-500 rounded-full mr-3"></div>
                            <span class="text-gray-700">Completed</span>
                        </div>
                        <span class="text-2xl font-bold text-emerald-600"><?php echo $stats['booking_status']['completed'] ?? 0; ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-xl">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                            <span class="text-gray-700">Cancelled</span>
                        </div>
                        <span class="text-2xl font-bold text-red-600"><?php echo $stats['booking_status']['cancelled'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
