<?php
// Start session
session_start();

// Include authentication check
try {
    require_once 'check-login.php';
} catch (Exception $e) {
    error_log("Authentication error in bookings.php: " . $e->getMessage());
    header("Location: index.php?error=auth_error"); // Use index.php for login
    exit;
}

// Include database functions
try {
    require_once '../includes/db.php';
    // Include debug helper (optional)
    // require_once 'includes/debug.php';
} catch (Exception $e) {
    error_log("Database error in bookings.php: " . $e->getMessage());
    $errorMessage = "Database connection error. Please try again later.";
}

// Page title and active menu item
$pageTitle = 'Manage Bookings';
$activePage = 'bookings';
$hideDefaultHeader = true;

// Initialize variables
$bookings = [];
$error = $errorMessage ?? '';
$success = '';
$filters = [];

// Process filter parameters if present
$filterStatus = $_GET['status'] ?? '';
$filterSearch = $_GET['search'] ?? '';

if (!empty($filterStatus)) {
    $filters['status'] = $filterStatus;
}
if (!empty($filterSearch)) {
    $filters['search'] = $filterSearch;
}


// Helper function to get status color class (Tailwind/DaisyUI)
function getStatusColorClass($status) {
    switch (strtolower($status)) {
        case 'pending': return 'badge-warning';
        case 'confirmed': return 'badge-primary';
        case 'cancelled': return 'badge-error'; // Use 'error' for danger
        case 'completed': return 'badge-success';
        default: return 'badge-neutral'; // Use 'neutral' for secondary/gray
    }
}

// Process booking status updates
if (isset($_GET['action']) && $_GET['action'] === 'update-status' && isset($_GET['id']) && isset($_GET['status'])) {
    $bookingId = (int)$_GET['id'];
    $newStatus = $_GET['status'];

    // Validate status
    $validStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
    if (in_array($newStatus, $validStatuses)) {
        // Ensure updateBookingStatus exists and works
        if (function_exists('updateBookingStatus') && updateBookingStatus($bookingId, $newStatus, $_SESSION['admin_user']['username'])) {
            $success = "Booking status updated successfully.";
        } else {
            $error = "Failed to update booking status. Function might be missing or failed.";
        }
    } else {
        $error = "Invalid status value.";
    }
}

// Load bookings with error handling
try {
    // Ensure getBookings exists and works
    if (function_exists('getBookings')) {
        $bookings = getBookings($filters);
    } else {
         $error = "Error loading bookings: getBookings function not found.";
         $bookings = [];
    }
} catch (Exception $e) {
    error_log("Error fetching bookings: " . $e->getMessage());
    $error = "Error loading bookings: " . $e->getMessage();
    $bookings = [];
}

// Include header
include 'includes/header.php';
?>

<!-- Bookings Content -->
<!-- Page Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-3">
            <span class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white p-2.5 rounded-xl">
                <i class="fas fa-calendar-alt"></i>
            </span>
            Tour Bookings
        </h1>
        <p class="text-gray-500 text-sm mt-1">Manage and track all tour bookings</p>
    </div>
    <div class="flex gap-2">
        <a href="export-bookings.php" class="admin-btn admin-btn-success admin-btn-sm">
            <i class="fas fa-download"></i>
            <span>Export</span>
        </a>
        <label for="filter_modal" class="admin-btn admin-btn-primary admin-btn-sm cursor-pointer">
            <i class="fas fa-filter"></i>
            <span>Filter</span>
        </label>
    </div>
</div>

<?php if (!empty($error)): ?>
<div role="alert" class="alert alert-error mb-4">
  <i class="fas fa-times-circle"></i>
  <span><?php echo htmlspecialchars($error); ?></span>
</div>
<?php endif; ?>

<?php if (!empty($success)): ?>
<div role="alert" class="alert alert-success mb-4">
  <i class="fas fa-check-circle"></i>
  <span><?php echo htmlspecialchars($success); ?></span>
</div>
<?php endif; ?>

<!-- Filter Summary -->
<?php if (!empty($filters)): ?>
<div class="card bg-base-100 shadow-md mb-4 border-l-4 border-primary">
    <div class="card-body p-4">
        <div class="flex flex-col sm:flex-row justify-between items-center">
            <div class="mb-2 sm:mb-0">
                <strong class="mr-2">Active Filters:</strong>
                <?php
                $filterLabels = [];
                if (isset($filters['status'])) {
                    $filterLabels[] = "Status: <span class='badge " . getStatusColorClass($filters['status']) . "'>" . ucfirst($filters['status']) . "</span>";
                }
                if (isset($filters['search'])) {
                    $filterLabels[] = "Search: <span class='font-semibold'>" . htmlspecialchars($filters['search']) . "</span>";
                }
                echo implode(' <span class="mx-2 text-gray-300">|</span> ', $filterLabels);
                ?>
            </div>
            <a href="bookings.php" class="btn btn-xs btn-outline btn-secondary">Clear Filters</a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Bookings List Card -->
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title text-primary mb-4">Bookings List</h2>
        <?php if (empty($bookings)): ?>
        <div role="alert" class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <span>No bookings found. <?php echo !empty($filters) ? 'Try changing your filters.' : ''; ?></span>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Ref. No.</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Guests</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td class="font-mono text-xs"><?php echo htmlspecialchars($booking['booking_ref']); ?></td>
                        <td><?php echo htmlspecialchars($booking['name']); ?></td>
                        <td>
                            <div class="text-sm"><?php echo htmlspecialchars($booking['email']); ?></div>
                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($booking['phone']); ?></div>
                        </td>
                        <td>
                            <?php
                            $serviceType = 'Other';
                            $serviceName = '';
                            if (!empty($booking['tour_id']) && !empty($booking['tour_name'])) {
                                $serviceType = 'Tour';
                                $serviceName = $booking['tour_name'];
                                echo '<span class="badge badge-info badge-outline">' . $serviceType . '</span>';
                            } elseif (!empty($booking['vehicle_id']) && !empty($booking['vehicle_name'])) {
                                $serviceType = 'Vehicle';
                                $serviceName = $booking['vehicle_name'];
                                echo '<span class="badge badge-warning badge-outline">' . $serviceType . '</span>';
                            } elseif (!empty($booking['tour_package'])) {
                                $serviceType = 'Package';
                                $serviceName = $booking['tour_package'];
                                echo '<span class="badge badge-primary badge-outline">' . $serviceType . '</span>';
                            } else {
                                 echo '<span class="badge badge-neutral badge-outline">' . $serviceType . '</span>';
                            }
                            if ($serviceName) {
                                echo '<div class="text-xs mt-1">' . htmlspecialchars($serviceName) . '</div>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            echo date('d M Y', strtotime($booking['travel_date']));
                            if (!empty($booking['end_date'])) {
                                echo '<div class="text-xs text-gray-500">to ' . date('d M Y', strtotime($booking['end_date'])) . '</div>';
                            }
                            ?>
                        </td>
                        <td class="text-center"><?php echo $booking['guests']; ?></td>
                        <td>
                            <div class="dropdown dropdown-end">
                              <div tabindex="0" role="button" class="badge <?php echo getStatusColorClass($booking['status']); ?> cursor-pointer"><?php echo ucfirst($booking['status']); ?> <i class="fas fa-caret-down ml-1"></i></div>
                              <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                <li><a href="bookings.php?action=update-status&id=<?php echo $booking['id']; ?>&status=pending">Pending</a></li>
                                <li><a href="bookings.php?action=update-status&id=<?php echo $booking['id']; ?>&status=confirmed">Confirmed</a></li>
                                <li><a href="bookings.php?action=update-status&id=<?php echo $booking['id']; ?>&status=completed">Completed</a></li>
                                <li><hr class="my-1"></li>
                                <li><a class="text-error" href="bookings.php?action=update-status&id=<?php echo $booking['id']; ?>&status=cancelled">Cancelled</a></li>
                              </ul>
                            </div>
                        </td>
                        <td class="space-x-1 whitespace-nowrap">
                            <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="btn btn-xs btn-info btn-outline">
                                <i class="fas fa-eye"></i>
                            </a>
                             <label for="email_modal_<?php echo $booking['id']; ?>" class="btn btn-xs btn-primary btn-outline">
                                <i class="fas fa-envelope"></i>
                            </label>
                     
                        </td>
                    </tr>
                     <!-- Email Modal Per Row -->
                    <input type="checkbox" id="email_modal_<?php echo $booking['id']; ?>" class="modal-toggle" />
                    <div class="modal" role="dialog">
                      <div class="modal-box">
                        <h3 class="font-bold text-lg">Send Email to <?php echo htmlspecialchars($booking['name']); ?></h3>
                        <form class="py-4 space-y-4 email-form" data-booking-id="<?php echo $booking['id']; ?>">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                            <div class="form-control">
                                <label class="label"><span class="label-text">To</span></label>
                                <input type="email" name="email_to" value="<?php echo htmlspecialchars($booking['email']); ?>" class="input input-bordered" readonly />
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text">Subject</span></label>
                                <input type="text" name="subject" placeholder="Email Subject" class="input input-bordered" value="Regarding your booking <?php echo htmlspecialchars($booking['booking_ref']); ?>" required />
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text">Message</span></label>
                                <textarea name="message" class="textarea textarea-bordered h-24" placeholder="Your message..." required></textarea>
                            </div>
                             <div class="form-control">
                                <label class="label cursor-pointer justify-start space-x-2">
                                  <input type="checkbox" name="include_details" checked="checked" class="checkbox checkbox-sm" />
                                  <span class="label-text">Include booking details</span>
                                </label>
                              </div>
                        </form>
                        <div class="modal-action">
                          <label for="email_modal_<?php echo $booking['id']; ?>" class="btn btn-ghost">Cancel</label>
                          <button type="button" class="btn btn-primary send-email-btn">Send Email</button>
                        </div>
                      </div>
                       <label class="modal-backdrop" for="email_modal_<?php echo $booking['id']; ?>">Close</label>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<!-- End Bookings List Card -->

<!-- Filter Modal -->
<input type="checkbox" id="filter_modal" class="modal-toggle" />
<div class="modal" role="dialog">
  <div class="modal-box">
    <h3 class="font-bold text-lg">Filter Bookings</h3>
    <form action="bookings.php" method="GET" id="filterForm" class="py-4 space-y-4">
        <div class="form-control">
            <label class="label"><span class="label-text">Search</span></label>
            <input type="text" name="search" placeholder="Name, Email, Phone, Ref" class="input input-bordered w-full" value="<?php echo htmlspecialchars($filterSearch); ?>">
        </div>
        <div class="form-control">
            <label class="label"><span class="label-text">Status</span></label>
            <select class="select select-bordered w-full" name="status">
                <option value="">All Statuses</option>
                <option value="pending" <?php echo ($filterStatus === 'pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="confirmed" <?php echo ($filterStatus === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                <option value="completed" <?php echo ($filterStatus === 'completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo ($filterStatus === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
        <!-- Add more filters here -->
    </form>
    <div class="modal-action">
      <label for="filter_modal" class="btn btn-ghost">Close</label>
      <button type="button" class="btn btn-primary" onclick="document.getElementById('filterForm').submit();">Apply Filters</button>
    </div>
  </div>
   <label class="modal-backdrop" for="filter_modal">Close</label>
</div>

<!-- Toast Container for Notifications -->
<div id="toast-container" class="toast toast-top toast-end z-50"></div>

<!-- Add custom JavaScript for bookings page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remove DataTables initialization

    // Handle email form submission via delegation
    document.addEventListener('click', function(event) {
        const target = event.target;
        if (target.classList.contains('send-email-btn')) {
            const modal = target.closest('.modal');
            const form = modal.querySelector('.email-form');

            if (form && form.checkValidity()) {
                // Show sending status
                target.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Sending...';
                target.disabled = true;

                const formData = new FormData(form);

                fetch('api/send-email.php', { // Ensure this API endpoint exists and works
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    target.innerHTML = 'Send Email';
                    target.disabled = false;

                    // Close modal by finding the corresponding checkbox toggle
                    const modalId = modal.querySelector('.modal-toggle')?.id || modal.closest('[id^="email_modal_"]')?.id;
                    if (modalId) {
                         const checkbox = document.getElementById(modalId);
                         if(checkbox) checkbox.checked = false;
                    }


                    if (data.success) {
                        showNotification('Email sent successfully!', 'success');
                    } else {
                        showNotification('Failed to send email: ' + (data.message || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    target.innerHTML = 'Send Email';
                    target.disabled = false;
                    showNotification('Network error. Please try again.', 'error');
                });
            } else if (form) {
                // Trigger browser validation UI
                form.reportValidity();
            }
        }
    });

    // Notification function using DaisyUI toast
    window.showNotification = function(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) return;

        const alertType = type === 'success' ? 'alert-success' : 'alert-error';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';

        const toastElement = document.createElement('div');
        toastElement.className = `alert ${alertType} shadow-lg flex`;
        toastElement.innerHTML = `
            <i class="fas ${icon} mr-2"></i>
            <span>${message}</span>
        `;

        toastContainer.appendChild(toastElement);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            toastElement.remove();
        }, 5000);
    };
});
</script>
<!-- End Bookings Content -->
<?php
// Include footer
include 'includes/footer.php';
?>
