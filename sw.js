const CACHE_NAME = 'avero-static-v3';
const STATIC_FILES = [
  './offline.html',
  './style/offline.css?v=2',
  './style/responsive.css?v=3',
  './style/nota.css',
  './style/tandapenerima.css',
  './uploads/logo/logo_averokomputer(1).png',
  './uploads/logo/pwa-192.png',
  './uploads/logo/pwa-512.png'
];

self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_FILES)));
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key)))));
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  const request = event.request;
  const url = new URL(request.url);
  if (request.method !== 'GET' || url.origin !== self.location.origin) return;

  // Jangan cache halaman PHP yang berisi data pelanggan, servis, atau sesi admin.
  if (url.pathname.endsWith('.php') || request.mode === 'navigate') {
    if (request.mode === 'navigate') {
      event.respondWith(fetch(request).catch(() => caches.match('./offline.html')));
    }
    return;
  }

  if (/\.(css|js|png|jpe?g|webp|svg|woff2?)$/i.test(url.pathname)) {
    event.respondWith(caches.match(request).then(cached => {
      if (cached) return cached;
      return fetch(request).then(response => {
        if (!response.ok) return response;
        // Clone synchronously, before the browser consumes the original response body.
        const cacheCopy = response.clone();
        return caches.open(CACHE_NAME)
          .then(cache => cache.put(request, cacheCopy))
          .catch(error => console.warn('Aset tidak dapat disimpan ke cache:', error))
          .then(() => response);
      });
    }));
  }
});
