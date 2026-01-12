/**
 * Global error handler for Shimla Air Lines
 * Manages errors and dependencies to ensure graceful degradation
 */

// Global error handler
window.appErrors = {
    errors: [],
    log: function(error, source) {
        this.errors.push({
            error: error,
            source: source,
            time: new Date()
        });
        console.error(`[${source}]`, error);
    }
};

// Dependency checker
window.dependencyCheck = {
    check: function(dependency, name) {
        const exists = typeof window[dependency] !== 'undefined';
        if (!exists) {
            window.appErrors.log(`Dependency ${name} not available`, 'DependencyCheck');
        }
        return exists;
    },
    
    // Check critical dependencies and take actions
    checkAll: function() {
        // Check for libraries
        this.check('THREE', 'Three.js');
        this.check('AOS', 'AOS Animation');
        
        // Initialize fallbacks if needed
        if (!this.check('weatherService', 'Weather Service')) {
            // Create empty weather service to prevent errors
            window.weatherService = {
                getWeather: function() {
                    return Promise.reject(new Error("Weather service not available"));
                }
            };
        }
    }
};

// Run checks when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.dependencyCheck.checkAll();
});
