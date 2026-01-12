// ThreeJS scene for the hero section with dynamic weather effects

let scene, camera, renderer, width, height;

// Weather-specific variables
let currentWeather = null;
let weatherUpdateInterval = null;
let weatherMode = 'clear';

// Don't redeclare weatherService if it already exists
if (typeof weatherService === 'undefined') {
    let weatherService = {
        // Service methods will be injected by weather-service.js
    };
}

// Particle systems for different weather effects
let rainSystem = null;
let snowSystem = null;
let cloudSystem = null;
let fogSystem = null;
let particlesSystem = null;
let lightningSystem = null;

// Environment elements
let mountains = null;
let skybox = null;
let directionalLight = null;
let ambientLight = null;

// Weather transition control
let transitionInProgress = false;
let transitionTimer = 0;
const TRANSITION_DURATION = 2.0; // seconds

// Constants for particles
const RAIN_COUNT = 15000;
const SNOW_COUNT = 5000;
const PARTICLE_COUNT = 750;
const CLOUD_COUNT = 20;
const CLOUD_LAYERS = 3;

// Only updating this function to improve responsiveness

function initThreeJsScene() {
    const heroCanvas = document.getElementById('hero-canvas');
    if (!heroCanvas) return;

    try {
        // Initialize weather service
        weatherService = new WeatherService();

        // Set up the scene
        scene = new THREE.Scene();

        // Set up the camera with responsive settings
        width = window.innerWidth;
        height = window.innerHeight;

        // Better mobile detection
        const isMobile = width <= 991 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        // Adjust settings more drastically for very small screens
        const isVerySmallScreen = width < 480;

        // Adjust camera FOV based on device width for better mobile appearance
        const fov = isMobile ? (isVerySmallScreen ? 95 : 85) : 75;

        camera = new THREE.PerspectiveCamera(fov, width / height, 1, 3000);
        camera.position.z = isMobile ? (isVerySmallScreen ? 500 : 450) : 350;

        // Set up the WebGL renderer with optimized settings
        renderer = new THREE.WebGLRenderer({
            canvas: heroCanvas,
            alpha: true,
            antialias: !isMobile,
            powerPreference: "high-performance"
        });

        renderer.setSize(width, height);

        // Lower pixelRatio even more on very small screens for better performance
        const pixelRatio = Math.min(window.devicePixelRatio, isVerySmallScreen ? 1 : (isMobile ? 1.5 : 2));
        renderer.setPixelRatio(pixelRatio);

        // Create scene elements with error handling
        try {
            createLights();
            createSkybox();
            createMountainSilhouette();

            // Adjust particle counts for mobile
            let particleReduction = isMobile ? (isVerySmallScreen ? 0.25 : 0.5) : 1;

            let localParticleCount = Math.floor(PARTICLE_COUNT * particleReduction);
            let localRainCount = Math.floor(RAIN_COUNT * particleReduction);
            let localSnowCount = Math.floor(SNOW_COUNT * particleReduction);
            let localCloudCount = Math.floor(CLOUD_COUNT * particleReduction);

            // Create particle systems with optimized counts
            createBackgroundParticles(localParticleCount);
            createRainSystem(localRainCount);
            createSnowSystem(localSnowCount);
            createCloudSystem(localCloudCount);
            createFogSystem(localSnowCount);
            createLightningSystem();
        } catch (error) {
            console.error("Error creating scene elements:", error);
            // If scene creation fails, hide canvas and show a fallback
            heroCanvas.style.opacity = '0';
            return;
        }

        // Initially hide weather systems except default
        hideAllWeatherSystems();

        // Initialize with current weather
        initWeather();

        // Optimize resize handling with debounce
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(onWindowResize, 250);
        }, false);

        // Add orientation change handler for mobile
        window.addEventListener('orientationchange', () => {
            setTimeout(onWindowResize, 300);
        });

        // Start animation loop with optimized timing
        animate();
    } catch (error) {
        console.error("Error initializing Three.js scene:", error);
        // If initialization fails, hide canvas to allow rest of site to function
        heroCanvas.style.opacity = '0';
    }
}

async function initWeather() {
    // Fetch weather data from Shimla API
    currentWeather = await weatherService.fetchWeather();

    // Display weather info
    updateWeatherDisplay(currentWeather);

    // Apply appropriate weather effect immediately based on API data
    applyWeatherEffect(currentWeather.type, true);

    // Add refresh button to weather display
    const weatherDisplay = document.getElementById('weather-display');
    if (weatherDisplay) {
        const refreshButton = document.createElement('button');
        refreshButton.className = 'weather-refresh';
        refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i>';
        refreshButton.title = 'Refresh weather data';
        refreshButton.addEventListener('click', refreshWeather);
        weatherDisplay.appendChild(refreshButton);
    }

    // Update weather periodically
    weatherUpdateInterval = setInterval(async () => {
        const newWeather = await weatherService.fetchWeather();
        updateWeatherDisplay(newWeather);

        // Always update weather effect when new data arrives
        if (newWeather.type !== weatherMode) {
            applyWeatherEffect(newWeather.type, false);
        }
    }, 600000); // Update every 10 minutes
}

// Add this function to allow manual weather refresh
function refreshWeather() {
    if (weatherUpdateInterval) {
        clearInterval(weatherUpdateInterval);
    }
    initWeather();
}

// Make function available globally
window.refreshWeather = refreshWeather;

// Only modifying the updateWeatherDisplay function

function updateWeatherDisplay(weatherData) {
    // Create or update weather info overlay
    let weatherDisplay = document.getElementById('weather-display');
    if (!weatherDisplay) {
        weatherDisplay = document.createElement('div');
        weatherDisplay.id = 'weather-display';
        weatherDisplay.className = 'position-absolute text-white rounded-lg d-flex align-items-center flex-column flex-md-row';

        const heroSection = document.querySelector('.hero-section');
        if (heroSection) {
            heroSection.style.position = 'relative';
            heroSection.appendChild(weatherDisplay);

            // Add hover effect with smoother transition
            weatherDisplay.addEventListener('mouseenter', () => {
                weatherDisplay.style.transform = 'translateY(-5px)';
                weatherDisplay.style.boxShadow = '0 10px 30px rgba(0,0,0,0.35)';
            });

            weatherDisplay.addEventListener('mouseleave', () => {
                weatherDisplay.style.transform = 'translateY(0)';
                weatherDisplay.style.boxShadow = '0 8px 20px rgba(0,0,0,0.2)';
            });
        }
    }

    // Check if we need to use compact mode for small screens
    const useCompactMode = window.innerWidth < 576;
    if (useCompactMode) {
        weatherDisplay.classList.add('weather-display-compact');
    } else {
        weatherDisplay.classList.remove('weather-display-compact');
    }

    // Update content with OpenMeteo API data
    let icon = '';
    switch (weatherData.type) {
        case 'clear': icon = weatherData.isDay ? 'sun' : 'moon'; break;
        case 'clouds': icon = 'cloud'; break;
        case 'rain': icon = 'cloud-rain'; break;
        case 'snow': icon = 'snowflake'; break;
        case 'mist': icon = 'smog'; break;
        case 'thunderstorm': icon = 'bolt'; break;
        default: icon = weatherData.isDay ? 'sun' : 'moon';
    }

    if (useCompactMode) {
        // Compact layout for mobile
        weatherDisplay.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="weather-icon-large me-2">
                    <i class="fas fa-${icon} fa-lg"></i>
                </div>
                <div>
                    <span class="temperature">${weatherData.temperature}°C</span>
                </div>
            </div>
            <div class="ms-auto">
                <span class="location small">${weatherData.location}</span>
            </div>
        `;
    } else {
        // Standard layout for larger screens
        weatherDisplay.innerHTML = `
            <div class="weather-icon-large me-md-3 mb-2 mb-md-0">
                <i class="fas fa-${icon} fa-2x"></i>
            </div>
            <div class="weather-details text-center text-md-start">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                    <span class="temperature fw-bold">${weatherData.temperature}°C</span>
                    <span class="location ms-2">${weatherData.location}</span>
                </div>
                <div class="weather-info small">
                    <div class="mt-1">
                        <i class="fas fa-tint me-1"></i> ${weatherData.humidity} 
                        <i class="fas fa-wind ms-2 me-1"></i> ${weatherData.windSpeed} km/h
                        <i class="fas fa-compress-alt ms-2 me-1"></i> ${weatherData.pressure}
                    </div>
                    <div class="text-white-50 small mt-1">Updated: ${weatherData.observationTime}</div>
                </div>
            </div>
        `;
    }

    // Add refresh button to weather display
    const refreshButton = document.createElement('button');
    refreshButton.className = 'weather-refresh';
    refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i>';
    refreshButton.title = 'Refresh weather data';
    refreshButton.addEventListener('click', function (e) {
        e.stopPropagation(); // Prevent event bubbling
        refreshWeather();
    });
    weatherDisplay.appendChild(refreshButton);
}

// Add responsive resize handler for weather display
window.addEventListener('resize', function () {
    const weatherDisplay = document.getElementById('weather-display');
    if (weatherDisplay) {
        if (window.innerWidth < 576) {
            weatherDisplay.classList.add('weather-display-compact');
        } else {
            weatherDisplay.classList.remove('weather-display-compact');
        }
    }
});

function applyWeatherEffect(weatherType, instant = false) {
    if (transitionInProgress && !instant) return;

    const prevWeatherMode = weatherMode;
    weatherMode = weatherType;

    if (instant) {
        // Apply weather immediately
        setWeatherMode(weatherType);
    } else {
        // Start transition
        transitionInProgress = true;
        transitionTimer = 0;

        // Fade out previous weather
        fadeOutWeatherSystem(prevWeatherMode);

        // After short delay, fade in new weather
        setTimeout(() => {
            setWeatherMode(weatherType);
            fadeInWeatherSystem(weatherType);
        }, 1000);
    }
}

function setWeatherMode(mode) {
    // Hide all weather systems first
    hideAllWeatherSystems();

    // Configure scene based on weather mode
    switch (mode) {
        case 'clear':
            scene.fog = null;
            directionalLight.intensity = currentWeather?.isDay ? 1.0 : 0.2;
            ambientLight.intensity = currentWeather?.isDay ? 0.5 : 0.1;
            particlesSystem.visible = true;
            break;

        case 'clouds':
            scene.fog = null;
            directionalLight.intensity = 0.6;
            ambientLight.intensity = 0.7;
            cloudSystem.visible = true;
            particlesSystem.visible = true;
            break;

        case 'rain':
            scene.fog = new THREE.FogExp2(0x555555, 0.002);
            directionalLight.intensity = 0.3;
            ambientLight.intensity = 0.4;
            rainSystem.visible = true;
            cloudSystem.visible = true;
            break;

        case 'snow':
            scene.fog = new THREE.FogExp2(0xaaaaaa, 0.001);
            directionalLight.intensity = 0.7;
            ambientLight.intensity = 0.6;
            snowSystem.visible = true;
            cloudSystem.visible = true;
            break;

        case 'mist':
            scene.fog = new THREE.FogExp2(0xcccccc, 0.005);
            directionalLight.intensity = 0.4;
            ambientLight.intensity = 0.6;
            fogSystem.visible = true;
            break;

        case 'thunderstorm':
            scene.fog = new THREE.FogExp2(0x333333, 0.003);
            directionalLight.intensity = 0.2;
            ambientLight.intensity = 0.3;
            rainSystem.visible = true;
            cloudSystem.visible = true;
            lightningSystem.visible = true;
            break;

        default:
            scene.fog = null;
            directionalLight.intensity = 1.0;
            ambientLight.intensity = 0.5;
            particlesSystem.visible = true;
    }

    // Update sky color based on weather
    updateSkyColor();
}

function updateSkyColor() {
    const isDay = currentWeather?.isDay ?? true;
    let color;

    switch (weatherMode) {
        case 'clear':
            color = isDay ? 0x87CEEB : 0x0C1445;
            break;
        case 'clouds':
            color = isDay ? 0xA8C0D8 : 0x283847;
            break;
        case 'rain':
            color = isDay ? 0x667788 : 0x222833;
            break;
        case 'snow':
            color = isDay ? 0xD0D8E0 : 0x394048;
            break;
        case 'mist':
            color = isDay ? 0xB8C0C8 : 0x2D3035;
            break;
        case 'thunderstorm':
            color = isDay ? 0x445566 : 0x111927;
            break;
        default:
            color = isDay ? 0x87CEEB : 0x0C1445;
    }

    if (skybox && skybox.material) {
        skybox.material.color.set(color);
    }
}

// Fix the hideAllWeatherSystems function - critical bug that affects all weather systems
function hideAllWeatherSystems() {
    if (rainSystem) rainSystem.visible = false;
    if (snowSystem) snowSystem.visible = false; // Fixed this line
    if (cloudSystem) cloudSystem.visible = false; // Fixed this line
    if (fogSystem) fogSystem.visible = false; // Fixed this line
    if (particlesSystem) particlesSystem.visible = false; // Fixed this line
    if (lightningSystem) lightningSystem.visible = false; // Fixed this line
}

function fadeOutWeatherSystem(mode) {
    // Fade out the current weather system gradually
    switch (mode) {
        case 'rain':
            fadeOutSystem(rainSystem);
            break;
        case 'snow':
            fadeOutSystem(snowSystem);
            break;
        case 'clouds':
            fadeOutSystem(cloudSystem);
            break;
        case 'mist':
            fadeOutSystem(fogSystem);
            break;
        case 'clear':
            fadeOutSystem(particlesSystem);
            break;
    }
}

function fadeInWeatherSystem(mode) {
    // Fade in the new weather system gradually
    switch (mode) {
        case 'rain':
            fadeInSystem(rainSystem);
            break;
        case 'snow':
            fadeInSystem(snowSystem);
            break;
        case 'clouds':
            fadeInSystem(cloudSystem);
            break;
        case 'mist':
            fadeInSystem(fogSystem);
            break;
        case 'clear':
            fadeInSystem(particlesSystem);
            break;
    }
}

function fadeInSystem(system) {
    if (!system) return;
    system.visible = true;
    system.material.opacity = 0;

    gsap.to(system.material, {
        opacity: 1,
        duration: TRANSITION_DURATION,
        ease: 'power1.inOut',
        onComplete: () => {
            transitionInProgress = false;
        }
    });
}

function fadeOutSystem(system) {
    if (!system || !system.visible) return;

    gsap.to(system.material, {
        opacity: 0,
        duration: TRANSITION_DURATION / 2,
        ease: 'power1.inOut',
        onComplete: () => {
            system.visible = false;
        }
    });
}

function createLights() {
    // Create main directional light (sun)
    directionalLight = new THREE.DirectionalLight(0xffffff, 1.0);
    directionalLight.position.set(0, 100, 100);
    scene.add(directionalLight);

    // Create ambient light
    ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    scene.add(ambientLight);
}

function createSkybox() {
    // Simple sky box
    const skyGeometry = new THREE.SphereGeometry(1000, 32, 32);
    const skyMaterial = new THREE.MeshBasicMaterial({
        color: 0x87CEEB, // Default sky blue
        side: THREE.BackSide,
        fog: false
    });
    skybox = new THREE.Mesh(skyGeometry, skyMaterial);
    scene.add(skybox);
}

function createMountainSilhouette() {
    const mountainCanvas = document.createElement('canvas');
    mountainCanvas.width = width;
    mountainCanvas.height = 300;
    const ctx = mountainCanvas.getContext('2d');

    // Draw a mountain range silhouette
    ctx.fillStyle = 'rgba(0, 0, 0, 0.8)';
    ctx.beginPath();
    ctx.moveTo(0, 300);

    // Create a series of peaks and valleys
    const segments = 10;
    const segmentWidth = width / segments;

    for (let i = 0; i <= segments; i++) {
        const x = i * segmentWidth;
        // Generate different heights for mountains
        const height = 150 + Math.random() * 120;
        const y = 300 - height;
        ctx.lineTo(x, y);
    }

    ctx.lineTo(width, 300);
    ctx.closePath();
    ctx.fill();

    // Create a second layer of mountains (lower opacity)
    ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';
    ctx.beginPath();
    ctx.moveTo(0, 300);

    for (let i = 0; i <= segments * 1.5; i++) {
        const x = i * (width / (segments * 1.5));
        // Generate different heights for mountains
        const height = 100 + Math.random() * 80;
        const y = 300 - height;
        ctx.lineTo(x, y);
    }

    ctx.lineTo(width, 300);
    ctx.closePath();
    ctx.fill();

    // Convert the canvas to a texture
    const texture = new THREE.CanvasTexture(mountainCanvas);

    // Create a plane geometry to display the mountains
    const mountainGeometry = new THREE.PlaneGeometry(width, 300);
    const mountainMaterial = new THREE.MeshBasicMaterial({
        map: texture,
        transparent: true
    });

    mountains = new THREE.Mesh(mountainGeometry, mountainMaterial);
    mountains.position.y = -height / 2 + 50;
    mountains.position.z = -100;

    scene.add(mountains);
}

// Update these functions to accept the dynamic particle counts
function createBackgroundParticles(count = PARTICLE_COUNT) {
    const particleGeometry = new THREE.BufferGeometry();
    const particleMaterial = new THREE.PointsMaterial({
        color: 0xFFFFFF,
        size: 2,
        transparent: true,
        opacity: 0.8,
        blending: THREE.AdditiveBlending
    });

    // Create an array of positions for particles
    const positions = new Float32Array(count * 3);
    const velocities = new Float32Array(count);

    for (let i = 0; i < count; i++) {
        // Position
        const i3 = i * 3;
        positions[i3] = (Math.random() * width - width / 2) * 2;
        positions[i3 + 1] = (Math.random() * height - height / 2) * 2;
        positions[i3 + 2] = (Math.random() * 400 - 200);

        // Velocity (for animation)
        velocities[i] = 0.1 + Math.random() * 0.5;
    }

    particleGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    particlesSystem = new THREE.Points(particleGeometry, particleMaterial);
    particlesSystem.velocities = velocities;
    scene.add(particlesSystem);
}

function createRainSystem(count = RAIN_COUNT) {
    const rainGeometry = new THREE.BufferGeometry();
    const rainMaterial = new THREE.PointsMaterial({
        color: 0xaaaaaa,
        size: 1.5,
        transparent: true,
        opacity: 0.6
    });

    // Create an array of positions for raindrops
    const positions = new Float32Array(count * 3);
    const velocities = new Float32Array(count);

    for (let i = 0; i < count; i++) {
        // Position
        const i3 = i * 3;
        positions[i3] = (Math.random() * width - width / 2) * 1.5;
        positions[i3 + 1] = (Math.random() * height - height / 2) * 1.5;
        positions[i3 + 2] = (Math.random() * 400 - 200);

        // Velocity (for animation)
        velocities[i] = 10 + Math.random() * 5;
    }

    rainGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    rainSystem = new THREE.Points(rainGeometry, rainMaterial);
    rainSystem.velocities = velocities;
    scene.add(rainSystem);
    rainSystem.visible = false;
}

function createSnowSystem(count = SNOW_COUNT) {
    const snowGeometry = new THREE.BufferGeometry();
    const snowMaterial = new THREE.PointsMaterial({
        color: 0xffffff,
        size: 3,
        transparent: true,
        opacity: 0.8
    });

    // Create an array of positions for snowflakes
    const positions = new Float32Array(count * 3);
    const velocities = new Float32Array(count);
    const swayFactors = new Float32Array(count);

    for (let i = 0; i < count; i++) {
        // Position
        const i3 = i * 3;
        positions[i3] = (Math.random() * width - width / 2) * 1.5;
        positions[i3 + 1] = (Math.random() * height - height / 2) * 1.5;
        positions[i3 + 2] = (Math.random() * 400 - 200);

        // Velocity and sway (for animation)
        velocities[i] = 1 + Math.random() * 2;
        swayFactors[i] = Math.random() * 0.1;
    }

    snowGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    snowSystem = new THREE.Points(snowGeometry, snowMaterial);
    snowSystem.velocities = velocities;
    snowSystem.swayFactors = swayFactors;
    scene.add(snowSystem);
    snowSystem.visible = false;
}

function createCloudSystem(count = CLOUD_COUNT) {
    // Create a group to hold all clouds
    cloudSystem = new THREE.Group();

    // Create cloud layers
    for (let layer = 0; layer < CLOUD_LAYERS; layer++) {
        const layerDepth = -200 - layer * 150;
        const cloudCount = count / (layer + 1);
        const scale = 1 - layer * 0.2;

        for (let i = 0; i < cloudCount; i++) {
            const cloud = createCloudParticle(scale);
            cloud.position.x = (Math.random() * width - width / 2) * 1.5;
            cloud.position.y = (Math.random() * height / 4) + (height / 5);
            cloud.position.z = layerDepth + Math.random() * 100;

            // Add custom properties for animation
            cloud.userData = {
                speed: 0.1 + Math.random() * 0.2,
                initialX: cloud.position.x
            };

            cloudSystem.add(cloud);
        }
    }

    scene.add(cloudSystem);
    cloudSystem.visible = false;
}

function createCloudParticle(scale = 1) {
    // Cloud made of multiple sprites
    const cloudGroup = new THREE.Group();

    const cloudSize = 30 * scale;
    const cloudCircleCount = 5 + Math.floor(Math.random() * 4);

    for (let i = 0; i < cloudCircleCount; i++) {
        const cloudGeometry = new THREE.PlaneGeometry(cloudSize, cloudSize);

        // Create gradient for cloud particles
        const canvas = document.createElement('canvas');
        canvas.width = 128;
        canvas.height = 128;
        const ctx = canvas.getContext('2d');

        const gradient = ctx.createRadialGradient(
            64, 64, 0,
            64, 64, 64
        );
        gradient.addColorStop(0, 'rgba(255,255,255,1)');
        gradient.addColorStop(0.5, 'rgba(255,255,255,0.5)');
        gradient.addColorStop(1, 'rgba(255,255,255,0)');

        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, 128, 128);

        const cloudTexture = new THREE.CanvasTexture(canvas);

        const cloudMaterial = new THREE.MeshBasicMaterial({
            map: cloudTexture,
            transparent: true,
            depthTest: false
        });

        const cloudMesh = new THREE.Mesh(cloudGeometry, cloudMaterial);

        // Random position around center for varied cloud shape
        cloudMesh.position.x = (Math.random() - 0.5) * cloudSize * 1.5;
        cloudMesh.position.y = (Math.random() - 0.5) * cloudSize * 0.5;

        cloudGroup.add(cloudMesh);
    }

    return cloudGroup;
}

function createFogSystem(count = SNOW_COUNT) {
    const fogGeometry = new THREE.BufferGeometry();
    const fogMaterial = new THREE.PointsMaterial({
        color: 0xcccccc,
        size: 8,
        transparent: true,
        opacity: 0.3
    });

    // Create an array of positions for fog particles
    const positions = new Float32Array(count * 3);
    const velocities = new Float32Array(count);
    const swayFactors = new Float32Array(count);

    for (let i = 0; i < count; i++) {
        // Position
        const i3 = i * 3;
        positions[i3] = (Math.random() * width - width / 2) * 2;
        positions[i3 + 1] = (Math.random() * height - height / 2) * 1.5;
        positions[i3 + 2] = (Math.random() * 500 - 250);

        // Velocity and sway (for animation)
        velocities[i] = 0.2 + Math.random() * 0.5;
        swayFactors[i] = Math.random() * 0.05;
    }

    fogGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    fogSystem = new THREE.Points(fogGeometry, fogMaterial);
    fogSystem.velocities = velocities;
    fogSystem.swayFactors = swayFactors;
    scene.add(fogSystem);
    fogSystem.visible = false;
}

function createLightningSystem() {
    // Group to hold lightning elements
    lightningSystem = new THREE.Group();
    lightningSystem.visible = false;

    // Add to scene
    scene.add(lightningSystem);
}

function triggerLightning() {
    if (!lightningSystem || !lightningSystem.visible) return;

    const lightning = createLightningBolt();
    lightningSystem.add(lightning);

    // Flash effect
    const originalIntensity = ambientLight.intensity;
    ambientLight.intensity = originalIntensity + 1.0;

    // Remove after flash
    setTimeout(() => {
        ambientLight.intensity = originalIntensity;
        lightningSystem.remove(lightning);
    }, 200);
}

function createLightningBolt() {
    // Create a lightning bolt
    const lightningMaterial = new THREE.LineBasicMaterial({
        color: 0xf0f8ff,
        linewidth: 3
    });

    const lightningPoints = [];

    // Random position for lightning
    const startX = (Math.random() * width - width / 2) * 0.8;
    const startY = height / 2;

    // Add zigzag points for lightning effect
    lightningPoints.push(new THREE.Vector3(startX, startY, -100));

    const segments = 6 + Math.floor(Math.random() * 4);
    let currentY = startY;
    let currentX = startX;

    for (let i = 0; i < segments; i++) {
        currentY -= height / segments;
        currentX += (Math.random() - 0.5) * 50;
        lightningPoints.push(new THREE.Vector3(currentX, currentY, -100));
    }

    const lightningGeometry = new THREE.BufferGeometry().setFromPoints(lightningPoints);
    const lightningBolt = new THREE.Line(lightningGeometry, lightningMaterial);

    return lightningBolt;
}

function updateParticles(delta) {
    // Update standard particles
    if (particlesSystem && particlesSystem.visible) {
        const positions = particlesSystem.geometry.attributes.position.array;

        for (let i = 0; i < PARTICLE_COUNT; i++) {
            const i3 = i * 3;

            // Slowly move particles down and slightly to the right
            positions[i3 + 1] -= particlesSystem.velocities[i];
            positions[i3] += particlesSystem.velocities[i] * 0.1;

            // Reset particles that go off screen
            if (positions[i3 + 1] < -height) {
                positions[i3 + 1] = height;
                positions[i3] = (Math.random() * width - width / 2) * 2;
                positions[i3 + 2] = (Math.random() * 400 - 200);
            }
        }

        particlesSystem.geometry.attributes.position.needsUpdate = true;
    }

    // Update rain particles
    if (rainSystem && rainSystem.visible) {
        const positions = rainSystem.geometry.attributes.position.array;

        for (let i = 0; i < RAIN_COUNT; i++) {
            const i3 = i * 3;

            // Move raindrops down quickly
            positions[i3 + 1] -= rainSystem.velocities[i] * delta * 60;

            // Add slight horizontal motion for realism
            positions[i3] += (Math.sin(Date.now() * 0.001) * 0.2 + 1) * delta * 10;

            // Reset raindrops that go off screen
            if (positions[i3 + 1] < -height) {
                positions[i3 + 1] = height;
                positions[i3] = (Math.random() * width - width / 2) * 1.5;
                positions[i3 + 2] = (Math.random() * 400 - 200);
            }
        }

        rainSystem.geometry.attributes.position.needsUpdate = true;

        // Occasionally trigger lightning in thunderstorm mode
        if (weatherMode === 'thunderstorm' && Math.random() < 0.005) {
            triggerLightning();
        }
    }

    // Update snow particles
    if (snowSystem && snowSystem.visible) {
        const positions = snowSystem.geometry.attributes.position.array;

        for (let i = 0; i < SNOW_COUNT; i++) {
            const i3 = i * 3;

            // Move snowflakes down slowly with swaying motion
            positions[i3 + 1] -= snowSystem.velocities[i] * delta * 20;
            positions[i3] += Math.sin(Date.now() * 0.001 + i) * snowSystem.swayFactors[i] * delta * 20;

            // Reset snowflakes that go off screen
            if (positions[i3 + 1] < -height) {
                positions[i3 + 1] = height;
                positions[i3] = (Math.random() * width - width / 2) * 1.5;
                positions[i3 + 2] = (Math.random() * 400 - 200);
            }
        }

        snowSystem.geometry.attributes.position.needsUpdate = true;
    }

    // Update fog particles
    if (fogSystem && fogSystem.visible) {
        const positions = fogSystem.geometry.attributes.position.array;

        for (let i = 0; i < SNOW_COUNT; i++) {
            const i3 = i * 3;

            // Move fog particles slowly with more horizontal than vertical motion
            positions[i3 + 0] += Math.sin(Date.now() * 0.0005 + i) * fogSystem.swayFactors[i] * delta * 10;
            positions[i3 + 1] += Math.cos(Date.now() * 0.0003 + i) * fogSystem.swayFactors[i] * delta * 5;

            // Reset fog particles that move too far
            if (Math.abs(positions[i3]) > width) {
                positions[i3] = (Math.random() * width - width / 2) * 1.5;
            }
            if (Math.abs(positions[i3 + 1]) > height) {
                positions[i3 + 1] = (Math.random() * height - height / 2) * 1.5;
            }
        }

        fogSystem.geometry.attributes.position.needsUpdate = true;
    }

    // Update cloud system
    if (cloudSystem && cloudSystem.visible) {
        cloudSystem.children.forEach(cloud => {
            // Move clouds slowly from right to left
            cloud.position.x -= cloud.userData.speed * delta * 10;

            // Reset clouds that go off screen
            if (cloud.position.x < -width) {
                cloud.position.x = width;
            }
        });
    }

    // Handle transitions between weather states
    if (transitionInProgress) {
        transitionTimer += delta;
        if (transitionTimer >= TRANSITION_DURATION) {
            transitionInProgress = false;
        }
    }
}

// Handle window resize
function onWindowResize() {
    width = window.innerWidth;
    height = window.innerHeight;

    camera.aspect = width / height;
    camera.updateProjectionMatrix();

    renderer.setSize(width, height);
}

// Add error handling to the animation loop to prevent blank screens
function animate() {
    try {
        requestAnimationFrame(animate);

        const delta = 1 / 60;

        // Update all particle systems based on weather
        updateParticles(delta);

        // Subtle camera movement for more interest
        camera.position.x = Math.sin(Date.now() * 0.0001) * 30;
        camera.position.y = Math.cos(Date.now() * 0.0001) * 10;
        camera.lookAt(scene.position);

        renderer.render(scene, camera);
    } catch (error) {
        console.error("Error in animation loop:", error);
        // Fallback to ensure the site remains usable if ThreeJS fails
        const heroCanvas = document.getElementById('hero-canvas');
        if (heroCanvas) {
            heroCanvas.style.opacity = '0';
        }
    }
}

/**
 * Three.js scene handling for Travel In Peace
 * Renders 3D elements and animations on the website
 */

// Don't redeclare weatherService if it already exists in global scope
if (typeof window.weatherService === 'undefined') {
    console.log("Weather service not found in global scope - Three scene will handle this separately");
    // Create local reference instead
    const threeSceneWeatherData = {
        // Local weather data for three.js animations if needed
    };
}

// Scene initialization
const initThreeScene = () => {
    try {
        const container = document.getElementById('logo-canvas');

        // If container doesn't exist, exit early
        if (!container) return;

        // Scene setup code
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);

        // Create renderer with error handling
        let renderer;
        try {
            renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
            renderer.setSize(container.clientWidth, container.clientHeight);
            container.appendChild(renderer.domElement);
        } catch (error) {
            console.error("WebGL rendering error:", error);
            return; // Exit if rendering fails
        }

        // Add responsive listener
        window.addEventListener('resize', () => {
            if (renderer && camera) {
                camera.aspect = container.clientWidth / container.clientHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(container.clientWidth, container.clientHeight);
            }
        });

        // Rest of three.js implementation would go here
    } catch (error) {
        console.error("Error initializing Three.js scene:", error);
    }
};

// Initialize scene when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Only try to initialize if THREE is available
    if (typeof THREE !== 'undefined') {
        initThreeScene();
    } else {
        console.warn("THREE.js library not loaded");
    }
});
