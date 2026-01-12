/**
 * Weather Drawer - connects the WeatherService with the UI
 * Handles displaying weather data in the side drawer
 */

// Initialize the weather service and state
const weatherService = new WeatherService();
const weatherDrawerState = {
    currentScreen: 'weather',
    loading: false,
    error: null,
    data: null
};

// DOM elements
const refreshWeatherBtn = document.getElementById('refresh-weather');
const locationSelect = document.getElementById('location-select');

// Cache for 10 minutes to avoid excessive API calls
const CACHE_DURATION = 10 * 60 * 1000; // 10 minutes in milliseconds
let lastFetchTime = 0;

/**
 * Generate weather icon based on weather type and time of day
 */
function getWeatherIcon(type, isDay) {
    let iconClass = 'fa-sun';
    
    switch(type) {
        case 'clear':
            iconClass = isDay ? 'fa-sun' : 'fa-moon';
            break;
        case 'clouds':
            iconClass = isDay ? 'fa-cloud-sun' : 'fa-cloud-moon';
            break;
        case 'rain':
            iconClass = 'fa-cloud-rain';
            break;
        case 'snow':
            iconClass = 'fa-snowflake';
            break;
        case 'thunderstorm':
            iconClass = 'fa-bolt';
            break;
        case 'mist':
            iconClass = 'fa-smog';
            break;
    }
    
    return `<i class="fas ${iconClass} fa-2x"></i>`;
}

/**
 * Generate HTML for the current weather
 */
function generateCurrentWeatherHTML(data) {
    return `
        <div class="weather-card text-center">
            <div class="weather-icon-large mx-auto mb-3">
                ${getWeatherIcon(data.type, data.isDay)}
            </div>
            <div class="temperature">${data.temperature}°C</div>
            <div class="location">${data.location}, Himachal Pradesh</div>
            <div class="weather-condition">${data.type.charAt(0).toUpperCase() + data.type.slice(1)}</div>
            
            <div class="weather-details mt-4">
                <div class="weather-detail">
                    <i class="fas fa-tint"></i>
                    <span>Humidity: ${data.humidity}</span>
                </div>
                <div class="weather-detail">
                    <i class="fas fa-wind"></i>
                    <span>Wind: ${data.windSpeed}</span>
                </div>
                <div class="weather-detail">
                    <i class="fas fa-compress-alt"></i>
                    <span>Pressure: ${data.pressure}</span>
                </div>
            </div>
            <div class="text-xs mt-3 opacity-70">
                Last updated: ${data.observationTime}
            </div>
        </div>
    `;
}

/**
 * Generate weather display HTML
 */
function generateWeatherHTML(weatherData) {
    if (!weatherData) return '';
    
    return `
        <div class="weather-card p-4">
            <div class="weather-header mb-4 text-center">
                <h4 class="mb-1">${weatherData.location}</h4>
                <div class="weather-icon-large mb-3">
                    ${getWeatherIcon(weatherData.type, weatherData.isDay)}
                </div>
                <div class="temperature display-4 fw-bold mb-2">
                    ${weatherData.temperature}°C
                </div>
                <div class="weather-condition text-capitalize">
                    ${weatherData.type}
                </div>
            </div>
            
            <div class="weather-details">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="detail-item">
                            <i class="fas fa-tint text-primary me-2"></i>
                            <span>Humidity: ${weatherData.humidity}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="detail-item">
                            <i class="fas fa-wind text-primary me-2"></i>
                            <span>Wind: ${weatherData.windSpeed} km/h</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="detail-item">
                            <i class="fas fa-compress-alt text-primary me-2"></i>
                            <span>Pressure: ${weatherData.pressure}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="weather-footer mt-4 text-center">
                <small class="text-muted">
                    Last updated: ${weatherData.observationTime}
                </small>
                <button class="btn btn-sm btn-outline-primary d-block w-100 mt-3 refresh-weather">
                    <i class="fas fa-sync-alt me-2"></i> Refresh
                </button>
            </div>
        </div>
    `;
}

/**
 * Update the weather drawer content
 */
async function updateWeatherDrawer() {
    const drawerContent = document.getElementById('weather-drawer-content');
    if (!drawerContent) return;
    
    if (weatherDrawerState.loading) {
        drawerContent.innerHTML = `
            <div class="p-4 text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Fetching weather data...</p>
            </div>
        `;
        return;
    }
    
    if (weatherDrawerState.error) {
        drawerContent.innerHTML = `
            <div class="p-4 text-center">
                <div class="text-danger mb-3">
                    <i class="fas fa-exclamation-circle fa-2x"></i>
                </div>
                <p class="text-danger">${weatherDrawerState.error}</p>
                <button class="btn btn-primary mt-3 refresh-weather">
                    <i class="fas fa-redo me-2"></i> Try Again
                </button>
            </div>
        `;
        return;
    }
    
    drawerContent.innerHTML = generateWeatherHTML(weatherDrawerState.data);
    attachWeatherEvents();
}

/**
 * Fetch weather data
 */
async function fetchWeatherData() {
    weatherDrawerState.loading = true;
    weatherDrawerState.error = null;
    updateWeatherDrawer();
    
    try {
        const data = await weatherService.fetchWeather();
        weatherDrawerState.data = data;
        weatherDrawerState.error = null;
    } catch (error) {
        console.error('Weather fetch error:', error);
        weatherDrawerState.error = 'Failed to load weather data';
        weatherDrawerState.data = null;
    } finally {
        weatherDrawerState.loading = false;
        updateWeatherDrawer();
    }
}

/**
 * Attach event listeners
 */
function attachWeatherEvents() {
    const refreshBtn = document.querySelector('.refresh-weather');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', fetchWeatherData);
    }
}

// Initialize weather drawer when the DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Create weather drawer elements if they don't exist
    if (!document.getElementById('weather-drawer-content')) {
        const drawer = document.createElement('div');
        drawer.id = 'weather-drawer';
        drawer.classList.add('weather-drawer', 'drawer', 'drawer-end');
        
        drawer.innerHTML = `
            <input id="weather-drawer-toggle" type="checkbox" class="drawer-toggle">
            <div class="drawer-content">
                <!-- Page content here -->
            </div>
            <div class="drawer-side">
                <label for="weather-drawer-toggle" class="drawer-overlay"></label>
                <div class="p-4 w-80 min-h-full bg-base-200 text-base-content" id="weather-drawer-content">
                    <!-- Drawer content will be added here -->
                </div>
            </div>
        `;
        
        document.body.appendChild(drawer);
        
        // Create drawer toggle button
        const toggleBtn = document.createElement('label');
        toggleBtn.htmlFor = 'weather-drawer-toggle';
        toggleBtn.classList.add('weather-drawer-btn');
        toggleBtn.innerHTML = `
            <i class="fas fa-cloud-sun"></i>
            <span>Weather</span>
        `;
        
        document.body.appendChild(toggleBtn);
    }
    
    // Fetch weather data
    fetchWeatherData();
});


