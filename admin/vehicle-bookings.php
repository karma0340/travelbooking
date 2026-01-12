<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

// Get user data
$user = $_SESSION['admin_user'];

// Page title and active menu item
$pageTitle = 'Vehicle Bookings';
$activePage = 'vehicle-bookings';
$hideDefaultHeader = true;

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorMessage = "Security verification failed.";
    } else {
        $bookingId = (int)$_POST['booking_id'];
        $status = $_POST['status'];
        
        if (updateBookingStatus($bookingId, $status, $user['username'])) {
            $successMessage = "Booking status updated successfully.";
        } else {
            $errorMessage = "Failed to update booking status.";
        }
    }
}

// Get vehicle bookings
// Note: getBookings() returns all bookings, so we filter for those with vehicle_id
$allBookings = getBookings();
$bookings = array_filter($allBookings, function($booking) {
    return !empty($booking['vehicle_id']);
});

// Get vehicle data for each booking
foreach ($bookings as &$booking) {
    if (!empty($booking['vehicle_id'])) {
        $vehicle = getVehicleById($booking['vehicle_id']);
        $booking['vehicle'] = $vehicle ?: [
            'name' => 'Unknown Vehicle',
            'seats' => '?',
            'bags' => '?'
        ];
    }
}
unset($booking); // Break reference

// Helper function to get status color
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

<!-- Vehicle Bookings Content -->
<!-- Page Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-3">
            <span class="bg-gradient-to-br from-cyan-500 to-blue-600 text-white p-2.5 rounded-xl">
                <i class="fas fa-car"></i>
            </span>
            Vehicle Bookings
        </h1>
        <p class="text-gray-500 text-sm mt-1">Manage all vehicle rental bookings</p>
    </div>
    <div class="flex gap-2">
        <a href="export-bookings.php?type=vehicle" class="admin-btn admin-btn-success admin-btn-sm">
            <i class="fas fa-download"></i>
            <span>Export</span>
        </a>
        <a href="vehicles.php" class="admin-btn admin-btn-outline admin-btn-sm">
            <i class="fas fa-car"></i>
            <span>Manage Vehicles</span>
        </a>
    </div>
</div>

<!-- Bookings Card -->
<div class="admin-card">
    <div class="overflow-x-auto">
        <?php if (empty($bookings)): ?>
        <div class="admin-empty-state">
            <div class="icon">
                <i class="fas fa-car"></i>
            </div>
            <h3>No vehicle bookings found</h3>
            <p>Vehicle bookings will appear here when customers book vehicles</p>
            <a href="vehicles.php" class="admin-btn admin-btn-primary">
                <i class="fas fa-car"></i>
                <span>Manage Vehicles</span>
            </a>
        </div>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Booking Ref</th>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Dates</th>
                    <th>Guests</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td class="font-mono text-xs"><?php echo htmlspecialchars($booking['booking_ref']); ?></td>
                    <td>
                        <div class="font-semibold"><?php echo htmlspecialchars($booking['name']); ?></div>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($booking['email']); ?></div>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($booking['phone']); ?></div>
                    </td>
                    <td>
                        <?php if (isset($booking['vehicle'])): ?>
                        <div class="font-semibold"><?php echo htmlspecialchars($booking['vehicle']['name']); ?></div>
                        <div class="text-xs">
                            <span class="badge badge-sm badge-ghost"><?php echo $booking['vehicle']['seats']; ?> seats</span>
                            <span class="badge badge-sm badge-ghost"><?php echo $booking['vehicle']['bags']; ?> bags</span>
                        </div>
                        <?php else: ?>
                        <span class="text-gray-500">Unknown vehicle</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="text-sm"><i class="fas fa-calendar-day mr-1 text-info"></i> <?php echo date('d M Y', strtotime($booking['travel_date'])); ?></div>
                        <?php if (!empty($booking['end_date'])): ?>
                        <div class="text-xs text-gray-500"><i class="fas fa-calendar-day mr-1"></i> <?php echo date('d M Y', strtotime($booking['end_date'])); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge"><?php echo $booking['guests']; ?></span>
                    </td>
                    <td>
                        <div class="dropdown dropdown-end">
                            <div tabindex="0" role="button" class="badge badge-lg <?php echo getStatusColorClass($booking['status']); ?> cursor-pointer gap-1">
                                <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                                <i class="fas fa-caret-down text-xs"></i>
                            </div>
                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-40">
                                <li>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="status" value="pending">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="submit" class="text-warning">Pending</button>
                                    </form>
                                </li>
                                <li>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="status" value="confirmed">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="submit" class="text-primary">Confirmed</button>
                                    </form>
                                </li>
                                <li>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="status" value="completed">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="submit" class="text-success">Completed</button>
                                    </form>
                                </li>
                                <li>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="status" value="cancelled">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="submit" class="text-error">Cancelled</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                    <td class="text-right">
                        <div class="admin-actions justify-end">
                            <button onclick="showBookingDetails(<?php echo $booking['id']; ?>)" class="admin-action-btn view" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="admin-action-btn edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- View Booking Modal -->
<dialog id="booking_details_modal" class="modal">
    <div class="modal-box max-w-3xl">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <h3 class="font-bold text-lg mb-4" id="modal_title">Booking Details</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="bg-base-200 p-4 rounded-box">
                <h4 class="font-bold text-sm uppercase text-gray-500 mb-2">Booking Information</h4>
                <table class="w-full">
                    <tr>
                        <td class="text-sm font-semibold pr-4 py-1">Reference</td>
                        <td class="text-sm py-1" id="booking_ref"></td>
                    </tr>
                    <tr>
                        <td class="text-sm font-semibold pr-4 py-1">Status</td>
                        <td class="text-sm py-1" id="booking_status"></td>
                    </tr>
                    <tr>
                        <td class="text-sm font-semibold pr-4 py-1">Vehicle</td>
                        <td class="text-sm py-1" id="booking_vehicle"></td>
                    </tr>
                    <tr>
                        <td class="text-sm font-semibold pr-4 py-1">Travel Date</td>
                        <td class="text-sm py-1" id="booking_travel_date"></td>
                    </tr>
                    <tr>
                        <td class="text-sm font-semibold pr-4 py-1">Return Date</td>
                        <td class="text-sm py-1" id="booking_end_date"></td>
                    </tr>
                    <tr>
                        <td class="text-sm font-semibold pr-4 py-1">Passengers</td>
                        <td class="text-sm py-1" id="booking_guests"></td>
                    </tr>
                </table>
            </div>
            
            <div class="bg-base-200 p-4 rounded-box">
                <h4 class="font-bold text-sm uppercase text-gray-500 mb-2">Customer Information</h4>
                <table class="w-full">
                    <tr>
                        <td class="text-sm font-semibold pr-4 py-1">Name</td>
                        <td class="text-sm py-1" id="customer_name"></td>
                    </tr>
                    <tr>
                        <td class="text-sm font-semibold pr-4 py-1">Email</td>
                        <td class="text-sm py-1" id="customer_email"></td>
                    </tr>
                    <tr>
                        <td class="text-sm font-semibold pr-4 py-1">Phone</td>
                        <td class="text-sm py-1" id="customer_phone"></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="bg-base-200 p-4 rounded-box mb-4">
            <h4 class="font-bold text-sm uppercase text-gray-500 mb-2">Special Requests/Message</h4>
            <p class="text-sm py-2" id="customer_message"></p>
        </div>
        
        <div class="modal-action">
            <form method="dialog">
                <button class="btn">Close</button>
            </form>
            <a id="view_full_details_btn" href="#" class="btn btn-primary">View Full Details</a>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<!-- Custom JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check for success/error messages and show toast notifications
    <?php if (isset($successMessage)): ?>
    if (window.showNotification) {
        window.showNotification('<?php echo addslashes($successMessage); ?>', 'success');
    }
    <?php endif; ?>
    
    <?php if (isset($errorMessage)): ?>
    if (window.showNotification) {
        window.showNotification('<?php echo addslashes($errorMessage); ?>', 'error');
    }
    <?php endif; ?>
});

// Function to show booking details modal
function showBookingDetails(bookingId) {
    // Fetch booking data with AJAX
    fetch('api/get-booking.php?id=' + bookingId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const booking = data.booking;
                
                // Populate modal with booking details
                document.getElementById('modal_title').textContent = 'Booking: ' + booking.booking_ref;
                document.getElementById('booking_ref').textContent = booking.booking_ref;
                
                const statusBadge = document.createElement('span');
                statusBadge.className = 'badge ' + getStatusColorClass(booking.status);
                statusBadge.textContent = booking.status.charAt(0).toUpperCase() + booking.status.slice(1);
                document.getElementById('booking_status').innerHTML = '';
                document.getElementById('booking_status').appendChild(statusBadge);
                
                document.getElementById('booking_vehicle').textContent = booking.vehicle ? booking.vehicle.name : 'Unknown';
                document.getElementById('booking_travel_date').textContent = formatDate(booking.travel_date);
                document.getElementById('booking_end_date').textContent = booking.end_date ? formatDate(booking.end_date) : 'N/A';
                document.getElementById('booking_guests').textContent = booking.guests;
                
                document.getElementById('customer_name').textContent = booking.name;
                document.getElementById('customer_email').textContent = booking.email;
                document.getElementById('customer_phone').textContent = booking.phone;
                
                document.getElementById('customer_message').textContent = booking.message ? booking.message : 'No special requests provided.';
                
                // Update full details button
                document.getElementById('view_full_details_btn').href = 'booking-details.php?id=' + booking.id;
                
                // Show the modal
                document.getElementById('booking_details_modal').showModal();
            } else {
                if (window.showNotification) window.showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.showNotification) window.showNotification('Failed to load booking details', 'error');
        });
}

// Helper function to format dates
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Helper function to get status color class (JS version)
function getStatusColorClass(status) {
    switch (status.toLowerCase()) {
        case 'pending': return 'badge-warning';
        case 'confirmed': return 'badge-primary';
        case 'cancelled': return 'badge-error';
        case 'completed': return 'badge-success';
        default: return 'badge-neutral';
    }
}
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
