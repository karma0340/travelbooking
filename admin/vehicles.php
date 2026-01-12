<?php
session_start();

try {
    require_once 'check-login.php';
} catch (Exception $e) {
    error_log("Authentication error: " . $e->getMessage());
    header("Location: login.php?error=auth_error");
    exit;
}

try {
    require_once '../includes/db.php';
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    include 'includes/header.php';
    echo '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Database connection error.</div>';
    include 'includes/footer.php';
    exit;
}

$pageTitle = 'Manage Vehicles';
$activePage = 'vehicles';
$hideDefaultHeader = true;

$error = '';
$success = '';
$vehicleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$vehicles = [];

if ($action == 'delete' && $vehicleId) {
    try {
        if (deleteVehicle($vehicleId)) {
            $success = "Vehicle deleted successfully!";
            header("Location: vehicles.php?success=" . urlencode($success));
            exit;
        } else {
            $error = "Error deleting vehicle.";
        }
    } catch (Exception $e) {
        error_log("Error deleting vehicle: " . $e->getMessage());
        $error = "Error deleting vehicle. Please try again.";
    }
}

try {
    $vehicles = getVehicles(null);
    if (!is_array($vehicles)) {
        $vehicles = [];
    }
} catch (Exception $e) {
    error_log("Error fetching vehicles: " . $e->getMessage());
    $error = "Error loading vehicles.";
    $vehicles = [];
}

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-3">
            <span class="bg-gradient-to-br from-cyan-500 to-blue-600 text-white p-2.5 rounded-xl">
                <i class="fas fa-car"></i>
            </span>
            Manage Vehicles
        </h1>
        <p class="text-gray-500 text-sm mt-1">Add and manage your vehicle fleet</p>
    </div>
    <a href="vehicle-edit.php" class="admin-btn admin-btn-primary">
        <i class="fas fa-plus"></i>
        <span>Add Vehicle</span>
    </a>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error mb-4">
    <i class="fas fa-exclamation-circle"></i>
    <span><?php echo $error; ?></span>
</div>
<?php endif; ?>

<?php if (!empty($success) || isset($_GET['success'])): ?>
<div class="alert alert-success mb-4">
    <i class="fas fa-check-circle"></i>
    <span><?php echo $success ?: $_GET['success']; ?></span>
</div>
<?php endif; ?>

<!-- Vehicles Table Card -->
<div class="admin-card">
    <div class="overflow-x-auto">
        <?php if (empty($vehicles)): ?>
        <div class="admin-empty-state">
            <div class="icon">
                <i class="fas fa-car"></i>
            </div>
            <h3>No vehicles found</h3>
            <p>Start building your fleet by adding the first vehicle</p>
            <a href="vehicle-edit.php" class="admin-btn admin-btn-primary">
                <i class="fas fa-plus"></i>
                <span>Add First Vehicle</span>
            </a>
        </div>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Vehicle Name</th>
                    <th>Seats</th>
                    <th>Price/Day</th>
                    <th>Status</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehicles as $vehicle): ?>
                <tr>
                    <td>
                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">#<?php echo $vehicle['id']; ?></span>
                    </td>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="admin-avatar admin-avatar-primary">
                                <i class="fas fa-car"></i>
                            </div>
                            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($vehicle['name']); ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="flex items-center gap-1.5 text-gray-600">
                            <i class="fas fa-users text-gray-400"></i>
                            <?php echo $vehicle['seats']; ?> seats
                        </span>
                    </td>
                    <td>
                        <span class="font-semibold text-gray-800">₹<?php echo number_format($vehicle['price_per_day']); ?></span>
                        <span class="text-gray-400 text-xs">/day</span>
                    </td>
                    <td>
                        <?php if ($vehicle['active']): ?>
                        <span class="admin-badge admin-badge-success">
                            <i class="fas fa-check-circle"></i> Active
                        </span>
                        <?php else: ?>
                        <span class="admin-badge admin-badge-error">
                            <i class="fas fa-times-circle"></i> Inactive
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="admin-actions">
                            <a href="vehicle-edit.php?id=<?php echo $vehicle['id']; ?>" 
                               class="admin-action-btn edit tooltip" data-tip="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="vehicles.php?action=delete&id=<?php echo $vehicle['id']; ?>" 
                               class="admin-action-btn delete tooltip" data-tip="Delete"
                               onclick="return confirm('Are you sure you want to delete this vehicle?')">
                                <i class="fas fa-trash"></i>
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

<?php include 'includes/footer.php'; ?>
