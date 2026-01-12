</div> <!-- End of content-container -->
            </main> <!-- End of main-content -->
            
            <!-- Footer -->
            <footer class="p-4 bg-white text-center text-gray-500 text-sm border-t">
                &copy; <?php echo date('Y'); ?> Travel In Peace. All rights reserved.
            </footer>
        </div> <!-- End of drawer-content -->
        
        <!-- Sidebar Drawer - Must exactly match DaisyUI structure -->
        <div class="drawer-side z-40">
            <!-- This label must match the checkbox ID -->
            <label for="my-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
            <div class="w-64 min-h-full bg-secondary text-gray-200 relative">
                <!-- Mobile Close Button -->
                <label for="my-drawer" class="sidebar-close-btn text-white lg:hidden">
                    <i class="fas fa-times"></i>
                </label>
                <!-- Sidebar Header -->
                <div class="p-4 border-b border-gray-700">
                    <div class="flex items-center justify-center lg:justify-start">
                        <i class="fas fa-mountain text-xl mr-2 text-primary"></i>
                        <h1 class="text-lg font-bold">Travel In Peace</h1>
                    </div>
                    <!-- User Profile Info (Desktop) -->
                    <div class="mt-4 pb-2 hidden lg:block">
                        <div class="flex items-center">
                            <div class="avatar placeholder">
                                <div class="bg-primary text-white rounded-full w-10">
                                    <span><?php echo substr($user['name'] ?? 'U', 0, 1); ?></span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="font-semibold text-sm"><?php echo $user['name'] ?? 'User'; ?></p>
                                <?php 
                                $role = ucfirst($user['role'] ?? 'admin');
                                $name = $user['name'] ?? '';
                                // Only show role if it's not redundant
                                if (stripos($name, $role) === false && $role !== 'Admin') {
                                    echo '<p class="text-xs opacity-70">' . $role . '</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar Menu -->
                <ul class="menu p-4 gap-2">
                    <!-- Dashboard -->
                    <li>
                        <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt w-5"></i> Dashboard
                        </a>
                    </li>
                    
                    <!-- Tours Section -->
                    <li>
                        <a href="tours.php" class="<?php echo ($activePage == 'tours' || $activePage == 'tour-edit') ? 'active' : ''; ?>">
                            <i class="fas fa-map-marked-alt w-5"></i> Tours
                        </a>
                    </li>
                    
                    <!-- Tour Categories -->
                    <li>
                        <a href="categories.php" class="<?php echo ($activePage == 'categories' || $activePage == 'category-edit') ? 'active' : ''; ?>">
                            <i class="fas fa-th-large w-5"></i> Categories
                        </a>
                    </li>
                    
                    <!-- Vehicles Section -->
                    <li>
                        <a href="vehicles.php" class="<?php echo ($activePage == 'vehicles' || $activePage == 'vehicle-edit') ? 'active' : ''; ?>">
                            <i class="fas fa-car w-5"></i> Vehicles
                        </a>
                    </li>
                    
                    <!-- Vehicle Bookings -->
                    <li>
                        <a href="vehicle-bookings.php" class="<?php echo ($activePage == 'vehicle-bookings') ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-check w-5"></i> Vehicle Bookings
                        </a>
                    </li>
                    
                    <!-- Tour Bookings -->
                    <li>
                        <a href="bookings.php" class="<?php echo ($activePage == 'bookings' || $activePage == 'booking-details') ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-alt w-5"></i> Tour Bookings
                        </a>
                    </li>
                    
                    <!-- Reviews -->
                    <!-- <li>
                        <a href="reviews.php" class="<?php echo ($activePage == 'reviews') ? 'active' : ''; ?>">
                            <i class="fas fa-star w-5"></i> Reviews
                        </a>
                    </li> -->
                    
                    <!-- Admin Users - Only visible to admins -->
                    <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                    <li class="mt-2 pt-2 border-t border-gray-700">
                        <a href="users.php" class="<?php echo ($activePage == 'users') ? 'active' : ''; ?>">
                            <i class="fas fa-users w-5"></i> Users
                        </a>
                    </li>
 
                    <?php endif; ?>
                    
                    <!-- Logout -->
                    <li class="mt-2 pt-2 border-t border-gray-700">
                        <label for="logout_modal" class="text-red-300 hover:bg-red-900 hover:text-white">
                            <i class="fas fa-sign-out-alt w-5"></i> Logout
                        </label>
                    </li>
                </ul>
            </div>
        </div>
    </div> <!-- End of Drawer -->
    
    <!-- Logout Modal -->
    <input type="checkbox" id="logout_modal" class="modal-toggle" />
    <div class="modal" role="dialog">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Ready to Leave?</h3>
            <p class="py-4">Select "Logout" below if you are ready to end your current session.</p>
            <div class="modal-action">
                <label for="logout_modal" class="btn">Cancel</label>
                <a href="logout.php" class="btn btn-primary">Logout</a>
            </div>
        </div>
        <label class="modal-backdrop" for="logout_modal">Close</label>
    </div>
    
    <!-- Toast Container for Notifications -->
    <div id="toast-container" class="toast toast-top toast-end z-50"></div>
    
    <!-- Common JavaScript -->
    <script>
    // Session keepalive
    setInterval(function() {
        fetch('session-keepalive.php', {
            method: 'GET',
            credentials: 'include'
        }).catch(error => console.error('Session keepalive failed:', error));
    }, 600000); // 10 minutes
    
    // Delete confirmation handler
    document.addEventListener('DOMContentLoaded', function() {
        // Delete confirmations
        document.querySelectorAll('.delete-confirm').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
        
        // Notification function
        window.showNotification = function(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container');
            if (!toastContainer) return;
            
            const alertClass = type === 'success' ? 'alert-success' : 
                             type === 'error' ? 'alert-error' : 
                             type === 'warning' ? 'alert-warning' : 'alert-info';
            
            const iconClass = type === 'success' ? 'fa-check-circle' : 
                            type === 'error' ? 'fa-times-circle' : 
                            type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
            
            const toast = document.createElement('div');
            toast.className = `alert ${alertClass} shadow-lg my-2`;
            toast.innerHTML = `
                <div class="flex items-start">
                    <i class="fas ${iconClass} mt-1"></i>
                    <span class="ml-2">${message}</span>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                toast.classList.add('opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        };
        
        // Mobile drawer improvements
        const drawerToggle = document.getElementById('my-drawer');
        if (drawerToggle) {
            // Check screen size and auto-close drawer on smaller screens
            const checkScreenSize = () => {
                if (window.innerWidth >= 1024 && drawerToggle.checked) {
                    // On large screens, keep drawer open (handled by CSS)
                } else if (window.innerWidth < 1024 && drawerToggle.checked) {
                    // Optional: auto-close on resize to mobile
                }
            };
            
            window.addEventListener('resize', checkScreenSize);
            
            // Close drawer when clicking overlay or menu items on mobile
            document.querySelectorAll('.drawer-side a, .drawer-overlay, .sidebar-close-btn').forEach(el => {
                el.addEventListener('click', () => {
                    if (window.innerWidth < 1024) {
                        drawerToggle.checked = false;
                    }
                });
            });
        }
    });
    </script>
      <!-- Drawer helper must come first to ensure mobile drawer functionality -->
    <script src="js/drawer-helper.js"></script>
    
    <!-- Important: Load admin.js before any page-specific scripts -->
    <script src="js/admin.js"></script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($extraJs)): echo $extraJs; endif; ?>
</body>
</html>
