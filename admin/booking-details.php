<?php
// Start session
session_start();

// Include authentication check
try {
    require_once 'check-login.php';
} catch (Exception $e) {
    error_log("Authentication error: " . $e->getMessage());
    header("Location: index.php?error=auth_error"); // Use index.php for login
    exit;
}

// Include database functions
require_once '../includes/db.php';

// Page title and active menu item
$pageTitle = 'Booking Details';
$activePage = 'bookings';

// Get booking ID from URL
$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize variables
$booking = null;
$error = '';
$success = '';

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

// Process status update if submitted
if (isset($_POST['action']) && $_POST['action'] === 'update-status' && isset($_POST['status'])) {
    $newStatus = $_POST['status'];

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

// Load booking with error handling
if ($bookingId > 0) {
    try {
        // Ensure getBookingById exists and works
        if (function_exists('getBookingById')) {
            $booking = getBookingById($bookingId);
            if (!$booking) {
                $error = "Booking not found.";
            }
        } else {
            $error = "Error loading booking details: getBookingById function not found.";
        }
    } catch (Exception $e) {
        error_log("Error fetching booking: " . $e->getMessage());
        $error = "Error loading booking details.";
    }
} else {
    $error = "Invalid booking ID.";
}

// Include header
include 'includes/header.php';
?>

<!-- Booking Details Content -->
<div class="flex flex-col sm:flex-row justify-between items-center mb-6">
    <!-- Moved Page Title to header.php -->
    <a href="bookings.php" class="btn btn-sm btn-outline btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Back to Bookings
    </a>
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

<?php if ($booking): ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Booking Information -->
    <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                    <h2 class="card-title text-primary">Booking Information</h2>
                    <span class="badge <?php echo getStatusColorClass($booking['status']); ?> badge-lg mt-2 sm:mt-0">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                </div>

                <div class="mb-4">
                    <h4 class="text-lg font-semibold font-mono"><?php echo htmlspecialchars($booking['booking_ref']); ?></h4>
                    <p class="text-xs text-gray-500">Created on <?php echo date('d M Y, h:i A', strtotime($booking['created_at'])); ?></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 uppercase">Customer Name</label>
                        <p class="font-semibold"><?php echo htmlspecialchars($booking['name']); ?></p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase">Email</label>
                        <p><?php echo htmlspecialchars($booking['email']); ?></p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase">Phone</label>
                        <p><?php echo htmlspecialchars($booking['phone']); ?></p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase">Number of Guests</label>
                        <p><?php echo $booking['guests']; ?></p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase">Travel Date</label>
                        <p><?php echo date('d M Y', strtotime($booking['travel_date'])); ?></p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase">Return Date</label>
                        <p><?php echo !empty($booking['end_date']) ? date('d M Y', strtotime($booking['end_date'])) : 'N/A'; ?></p>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs text-gray-500 uppercase">Special Requests</label>
                    <p class="text-sm whitespace-pre-wrap"><?php echo !empty($booking['message']) ? htmlspecialchars($booking['message']) : 'No special requests'; ?></p>
                </div>
            </div>
        </div>

         <!-- Additional Details Card -->
         <div class="card bg-base-100 shadow-xl mt-6">
             <div class="card-body">
                <h2 class="card-title text-primary mb-4">Additional Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if (!empty($booking['tour_id']) || !empty($booking['vehicle_id']) || !empty($booking['tour_package'])): ?>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase">
                            <?php
                                if (!empty($booking['tour_id'])) echo 'Tour';
                                elseif (!empty($booking['vehicle_id'])) echo 'Vehicle';
                                elseif (!empty($booking['tour_package'])) echo 'Package';
                            ?>
                        </label>
                        <p>
                            <?php
                                $serviceName = '';
                                if (!empty($booking['tour_id']) && function_exists('getTourNameById')) $serviceName = getTourNameById($booking['tour_id']);
                                elseif (!empty($booking['vehicle_id']) && function_exists('getVehicleNameById')) $serviceName = getVehicleNameById($booking['vehicle_id']);
                                elseif (!empty($booking['tour_package'])) $serviceName = $booking['tour_package'];

                                echo $serviceName ? htmlspecialchars($serviceName) : 'N/A';
                            ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <div>
                        <label class="block text-xs text-gray-500 uppercase">IP Address</label>
                        <p><?php echo !empty($booking['ip_address']) ? htmlspecialchars($booking['ip_address']) : 'Not recorded'; ?></p>
                    </div>

                    <?php if (!empty($booking['updated_at'])): ?>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase">Last Updated</label>
                        <p><?php echo date('d M Y, h:i A', strtotime($booking['updated_at'])); ?></p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase">Updated By</label>
                        <p><?php echo !empty($booking['updated_by']) ? htmlspecialchars($booking['updated_by']) : 'N/A'; ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Actions -->
    <div class="lg:col-span-1">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-primary mb-4">Actions</h2>
                <form method="POST" class="mb-4">
                    <input type="hidden" name="action" value="update-status">
                    <div class="form-control mb-2">
                         <label class="label"><span class="label-text">Update Status</span></label>
                         <select class="select select-bordered w-full" name="status">
                            <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Update Status</button>
                </form>

                <div class="divider"></div>

                <div class="grid grid-cols-2 gap-3">
                    <label for="email_modal" class="btn btn-primary btn-outline w-full">
                        <i class="fas fa-envelope mr-1"></i> Email
                    </label>
                    <a href="generate-invoice.php?id=<?php echo $booking['id']; ?>" class="btn btn-success btn-outline w-full" target="_blank">
                        <i class="fas fa-file-invoice mr-1"></i> Invoice
                    </a>
                    <button class="btn btn-info btn-outline w-full" onclick="printBookingDetails()">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                     <label for="payment_modal" class="btn btn-warning btn-outline w-full">
                        <i class="fas fa-money-bill-wave mr-1"></i> Payment
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Modal -->
<input type="checkbox" id="email_modal" class="modal-toggle" />
<div class="modal" role="dialog">
  <div class="modal-box">
    <h3 class="font-bold text-lg">Send Email to Customer</h3>
    <form id="emailForm" class="py-4 space-y-4">
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
      <label for="email_modal" class="btn btn-ghost">Cancel</label>
      <button type="button" class="btn btn-primary" id="sendEmailBtn">Send Email</button>
    </div>
  </div>
   <label class="modal-backdrop" for="email_modal">Close</label>
</div>

<!-- Payment Modal -->
<input type="checkbox" id="payment_modal" class="modal-toggle" />
<div class="modal" role="dialog">
  <div class="modal-box">
    <h3 class="font-bold text-lg">Record Payment</h3>
     <form id="paymentForm" class="py-4 space-y-4">
        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
        <div class="form-control">
            <label class="label"><span class="label-text">Amount</span></label>
            <label class="input input-bordered flex items-center gap-2">
              <span>₹</span>
              <input type="number" name="amount" class="grow" placeholder="0.00" min="0.01" step="0.01" required />
            </label>
        </div>
        <div class="form-control">
            <label class="label"><span class="label-text">Payment Method</span></label>
            <select class="select select-bordered w-full" name="payment_method" required>
                <option value="" disabled selected>Select Method</option>
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="credit_card">Credit Card</option>
                <option value="upi">UPI</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="form-control">
            <label class="label"><span class="label-text">Payment Date</span></label>
            <input type="date" name="payment_date" class="input input-bordered w-full" value="<?php echo date('Y-m-d'); ?>" required />
        </div>
        <div class="form-control">
            <label class="label"><span class="label-text">Notes (Optional)</span></label>
            <textarea name="notes" class="textarea textarea-bordered h-20" placeholder="Transaction ID, remarks..."></textarea>
        </div>
    </form>
    <div class="modal-action">
      <label for="payment_modal" class="btn btn-ghost">Cancel</label>
      <button type="button" class="btn btn-primary" id="savePaymentBtn">Save Payment</button>
    </div>
  </div>
   <label class="modal-backdrop" for="payment_modal">Close</label>
</div>

<?php else: ?>
<div class="card bg-base-100 shadow-xl">
    <div class="card-body items-center text-center">
        <i class="fas fa-exclamation-circle fa-4x text-warning mb-3"></i>
        <h2 class="card-title">Booking Not Found</h2>
        <p>The booking you are looking for doesn't exist or may have been deleted.</p>
        <div class="card-actions justify-end mt-4">
            <a href="bookings.php" class="btn btn-primary">
                <i class="fas fa-arrow-left mr-2"></i> Back to Bookings
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Toast Container for Notifications -->
<div id="toast-container" class="toast toast-top toast-end z-50"></div>

<!-- Scripts -->
<script>
function printBookingDetails() {
    // Hide elements not needed for print
    const elementsToHide = document.querySelectorAll('.drawer-side, .navbar, .footer, .btn, .modal, .modal-toggle, .modal-backdrop, #toast-container, form');
    elementsToHide.forEach(el => el.style.display = 'none');

    // Optionally add specific print styles here if needed
    // e.g., document.body.classList.add('print-mode');

    window.print();

    // Restore hidden elements after print dialog closes
    elementsToHide.forEach(el => el.style.display = ''); // Restore default display
    // document.body.classList.remove('print-mode');
    // Use location.reload() only if absolutely necessary, as it loses state.
    // location.reload();
}

// Handle email sending
document.addEventListener('DOMContentLoaded', function() {
    const sendEmailBtn = document.getElementById('sendEmailBtn');
    if (sendEmailBtn) {
        sendEmailBtn.addEventListener('click', function() {
            const form = document.getElementById('emailForm');
            if (form && form.checkValidity()) {
                this.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Sending...';
                this.disabled = true;
                const formData = new FormData(form);

                fetch('api/send-email.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    this.innerHTML = 'Send Email';
                    this.disabled = false;
                    document.getElementById('email_modal').checked = false; // Close modal
                    if (data.success) {
                        showNotification('Email sent successfully!', 'success');
                    } else {
                        showNotification('Failed to send email: ' + (data.message || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.innerHTML = 'Send Email';
                    this.disabled = false;
                    showNotification('Network error. Please try again.', 'error');
                });
            } else if (form) {
                form.reportValidity();
            }
        });
    }

     // Handle payment saving (Placeholder - requires backend API)
    const savePaymentBtn = document.getElementById('savePaymentBtn');
    if (savePaymentBtn) {
        savePaymentBtn.addEventListener('click', function() {
             const form = document.getElementById('paymentForm');
             if (form && form.checkValidity()) {
                 this.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Saving...';
                 this.disabled = true;
                 const formData = new FormData(form);

                 // Replace with your actual API endpoint for saving payments
                 fetch('api/record-payment.php', { method: 'POST', body: formData })
                 .then(response => response.json())
                 .then(data => {
                     this.innerHTML = 'Save Payment';
                     this.disabled = false;
                     document.getElementById('payment_modal').checked = false; // Close modal
                     if (data.success) {
                         showNotification('Payment recorded successfully!', 'success');
                         // Optionally update UI or reload data
                     } else {
                         showNotification('Failed to record payment: ' + (data.message || 'Unknown error'), 'error');
                     }
                 })
                 .catch(error => {
                     console.error('Error:', error);
                     this.innerHTML = 'Save Payment';
                     this.disabled = false;
                     showNotification('Network error. Please try again.', 'error');
                 });
             } else if (form) {
                 form.reportValidity();
             }
        });
    }

    // Notification function using DaisyUI toast
    window.showNotification = function(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) return;
        const alertType = type === 'success' ? 'alert-success' : 'alert-error';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';
        const toastElement = document.createElement('div');
        toastElement.className = `alert ${alertType} shadow-lg flex`;
        toastElement.innerHTML = `<i class="fas ${icon} mr-2"></i><span>${message}</span>`;
        toastContainer.appendChild(toastElement);
        setTimeout(() => { toastElement.remove(); }, 5000);
    };
});
</script>
<!-- End Booking Details Content -->
<?php
/** Placeholder functions - implement these in db.php or another included file **/
if (!function_exists('getTourNameById')) {
    function getTourNameById($tourId) { return "Tour Name for ID " . $tourId; }
}
if (!function_exists('getVehicleNameById')) {
    function getVehicleNameById($vehicleId) { return "Vehicle Name for ID " . $vehicleId; }
}

// Include footer
include 'includes/footer.php';
?>
