// Firebase Cloud Messaging untuk Asrama IoT
const FirebaseNotif = {
    messaging: null,
    token: null,
    isSupported: false,

    async init() {
        console.log('🔥 Initializing Firebase...');

        // Check if browser supports FCM
        if (!('Notification' in window) || !('serviceWorker' in navigator)) {
            console.warn('FCM not supported');
            return false;
        }

        try {
            // Initialize Firebase
            const firebaseConfig = {
                apiKey: "AIzaSyCMRRDSYkgBV08qQSV2mfKS-_nft48jK9Y",
                authDomain: "bukutamu-a3749.firebaseapp.com",
                projectId: "bukutamu-a3749",
                storageBucket: "bukutamu-a3749.firebasestorage.app",
                messagingSenderId: "14772787878",
                appId: "1:14772787878:web:bb5ba479ff40fcb0a6b267"
            };

            // Check if already initialized
            if (!firebase.apps.length) {
                firebase.initializeApp(firebaseConfig);
            }

            this.messaging = firebase.messaging();
            this.isSupported = true;

            // Handle foreground messages
            this.messaging.onMessage((payload) => {
                console.log('[FCM] Foreground message:', payload);
                this.handleForegroundMessage(payload);
            });

            console.log('✅ Firebase initialized');
            return true;
        } catch (err) {
            console.error('❌ Firebase init error:', err);
            return false;
        }
    },

    async requestPermissionAndGetToken() {
        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                console.warn('Notification permission denied');
                return null;
            }

            // Register service worker
            const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
            console.log('✅ FCM Service Worker registered');

            // Get FCM token
            this.token = await this.messaging.getToken({
                vapidKey: 'BHgJv0sNvEXx6PQXZxQjVvBM3_rHLxQvLF0q9xVvL0GHhOLPz1nxKL9mPxVzQxDxPxZxQxVxBxMx3xRxHxLxQxVxLxFx0xQx9xXxVxVxLx0xGxHxHxOxLxPxZx1xNxXxKxLx9xMxPxXxVxZxQxDxPxXxPxZxQxVxBxMx3', // Optional, not needed for FCM HTTP v1
                serviceWorkerRegistration: registration
            });

            console.log('✅ FCM Token:', this.token);

            // Save token to server
            await this.saveTokenToServer(this.token);

            return this.token;
        } catch (err) {
            console.error('❌ FCM token error:', err);
            return null;
        }
    },

    async saveTokenToServer(token) {
        try {
            const response = await fetch('/api/fcm/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    token: token,
                    device_name: this.getDeviceName()
                })
            });

            if (response.ok) {
                console.log('✅ Token saved to server');
                return true;
            }
        } catch (err) {
            console.error('❌ Save token error:', err);
        }
        return false;
    },

    async removeTokenFromServer() {
        if (!this.token) return;

        try {
            await fetch('/api/fcm/unregister', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ token: this.token })
            });
            console.log('✅ Token removed from server');
        } catch (err) {
            console.error('❌ Remove token error:', err);
        }
    },

    handleForegroundMessage(payload) {
        const data = payload.data || {};
        const notification = payload.notification || {};

        const title = notification.title || data.title || '🚨 ALERT!';
        const body = notification.body || data.body || 'Ada kejadian baru';

        // Show notification
        if (Notification.permission === 'granted') {
            new Notification(title, {
                body: body,
                icon: '/icons/icon-192x192.png',
                tag: 'fire-alarm'
            });
        }

        // Trigger alarm if emergency
        if (data.event_type && ['SMOKE', 'FIRE', 'FIRE ALARM'].includes(data.event_type)) {
            if (typeof AsramaApp !== 'undefined') {
                AsramaApp.handleEmergency({
                    id: data.id || 0,
                    event_type: data.event_type,
                    floor: data.floor || 1,
                    device_id: data.device_id || 'UNKNOWN'
                });
            }
        }
    },

    getDeviceName() {
        const ua = navigator.userAgent;
        if (/Android/i.test(ua)) return 'Android';
        if (/iPhone|iPad/i.test(ua)) return 'iOS';
        if (/Windows/i.test(ua)) return 'Windows';
        if (/Mac/i.test(ua)) return 'Mac';
        if (/Linux/i.test(ua)) return 'Linux';
        return 'Unknown';
    },

    async checkSubscription() {
        // Check if token exists in localStorage
        const savedToken = localStorage.getItem('fcm_token');
        if (savedToken) {
            this.token = savedToken;
            return true;
        }
        return false;
    }
};

// Export
window.FirebaseNotif = FirebaseNotif;
