// Firebase Messaging Service Worker
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyCMRRDSYkgBV08qQSV2mfKS-_nft48jK9Y",
    authDomain: "bukutamu-a3749.firebaseapp.com",
    projectId: "bukutamu-a3749",
    storageBucket: "bukutamu-a3749.firebasestorage.app",
    messagingSenderId: "14772787878",
    appId: "1:14772787878:web:bb5ba479ff40fcb0a6b267"
});

const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    console.log('[FCM] Background message:', payload);
    
    const data = payload.data || {};
    const notification = payload.notification || {};
    
    const title = notification.title || data.title || '🚨 ALERT!';
    const options = {
        body: notification.body || data.body || 'Ada kejadian baru',
        icon: '/icons/icon-192x192.png',
        badge: '/icons/icon-96x96.png',
        tag: data.tag || 'fire-alarm-' + Date.now(),
        vibrate: [500, 200, 500, 200, 500, 200, 500],
        requireInteraction: true,
        data: {
            url: data.url || '/dashboard',
            event_type: data.event_type,
            floor: data.floor
        }
    };

    return self.registration.showNotification(title, options);
});

// Notification click
self.addEventListener('notificationclick', (event) => {
    console.log('[FCM] Notification clicked');
    event.notification.close();

    const url = event.notification.data?.url || '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
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

console.log('[FCM] Service Worker loaded');
