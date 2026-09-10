const CACHE_NAME = 'trovapiatto-v5';
const STATIC_ASSETS = [
  '/',
  '/index.html',
  '/app/',
  '/app/index.html',
  '/piatti/',
  '/piatti/index.html',
  '/ristorante.html',
  '/bari/',
  '/style.css',
  '/script.js',
  '/logo/logo_ufficiale.png',
  '/logo/default-ristorante.svg',
  '/favicon.svg',
  '/site.webmanifest'
];

const API_CACHE = 'trovapiatto-api-v1';
const API_CACHE_DURATION = 5 * 60 * 1000;

self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE_NAME).then(c => c.addAll(STATIC_ASSETS)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(k => k !== CACHE_NAME && k !== API_CACHE).map(k => caches.delete(k))
    )).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', e => {
  if (e.request.method !== 'GET') return;
  const url = new URL(e.request.url);

  if (url.pathname.includes('/api.php') || url.pathname.includes('/api-crud.php') || url.pathname.includes('/api-auth.php')) {
    e.respondWith(
      caches.open(API_CACHE).then(cache => {
        return cache.match(e.request).then(cached => {
          const fetchPromise = fetch(e.request).then(response => {
            if (response.ok) {
              const cloned = response.clone();
              cloned.text().then(body => {
                try {
                  const data = JSON.parse(body);
                  cache.put(e.request, new Response(JSON.stringify(data), {
                    headers: { 'Content-Type': 'application/json', 'X-Cached-At': Date.now().toString() }
                  }));
                } catch(err) {}
              });
            }
            return response;
          }).catch(() => cached);

          if (cached) {
            const cachedAt = parseInt(cached.headers.get('X-Cached-At') || '0');
            if (Date.now() - cachedAt < API_CACHE_DURATION) return cached;
          }
          return fetchPromise;
        });
      })
    );
    return;
  }

  e.respondWith(
    caches.match(e.request).then(cached => cached || fetch(e.request).then(response => {
      if (response.ok && url.origin === self.location.origin) {
        const cloned = response.clone();
        caches.open(CACHE_NAME).then(c => c.put(e.request, cloned));
      }
      return response;
    }))
  );
});
