/**
 * Consolidated admin panel JavaScript for Travel In Peace
 */
document.addEventListener('DOMContentLoaded', function () {
    // Drawer toggle functionality - using the correct ID 'my-drawer'
    const drawerToggle = document.getElementById('my-drawer');
    const toggleButtons = document.querySelectorAll('label[for="my-drawer"]');

    console.log('Drawer toggle found:', !!drawerToggle);
    console.log('Toggle buttons found:', toggleButtons.length);

    // Only run drawer logic if the toggle element exists
    if (drawerToggle) {
        // Add click handlers to drawer toggle buttons
        if (toggleButtons.length > 0) {
            toggleButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    console.log('Toggle button clicked');
                    drawerToggle.checked = !drawerToggle.checked;
                });
            });
        }

        // Close drawer when clicking overlay (with null check)
        const drawerOverlay = document.querySelector('.drawer-overlay');
        if (drawerOverlay) {
            drawerOverlay.addEventListener('click', function () {
                if (window.innerWidth < 1024) {
                    drawerToggle.checked = false;
                }
            });
        }

        // Close drawer when clicking menu items on mobile (with null check)
        const drawerSide = document.querySelector('.drawer-side');
        if (drawerSide) {
            const sidebarLinks = drawerSide.querySelectorAll('a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 1024) {
                        drawerToggle.checked = false;
                    }
                });
            });
        }

        // Close drawer on window resize
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024 && drawerToggle.checked) {
                drawerToggle.checked = false;
            }
        });
    }

    // Add touch/swipe support for mobile - WITH SAFER EVENT HANDLERS
    let touchStartX = 0;
    let touchEndX = 0;

    // Only add touch handlers if touch is available (prevents errors in non-touch devices)
    if ('ontouchstart' in window) {
        document.addEventListener('touchstart', function (e) {
            if (e && e.changedTouches && e.changedTouches.length > 0) {
                touchStartX = e.changedTouches[0].screenX;
            }
        }, { passive: true });

        document.addEventListener('touchend', function (e) {
            if (drawerToggle && e && e.changedTouches && e.changedTouches.length > 0) {
                touchEndX = e.changedTouches[0].screenX;
                const diff = touchEndX - touchStartX;

                // Only process significant swipes
                if (Math.abs(diff) > 50) {
                    if (diff > 0 && !drawerToggle.checked && touchStartX < 50) {
                        // Right swipe from the edge - open drawer
                        drawerToggle.checked = true;
                    } else if (diff < 0 && drawerToggle.checked) {
                        // Left swipe - close drawer
                        drawerToggle.checked = false;
                    }
                }
            }
        }, { passive: true });
    }

    // Other admin panel functionality

    // Delete confirmation (if needed)
    const deleteButtons = document.querySelectorAll('.delete-confirm');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});
