// Push Notification & Alarm System
const AlarmSystem = {
    audioContext: null,
    isPlaying: false,
    oscillator: null,
    gainNode: null,
    notificationPermission: false,
    lastEventId: 0,
    checkInterval: null,

    init() {
        this.requestPermission();
        this.startPolling();
        this.loadLastEventId();
        console.log('🔔 Alarm System initialized');
    },

    async requestPermission() {
        if (!('Notification' in window)) {
            console.warn('Browser tidak support notifikasi');
            return;
        }

        if (Notification.permission === 'granted') {
            this.notificationPermission = true;
        } else if (Notification.permission !== 'denied') {
            const permission = await Notification.requestPermission();
            this.notificationPermission = permission === 'granted';
        }

        if (this.notificationPermission) {
            console.log('✅ Notifikasi diizinkan');
        }
    },

    loadLastEventId() {
        const saved = localStorage.getItem('lastEventId');
        if (saved) {
            this.lastEventId = parseInt(saved);
        }
    },

    saveLastEventId(id) {
        this.lastEventId = id;
        localStorage.setItem('lastEventId', id.toString());
    },

    startPolling() {
        // Check setiap 3 detik
        this.checkInterval = setInterval(() => this.checkNewEvents(), 3000);
        // Check langsung
        this.checkNewEvents();
    },

    async checkNewEvents() {
        try {
            const response = await fetch('/api/riwayat?limit=5');
            const events = await response.json();
            
            if (events.length > 0) {
                const latestId = events[0].id;
                
                // Jika ada event baru
                if (latestId > this.lastEventId && this.lastEventId > 0) {
                    // Cek event baru yang emergency
                    for (const event of events) {
                        if (event.id > this.lastEventId) {
                            if (['SMOKE', 'FIRE', 'FIRE ALARM'].includes(event.event_type)) {
                                this.triggerAlarm(event);
                            }
                        }
                    }
                }
                
                this.saveLastEventId(latestId);
            }
        } catch (error) {
            console.error('Error checking events:', error);
        }
    },

    triggerAlarm(event) {
        // Show notification
        this.showNotification(event);
        // Play loud alarm
        this.playAlarm();
        // Show on-screen alert
        this.showOnScreenAlert(event);
    },

    showNotification(event) {
        if (!this.notificationPermission) return;

        const title = `🚨 ${event.event_type} TERDETEKSI!`;
        const options = {
            body: `Lantai ${event.floor} - ${event.device_id}\n${new Date(event.timestamp).toLocaleString('id-ID')}`,
            icon: '/favicon.ico',
            tag: 'fire-alarm-' + event.id,
            requireInteraction: true
        };

        const notification = new Notification(title, options);
        
        notification.onclick = () => {
            window.focus();
            this.stopAlarm();
            window.location.href = '/riwayat/' + event.id;
            notification.close();
        };
    },

    playAlarm() {
        if (this.isPlaying) return;
        
        try {
            // Create audio context
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.isPlaying = true;

            this.playAlarmPattern();
        } catch (error) {
            console.error('Error playing alarm:', error);
            // Fallback ke audio element
            this.playFallbackAlarm();
        }
    },

    playAlarmPattern() {
        if (!this.audioContext || !this.isPlaying) return;

        const now = this.audioContext.currentTime;
        
        // Pattern: High-Low siren
        for (let i = 0; i < 10; i++) {
            // High tone
            this.playTone(880, now + i * 0.5, 0.25, 0.8);
            // Low tone
            this.playTone(440, now + i * 0.5 + 0.25, 0.25, 0.8);
        }

        // Repeat pattern
        setTimeout(() => {
            if (this.isPlaying) {
                this.playAlarmPattern();
            }
        }, 5000);
    },

    playTone(frequency, startTime, duration, volume) {
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);
        
        oscillator.frequency.value = frequency;
        oscillator.type = 'square'; // Harsh sound for alarm
        
        gainNode.gain.setValueAtTime(volume, startTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, startTime + duration);
        
        oscillator.start(startTime);
        oscillator.stop(startTime + duration);
    },

    playFallbackAlarm() {
        // Create beep using oscillator
        const audio = document.getElementById('alarm-audio');
        if (audio) {
            audio.loop = true;
            audio.volume = 1.0;
            audio.play().catch(e => console.log('Audio play failed:', e));
        }
    },

    stopAlarm() {
        this.isPlaying = false;
        
        if (this.audioContext) {
            this.audioContext.close();
            this.audioContext = null;
        }

        const audio = document.getElementById('alarm-audio');
        if (audio) {
            audio.pause();
            audio.currentTime = 0;
        }

        // Hide on-screen alert
        const alert = document.getElementById('alarm-overlay');
        if (alert) {
            alert.remove();
        }

        console.log('🔇 Alarm stopped');
    },

    showOnScreenAlert(event) {
        // Remove existing
        const existing = document.getElementById('alarm-overlay');
        if (existing) existing.remove();

        const overlay = document.createElement('div');
        overlay.id = 'alarm-overlay';
        overlay.innerHTML = `
            <div style="position:fixed;inset:0;background:rgba(220,38,38,0.95);z-index:9999;display:flex;flex-direction:column;align-items:center;justify-content:center;animation:pulse 0.5s infinite alternate;">
                <div style="text-align:center;color:white;">
                    <div style="font-size:80px;margin-bottom:20px;">🚨</div>
                    <div style="font-size:48px;font-weight:bold;margin-bottom:10px;">${event.event_type}</div>
                    <div style="font-size:32px;margin-bottom:10px;">TERDETEKSI!</div>
                    <div style="font-size:24px;margin-bottom:30px;">Lantai ${event.floor} - ${event.device_id}</div>
                    <button onclick="AlarmSystem.stopAlarm();window.location.href='/riwayat/${event.id}'" 
                            style="padding:15px 40px;font-size:20px;background:white;color:#dc2626;border:none;border-radius:10px;cursor:pointer;font-weight:bold;">
                        LIHAT DETAIL & STOP ALARM
                    </button>
                    <div style="margin-top:20px;">
                        <button onclick="AlarmSystem.stopAlarm()" 
                                style="padding:10px 30px;font-size:16px;background:transparent;color:white;border:2px solid white;border-radius:5px;cursor:pointer;">
                            Stop Alarm Saja
                        </button>
                    </div>
                </div>
            </div>
            <style>
                @keyframes pulse {
                    from { background: rgba(220,38,38,0.95); }
                    to { background: rgba(185,28,28,0.95); }
                }
            </style>
        `;
        document.body.appendChild(overlay);
    },

    // Test function
    test() {
        this.triggerAlarm({
            id: 999,
            event_type: 'FIRE ALARM',
            floor: 1,
            device_id: 'TEST-DEVICE',
            timestamp: new Date().toISOString()
        });
    }
};

// Initialize when DOM ready
document.addEventListener('DOMContentLoaded', () => {
    AlarmSystem.init();
});

// Expose globally
window.AlarmSystem = AlarmSystem;
