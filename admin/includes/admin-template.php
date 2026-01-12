<?php
// Ensure user is logged in
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

// Get user data
$user = $_SESSION['admin_user'];

// Get current page for highlighting active menu item
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?> - Travel In Peace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
    <?php if (isset($page_css)): echo $page_css; endif; ?>
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <h5 class="mb-0 d-flex align-items-center">
                <i class="fas fa-plane me-2"></i>
                <span>Travel In Peace</span>
            </h5>
        </div>
        
        <ul class="nav flex-column sidebar-nav">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'flights.php') ? 'active' : ''; ?>" href="flights.php">
                    <i class="fas fa-plane-departure"></i> Flights
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'tours.php') ? 'active' : ''; ?>" href="tours.php">
                    <i class="fas fa-map-marked-alt"></i> Tours
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'vehicles.php') ? 'active' : ''; ?>" href="vehicles.php">
                    <i class="fas fa-car"></i> Vehicles
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'bookings.php') ? 'active' : ''; ?>" href="bookings.php">
                    <i class="fas fa-calendar-check"></i> Bookings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'reviews.php') ? 'active' : ''; ?>" href="reviews.php">
                    <i class="fas fa-star"></i> Reviews
                </a>
            </li>
            <?php if ($user['role'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>" href="users.php">
                    <i class="fas fa-users"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>" href="settings.php">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item mt-3">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <?php if (isset($page_content)): ?>
                <?php echo $page_content; ?>
            <?php else: ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Page content not defined.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($page_js)): echo $page_js; endif; ?>
    <script>
        // Mobile sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const toggleSidebar = document.createElement('button');
            toggleSidebar.classList.add('btn', 'btn-primary', 'position-fixed');
            toggleSidebar.style.top = '10px';
            toggleSidebar.style.left = '10px';
            toggleSidebar.style.zIndex = '1001';
            toggleSidebar.style.display = 'none';
            toggleSidebar.innerHTML = '<i class="fas fa-bars"></i>';
            
            document.body.appendChild(toggleSidebar);
            
            toggleSidebar.addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('active');
                document.querySelector('.main-content').classList.toggle('active');
            });
            
            function checkWindowSize() {
                if (window.innerWidth <= 768) {
                    toggleSidebar.style.display = 'block';
                } else {
                    toggleSidebar.style.display = 'none';
                    document.getElementById('sidebar').classList.remove('active');
                    document.querySelector('.main-content').classList.remove('active');
                }
            }
            
            window.addEventListener('resize', checkWindowSize);
            checkWindowSize();
        });
    </script>
</body>
</html>
