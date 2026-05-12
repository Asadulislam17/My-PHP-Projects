/**
 * ══════════════════════════════════════════════
 * SERVICE WORKER — RealEstate BD PWA
 * Version: 2.0.0
 * Strategy: Cache First + Network Fallback
 * ══════════════════════════════════════════════
 */

const SW_VERSION    = 'v2.0.0';
const CACHE_STATIC  = `re-static-${SW_VERSION}`;
const CACHE_DYNAMIC = `re-dynamic-${SW_VERSION}`;
const CACHE_IMAGES  = `re-images-${SW_VERSION}`;
const CACHE_API     = `re-api-${SW_VERSION}`;

/* ── Files to precache on install ── */
const PRECACHE_URLS = [
  '/',
  '/?page=home',
  '/?page=listing',
  '/?page=tools&tool=emi',
  '/assets/css/style.css',
  '/assets/js/main.js',
  '/assets/images/no-image.webp',
  '/assets/images/logo.webp',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
];

/* ── Offline fallback page ── */
const OFFLINE_PAGE  = '/offline.html';
const OFFLINE_IMAGE = '/assets/images/offline-placeholder.webp';

/* ── Cache limits ── */
const MAX_DYNAMIC_ITEMS = 100;
const MAX_IMAGE_ITEMS   = 150;
const MAX_API_ITEMS     = 50;
const CACHE_TTL_API     = 5 * 60; // 5 minutes in seconds

/* ════════════════════════════════════════════
   INSTALL — Precache static assets
════════════════════════════════════════════ */
self.addEventListener('install', event => {
  console.log('[SW] Installing', SW_VERSION);

  event.waitUntil(
    caches.open(CACHE_STATIC).then(cache => {
      console.log('[SW] Precaching static assets');
      return cache.addAll(PRECACHE_URLS.map(url => new Request(url, { cache: 'reload' })));
    }).then(() => self.skipWaiting())
  );
});

/* ════════════════════════════════════════════
   ACTIVATE — Clean old caches
════════════════════════════════════════════ */
self.addEventListener('activate', event => {
  console.log('[SW] Activating', SW_VERSION);

  const validCaches = [CACHE_STATIC, CACHE_DYNAMIC, CACHE_IMAGES, CACHE_API];

  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => !validCaches.includes(key))
          .map(key => {
            console.log('[SW] Deleting old cache:', key);
            return caches.delete(key);
          })
      )
    ).then(() => self.clients.claim())
  );
});

/* ════════════════════════════════════════════
   FETCH — Routing strategies
════════════════════════════════════════════ */
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // ── Skip non-GET & browser-extension ──
  if (request.method !== 'GET') return;
  if (!['http:', 'https:'].includes(url.protocol)) return;

  // ── API requests → Network first, short cache ──
  if (url.pathname.startsWith('/api/v1/')) {
    event.respondWith(networkFirstWithCache(request, CACHE_API, CACHE_TTL_API));
    return;
  }

  // ── Images → Cache first, long cache ──
  if (request.destination === 'image' || url.pathname.match(/\.(webp|jpg|jpeg|png|gif|svg|ico)$/i)) {
    event.respondWith(cacheFirstWithFallback(request, CACHE_IMAGES, MAX_IMAGE_ITEMS));
    return;
  }

  // ── Fonts & CDN → Cache first ──
  if (url.hostname.includes('fonts.googleapis.com') ||
      url.hostname.includes('cdn.jsdelivr.net') ||
      url.hostname.includes('unpkg.com')) {
    event.respondWith(cacheFirstWithFallback(request, CACHE_STATIC, 999));
    return;
  }

  // ── Static assets ──
  if (url.pathname.match(/\.(css|js|woff2?|ttf|eot)$/i)) {
    event.respondWith(cacheFirstWithFallback(request, CACHE_STATIC, 999));
    return;
  }

  // ── HTML pages → Network first, fallback to cache ──
  if (request.destination === 'document') {
    event.respondWith(networkFirstHtml(request));
    return;
  }

  // ── Everything else → Stale While Revalidate ──
  event.respondWith(staleWhileRevalidate(request));
});

/* ════════════════════════════════════════════
   STRATEGIES
════════════════════════════════════════════ */

/** Network First — fresh data with cache backup */
async function networkFirstWithCache(request, cacheName, ttlSeconds) {
  try {
    const networkResponse = await fetchWithTimeout(request, 5000);
    if (networkResponse.ok) {
      const cache = await caches.open(cacheName);
      // Add timestamp header
      const headers   = new Headers(networkResponse.headers);
      const cloned    = networkResponse.clone();
      const body      = await cloned.arrayBuffer();
      const cachedRes = new Response(body, {
        status: networkResponse.status,
        headers: headers,
      });
      await cache.put(request, cachedRes);
      await trimCache(cacheName, MAX_API_ITEMS);
      return networkResponse;
    }
    throw new Error('Bad response');
  } catch {
    const cached = await caches.match(request);
    return cached || new Response(
      JSON.stringify({ status: 'error', message: 'Offline — cached data unavailable' }),
      { headers: { 'Content-Type': 'application/json' } }
    );
  }
}

/** Cache First — serve from cache, update in background */
async function cacheFirstWithFallback(request, cacheName, maxItems) {
  const cached = await caches.match(request);
  if (cached) return cached;

  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const cache = await caches.open(cacheName);
      await cache.put(request, networkResponse.clone());
      await trimCache(cacheName, maxItems);
    }
    return networkResponse;
  } catch {
    if (request.destination === 'image') {
      return caches.match(OFFLINE_IMAGE);
    }
    throw new Error('Fetch failed');
  }
}

/** Network First for HTML with offline fallback */
async function networkFirstHtml(request) {
  try {
    const networkResponse = await fetchWithTimeout(request, 8000);
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_DYNAMIC);
      await cache.put(request, networkResponse.clone());
      await trimCache(CACHE_DYNAMIC, MAX_DYNAMIC_ITEMS);
    }
    return networkResponse;
  } catch {
    const cached = await caches.match(request);
    if (cached) return cached;

    // Generic offline page
    const offlinePage = await caches.match(OFFLINE_PAGE);
    return offlinePage || new Response(
      `<!DOCTYPE html>
      <html lang="bn">
      <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>অফলাইন — RealEstate BD</title>
        <style>
          *{margin:0;padding:0;box-sizing:border-box}
          body{font-family:'Inter',sans-serif;background:#0F172A;color:#fff;
               display:flex;align-items:center;justify-content:center;
               min-height:100vh;text-align:center;padding:20px}
          .offline-card{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);
                        border-radius:24px;padding:50px 40px;max-width:400px}
          .offline-icon{font-size:4rem;margin-bottom:20px}
          h1{font-size:1.5rem;margin-bottom:10px;color:#C5A059}
          p{color:rgba(255,255,255,0.55);line-height:1.8;margin-bottom:24px}
          button{background:#C5A059;color:#0F172A;border:none;padding:12px 28px;
                 border-radius:10px;font-weight:700;cursor:pointer;font-size:1rem}
        </style>
      </head>
      <body>
        <div class="offline-card">
          <div class="offline-icon">📡</div>
          <h1>ইন্টারনেট সংযোগ নেই</h1>
          <p>আপনি বর্তমানে অফলাইনে আছেন। ইন্টারনেট সংযোগ দিয়ে আবার চেষ্টা করুন।</p>
          <button onclick="location.reload()">🔄 আবার চেষ্টা করুন</button>
        </div>
      </body>
      </html>`,
      { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
    );
  }
}

/** Stale While Revalidate */
async function staleWhileRevalidate(request) {
  const cache  = await caches.open(CACHE_DYNAMIC);
  const cached = await cache.match(request);

  const fetchPromise = fetch(request).then(networkResponse => {
    if (networkResponse.ok) {
      cache.put(request, networkResponse.clone());
      trimCache(CACHE_DYNAMIC, MAX_DYNAMIC_ITEMS);
    }
    return networkResponse;
  }).catch(() => cached);

  return cached || fetchPromise;
}

/* ════════════════════════════════════════════
   UTILITIES
════════════════════════════════════════════ */

/** Fetch with timeout */
async function fetchWithTimeout(request, timeoutMs) {
  const controller = new AbortController();
  const timeout    = setTimeout(() => controller.abort(), timeoutMs);
  try {
    const response = await fetch(request, { signal: controller.signal });
    clearTimeout(timeout);
    return response;
  } catch (e) {
    clearTimeout(timeout);
    throw e;
  }
}

/** Trim cache to max items (oldest first) */
async function trimCache(cacheName, maxItems) {
  const cache = await caches.open(cacheName);
  const keys  = await cache.keys();
  if (keys.length > maxItems) {
    await cache.delete(keys[0]);
    await trimCache(cacheName, maxItems);
  }
}

/* ════════════════════════════════════════════
   PUSH NOTIFICATIONS
════════════════════════════════════════════ */
self.addEventListener('push', event => {
  if (!event.data) return;

  let data;
  try { data = event.data.json(); }
  catch { data = { title: 'RealEstate BD', body: event.data.text() }; }

  const options = {
    body:    data.body    || 'নতুন আপডেট আছে!',
    icon:    data.icon    || '/assets/icons/icon-192.png',
    badge:   data.badge   || '/assets/icons/badge-72.png',
    image:   data.image   || null,
    tag:     data.tag     || 'realestate-notification',
    renotify: data.renotify ?? true,
    requireInteraction: data.requireInteraction ?? false,
    silent:  data.silent  ?? false,
    vibrate: [200, 100, 200],
    data: {
      url:        data.url || '/',
      propertyId: data.propertyId || null,
      type:       data.type || 'general',
    },
    actions: data.actions || [
      { action: 'open',    title: '👀 দেখুন', icon: '/assets/icons/view-icon.png' },
      { action: 'dismiss', title: '✕ বন্ধ করুন' },
    ],
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'RealEstate BD', options)
  );
});

/* ── Notification Click ── */
self.addEventListener('notificationclick', event => {
  event.notification.close();

  const data = event.notification.data || {};
  let targetUrl = data.url || '/';

  if (event.action === 'dismiss') return;

  if (data.propertyId) {
    targetUrl = `/?page=property&id=${data.propertyId}`;
  }

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then(clientList => {
        // Focus existing window if open
        for (const client of clientList) {
          if (client.url === targetUrl && 'focus' in client) {
            return client.focus();
          }
        }
        // Open new window
        if (clients.openWindow) return clients.openWindow(targetUrl);
      })
  );
});

/* ── Background Sync ── */
self.addEventListener('sync', event => {
  if (event.tag === 'sync-inquiries') {
    event.waitUntil(syncPendingInquiries());
  }
  if (event.tag === 'sync-bookings') {
    event.waitUntil(syncPendingBookings());
  }
});

async function syncPendingInquiries() {
  // Sync offline inquiries when back online
  const pendingInquiries = await getFromIndexedDB('pending_inquiries');
  for (const inquiry of pendingInquiries) {
    try {
      await fetch('/api/v1/inquiries', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(inquiry),
      });
      await removeFromIndexedDB('pending_inquiries', inquiry.id);
    } catch { /* Will retry on next sync */ }
  }
}

async function syncPendingBookings() {
  const pendingBookings = await getFromIndexedDB('pending_bookings');
  for (const booking of pendingBookings) {
    try {
      await fetch('/api/v1/bookings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(booking),
      });
      await removeFromIndexedDB('pending_bookings', booking.id);
    } catch { /* Will retry */ }
  }
}

/* ── IndexedDB Helpers (stub) ── */
async function getFromIndexedDB(store) {
  return []; // Implement with idb library in production
}
async function removeFromIndexedDB(store, id) {
  return true; // Implement with idb library in production
}

/* ── Message Handler (from main thread) ── */
self.addEventListener('message', event => {
  if (event.data?.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  if (event.data?.type === 'CACHE_URLS') {
    event.waitUntil(
      caches.open(CACHE_DYNAMIC).then(cache =>
        cache.addAll(event.data.urls || [])
      )
    );
  }
  if (event.data?.type === 'CLEAR_CACHE') {
    event.waitUntil(
      caches.keys().then(keys => Promise.all(keys.map(k => caches.delete(k))))
    );
  }
});

console.log('[SW] RealEstate BD Service Worker', SW_VERSION, 'loaded ✓');