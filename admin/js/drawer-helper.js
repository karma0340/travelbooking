/**
 * Drawer toggle helper for the Shimla Tours admin panel
 * Ensures the drawer toggle works correctly, even if the native checkbox+label approach fails
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get the drawer toggle checkbox by its ID 'my-drawer' (must match the ID in header.php)
    const drawerToggle = document.getElementById('my-drawer');
    const toggleButtons = document.querySelectorAll('label[for="my-drawer"]');
    
    // Debug output to verify elements are found
    console.log("Drawer toggle found:", !!drawerToggle);
    console.log("Toggle buttons found:", toggleButtons.length);
    
    if (!drawerToggle || toggleButtons.length === 0) {
        console.error("Drawer elements not found");
        return;
    }
      // Add manual click handlers to ensure toggle works
    toggleButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Toggle the checkbox state directly
            drawerToggle.checked = !drawerToggle.checked;
            // Add a short timeout to ensure the state change is registered
            setTimeout(() => {
                // Force reflow to ensure the drawer state updates
                document.body.classList.toggle('drawer-open', drawerToggle.checked);
            }, 10);
            console.log("Drawer toggle clicked, new state:", drawerToggle.checked);
        });
    });
      // Close drawer when clicking overlay on mobile
    const drawerOverlay = document.querySelector('.drawer-overlay');
    if (drawerOverlay) {
        // Use both click and touchend events for better mobile support
        ['click', 'touchend'].forEach(eventType => {
            drawerOverlay.addEventListener(eventType, function(e) {
                if (window.innerWidth < 1024) { // Only on mobile
                    drawerToggle.checked = false;
                    console.log("Drawer overlay clicked, closing drawer");
                    // Prevent event bubbling
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
    }
    
    // Close drawer when clicking menu items on mobile
    const sidebarLinks = document.querySelectorAll('.drawer-side a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) { // Only on mobile
                drawerToggle.checked = false;
            }
        });
    });
      // Auto-close drawer when window is resized above mobile breakpoint
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024 && drawerToggle.checked) {
            drawerToggle.checked = false;
        }
    });
    
    // Add touch swipe support for mobile devices
    let touchStartX = 0;
    let touchEndX = 0;
    
    if ('ontouchstart' in window) {
        document.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        
        document.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            const diff = touchEndX - touchStartX;
            
            // Only process significant swipes
            if (Math.abs(diff) > 50) {
                // Swipe from left edge to open drawer
                if (diff > 0 && !drawerToggle.checked && touchStartX < 50) {
                    drawerToggle.checked = true;
                    console.log("Right swipe detected, opening drawer");
                }
                // Swipe left to close drawer
                else if (diff < 0 && drawerToggle.checked) {
                    drawerToggle.checked = false;
                    console.log("Left swipe detected, closing drawer");
                }
            }
        }, { passive: true });
    }
});
