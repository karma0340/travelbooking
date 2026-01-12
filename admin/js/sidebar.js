document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const wrapper = document.getElementById('wrapper');
    const overlay = document.createElement('div');
    
    // Create overlay for mobile
    overlay.className = 'sidebar-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1029;
        display: none;
    `;
    document.body.appendChild(overlay);

    // Toggle sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('show');
        document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
    }

    // Event listeners
    sidebarToggle.addEventListener('click', function(e) {
        e.preventDefault();
        toggleSidebar();
    });

    // Close sidebar when clicking overlay
    overlay.addEventListener('click', function() {
        toggleSidebar();
    });

    // Close sidebar on window resize if window width > 768px
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('show');
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    });
});
