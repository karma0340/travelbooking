/**
 * Cache Manager for Shimla Air Lines
 * Handles client-side cache management and cache clearing when user leaves page
 */

class CacheManager {
    constructor(options = {}) {
        this.cacheToken = options.cacheToken || '';
        this.cacheFiles = options.cacheFiles || [];
        this.clearEndpoint = options.clearEndpoint || 'clear-cache.php';
        this.autoCleanup = options.autoCleanup !== false;
        this.initialized = false;
        
        if (this.autoCleanup) {
            this.setupPageLeaveListener();
        }
    }
    
    /**
     * Set up event listeners for when user leaves page
     */
    setupPageLeaveListener() {
        // Use beforeunload event to detect when user is about to leave page
        window.addEventListener('beforeunload', () => {
            this.clearServerCache();
        });
        
        // Also use the visibilitychange event as a fallback
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                this.clearServerCache();
            }
        });
        
        this.initialized = true;
    }
    
    /**
     * Clear server-side cache using the Beacon API
     * This helps ensure the request completes even if the page is unloading
     */
    clearServerCache() {
        if (!this.cacheToken) {
            console.warn('Cache token not provided, cache cannot be cleared');
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('token', this.cacheToken);
            
            if (this.cacheFiles.length > 0) {
                formData.append('cache_files', JSON.stringify(this.cacheFiles));
            }
            
            // Use sendBeacon for reliable delivery during page unload
            if (navigator.sendBeacon) {
                navigator.sendBeacon(this.clearEndpoint, formData);
            } else {
                // Fallback to traditional AJAX for older browsers
                const xhr = new XMLHttpRequest();
                xhr.open('POST', this.clearEndpoint, false); // synchronous request
                xhr.send(formData);
            }
        } catch (error) {
            console.error('Failed to clear cache:', error);
        }
    }
}

// Initialize cache manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if cache token is available in the global scope
    if (typeof CACHE_TOKEN !== 'undefined') {
        window.cacheManager = new CacheManager({
            cacheToken: CACHE_TOKEN,
            cacheFiles: CACHE_FILES || [],
            autoCleanup: true
        });
    }
});
