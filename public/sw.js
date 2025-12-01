// Service Worker untuk PWA - Asrama IoT v2
const CACHE_NAME = 'asrama-iot-v2';

// Install - skip caching untuk hindari error
self.addEventListener('install', (event) => {
    console.log('[SW] Installing v2...');
    self.skipWaiting();
});

// Activate
self.addEventListener('activate', (event) => {
    console.log('[SW] Activated v2');
    // Clear old caches
    event.waitUntil(
        caches.keys().then(names => {
            return Promise.all(
                names.filter(name => name !== CACHE_NAME)
                    .map(name => caches.delete(name))
            );
        }).then(() => clients.claim())
    );
});

// Fetch - skip non-http requests completely
self.addEventListener('fetch', (event) => {
    const url = event.request.url;
    
    // Skip chrome-extension, data:, blob: etc
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
        return;
    }
    
    // Network only - no caching
    event.respondWith(
        fetch(event.request).catch(() => {
            if (event.request.mode === 'navigate') {
                return new Response(
                    '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Offline</title></head><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#4f46e5;color:white;text-align:center"><div><h1>📡 Offline</h1><p>Tidak ada koneksi internet</p><button onclick="location.reload()" style="padding:10px 20px;border:none;border-radius:5px;cursor:pointer;margin-top:20px">Coba Lagi</button></div></body></html>',
                    { headers: { 'Content-Type': 'text/html' } }
                );
            }
        })
    );
});

// Message from main thread - show notification
self.addEventListener('message', (event) => {
    if (event.data.type === 'SHOW_NOTIFICATION') {
        const data = event.data.payload;
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/icons/icon-192x192.png',
            badge: '/icons/icon-96x96.png',
            tag: data.tag || 'fire-alarm',
            vibrate: [500, 200, 500, 200, 500],
            requireInteraction: true,
            data: { url: data.url || '/dashboard' }
        });
    }
});

// Notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/dashboard';
    
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then((clientList) => {
            for (const client of clientList) {
                if ('focus' in client) {
                    client.focus();
                    return client.navigate(url);
                }
            }
            return clients.openWindow(url);
        })
    );
});

console.log('[SW] Loaded v2');
