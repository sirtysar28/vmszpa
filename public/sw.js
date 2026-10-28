/* ============================================================
   VMS ZPA — Service Worker (PWA)
   - Aset inti: cache-first (cepat saat dibuka ulang)
   - Navigasi halaman: network-first (data selalu segar)
   - Hanya permintaan GET yang di-cache (POST/login tak tersentuh)
   ============================================================ */
var CACHE = 'vmszpa-v1';
var CORE_ASSETS = [
    '/css/app.css',
    '/css/kiosk.css',
    '/js/app.js',
    '/js/kiosk.js',
    '/manifest.webmanifest',
    '/images/logo-kecil.png',
    '/images/icons/icon-192.png',
    '/images/icons/icon-512.png',
];

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE).then(function (cache) {
            // addAll gagal total jika satu file error → pakai put individual
            return Promise.all(CORE_ASSETS.map(function (url) {
                return cache.add(url).catch(function () { /* abaikan gagal */ });
            }));
        }).then(function () {
            return self.skipWaiting();
        })
    );
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(keys.filter(function (k) { return k !== CACHE; })
                .map(function (k) { return caches.delete(k); }));
        }).then(function () {
            return self.clients.claim();
        })
    );
});

self.addEventListener('fetch', function (event) {
    var req = event.request;

    if (req.method !== 'GET') return;

    var url = new URL(req.url);
    if (url.origin !== location.origin) return; // hanya aset sendiri

    // Navigasi halaman: network-first, fallback cache (offline)
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req).then(function (res) {
                var copy = res.clone();
                caches.open(CACHE).then(function (c) { c.put(req, copy); });
                return res;
            }).catch(function () {
                return caches.match(req).then(function (cached) {
                    return cached || caches.match('/login');
                });
            })
        );
        return;
    }

    // Aset statis: cache-first + refresh di belakang
    var isStatic = /\.(css|js|png|jpg|jpeg|svg|webp|ico|woff2?)$/.test(url.pathname)
        || url.pathname.indexOf('/css/') === 0
        || url.pathname.indexOf('/js/') === 0
        || url.pathname.indexOf('/images/') === 0;

    if (isStatic) {
        event.respondWith(
            caches.match(req).then(function (cached) {
                var fresh = fetch(req).then(function (res) {
                    if (res && res.status === 200) {
                        var copy = res.clone();
                        caches.open(CACHE).then(function (c) { c.put(req, copy); });
                    }
                    return res;
                }).catch(function () { return cached; });
                return cached || fresh;
            })
        );
    }
});
