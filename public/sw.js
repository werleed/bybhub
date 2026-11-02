// BYBHUB Service Worker — v3.0
// Strategies: network-first HTML, cache-first static, push notifications,
//             periodic sync, App Badging API (Android/Desktop), iOS title fallback

const CACHE_VERSION    = 'bybhub-v4';  // bumped — SVG icons + badge API
const STATIC_CACHE     = `${CACHE_VERSION}-static`;
const PAGES_CACHE      = `${CACHE_VERSION}-pages`;
const ICONS_URL        = '/images/icon.svg';
// Badge icon: small opaque PNG — used by browsers that need a raster badge image
const BADGE_URL        = '/images/icon-96.png';

// Critical static assets pre-cached on install
const PRECACHE_ASSETS = [
    '/offline',
    '/manifest.json',
    '/images/logo.jpg',
    '/images/placeholder.svg',
];

// These paths are NEVER cached — always live
// Covers: all authenticated pages, payments, wallet, orders, checkout, API mutations
const NEVER_CACHE_PATHS = [
    '/admin/',
    '/dashboard/',       // all authenticated user pages — never cache
    '/hub/dashboard',    // BYBLEARN authenticated pages
    '/logout',
    '/login',
    '/register',
    '/password/',
    '/webhook/',
    '/api/',             // all API responses — always live
    '/.well-known/',
];

// HTTP methods that are NEVER intercepted
// POST/PUT/PATCH/DELETE are always passed straight to the network
const NEVER_CACHE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

// ─── Install ──────────────────────────────────────────────────────────────────
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => cache.addAll(PRECACHE_ASSETS).catch(() => {}))
            .then(() => self.skipWaiting())
    );
});

// ─── Activate ─────────────────────────────────────────────────────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys
                    .filter(k => k.startsWith('bybhub-') && k !== STATIC_CACHE && k !== PAGES_CACHE)
                    .map(k => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

// ─── Fetch ────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET and cross-origin
    if (request.method !== 'GET') return;
    if (url.origin !== location.origin) return;

    // Skip never-cache paths
    if (NEVER_CACHE_PATHS.some(p => url.pathname.startsWith(p))) return;

    // Cache-first: static assets (images, fonts, CSS, JS)
    if (url.pathname.match(/\.(css|js|woff2?|ttf|otf|png|jpg|jpeg|gif|webp|svg|ico)$/i)) {
        event.respondWith(
            caches.match(request).then(cached => {
                if (cached) return cached;
                return fetch(request)
                    .then(response => {
                        if (response.ok) {
                            caches.open(STATIC_CACHE)
                                .then(c => c.put(request, response.clone()));
                        }
                        return response;
                    })
                    .catch(() => cached || new Response('', { status: 404 }));
            })
        );
        return;
    }

    // Network-first: HTML pages with offline fallback
    if (request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then(response => {
                    if (response.ok) {
                        caches.open(PAGES_CACHE)
                            .then(c => c.put(request, response.clone()));
                    }
                    return response;
                })
                .catch(() =>
                    caches.match(request)
                        .then(cached => cached || caches.match('/offline'))
                )
        );
        return;
    }
});

// ─── Badge helpers ────────────────────────────────────────────────────────────
// setOsBadge: update the OS app-icon badge counter (Android/Desktop Chrome ≥ 81)
// iOS Safari does not support setAppBadge — the page-side JS handles title fallback.
async function setOsBadge(count) {
    if ('setAppBadge' in navigator) {
        try {
            if (count > 0) {
                await navigator.setAppBadge(count);
            } else {
                await navigator.clearAppBadge();
            }
        } catch (_) { /* permission not granted or not installed */ }
    }
}

// broadcastCount: push the fresh unread count to all open app windows so they
// can update their in-page badge UI and document title (iOS fallback) instantly.
async function broadcastCount(count) {
    const allClients = await clients.matchAll({ type: 'window', includeUncontrolled: true });
    for (const client of allClients) {
        client.postMessage({ type: 'BYBHUB_UNREAD_COUNT', count });
    }
}

// ─── Push Notifications ───────────────────────────────────────────────────────
self.addEventListener('push', event => {
    let payload = {
        title:  'BYBHUB',
        body:   'You have a new notification.',
        icon:   ICONS_URL,
        badge:  BADGE_URL,
        tag:    'bybhub-general',
        data:   { url: '/dashboard', unreadCount: null },
    };

    if (event.data) {
        try {
            const d = event.data.json();
            payload = {
                title:   d.title  || payload.title,
                body:    d.body   || payload.body,
                icon:    d.icon   || ICONS_URL,
                badge:   BADGE_URL,
                tag:     d.tag    || 'bybhub-general',
                image:   d.image  || undefined,
                vibrate: [200, 100, 200],
                data:    { url: d.url || '/dashboard', unreadCount: d.unreadCount ?? null },
                actions: d.actions || [],
            };
        } catch (e) {
            payload.body = event.data.text();
        }
    }

    event.waitUntil(
        Promise.all([
            self.registration.showNotification(payload.title, {
                body:    payload.body,
                icon:    payload.icon,
                badge:   payload.badge,
                tag:     payload.tag,
                image:   payload.image,
                vibrate: payload.vibrate,
                data:    payload.data,
                actions: payload.actions,
            }),
            // If the push payload carries a count use it; otherwise fetch live count.
            (async () => {
                let count = payload.data?.unreadCount;
                if (count === null || count === undefined) {
                    try {
                        const r = await fetch('/api/notifications/unread-count', { credentials: 'include' });
                        if (r.ok) count = (await r.json()).count ?? 1;
                        else count = 1;
                    } catch (_) { count = 1; }
                }
                await setOsBadge(count);
                await broadcastCount(count);
            })(),
        ])
    );
});

// Notification click — open the relevant URL and clear the badge
self.addEventListener('notificationclick', event => {
    event.notification.close();

    const url = event.notification.data?.url || '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(async windowClients => {
                // If app already open, focus it and navigate
                const existing = windowClients.find(c => c.url.includes(location.origin));
                if (existing) {
                    existing.focus();
                    existing.navigate(url);
                } else {
                    await clients.openWindow(url);
                }
                // Refresh badge after the user acts on the notification
                try {
                    const r = await fetch('/api/notifications/unread-count', { credentials: 'include' });
                    if (r.ok) {
                        const { count = 0 } = await r.json();
                        await setOsBadge(count);
                        await broadcastCount(count);
                    }
                } catch (_) { /* offline */ }
            })
    );
});

// ─── Message handler — page can ask the SW to refresh the badge count ─────────
self.addEventListener('message', event => {
    if (event.data?.type === 'BYBHUB_REFRESH_BADGE') {
        syncNotifications();
    }
});

// ─── Periodic Background Sync ─────────────────────────────────────────────────
// Syncs unread notification count and order status updates in background
self.addEventListener('periodicsync', event => {
    if (event.tag === 'bybhub-notifications') {
        event.waitUntil(syncNotifications());
    }
    if (event.tag === 'bybhub-orders') {
        event.waitUntil(syncOrderStatus());
    }
});

async function syncNotifications() {
    try {
        const response = await fetch('/api/notifications/unread-count', {
            credentials: 'include',
        });
        if (response.ok) {
            const data = await response.json();
            const count = data.count ?? 0;
            // Update OS app-icon badge (Android/Desktop Chrome)
            await setOsBadge(count);
            // Tell open windows the fresh count (triggers iOS title + in-page badge update)
            await broadcastCount(count);
            // Cache the count for next app open
            const cache = await caches.open(STATIC_CACHE);
            await cache.put(
                '/api/notifications/unread-count',
                new Response(JSON.stringify(data), { headers: { 'Content-Type': 'application/json' } })
            );
        }
    } catch (e) { /* offline — silent */ }
}

async function syncOrderStatus() {
    try {
        const response = await fetch('/api/orders/recent-status', {
            credentials: 'include',
        });
        if (!response.ok) return;
        const data = await response.json();
        // Fire a local notification if any order had a status change
        if (data.changed && data.changed.length > 0) {
            const order = data.changed[0];
            await self.registration.showNotification('Order Update — BYBHUB', {
                body:  `Order #${order.number}: ${order.status_label}`,
                icon:  ICONS_URL,
                badge: BADGE_URL,
                tag:   `order-${order.id}`,
                data:  { url: `/dashboard/orders/${order.id}` },
                vibrate: [100, 50, 100],
            });
        }
    } catch (e) { /* offline — silent */ }
}

// ─── Background Sync (one-off, for actions done while offline) ────────────────
self.addEventListener('sync', event => {
    if (event.tag === 'bybhub-cart-sync') {
        event.waitUntil(syncCart());
    }
});

async function syncCart() {
    // Replay any cart operations that were queued while offline
    const cache = await caches.open(STATIC_CACHE);
    const queued = await cache.match('/offline-cart-queue');
    if (!queued) return;
    try {
        const items = await queued.json();
        for (const item of items) {
            await fetch('/dashboard/cart/add', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(item),
            });
        }
        await cache.delete('/offline-cart-queue');
    } catch (e) { /* will retry on next sync */ }
}
