/**
 * Ultimate Weather Layer - Professional Atmospheric Engine
 * Uses Procedural Textures for High-Fidelity "Soft" Particles.
 * No external image dependencies required.
 */

(function () {
    let scene, camera, renderer, width, height;
    let weatherService = null;
    let weatherMode = 'clear';
    let currentTheme = 'light';

    // Systems
    let rainSystem = null;
    let snowSystem = null;
    let cloudGroup = null;
    let starField = null;
    let glintSystem = null;
    let lightningLight = null;
    let ambientLight = null;
    let directionalLight = null;

    // Responsive Config
    const getDensity = (base) => window.innerWidth < 768 ? base * 0.5 : base;
    const getSize = (base) => window.innerWidth < 768 ? base * 2.0 : base;

    // --- PROCEDURAL TEXTURE GENERATION ---
    // Creates professional soft-glow gradients on the fly
    function createSoftTexture() {
        const canvas = document.createElement('canvas');
        canvas.width = 32;
        canvas.height = 32;
        const context = canvas.getContext('2d');
        const gradient = context.createRadialGradient(16, 16, 0, 16, 16, 16);
        gradient.addColorStop(0, 'rgba(255,255,255,1)');
        gradient.addColorStop(0.2, 'rgba(255,255,255,0.8)');
        gradient.addColorStop(0.5, 'rgba(255,255,255,0.2)');
        gradient.addColorStop(1, 'rgba(0,0,0,0)');
        context.fillStyle = gradient;
        context.fillRect(0, 0, 32, 32);
        const texture = new THREE.CanvasTexture(canvas);
        return texture;
    }

    function createRainTexture() {
        const canvas = document.createElement('canvas');
        canvas.width = 32;
        canvas.height = 128;
        const context = canvas.getContext('2d');
        const gradient = context.createLinearGradient(0, 0, 0, 128);
        gradient.addColorStop(0, 'rgba(255,255,255,0)');
        gradient.addColorStop(0.5, 'rgba(170, 200, 255, 0.8)'); // Light blue tint
        gradient.addColorStop(1, 'rgba(255,255,255,0)');
        context.fillStyle = gradient;
        context.fillRect(0, 0, 32, 128);
        const texture = new THREE.CanvasTexture(canvas);
        return texture;
    }

    function createCloudTexture() {
        const canvas = document.createElement('canvas');
        canvas.width = 128; // Larger for clouds
        canvas.height = 128;
        const context = canvas.getContext('2d');
        // Create a puffy cloud shape using radial gradients
        const gradient = context.createRadialGradient(64, 64, 0, 64, 64, 64);
        gradient.addColorStop(0, 'rgba(255,255,255, 0.9)');
        gradient.addColorStop(0.4, 'rgba(255,255,255, 0.5)');
        gradient.addColorStop(0.8, 'rgba(255,255,255, 0.1)');
        gradient.addColorStop(1, 'rgba(0,0,0,0)');
        context.fillStyle = gradient;
        context.fillRect(0, 0, 128, 128);
        const texture = new THREE.CanvasTexture(canvas);
        return texture;
    }


    function init() {
        if (typeof THREE === 'undefined') {
            console.warn('Three.js not loaded. Skipping atmospheric engine.');
            return;
        }

        const canvas = document.getElementById('hero-canvas');
        if (!canvas) return;

        if (typeof window.WeatherService !== 'undefined' && !weatherService) {
            weatherService = new window.WeatherService();
            // Silent init, don't wait
            updateWeather();
        }

        try {
            scene = new THREE.Scene();
            // Subtle blue-ish fog for depth in dark mode
            scene.fog = new THREE.FogExp2(0x000000, 0.0003);

            width = window.innerWidth;
            height = window.innerHeight;
            currentTheme = document.documentElement.getAttribute('data-theme') || 'light';

            camera = new THREE.PerspectiveCamera(70, width / height, 1, 5000);
            camera.position.z = 100;

            renderer = new THREE.WebGLRenderer({
                canvas: canvas,
                alpha: true,
                antialias: false,
                powerPreference: "high-performance"
            });
            renderer.setSize(width, height);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

            // Lighting
            ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
            scene.add(ambientLight);

            directionalLight = new THREE.DirectionalLight(0xa5b4fc, 1.2);
            directionalLight.position.set(2, 5, 5);
            scene.add(directionalLight);

            lightningLight = new THREE.PointLight(0xffffff, 0, 5000);
            lightningLight.position.set(0, 1000, 0);
            scene.add(lightningLight);

            // Create Professional Textured Systems
            createRain();
            createSnow();
            createClouds();
            createStars();
            createGlints();
            triggerShootingStar(); // Start the celestial cycle

            setWeatherEffect('clear', true);

            // Custom Event Listener for instant theme updates
            window.addEventListener('themeChanged', (e) => {
                currentTheme = e.detail.theme;
                updateThemeColors();
            });

            // Fallback: Initial check
            currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            updateThemeColors();

            window.addEventListener('resize', onResize);
            animate();

            // Force visibility immediately
            canvas.style.opacity = '1';
            canvas.style.visibility = 'visible';
            canvas.style.transition = 'none'; // Disable CSS fade-in

            // Initial color set
            updateThemeColors();

            // INSTANT APPEARANCE: Render one frame immediately before loop
            renderer.render(scene, camera);

            // Set initial state without animation
            // This ensures particles are visible frame 1
            const initialType = 'clear';
            // We use 'clear' as safe default, weather update will override in ms
            const isDay = new Date().getHours() > 6 && new Date().getHours() < 18;
            setWeatherEffect(initialType, isDay, true); // true = instant

        } catch (e) {
            console.error("Atmospheric Engine Error:", e);
        }
    }

    function updateThemeColors() {
        const isLight = currentTheme === 'light';
        const dur = 1.0;
        const isGsap = typeof gsap !== 'undefined';

        const setColor = (system, colorHex) => {
            if (system && system.material) {
                const col = new THREE.Color(colorHex);
                if (isGsap) gsap.to(system.material.color, { r: col.r, g: col.g, b: col.b, duration: dur });
                else system.material.color.set(colorHex);
            }
        };

        // Darker particles for Light Theme (Visible on white), Lighter for Dark Theme
        setColor(rainSystem, isLight ? 0x2255aa : 0x88ccff);     // Deep Blue vs Bright Blue
        setColor(snowSystem, isLight ? 0x8899aa : 0xffffff);     // Grey-Blue vs White
        setColor(starField, isLight ? 0x444444 : 0xffffff);      // Dark Grey vs White
        setColor(glintSystem, isLight ? 0xffaa00 : 0xfcd34d);    // Deep Gold vs Light Gold

        if (cloudGroup) {
            cloudGroup.children.forEach(c => {
                const col = new THREE.Color(isLight ? 0xbbbbbb : 0xffffff); // Grey vs White
                if (isGsap) gsap.to(c.material.color, { r: col.r, g: col.g, b: col.b, duration: dur });
                else c.material.color.set(col);
            });
        }
    }

    function createRain() {
        const density = getDensity(2000);
        const geo = new THREE.BufferGeometry();
        const pos = new Float32Array(density * 3);
        const vels = [];
        for (let i = 0; i < density; i++) {
            pos[i * 3] = (Math.random() - 0.5) * 4000;
            pos[i * 3 + 1] = Math.random() * 2000 - 1000;
            pos[i * 3 + 2] = Math.random() * 1000 - 500;
            vels.push(40 + Math.random() * 20); // Fast fall
        }
        geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));

        const mat = new THREE.PointsMaterial({
            color: 0xffffff,
            size: getSize(12), // Taller streaks
            map: createRainTexture(),
            transparent: true,
            opacity: 0,
            blending: THREE.AdditiveBlending,
            depthWrite: false
        });
        rainSystem = new THREE.Points(geo, mat);
        rainSystem.userData.vels = vels;
        scene.add(rainSystem);
    }

    function createSnow() {
        const density = getDensity(1500);
        const geo = new THREE.BufferGeometry();
        const pos = new Float32Array(density * 3);
        const vels = [];
        for (let i = 0; i < density; i++) {
            pos[i * 3] = (Math.random() - 0.5) * 4000;
            pos[i * 3 + 1] = Math.random() * 2000 - 1000;
            pos[i * 3 + 2] = Math.random() * 1000 - 600;
            vels.push(3 + Math.random() * 3);
        }
        geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));

        const mat = new THREE.PointsMaterial({
            color: 0xffffff,
            size: getSize(8), // Soft balls
            map: createSoftTexture(), // Soft radial gradient
            transparent: true,
            opacity: 0,
            blending: THREE.AdditiveBlending,
            depthWrite: false
        });
        snowSystem = new THREE.Points(geo, mat);
        snowSystem.userData.vels = vels;
        scene.add(snowSystem);
    }

    function createClouds() {
        cloudGroup = new THREE.Group();
        const count = getDensity(8);
        const tex = createCloudTexture();

        for (let i = 0; i < count; i++) {
            const mat = new THREE.MeshBasicMaterial({
                color: 0xffffff,
                map: tex,
                transparent: true,
                opacity: 0,
                depthWrite: false,
                blending: THREE.AdditiveBlending
            });
            const mesh = new THREE.Mesh(new THREE.PlaneGeometry(getSize(2000), getSize(1200)), mat);
            mesh.position.set(
                (Math.random() - 0.5) * 5000,
                300 + Math.random() * 500,
                -1500 + Math.random() * 500
            );
            mesh.userData.speed = 0.1 + Math.random() * 0.3;
            cloudGroup.add(mesh);
        }
        scene.add(cloudGroup);
    }

    let shootingStar = null;
    function createStars() {
        const count = getDensity(3000);
        const backgrounds = new THREE.Group();

        // Helper to create a star field at a specific depth
        const createField = (depth, size, color, density) => {
            const geo = new THREE.BufferGeometry();
            const pos = new Float32Array(density * 3);
            const colors = new Float32Array(density * 3);
            const colorObj = new THREE.Color();

            for (let i = 0; i < density; i++) {
                pos[i * 3] = (Math.random() - 0.5) * 6000;
                pos[i * 3 + 1] = (Math.random() - 0.5) * 4000;
                pos[i * 3 + 2] = depth;

                // Subtle color variations (Blues and Yellows)
                const variant = Math.random();
                if (variant > 0.8) colorObj.set(0xa5b4fc); // Blue-ish
                else if (variant > 0.6) colorObj.set(0xfef08a); // Yellow-ish
                else colorObj.set(0xffffff);

                colors[i * 3] = colorObj.r;
                colors[i * 3 + 1] = colorObj.g;
                colors[i * 3 + 2] = colorObj.b;
            }
            geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
            geo.setAttribute('color', new THREE.BufferAttribute(colors, 3));

            const mat = new THREE.PointsMaterial({
                size: getSize(size),
                map: createSoftTexture(),
                transparent: true,
                opacity: 0,
                vertexColors: true,
                blending: THREE.AdditiveBlending,
                depthWrite: false
            });
            return new THREE.Points(geo, mat);
        };

        // Create 3 layers for parallax depth
        starField = new THREE.Group();
        starField.add(createField(-1500, 4, 0xffffff, count * 0.5));
        starField.add(createField(-1000, 6, 0xffffff, count * 0.3));
        starField.add(createField(-600, 8, 0xffffff, count * 0.2));
        scene.add(starField);

        // Shooting Star System
        const sGeo = new THREE.BufferGeometry();
        sGeo.setAttribute('position', new THREE.BufferAttribute(new Float32Array([0, 0, 0, 0, 0, 0]), 3));
        const sMat = new THREE.LineBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0 });
        shootingStar = new THREE.Line(sGeo, sMat);
        scene.add(shootingStar);
    }

    function createGlints() {
        const density = getDensity(200);
        const geo = new THREE.BufferGeometry();
        const pos = new Float32Array(density * 3);
        for (let i = 0; i < density; i++) {
            pos[i * 3] = (Math.random() - 0.5) * 3000;
            pos[i * 3 + 1] = (Math.random() - 0.5) * 2000;
            pos[i * 3 + 2] = Math.random() * 500 - 500;
        }
        geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));

        const mat = new THREE.PointsMaterial({
            color: 0xfcd34d,
            size: getSize(12),
            map: createSoftTexture(), // Soft sun flecks
            transparent: true,
            opacity: 0,
            blending: THREE.AdditiveBlending,
            depthWrite: false
        });
        glintSystem = new THREE.Points(geo, mat);
        scene.add(glintSystem);
    }

    function triggerShootingStar() {
        if (weatherMode !== 'clear' || !shootingStar) {
            setTimeout(triggerShootingStar, 5000);
            return;
        }

        const startX = (Math.random() - 0.5) * 2000;
        const startY = 800 + Math.random() * 200;
        const angle = Math.PI / 4 + Math.random() * 0.5;
        const length = 200;

        shootingStar.material.opacity = 0.8;
        let progress = 0;

        const move = () => {
            progress += 0.05;
            const x = startX + Math.cos(angle) * progress * 2000;
            const y = startY - Math.sin(angle) * progress * 2000;

            const pos = shootingStar.geometry.attributes.position.array;
            pos[0] = x; pos[1] = y;
            pos[3] = x - Math.cos(angle) * length;
            pos[4] = y + Math.sin(angle) * length;
            shootingStar.geometry.attributes.position.needsUpdate = true;

            if (progress < 1) requestAnimationFrame(move);
            else {
                shootingStar.material.opacity = 0;
                setTimeout(triggerShootingStar, 8000 + Math.random() * 15000);
            }
        };
        move();
    }

    // ... (Keep existing updateWeather, updatePill, setWeatherEffect, and animate functions exactly as they were, just ensuring they use the new variables) ...
    // Note: I will rewrite them to be safe.

    async function updateWeather() {
        try {
            const data = await weatherService.fetchWeather();
            setWeatherEffect(data.type, data.isDay);
            updatePill(data);

            window.onWeatherUpdate = (newData) => {
                setWeatherEffect(newData.type, newData.isDay);
                updatePill(newData);
            };
            if (weatherService) {
                setTimeout(updateWeather, 600000); // 10 minutes
            }
        } catch (e) {
            setWeatherEffect('clear', true);
        }
    }

    function updatePill(data) {
        let pill = document.querySelector('.weather-indicator-pill');
        if (!pill) {
            pill = document.createElement('div');
            pill.className = 'weather-indicator-pill';
            document.body.appendChild(pill);

            // Inject Styles for the original balanced pill
            const style = document.createElement('style');
            style.textContent = `
                .weather-indicator-pill {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: rgba(255, 255, 255, 0.1);
                    backdrop-filter: blur(15px) saturate(180%);
                    -webkit-backdrop-filter: blur(15px) saturate(180%);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    border-radius: 50px;
                    padding: 8px 18px;
                    color: white;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    z-index: 10001;
                    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
                    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    font-family: inherit;
                    font-size: 0.85rem;
                    font-weight: 700;
                    opacity: 0;
                    transform: translateY(-20px);
                }
                .weather-indicator-pill.visible {
                    opacity: 1;
                    transform: translateY(0);
                }
                .weather-indicator-pill i {
                    color: #fbbf24;
                    font-size: 1.1rem;
                    filter: drop-shadow(0 0 5px rgba(251, 191, 36, 0.5));
                }
                [data-theme="light"] .weather-indicator-pill {
                    background: rgba(15, 23, 42, 0.05);
                    color: #1e293b;
                    border-color: rgba(0, 0, 0, 0.1);
                }
                @media (max-width: 768px) {
                    .weather-indicator-pill {
                        top: 15px;
                        right: 15px;
                        padding: 6px 14px;
                        font-size: 0.75rem;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        const icons = {
            clear: 'sun',
            clouds: 'cloud',
            rain: 'cloud-showers-heavy',
            snow: 'snowflake',
            mist: 'smog',
            thunderstorm: 'bolt'
        };

        let icon = icons[data.type] || (data.isDay ? 'sun' : 'moon');
        if (!data.isDay && data.type === 'clear') icon = 'moon';

        pill.innerHTML = `<i class="fas fa-${icon}"></i> <span>${data.location} · ${data.temperature}°C</span>`;

        // Show with animation
        setTimeout(() => pill.classList.add('visible'), 100);
    }


    let lightningTimeout;
    function triggerLightning() {
        if (weatherMode !== 'thunderstorm' || !lightningLight) return;

        // Randomly trigger a flash
        const flash = () => {
            if (!lightningLight) return;
            const power = 3 + Math.random() * 7;
            lightningLight.intensity = power;

            // Subtle camera shake
            if (camera) {
                camera.position.x += (Math.random() - 0.5) * 10;
                camera.position.y += (Math.random() - 0.5) * 10;
            }

            setTimeout(() => {
                lightningLight.intensity = 0;
                // Double strike chance
                if (Math.random() > 0.6) {
                    setTimeout(() => {
                        lightningLight.intensity = power * 0.5;
                        setTimeout(() => { lightningLight.intensity = 0; }, 50);
                    }, 100);
                }
            }, 70);
        };

        flash();
        lightningTimeout = setTimeout(triggerLightning, 4000 + Math.random() * 10000);
    }


    function setWeatherEffect(type, isDay, isInstant = false) {
        weatherMode = type;
        const dur = isInstant ? 0 : 2.0;
        const isGsapActive = typeof gsap !== 'undefined';

        if (lightningTimeout) clearTimeout(lightningTimeout);
        if (lightningLight) lightningLight.intensity = 0;

        const update = (obj, opacity) => {
            if (!obj || !obj.material) return;
            if (isInstant) {
                obj.material.opacity = opacity;
                obj.visible = opacity > 0;
            } else if (isGsapActive) {
                if (opacity > 0) obj.visible = true;
                gsap.to(obj.material, {
                    opacity: opacity,
                    duration: dur,
                    onComplete: () => { if (opacity === 0) obj.visible = false; }
                });
            } else {
                obj.material.opacity = opacity;
                obj.visible = opacity > 0;
            }
        };

        // Reset all systems
        update(rainSystem, 0);
        update(snowSystem, 0);
        if (starField) starField.children.forEach(c => update(c, 0));
        update(glintSystem, 0);
        if (cloudGroup) cloudGroup.children.forEach(c => update(c, 0));

        // Activate specific systems
        if (type === 'rain' || type === 'thunderstorm') {
            update(rainSystem, 0.7);
            if (cloudGroup) cloudGroup.children.forEach(c => update(c, isDay ? 0.3 : 0.15));
            if (type === 'thunderstorm') triggerLightning();
        } else if (type === 'snow') {
            update(snowSystem, 0.8);
            if (cloudGroup) cloudGroup.children.forEach(c => update(c, isDay ? 0.4 : 0.2));
        } else if (type === 'clouds' || type === 'mist') {
            if (cloudGroup) cloudGroup.children.forEach(c => update(c, isDay ? 0.4 : 0.2));
        } else if (type === 'clear') {
            if (isDay) update(glintSystem, 0.6);
            else if (starField) starField.children.forEach(c => update(c, 1.0));
        }

        // Scene Fog / Ambient Light adjustments
        if (scene && scene.fog) {
            const fogColor = (type === 'rain' || type === 'thunderstorm') ? 0x1e293b : 0x000000;
            if (isGsapActive) gsap.to(scene.fog.color, { ...new THREE.Color(fogColor), duration: dur });
            else scene.fog.color.set(fogColor);
        }
    }

    function animate() {
        if (!renderer || !scene || !camera) return;
        requestAnimationFrame(animate);
        const t = Date.now() * 0.001;

        // Base idle camera motion
        camera.position.x += (Math.sin(t * 0.3) * 0.5 - camera.position.x) * 0.05;
        camera.lookAt(0, 0, 0);

        if (rainSystem && rainSystem.material.opacity > 0) {
            const pos = rainSystem.geometry.attributes.position.array;
            const vels = rainSystem.userData.vels;
            for (let i = 0; i < pos.length / 3; i++) {
                pos[i * 3 + 1] -= vels[i];
                pos[i * 3] -= 5; // Wind Slant (Zomato Style)
                if (pos[i * 3 + 1] < -1200) {
                    pos[i * 3 + 1] = 1200;
                    pos[i * 3] = (Math.random() - 0.5) * 4000;
                }
            }
            rainSystem.geometry.attributes.position.needsUpdate = true;
        }

        if (snowSystem && snowSystem.material.opacity > 0) {
            const pos = snowSystem.geometry.attributes.position.array;
            const vels = snowSystem.userData.vels;
            for (let i = 0; i < pos.length / 3; i++) {
                pos[i * 3 + 1] -= vels[i];
                pos[i * 3] += Math.sin(t + i) * 1.5; // Swirly snow
                if (pos[i * 3 + 1] < -1000) pos[i * 3 + 1] = 1000;
            }
            snowSystem.geometry.attributes.position.needsUpdate = true;
        }

        if (glintSystem && glintSystem.material.opacity > 0) {
            glintSystem.rotation.y += 0.001;
            glintSystem.rotation.z += 0.0005;
        }

        if (starField && starField.children[0].material.opacity > 0) {
            starField.children.forEach((layer, i) => {
                layer.rotation.y += 0.0001 * (i + 1);
                // Randomized twinkling
                layer.material.size = getSize(4 + i * 2) + Math.sin(t * (2 + i)) * 1.5;
            });
        }

        if (cloudGroup) {
            cloudGroup.children.forEach(c => {
                if (c.material.opacity > 0) {
                    c.position.x += c.userData.speed;
                    if (c.position.x > 3500) c.position.x = -3500;
                }
            });
        }

        renderer.render(scene, camera);
    }

    function onResize() {
        if (!camera || !renderer) return;
        width = window.innerWidth;
        height = window.innerHeight;
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
