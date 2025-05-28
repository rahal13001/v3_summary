var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/offline',
    '/css/app.css',
    '/js/app.js',
    "/storage/01JVCMCD7JSG0HZ1E2GQJDBNAG.png",
    "/storage/01JVCMP9XB7GJEWWM6175N56ZC.png",
    "/storage/01JVCMP9XN75BGWNYZ98W85AD0.png",
    "/storage/01JVCMP9XXGPBQCPW42321Z8JS.png",
    "/storage/01JVCMP9Y4HTS3F2DN9KH8K4V8.png",
    "/storage/01JVCMP9YD5R8SDBJXS68SWVQA.png",
    "/storage/01JVCMP9YPH74NBS6FRDT9S7PQ.png",
    "/storage/01JVCMCD83WVS00XT7SEMSVWTQ.png"
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
    )
});

// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa-")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline');
            })
    )
});
