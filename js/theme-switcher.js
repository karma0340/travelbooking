/**
 * Theme Switcher
 * Handles switching between light and dark themes
 */

(function() {
    // Get theme from local storage or default to light
    const getStoredTheme = () => localStorage.getItem('theme') || 'light';
    
    // Set theme in both localStorage and HTML data-theme attribute
    const setTheme = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        
        // Update checkbox state
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.checked = theme === 'dark';
        }
        
        // Debug information
        console.log('Theme set to:', theme);
    };
    
    // Initialize theme immediately (even before DOMContentLoaded)
    const initialTheme = getStoredTheme();
    setTheme(initialTheme);
    
    // Apply the stored theme on page load
    document.addEventListener('DOMContentLoaded', () => {
        const storedTheme = getStoredTheme();
        setTheme(storedTheme);
        
        // Set up toggle event listener
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.checked = storedTheme === 'dark';
            
            themeToggle.addEventListener('change', (e) => {
                const newTheme = e.target.checked ? 'dark' : 'light';
                setTheme(newTheme);
            });
        } else {
            console.error('Theme toggle element not found. Check if the element with id "theme-toggle" exists.');
        }
        
        // Remove preload class to enable transitions
        setTimeout(() => {
            document.documentElement.classList.remove('preload');
            document.body.classList.remove('preload');
        }, 300);
    });
    
    // Also respect OS preference when no stored preference
    if (!localStorage.getItem('theme')) {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        setTheme(prefersDark ? 'dark' : 'light');
    }
    
    // Handle OS theme preference changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) { // Only if user hasn't explicitly set a theme
            setTheme(e.matches ? 'dark' : 'light');
        }
    });
})();
