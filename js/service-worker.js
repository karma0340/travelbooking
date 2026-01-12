// Service worker for caching and offline support

const CACHE_VERSION = 'v1.0.0';
const CACHE_NAME = `shimla-airlines-${CACHE_VERSION}`;

const ASSETS_TO_CACHE = [
  '/',
  '/index.php',
  '/css/style.css',
  '/js/main.js',
  '/js/booking.js',
  '/js/weather-service.js',
  '/js/weather-drawer.js',
  '/js/theme-switcher.js',
  '/js/responsive-helper.js',
  '/img/logo.png',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Install event
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
});

// Activate event
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name.startsWith('shimla-airlines-') && name !== CACHE_NAME)
          .map((name) => caches.delete(name))
      );
    })
  );
});

// Fetch event
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests and browser extension requests
  if (event.request.method !== 'GET' || event.request.url.startsWith('chrome-extension://')) {
    return;
  }
  
  // Handle API requests differently - don't cache API calls
  if (event.request.url.includes('/api/') || event.request.url.includes('webhook.php')) {
    return fetch(event.request);
  }
  
  // For all other requests, try cache first, then network
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse;
      }
      
      // Not in cache, get from network
      return fetch(event.request).then((networkResponse) => {
        // Don't cache non-successful responses
        if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
          return networkResponse;
        }
        
        // Cache successful responses
        const responseToCache = networkResponse.clone();
        caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, responseToCache);
        });
        
        return networkResponse;
      }).catch(() => {
        // For HTML pages, return the offline page
        if (event.request.headers.get('accept').includes('text/html')) {
          return caches.match('/offline.html');
        }
        return new Response('Network error occurred', { status: 503 });
      });
    })
  );
});
