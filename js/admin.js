/**
 * Admin Panel JavaScript
 * Handles common admin panel functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    setupNotificationSystem();
});

/**
 * Set up notification system
 */
function setupNotificationSystem() {
    // Create global notification function
    window.showNotification = function(message, type = 'success', duration = 5000) {
        // Create notification container if it doesn't exist
        let container = document.getElementById('notification-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'toast toast-top toast-end z-50';
            document.body.appendChild(container);
        }
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert ${type === 'success' ? 'alert-success' : 'alert-error'} shadow-lg`;
        notification.innerHTML = `
            <div>
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        // Add to container
        container.appendChild(notification);
        
        // Auto dismiss
        setTimeout(() => {
            notification.remove();
        }, duration);
        
        // Close button handler
        const closeBtn = notification.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                notification.remove();
            });
        }
    };
}

/**
 * Format a date string
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

/**
 * Get status color class for badges
 */
function getStatusColorClass(status) {
    switch (status.toLowerCase()) {
        case 'pending': return 'badge-warning';
        case 'confirmed': return 'badge-primary';
        case 'cancelled': return 'badge-error';
        case 'completed': return 'badge-success';
        default: return 'badge-neutral';
    }
}
