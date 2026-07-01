/*
 | Service worker. CACHE-FIRST for static, immutable assets only
 | (/vuexy, /build). Everything else — all server-rendered, org-scoped HTML —
 | is NETWORK-FIRST and never cached, so one tenant can never be served
 | another tenant's HTML from cache.
 */
const STATIC_CACHE = 'tailor-static-v5';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== STATIC_CACHE).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

function isStaticAsset(url) {
    return url.pathname.startsWith('/organization/') || url.pathname.startsWith('/build/');
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    if (isStaticAsset(url)) {
        // Cache-first for immutable static assets.
        event.respondWith(
            caches.open(STATIC_CACHE).then((cache) =>
                cache.match(request).then((hit) =>
                    hit || fetch(request).then((res) => {
                        if (res.ok) cache.put(request, res.clone());
                        return res;
                    })
                )
            )
        );
        return;
    }

    // Network-first, NO caching, for everything else (org-scoped HTML/JSON).
    event.respondWith(fetch(request).catch(() => caches.match(request)));
});
