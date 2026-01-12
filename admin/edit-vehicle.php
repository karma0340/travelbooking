<?php
require_once '../includes/db.php';
require_once 'check-login.php'; // Make sure this exists to check admin login

$message = '';
$vehicle = [
    'id' => 0,
    'name' => '',
    'description' => '',
    'seats' => 4,
    'bags' => 2,
    'price_per_day' => 1500,
    'image' => '',
    'features' => ['AC', 'Music System'],
    'active' => 1
];

// Check if we have a vehicle ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $vehicleId = (int)$_GET['id'];
    $dbVehicle = getVehicleById($vehicleId);
    
    if ($dbVehicle) {
        $vehicle = $dbVehicle;
    } else {
        $message = '<div class="alert alert-danger">Vehicle not found</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $vehicle = [
        'id' => (int)($_POST['id'] ?? 0),
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'seats' => (int)($_POST['seats'] ?? 4),
        'bags' => (int)($_POST['bags'] ?? 2),
        'price_per_day' => (float)($_POST['price_per_day'] ?? 1500),
        'image' => $_POST['image'] ?? '',
        'features' => explode(',', $_POST['features'] ?? 'AC,Music System'),
        'active' => isset($_POST['active']) ? 1 : 0
    ];
    
    // Basic validation
    if (empty($vehicle['name'])) {
        $message = '<div class="alert alert-danger">Vehicle name is required</div>';
    } else {
        // Try to save the vehicle
        $result = saveVehicle($vehicle, $vehicle['id']);
        
        if ($result) {
            $message = '<div class="alert alert-success">Vehicle updated successfully</div>';
            
            // Refresh vehicle data
            $dbVehicle = getVehicleById($vehicle['id']);
            if ($dbVehicle) {
                $vehicle = $dbVehicle;
            }
        } else {
            $message = '<div class="alert alert-danger">Failed to update vehicle</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vehicle - Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit Vehicle</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="vehicles.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Vehicles
                        </a>
                    </div>
                </div>
                
                <?php echo $message; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="id" value="<?php echo $vehicle['id']; ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Vehicle Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($vehicle['name']); ?>" required>
                                    <div class="invalid-feedback">
                                        Please provide a vehicle name.
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="image" class="form-label">Image URL</label>
                                    <input type="url" class="form-control" id="image" name="image" value="<?php echo htmlspecialchars($vehicle['image']); ?>">
                                    <div class="form-text">Leave empty to use default image</div>
                                </div>
                                
                                <div class="col-md-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($vehicle['description']); ?></textarea>
                                    <div class="invalid-feedback">
                                        Please provide a description.
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="seats" class="form-label">Seats</label>
                                    <input type="number" class="form-control" id="seats" name="seats" value="<?php echo (int)$vehicle['seats']; ?>" min="1" max="20" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="bags" class="form-label">Bags</label>
                                    <input type="number" class="form-control" id="bags" name="bags" value="<?php echo (int)$vehicle['bags']; ?>" min="0" max="20" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="price_per_day" class="form-label">Price Per Day (₹)</label>
                                    <input type="number" class="form-control" id="price_per_day" name="price_per_day" value="<?php echo (float)$vehicle['price_per_day']; ?>" min="0" step="100" required>
                                </div>
                                
                                <div class="col-md-12">
                                    <label for="features" class="form-label">Features</label>
                                    <input type="text" class="form-control" id="features" name="features" value="<?php echo htmlspecialchars(implode(',', $vehicle['features'])); ?>" required>
                                    <div class="form-text">Comma-separated list of features (e.g. AC,Music System,GPS)</div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="active" name="active" <?php echo $vehicle['active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="active">
                                            Active (display on website)
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Update Vehicle</button>
                                    <a href="vehicles.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Form validation script -->
    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function () {
            'use strict'
            
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation')
            
            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
