/* global self, caches, fetch */
// Service Worker configuration
const staticCacheName = 'stephino-rpg-__VERSION__';
const staticCacheFiles = __FILES__;
const staticOfflineFile = '__OFFLINE_FILE__';

// Installation event
self.addEventListener('install', event => {
    console && console.log('%cstephino-rpg', 'color:purple', 'Installing app version __VERSION__...');

    event.waitUntil(caches.open(staticCacheName).then(cache => {
        // Cache the game files in the background
        return cache.addAll(staticCacheFiles);
    }).then(() => {
        // Close other workers
        return self.skipWaiting();
    }));
});

// Local file server
self.addEventListener('fetch', event => {
    // Skip non-GET requests
    if ('GET' === event.request.method) {
        // HTML navigation
        if ('navigate' === event.request.mode) {
            event.respondWith((async () => {
                try {
                    // First, try to use the navigation preload response if it's supported
                    const preloadResponse = await event.preloadResponse;
                    if (preloadResponse) {
                        return preloadResponse;
                    }

                    // Preload not supported by this browser
                    const networkResponse = await fetch(event.request);
                    return networkResponse;
                } catch (error) {
                    // Network error, deliver the "offline" page
                    console && console.log('%cstephino-rpg', 'color:purple', 'Offline', error);
                    const cache = await caches.open(staticCacheName);
                    const cachedResponse = await cache.match(staticOfflineFile);
                    return cachedResponse;
                }
            })());
        } else {
            // Prepare the response, checking the cache
            event.respondWith(caches.match(event.request).then(response => {
                // Cache hit
                if (response) {
                    return response;
                }

                // Cache dynamic CSS, JS and Media (except for PWA workers and Platformer files), 
                // static UI images, JS files and fonts and files that were not saved with cache.addAll
                // Also cache the default theme's resources (immutable)
                var cacheBuster = !!event.request.url.match(
                    /(?:\bload\-scripts\.php|\badmin\-ajax\.php\?.*?\bmethod=(?:js|css|media)\b(?!.*?\bview=(?:pwa|ptf)\b)|\bstephino-rpg(?:\-pro)?\/themes\b|\bstephino-rpg\/ui\/(?:img|fonts|js\/.*?\.js)\b)/g
                );

                // Fetch the new file; hacks.mozilla.org/2016/03/referrer-and-cache-control-apis-for-fetch/
                return fetch(event.request, {cache: cacheBuster ? 'reload' : 'no-cache'}).then(response => {
                    // Browser-Server caching
                    if (!cacheBuster) {
                        return response;
                    }

                    // Local caching
                    return caches.open(staticCacheName).then(cache => {
                        console && console.log('%cstephino-rpg', 'color:purple', 'DLC ' + event.request.url);
                        cache.put(event.request, response.clone());
                        return response;
                    });
                });
            }));
        }
    }
});

// Activation event
self.addEventListener('activate', event => {
    console && console.log('%cstephino-rpg', 'color:purple', 'Activating app version __VERSION__...');
    
    // Enable navigation preload if it's supported
    event.waitUntil((async () => {
        if ('navigationPreload' in self.registration) {
            await self.registration.navigationPreload.enable();
        }
    })());
    
    // Cache clean
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(cacheNames.map(cacheName => {
                    if (-1 === [staticCacheName].indexOf(cacheName)) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            return self.clients.claim();
        })
    );
});