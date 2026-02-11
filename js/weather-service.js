/**
 * Weather Service using Open-Meteo API
 * Provides accurate weather data and handles user location detection
 */

class WeatherService {
    constructor() {
        console.log("Weather Service v2.0 - Loaded (BigDataCloud API)");
        // No defaults to avoid showing wrong city during load
        this.latitude = null;
        this.longitude = null;
        this.location = 'Detecting...';
        this.isCustomLocation = false;
    }

    /**
     * Attempts to get the user's current location via Geolocation API
     */
    async getUserLocation() {
        return new Promise((resolve) => {
            if (!navigator.geolocation) {
                console.warn("Geolocation not supported.");
                resolve(false);
                return;
            }

            const options = {
                enableHighAccuracy: true,
                timeout: 8000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    this.latitude = position.coords.latitude;
                    this.longitude = position.coords.longitude;
                    this.isCustomLocation = true;

                    try {
                        // Use BigDataCloud API (Free, reliable, no Auth needed for client-side)
                        const response = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${this.latitude}&longitude=${this.longitude}&localityLanguage=en`);
                        if (response.ok) {
                            const data = await response.json();
                            this.location = data.locality || data.city || data.principalSubdivision || 'Your Location';
                        }
                    } catch (e) {
                        // Silent fail
                        this.location = 'Near You';
                    }
                    resolve(true);
                },
                (error) => {
                    // Silent fail
                    resolve(false);
                },
                options
            );
        });
    }

    /**
     * Silent IP-based location fallback (Works instantly without prompts)
     */
    /**
     * Silent IP-based location fallback (Works instantly without prompts)
     * Uses multiple providers for redundancy, prioritizing HTTPS
     */
    async getIPLocation() {
        // Provider 1: ipapi.co (HTTPS, Reliable)
        try {
            const response = await fetch('https://ipapi.co/json/');
            if (response.ok) {
                const data = await response.json();
                if (data.latitude && data.longitude) {
                    this.latitude = data.latitude;
                    this.longitude = data.longitude;
                    this.location = data.city || data.region;
                    this.isCustomLocation = true;
                    return true;
                }
            }
        } catch (e) {
            // Silent catch
        }

        // Provider 2: ip-api (HTTP fallback, might be blocked on HTTPS sites but okay for localhost)
        if (window.location.protocol === 'http:') {
            try {
                const response = await fetch('http://ip-api.com/json/');
                const data = await response.json();
                if (data.status === 'success') {
                    this.latitude = data.lat;
                    this.longitude = data.lon;
                    this.location = data.city || data.regionName;
                    this.isCustomLocation = true;
                    return true;
                }
            } catch (e) { }
        }

        return false;
    }

    async fetchWeather() {
        try {
            // Step 1: Silent IP Detection
            if (!this.isCustomLocation) {
                const found = await this.getIPLocation();
                if (found && window.onWeatherUpdate) {
                    this.performFetch().then(data => {
                        if (window.onWeatherUpdate) window.onWeatherUpdate(data);
                    }).catch(() => { });
                }
            }

            // Step 2: High-Accuracy GPS Upgrade (Background)
            this.getUserLocation().then(async success => {
                if (success) {
                    const freshData = await this.performFetch();
                    if (window.onWeatherUpdate) window.onWeatherUpdate(freshData);
                }
            });

            // If everything failed (e.g. localhost with no internet), use Shimla as last resort
            if (!this.latitude || !this.longitude) {
                this.latitude = 31.1048;
                this.longitude = 77.1734;
                this.location = 'Shimla';
            }

            return await this.performFetch();
        } catch (error) {
            // Silent fallback
            return this.getFallbackWeather();
        }
    }

    /**
     * Internal fetch logic to avoid duplication
     */
    async performFetch() {
        const apiUrl = `https://api.open-meteo.com/v1/forecast?latitude=${this.latitude}&longitude=${this.longitude}&current=precipitation,rain,showers,snowfall,temperature_2m,is_day,surface_pressure,weather_code,wind_speed_10m,relative_humidity_2m`;
        const response = await fetch(apiUrl);
        if (!response.ok) return this.getFallbackWeather();
        const data = await response.json();
        return this.processWeatherData(data);
    }


    processWeatherData(data) {
        const { current } = data;
        if (!current) return this.getFallbackWeather();

        // Determine weather type based on WMO weather codes
        let type = 'clear';
        if (current.weather_code !== undefined) {
            const code = current.weather_code;
            if ([0, 1].includes(code)) type = 'clear';
            else if ([2, 3].includes(code) || (code >= 45 && code <= 48)) type = 'clouds';
            else if ([51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82].includes(code)) type = 'rain';
            else if ([71, 73, 75, 77, 85, 86].includes(code)) type = 'snow';
            else if ([95, 96, 99].includes(code)) type = 'thunderstorm';
            else if ([4, 5, 6, 7, 8, 9, 10, 30, 31, 32, 33, 34, 49, 50].includes(code)) type = 'mist';
        }

        const now = new Date();
        const observationTime = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });

        return {
            location: this.location,
            temperature: Math.round(current.temperature_2m),
            humidity: `${Math.round(current.relative_humidity_2m || 60)}%`,
            windSpeed: Math.round(current.wind_speed_10m || 5),
            observationTime: observationTime,
            type: type,
            isDay: current.is_day === 1,
            pressure: `${Math.round(current.surface_pressure / 100)} hPa`
        };
    }

    getFallbackWeather() {
        const now = new Date();
        const hour = now.getHours();
        const isDay = hour >= 6 && hour < 18;
        return {
            location: this.location || 'Shimla',
            temperature: 18,
            humidity: '60%',
            windSpeed: 5,
            observationTime: now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true }),
            type: 'clear',
            isDay: isDay,
            pressure: '1013 hPa'
        };
    }
}

window.WeatherService = WeatherService;
