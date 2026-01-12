<?php
// Ensure the user is logged in
if (!isset($_SESSION['admin_user'])) {
    $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
    header('Location: index.php');
    exit;
}

// Get user data
$user = $_SESSION['admin_user'] ?? [];

// Determine active page from filename if not set
if (!isset($activePage)) {
    $activePage = basename($_SERVER['PHP_SELF'], '.php');
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Admin Panel'; ?> - Travel In Peace</title>
    <!-- Optimized Local CSS (Pre-compiled for Performance) -->
    <link rel="stylesheet" href="css/admin-style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Styles -->    <style>
      /* Fix for DaisyUI drawer on desktop - prevent content shift */
      @media (min-width: 1024px) {
        .drawer-content {
          margin-left: 16rem; /* Width of the sidebar */
        }
        .drawer-side {
          width: 16rem;
          background-color: #0B0C1B;
          position: fixed;
          top: 0;
          bottom: 0;
          height: 100vh;
          overflow-y: auto;
        }
      }
      
      /* Tablet and medium screens */
      @media (min-width: 768px) and (max-width: 1023px) {
        .drawer-content {
          margin-left: 0;
        }
        .drawer-side {
          width: 16rem;
          transform: translateX(-100%);
        }
        .drawer-toggle:checked ~ .drawer-side {
          transform: translateX(0);
        }
      }
      
      /* Mobile drawer handling */
      @media (max-width: 1023px) {
        .drawer-content {
          margin-left: 0;
        }
        .drawer-side {
          width: 16rem;
          max-width: 280px;
          transition: transform 0.3s ease-in-out;
          transform: translateX(-100%);
          position: fixed;
          top: 0;
          left: 0;
          height: 100vh;
          z-index: 9999;
        }
        
        .drawer-toggle:checked ~ .drawer-side {
          transform: translateX(0) !important;
        }
        
        .drawer-overlay {
          position: fixed;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background-color: rgba(0, 0, 0, 0.5);
          z-index: 9998;
          opacity: 0;
          pointer-events: none;
          transition: opacity 0.3s ease;
        }
        
        .drawer-toggle:checked ~ .drawer-overlay {
          opacity: 1;
          pointer-events: auto;
        }
      }
      
      /* Better sidebar styling */
      .menu li a.active {
        background-color: #4F46E5;
        color: white;
      }
      .menu li a:hover:not(.active) {
        background-color: rgba(255, 255, 255, 0.1);
      }
      
      /* Responsive stat cards */
      .stats-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: 1fr;
      }
      @media (min-width: 640px) {
        .stats-grid {
          grid-template-columns: repeat(2, 1fr);
        }
      }
      @media (min-width: 1280px) {
        .stats-grid {
          grid-template-columns: repeat(4, 1fr);
        }
      }
      
      /* Main content padding */
      .main-content {
        min-height: calc(100vh - 4rem);
        padding: 1rem;
      }
      @media (min-width: 768px) {
        .main-content {
          padding: 1.5rem;
        }
      }
      
      /* Fix responsive tables */
      .table-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
      }
      
      /* Sidebar close button for mobile */
      .sidebar-close-btn {
        display: none;
      }
      @media (max-width: 1023px) {
        .sidebar-close-btn {
          display: flex;
          position: absolute;
          top: 1rem;
          right: 1rem;
          width: 2rem;
          height: 2rem;
          align-items: center;
          justify-content: center;
          background-color: rgba(255, 255, 255, 0.1);
          border-radius: 50%;
          cursor: pointer;
          z-index: 10;
        }
        .sidebar-close-btn:hover {
          background-color: rgba(255, 255, 255, 0.2);
        }
      }
      
      /* ============================================
         GLOBAL ADMIN UI STYLES
         ============================================ */
      
      /* Modern Card Styling */
      .admin-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
      }
      .admin-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
      }
      .admin-card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.75rem;
      }
      .admin-card-title .icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
      }
      .admin-card-body {
        padding: 1.5rem;
      }
      
      /* Modern Table Styling */
      .admin-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
      }
      .admin-table thead {
        background: linear-gradient(to right, #f8fafc, #f1f5f9);
      }
      .admin-table thead th {
        padding: 0.875rem 1rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0;
      }
      .admin-table tbody tr {
        transition: background-color 0.15s ease;
      }
      .admin-table tbody tr:hover {
        background-color: #f8fafc;
      }
      .admin-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        color: #374151;
        font-size: 0.875rem;
      }
      .admin-table tbody tr:last-child td {
        border-bottom: none;
      }
      
      /* Modern Badges */
      .admin-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
      }
      .admin-badge-success {
        background: #dcfce7;
        color: #166534;
      }
      .admin-badge-warning {
        background: #fef3c7;
        color: #92400e;
      }
      .admin-badge-error {
        background: #fee2e2;
        color: #991b1b;
      }
      .admin-badge-info {
        background: #dbeafe;
        color: #1e40af;
      }
      .admin-badge-primary {
        background: #e0e7ff;
        color: #3730a3;
      }
      .admin-badge-neutral {
        background: #f3f4f6;
        color: #374151;
      }
      
      /* Modern Buttons */
      .admin-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
      }
      .admin-btn-primary {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: white;
        box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3);
      }
      .admin-btn-primary:hover {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(79, 70, 229, 0.4);
      }
      .admin-btn-success {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
      }
      .admin-btn-success:hover {
        background: linear-gradient(135deg, #16a34a, #15803d);
        transform: translateY(-1px);
      }
      .admin-btn-warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
      }
      .admin-btn-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
      }
      .admin-btn-outline {
        background: transparent;
        border: 1px solid #d1d5db;
        color: #374151;
      }
      .admin-btn-outline:hover {
        background: #f9fafb;
        border-color: #9ca3af;
      }
      .admin-btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.8125rem;
      }
      .admin-btn-ghost {
        background: transparent;
        color: #6366f1;
      }
      .admin-btn-ghost:hover {
        background: #eef2ff;
      }
      
      /* Action Button Group */
      .admin-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
      }
      .admin-action-btn {
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        transition: all 0.2s;
        cursor: pointer;
        border: none;
        background: transparent;
      }
      .admin-action-btn.view {
        color: #6366f1;
      }
      .admin-action-btn.view:hover {
        background: #eef2ff;
      }
      .admin-action-btn.edit {
        color: #f59e0b;
      }
      .admin-action-btn.edit:hover {
        background: #fef3c7;
      }
      .admin-action-btn.delete {
        color: #ef4444;
      }
      .admin-action-btn.delete:hover {
        background: #fee2e2;
      }
      
      /* Empty State */
      .admin-empty-state {
        text-align: center;
        padding: 4rem 2rem;
      }
      .admin-empty-state .icon {
        width: 5rem;
        height: 5rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: #9ca3af;
        font-size: 2rem;
      }
      .admin-empty-state h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
      }
      .admin-empty-state p {
        color: #6b7280;
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
      }
      
      /* Page Header Enhancement */
      .page-header {
        margin-bottom: 1.5rem;
      }
      .page-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 0.75rem;
      }
      .page-header .subtitle {
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 0.25rem;
      }
      
      /* Avatar styles */
      .admin-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
      }
      .admin-avatar-primary {
        background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
        color: #4f46e5;
      }
      .admin-avatar-success {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #16a34a;
      }
      
      /* Filter/Search Bar */
      .admin-filter-bar {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 0.75rem;
      }
      .admin-filter-bar input,
      .admin-filter-bar select {
        padding: 0.5rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        background: white;
        transition: border-color 0.2s, box-shadow 0.2s;
      }
      .admin-filter-bar input:focus,
      .admin-filter-bar select:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
      }
      
      /* Stats Mini Cards */
      .admin-stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
      }
      .admin-stat-mini {
        background: white;
        padding: 1rem;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 0.75rem;
      }
      .admin-stat-mini .icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .admin-stat-mini .value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
      }
      .admin-stat-mini .label {
        font-size: 0.75rem;
        color: #6b7280;
      }
    </style>
    <?php if (isset($extraCss)): echo $extraCss; endif; ?>
</head>
<body class="bg-gray-100">
    <!-- Page Wrapper with Drawer - DaisyUI drawer component needs specific structure -->
    <div class="drawer lg:drawer-open">
        <!-- Drawer Toggle Checkbox - Critical: This ID must exactly match the label's 'for' attribute -->
        <input id="my-drawer" type="checkbox" class="drawer-toggle" />
        
        <!-- Main Content Area -->
        <div class="drawer-content flex flex-col">
            <!-- Mobile Navigation Bar -->
            <div class="bg-base-100 shadow-sm lg:hidden sticky top-0 z-30">
                <div class="navbar container mx-auto">
                    <div class="flex-none">
                        <!-- The label's 'for' attribute must exactly match the checkbox ID above -->
                        <label for="my-drawer" aria-label="open sidebar" class="btn btn-square btn-ghost">
                            <i class="fas fa-bars text-lg"></i>
                        </label>
                    </div>
                    <div class="flex-1">
                        <a href="dashboard.php" class="text-xl font-semibold flex items-center">
                            <i class="fas fa-mountain mr-2 text-primary"></i>
                            <span>Travel In Peace</span>
                        </a>
                    </div>
                    <div class="flex-none">
                        <div class="dropdown dropdown-end">
                            <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-white rounded-full w-10">
                                        <span><?php echo substr($user['name'] ?? 'U', 0, 1); ?></span>
                                    </div>
                                </div>
                            </div>
                            <ul tabindex="0" class="menu dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                                <li><label for="logout_modal"><i class="fas fa-sign-out-alt w-4 mr-2"></i> Logout</label></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Page Content -->
            <main class="main-content p-4 md:p-6">
                <!-- Page Header -->
                <?php if (!isset($hideDefaultHeader) || !$hideDefaultHeader): ?>
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800"><?= $pageTitle ?? 'Dashboard' ?></h1>
                        <p class="text-sm text-gray-500 mt-1">Welcome back, <?php echo $user['name'] ?? 'User'; ?></p>
                    </div>
                    
                    <?php if (isset($pageActions)): ?>
                    <div class="page-actions mt-4 md:mt-0">
                        <?= $pageActions ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Breadcrumbs (Optional) -->
                <?php if (isset($breadcrumbs)): ?>
                <div class="text-sm breadcrumbs mb-6">
                    <ul>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <?php echo $breadcrumbs; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Alert Messages -->
                <?php if (isset($errorMessage)): ?>
                <div class="alert alert-error mb-6">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $errorMessage; ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (isset($successMessage)): ?>
                <div class="alert alert-success mb-6">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $successMessage; ?></span>
                </div>
                <?php endif; ?>
                
                <!-- Toast notification container -->
                <div id="notification-container" class="toast toast-top toast-end z-50"></div>
                
                <!-- Main Content Container -->
                <div class="content-container">
                <!-- Content starts here - the closing tags are in footer.php -->
