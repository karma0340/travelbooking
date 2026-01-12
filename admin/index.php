<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Check if already logged in
if (isset($_SESSION['admin_user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Check for timeout
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $error = 'Your session has expired. Please log in again.';
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Verify credentials
        $user = getUserByUsername($username);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Valid login - check admin/manager role
            if (in_array($user['role'], ['admin', 'manager'])) {
                // Setup session
                $_SESSION['admin_user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
                $_SESSION['last_activity'] = time();
                
                // Redirect to dashboard or intended URL
                $redirect = $_SESSION['intended_url'] ?? 'dashboard.php';
                unset($_SESSION['intended_url']);
                header("Location: $redirect");
                exit;
            } else {
                // Not an admin/manager
                $error = 'You do not have permission to access the admin area';
            }
        } else {
            // Invalid login
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4F46E5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Admin Login - Travel In Peace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <!-- <link href="css/admin-style.css" rel="stylesheet">     -->
    <style>
        :root {
            --primary-color: #4F46E5;
            --primary-dark: #3730a3;
            --primary-light: #6366f1;
            --secondary-color: #0B0C1B;
            --accent-color: #10b981;
            --text-light: #f9fafb;
            --text-dark: #1f2937;
            --text-muted: #6b7280;
            --card-bg: #ffffff;
            --danger: #ef4444;
            --warning: #f59e0b;
            --success: #10b981;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, var(--secondary-color) 0%, #1a1b2e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('../images/shimla-landscape.jpg') center/cover no-repeat fixed;
            opacity: 0.08;
            z-index: -1;
        }
        
        .login-container {
            max-width: 1000px;
            width: 100%;
            margin: auto;
            position: relative;
        }
        
        /* ===== BRAND LOGO STYLING ===== */
        .brand-logo {
            text-align: center;
            margin-bottom: 30px;
            color: white;
            position: relative;
            padding-bottom: 15px;
            animation: fadeIn 0.8s ease-in-out;
        }
        
        .brand-logo::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--primary-light), transparent);
        }
        
        .brand-logo h2 {
            font-weight: 800;
            font-size: 2.2rem;
            margin-bottom: 8px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .brand-logo .logo-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: white;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            margin-right: 12px;
            box-shadow: 0 3px 10px rgba(79, 70, 229, 0.4);
            transform: rotate(-5deg);
            transition: transform 0.3s ease;
        }
        
        .brand-logo:hover .logo-icon {
            transform: rotate(5deg);
        }
        
        .brand-logo p {
            font-size: 1.1rem;
            opacity: 0.85;
            font-weight: 300;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 5px;
        }
        
        /* ===== LOGIN CARD STYLING ===== */
        .login-card {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2), 0 5px 15px rgba(0, 0, 0, 0.1);
            background-color: var(--card-bg);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: slideUp 0.6s ease-out;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), 0 15px 25px rgba(0, 0, 0, 0.2);
        }
        
        .login-sidebar {
            background: url("../images/shimla-landscape.jpg");
            background-position: center;
            background-size: cover;
            position: relative;
            min-height: 400px;
        }
        
        .login-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.85) 0%, rgba(11, 12, 27, 0.9) 100%);
        }
        
        .login-sidebar-content {
            position: absolute;
            top: 50%;
            left: 30px;
            right: 30px;
            transform: translateY(-50%);
            color: white;
            z-index: 1;
            text-align: left;
        }
        
        .login-sidebar-content h3 {
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 2rem;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            position: relative;
            padding-bottom: 15px;
        }
        
        .login-sidebar-content h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 3px;
            background-color: var(--accent-color);
            border-radius: 3px;
        }
        
        .login-sidebar-content p {
            font-size: 1.1rem;
            opacity: 0.95;
            line-height: 1.8;
            margin-bottom: 25px;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }
        
        .feature-list li i {
            color: var(--accent-color);
            margin-right: 10px;
        }
        
        /* ===== LOGIN FORM STYLING ===== */
        .login-form-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        
        .login-form {
            padding: 40px;
            background-color: var(--card-bg);
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .login-welcome {
            color: var(--text-dark);
            font-weight: 800;
            margin-bottom: 10px;
            font-size: 1.8rem;
            text-align: center;
        }
        
        .login-subtitle {
            color: var(--text-muted);
            margin-bottom: 30px;
            font-size: 1rem;
            text-align: center;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-control {
            height: 56px;
            border-radius: 10px;
            font-size: 1rem;
            border: 1px solid rgba(0,0,0,0.1);
            background-color: #f9fafb;
            padding-left: 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15);
            background-color: #fff;
        }
        
        .input-group-text {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            border-radius: 10px 0 0 10px;
            width: 48px;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .input-group .form-control {
            border-radius: 0 10px 10px 0;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 14px 0;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
            font-size: 1.05rem;
            letter-spacing: 0.5px;
        }
        
        .btn-primary:hover, 
        .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: none;
            border-radius: 10px;
            padding: 12px 15px;
        }
        
        .footer-link-container {
            text-align: center;
            margin-top: 25px;
        }
        
        .footer-link {
            color: var(--text-muted);
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 8px;
        }
        
        .footer-link i {
            margin-right: 8px;
            transition: transform 0.3s;
        }
        
        .footer-link:hover {
            color: var(--primary-color);
            background-color: rgba(79, 70, 229, 0.08);
        }
        
        .footer-link:hover i {
            transform: translateX(-3px);
        }
        
        /* ===== COPYRIGHT STYLING ===== */
        .copyright-container {
            margin-top: 30px;
            padding-top: 20px;
            color: rgba(255, 255, 255, 0.85);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }
        
        .copyright {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .copyright-logo {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        
        .copyright-text {
            font-size: 0.9rem;
        }
        
        .social-links {
            margin-top: 15px;
        }
        
        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            margin: 0 5px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
        }
        
        /* ===== ANIMATIONS ===== */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(30px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes rotate {
            100% { transform: rotate(360deg); }
        }
        
        /* ===== LOADING BUTTON STATE ===== */
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.8;
        }
        
        .btn-loading:after {
            content: '';
            width: 1em;
            height: 1em;
            border: 2px solid #fff;
            border-radius: 50%;
            border-right-color: transparent;
            position: absolute;
            right: 1.5em;
            animation: rotate 0.5s infinite linear;
        }
          /* ===== RESPONSIVE STYLES ===== */
        @media (max-width: 991px) {
            .login-sidebar {
                min-height: 250px;
            }
            
            .login-sidebar-content {
                padding: 30px;
            }
            
            .login-sidebar-content h3 {
                font-size: 1.6rem;
            }
            
            .login-sidebar-content p {
                font-size: 0.95rem;
            }
            
            .feature-list {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 0;
                height: auto;
                min-height: 100vh;
                display: block;
                background: var(--secondary-color);
            }
            
            .login-container {
                padding: 0;
                width: 100%;
                max-width: 100%;
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }
            
            /* Mobile card style layout */
            .login-card {
                flex: 1;
                margin: 0;
                border-radius: 0;
                box-shadow: none;
                background-color: var(--secondary-color);
                overflow: visible;
                display: flex;
                flex-direction: column;
                width: 100%;
            }
            
            
            /* Left sidebar styles */
            .login-sidebar {
                display: none !important;
            }
            
            /* Adjust branding for mobile */
            .brand-logo {
                margin: 20px 0;
                padding: 0 20px;
            }
            
            .brand-logo h2 {
                font-size: 1.5rem;
                text-align: left;
                justify-content: flex-start;
            }
            
            .brand-logo .logo-icon {
                width: 36px;
                height: 36px;
                font-size: 1.1rem;
                margin-right: 10px;
            }
            
            .brand-logo p {
                font-size: 0.85rem;
                letter-spacing: 1px;
                text-align: left;
            }
            
            .brand-logo::after {
                display: none;
            }
            
            /* Mobile form styles */
            .login-form-container {
                height: auto;
                padding: 0 20px;
                flex: 1;
                display: flex;
                align-items: flex-start;
            }
            
            .login-form {
                padding: 25px;
                border-radius: 16px;
                background-color: white;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                max-width: 100%;
                width: 100%;
            }
            
            .row.g-0 {
                flex-direction: column;
            }
            
            .login-welcome {
                font-size: 1.25rem;
                text-align: left;
                margin-bottom: 5px;
            }
            
            .login-subtitle {
                font-size: 0.85rem;
                margin-bottom: 20px;
                text-align: left;
            }
            
            .col-lg-6 {
                width: 100%;
                max-width: 100%;
                flex: 1;
                padding: 0;
            }
            
            .form-control, .input-group-text, .btn-primary {
                height: 46px;
                font-size: 16px;
            }
            
            .input-group {
                margin-bottom: 15px;
            }
            
            .form-check {
                margin: 15px 0;
            }
            
            .form-check-input {
                width: 18px;
                height: 18px;
            }
            
            .form-check-label {
                font-size: 0.9rem;
                padding-left: 5px;
            }
            
            /* Mobile Copyright/Footer */
            .copyright-container {
                margin-top: auto;
                padding: 30px 20px 25px;
                background-color: transparent;
            }
            
            .copyright-logo {
                font-size: 0.9rem;
            }
            
            .copyright-text {
                font-size: 0.75rem;
            }
            
            .social-links {
                margin-top: 10px;
            }
            
            .social-icon {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
                background-color: rgba(255, 255, 255, 0.1);
                margin: 0 3px;
            }
            
            /* Fix for branding and navigation placement */
            .admin-branding {
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                padding: 0 20px;
            }
            
            /* Fix for the website return link */
            .footer-link-container {
                margin-top: 20px;
            }
            
            .footer-link {
                font-size: 0.85rem;
                padding: 8px 0;
                display: inline-flex;
            }
            
            /* Better button for mobile */
            .btn-primary {
                margin-top: 10px;
            }
            
            .form-check {
                margin: 15px 0 20px;
            }
            
            .form-check-input {
                width: 20px;
                height: 20px;
            }
            
            .form-check-label {
                padding-left: 5px;
                font-size: 16px;
            }
            
            .copyright-container {
                margin-top: 20px;
                padding: 15px 5px;
            }
            
            .copyright-logo {
                font-size: 0.9rem;
            }
            
            .copyright-text {
                font-size: 0.8rem;
            }
            
            .social-icon {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
            }
        }
        
        /* ===== UTILITY CLASSES ===== */
        /* Prevent element highlighting on tap */
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        /* Smooth scrolling for iOS */
        body {
            -webkit-overflow-scrolling: touch;
        }
        
        /* Improved input styles for mobile */
        .form-control {
            -webkit-appearance: none;
            appearance: none;
        }
        
        /* Add active states for better touch feedback */
        .btn-primary:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>
    <div class="login-container">        <!-- Modern Brand Logo with Animation -->
        <div class="brand-logo">
            <h2>
                <span class="logo-icon"><i class="fas fa-mountain"></i></span>
                Travel In Peace
            </h2>
            <p>Administrative Dashboard</p>
        </div>
        
        <!-- Login Card with Improved Layout -->
        <div class="card login-card">
            <div class="row g-0">
                <!-- Sidebar with Feature Highlights (Hidden on Mobile) -->
                <div class="col-lg-6 d-none d-lg-block login-sidebar">
                    <div class="login-sidebar-content">
                        <h3>Why Choose Us?</h3>
                        <ul class="feature-list">
                            <li><i class="fas fa-check-circle"></i> Real-time Booking Management</li>
                            <li><i class="fas fa-user-shield"></i> Secure Admin Access</li>
                            <li><i class="fas fa-chart-line"></i> Analytics Dashboard</li>
                            <li><i class="fas fa-car"></i> Fleet & Vehicle Control</li>
                            <li><i class="fas fa-headset"></i> 24/7 Support</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 col-12 login-form-container">
                    <form class="login-form" method="post" autocomplete="off">
                        <div class="login-welcome">Welcome Back</div>
                        <div class="login-subtitle">Sign in to access your admin dashboard</div>
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger mb-3" role="alert"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" name="username" placeholder="Username" required autofocus>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" name="password" placeholder="Password" required>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Remember Me</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sign-in-alt me-2"></i>Sign In</button>
                        <div class="footer-link-container">
                            <a href="../index.php" class="footer-link"><i class="fas fa-arrow-left"></i> Return to Website</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Enhanced Copyright Section with Social Links -->
        <div class="copyright-container">
            <div class="copyright">
                <div class="copyright-logo">
                    <i class="fas fa-mountain"></i> Travel In Peace
                </div>
                <div class="copyright-text">
                    © 2025 ALL Rights Reserved | Crafted with <span style="color:#ef4444">❤</span> in Shimla
                </div>
                <div class="social-links">
                    <a href="#" class="social-icon" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
    </div>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="js/logo-animate.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animate elements on page load
            setTimeout(function() {
                document.querySelector('.login-card').style.opacity = '1';
                document.querySelector('.login-card').style.transform = 'translateY(0)';
            }, 100);
            
            // Add form submit handling with loading state and animation
            document.querySelector('form').addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                const btnText = submitBtn.innerHTML;
                submitBtn.classList.add('btn-loading');
                submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Signing In...';
                
                // Store original button content for potential error recovery
                submitBtn.dataset.originalContent = btnText;
                
                // Simulate network delay for better UX feedback (optional, remove in production)
                /*
                e.preventDefault();
                setTimeout(() => {
                    this.submit();
                }, 800);
                */
            });
    
            // Show error messages with enhanced Toastify styling
            <?php if (!empty($error)): ?>
            Toastify({
                text: "<?php echo addslashes($error); ?>",
                duration: 4000,
                gravity: "top",
                position: "center",
                backgroundColor: "#ef4444",
                stopOnFocus: true,
                onClick: function(){} // Closes the toast when clicked
            }).showToast();
            <?php endif; ?>
    
            // Add smooth form transitions with enhanced animation
            document.querySelectorAll('.form-control').forEach(input => {
                input.addEventListener('focus', function() {
                    const inputGroup = this.closest('.input-group');
                    inputGroup.style.transform = 'translateY(-3px)';
                    inputGroup.style.boxShadow = '0 5px 15px rgba(79, 70, 229, 0.15)';
                });
                
                input.addEventListener('blur', function() {
                    const inputGroup = this.closest('.input-group');
                    inputGroup.style.transform = 'none';
                    inputGroup.style.boxShadow = 'none';
                });
            });
    
            // Add touch feedback for all interactive elements
            const interactiveElements = document.querySelectorAll('.btn, .form-control, .social-icon, .footer-link');
            interactiveElements.forEach(element => {
                element.addEventListener('touchstart', function() {
                    this.style.opacity = '0.8';
                    this.style.transform = 'scale(0.98)';
                }, { passive: true });
                
                element.addEventListener('touchend', function() {
                    this.style.opacity = '1';
                    this.style.transform = 'scale(1)';
                }, { passive: true });
            });
            
            // Prevent double tap zoom on iOS
            document.addEventListener('dblclick', function(e) {
                e.preventDefault();
            }, { passive: false });
            
            // Prevent form zoom on iOS
            document.querySelector('form').addEventListener('focus', function(e) {
                if (e.target.tagName === 'INPUT') {
                    document.body.style.zoom = 1;
                }
            }, true);
            
            // Add subtle animation to logo
            const logoIcon = document.querySelector('.logo-icon');
            if (logoIcon) {
                setInterval(() => {
                    logoIcon.style.transform = 'rotate(-5deg)';
                    setTimeout(() => {
                        logoIcon.style.transform = 'rotate(5deg)';
                    }, 1500);
                }, 3000);
            }
            
            // Add hover effect to social icons
            document.querySelectorAll('.social-icon').forEach(icon => {
                icon.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                });
                
                icon.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Add better password visibility toggle
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                const toggleContainer = document.createElement('span');
                toggleContainer.style.position = 'absolute';
                toggleContainer.style.right = '10px';
                toggleContainer.style.top = '50%';
                toggleContainer.style.transform = 'translateY(-50%)';
                toggleContainer.style.zIndex = '10';
                toggleContainer.style.cursor = 'pointer';
                toggleContainer.style.padding = '5px';
                toggleContainer.innerHTML = '<i class="far fa-eye-slash"></i>';
                
                passwordInput.parentNode.style.position = 'relative';
                passwordInput.parentNode.appendChild(toggleContainer);
                
                toggleContainer.addEventListener('click', function() {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        this.innerHTML = '<i class="far fa-eye"></i>';
                    } else {
                        passwordInput.type = 'password';
                        this.innerHTML = '<i class="far fa-eye-slash"></i>';
                    }
                });
            }
        });
    </script>
</body>
</html>
