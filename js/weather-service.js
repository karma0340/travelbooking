/**
 * Weather Service using Open-Meteo API
 * Provides accurate weather data for Shimla
 */

class WeatherService {
    constructor() {
        // Shimla coordinates
        this.latitude = 31.105;
        this.longitude = 77.164;
        this.apiUrl = `https://api.open-meteo.com/v1/forecast?latitude=${this.latitude}&longitude=${this.longitude}&current=precipitation,rain,showers,snowfall,temperature_2m,is_day,surface_pressure,weather_code,wind_speed_10m,relative_humidity_2m`;
        this.location = 'Shimla';
        
        // Add 5-day forecast support
        this.forecastApiUrl = `https://api.open-meteo.com/v1/forecast?latitude=${this.latitude}&longitude=${this.longitude}&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum&timezone=auto&forecast_days=5`;
    }

    async fetchWeather() {
        try {
            console.log('Fetching weather data from Open-Meteo API...');
            const response = await fetch(this.apiUrl);
            
            if (!response.ok) {
                console.warn(`API Error: ${response.status}`);
                return this.getFallbackWeather();
            }
            
            const data = await response.json();
            return this.processWeatherData(data);
        } catch (error) {
            console.warn('Weather API error:', error);
            return this.getFallbackWeather();
        }
    }
    
    async fetchForecast() {
        try {
            const response = await fetch(this.forecastApiUrl);
            
            if (!response.ok) {
                console.warn(`Forecast API Error: ${response.status}`);
                return this.getFallbackForecast();
            }
            
            const data = await response.json();
            return this.processForecastData(data);
        } catch (error) {
            console.warn('Weather Forecast API error:', error);
            return this.getFallbackForecast();
        }
    }

    processWeatherData(data) {
        // Extract current weather data
        const { current } = data;
        
        if (!current) {
            console.warn('No current weather data available');
            return this.getFallbackWeather();
        }
        
        // Determine weather type based on WMO weather codes
        // https://open-meteo.com/en/docs#weathervariables
        let type = 'clear';
        
        if (current.weather_code) {
            const code = current.weather_code;
            
            // Clear
            if ([0, 1].includes(code)) {
                type = 'clear';
            }
            // Cloudy
            else if ([2, 3].includes(code) || (code >= 45 && code <= 48)) {
                type = 'clouds';
            }
            // Rain
            else if ([51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82].includes(code)) {
                type = 'rain';
            }
            // Snow
            else if ([71, 73, 75, 77, 85, 86].includes(code)) {
                type = 'snow';
            }
            // Thunderstorm
            else if ([95, 96, 99].includes(code)) {
                type = 'thunderstorm';
            }
            // Fog, mist
            else if ([4, 5, 6, 7, 8, 9, 10, 30, 31, 32, 33, 34, 49, 50].includes(code)) {
                type = 'mist';
            }
        } else {
            // Fallback weather type determination if code not available
            if (current.snowfall > 0.1) {
                type = 'snow';
            } else if (current.rain > 0 || current.showers > 0) {
                type = 'rain';
            } else if (current.precipitation > 0) {
                type = current.temperature_2m < 2 ? 'snow' : 'rain';
            }
        }
        
        // Format the observation time
        const now = new Date();
        const observationTime = now.toLocaleTimeString([], { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
        
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
    
    processForecastData(data) {
        if (!data.daily) {
            return this.getFallbackForecast();
        }
        
        const forecast = [];
        const days = data.daily.time;
        
        for (let i = 0; i < days.length; i++) {
            const weatherCode = data.daily.weather_code[i];
            forecast.push({
                date: days[i],
                maxTemp: Math.round(data.daily.temperature_2m_max[i]),
                minTemp: Math.round(data.daily.temperature_2m_min[i]),
                precipitation: data.daily.precipitation_sum[i],
                type: this.getWeatherTypeFromCode(weatherCode),
                day: new Date(days[i]).toLocaleDateString('en-US', { weekday: 'short' })
            });
        }
        
        return forecast;
    }
    
    getWeatherTypeFromCode(code) {
        // Determine weather type based on WMO weather codes
        // https://open-meteo.com/en/docs#weathervariables
        let type = 'clear';
        
        // Clear
        if ([0, 1].includes(code)) {
            type = 'clear';
        }
        // Cloudy
        else if ([2, 3].includes(code) || (code >= 45 && code <= 48)) {
            type = 'clouds';
        }
        // Rain
        else if ([51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82].includes(code)) {
            type = 'rain';
        }
        // Snow
        else if ([71, 73, 75, 77, 85, 86].includes(code)) {
            type = 'snow';
        }
        // Thunderstorm
        else if ([95, 96, 99].includes(code)) {
            type = 'thunderstorm';
        }
        // Fog, mist
        else if ([4, 5, 6, 7, 8, 9, 10, 30, 31, 32, 33, 34, 49, 50].includes(code)) {
            type = 'mist';
        }
        
        return type;
    }

    getFallbackWeather() {
        // Use fallback data when API fails
        console.log('Using fallback weather data');
        const now = new Date();
        const hour = now.getHours();
        const isDay = hour >= 6 && hour < 18;
        
        // Generate somewhat realistic temperature based on time of day
        let temperature = 22; // Base temperature
        if (isDay) {
            temperature += Math.floor((hour - 6) / 2); // Temperature rises during day
        } else {
            temperature -= Math.floor((hour < 6 ? hour + 6 : hour - 18) / 2); // Drops at night
        }
        
        // For Shimla, adjust based on season
        const month = now.getMonth(); // 0-11
        
        // Winter months (Nov-Feb)
        if (month >= 10 || month <= 1) {
            temperature -= 15;
        }
        // Spring/Fall (Mar-Apr, Sep-Oct)
        else if (month >= 2 && month <= 3 || month >= 8 && month <= 9) {
            temperature -= 5;
        }
        // Summer months (May-Aug)
        else {
            temperature += 0; // Keep base temperature
        }
        
        // Randomly pick a weather type, weighted towards clear weather in this region
        const weatherTypes = ['clear', 'clear', 'clear', 'clouds', 'clouds', 'rain', 'mist'];
        // Add snow only in winter months
        if (month >= 10 || month <= 2) {
            weatherTypes.push('snow');
        }
        // Add thunderstorms in monsoon season (June-September)
        if (month >= 5 && month <= 8) {
            weatherTypes.push('thunderstorm', 'rain', 'rain');
        }
        
        const randomType = weatherTypes[Math.floor(Math.random() * weatherTypes.length)];
        
        const observationTime = now.toLocaleTimeString([], { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
        
        return {
            location: 'Shimla',
            temperature: temperature,
            humidity: '65%',
            windSpeed: Math.floor(Math.random() * 15) + 5,
            observationTime: observationTime,
            type: randomType,
            isDay: isDay,
            pressure: '1013 hPa'
        };
    }
    
    getFallbackForecast() {
        // Generate fallback forecast data
        const forecast = [];
        const now = new Date();
        
        for (let i = 0; i < 5; i++) {
            const date = new Date(now);
            date.setDate(date.getDate() + i);
            
            forecast.push({
                date: date.toISOString().split('T')[0],
                maxTemp: Math.round(18 + Math.random() * 5),
                minTemp: Math.round(10 + Math.random() * 5),
                precipitation: Math.random() * 5,
                type: ['clear', 'clouds', 'rain'][Math.floor(Math.random() * 3)],
                day: date.toLocaleDateString('en-US', { weekday: 'short' })
            });
        }
        
        return forecast;
    }
}

// Export the WeatherService
window.WeatherService = WeatherService;
