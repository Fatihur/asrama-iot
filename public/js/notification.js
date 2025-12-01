// PWA + FCM Notification System - Asrama IoT
const AsramaApp = {
    deferredPrompt: null,
    isInstalled: false,
    isSubscribed: false,
    
    // Alarm state
    audioContext: null,
    isPlaying: false,

    // Initialize
    async init() {
        console.log('🚀 Initializing Asrama IoT PWA...');
        
        this.checkInstalled();
        this.setupInstallPrompt();
        
        // Initialize Firebase
        if (typeof FirebaseNotif !== 'undefined') {
            await FirebaseNotif.init();
            this.isSubscribed = await FirebaseNotif.checkSubscription();
        }
        
        this.updateUI();
        console.log('✅ PWA Ready!');
    },

    checkInstalled() {
        if (window.matchMedia('(display-mode: standalone)').matches) {
            this.isInstalled = true;
            console.log('📱 Running as installed PWA');
        }
    },

    // Toggle notification subscription
    async toggleNotification() {
        if (typeof FirebaseNotif === 'undefined') {
            alert('Firebase belum dimuat. Refresh halaman.');
            return;
        }

        if (this.isSubscribed) {
            // Unsubscribe
            await FirebaseNotif.removeTokenFromServer();
            localStorage.removeItem('fcm_token');
            this.isSubscribed = false;
            this.updateUI();
            alert('Notifikasi dinonaktifkan');
        } else {
            // Subscribe
            const btn = document.getElementById('push-subscribe-btn');
            if (btn) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
                btn.disabled = true;
            }

            const token = await FirebaseNotif.requestPermissionAndGetToken();
            
            if (token) {
                localStorage.setItem('fcm_token', token);
                this.isSubscribed = true;
                alert('✅ Notifikasi berhasil diaktifkan!\n\nAnda akan menerima alert kebakaran di device ini.');
            } else {
                alert('Gagal mengaktifkan notifikasi. Pastikan izin notifikasi diaktifkan.');
            }

            if (btn) btn.disabled = false;
            this.updateUI();
        }
    },

    // Update UI
    updateUI() {
        const btn = document.getElementById('push-subscribe-btn');
        const statusEl = document.getElementById('push-status');

        if (!btn) return;

        if (this.isSubscribed) {
            btn.innerHTML = '<i class="fas fa-bell mr-1"></i> Notifikasi Aktif';
            btn.className = 'bg-green-500 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-green-400';
            if (statusEl) statusEl.textContent = '✅ Notifikasi aktif - akan menerima alert kebakaran';
        } else {
            btn.innerHTML = '<i class="fas fa-bell-slash mr-1"></i> Aktifkan Notifikasi';
            btn.className = 'bg-yellow-500 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-yellow-400';
            if (statusEl) statusEl.textContent = 'Klik untuk mengaktifkan notifikasi alert kebakaran';
        }
    },

    // Test notification
    async testNotification() {
        try {
            const res = await fetch('/api/fcm/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            const data = await res.json();
            alert(`Test notification sent!\nSuccess: ${data.success || 0}\nFailed: ${data.failed || 0}`);
        } catch (err) {
            alert('Error: ' + err.message);
        }
    },

    // ========== ALARM SYSTEM ==========
    handleEmergency(event) {
        console.log('🚨 EMERGENCY:', event);
        this.playAlarm();
        this.showAlert(event);
    },

    playAlarm() {
        if (this.isPlaying) return;
        this.isPlaying = true;

        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.alarmLoop();
        } catch (err) {
            console.error('Audio error:', err);
        }
    },

    alarmLoop() {
        if (!this.isPlaying || !this.audioContext) return;

        const now = this.audioContext.currentTime;
        
        for (let i = 0; i < 8; i++) {
            this.beep(880, now + i * 0.4, 0.2);
            this.beep(440, now + i * 0.4 + 0.2, 0.2);
        }

        setTimeout(() => this.alarmLoop(), 3200);
    },

    beep(freq, start, dur) {
        const osc = this.audioContext.createOscillator();
        const gain = this.audioContext.createGain();
        
        osc.connect(gain);
        gain.connect(this.audioContext.destination);
        
        osc.frequency.value = freq;
        osc.type = 'square';
        
        gain.gain.setValueAtTime(0.5, start);
        gain.gain.exponentialRampToValueAtTime(0.01, start + dur);
        
        osc.start(start);
        osc.stop(start + dur);
    },

    stopAlarm() {
        this.isPlaying = false;
        if (this.audioContext) {
            this.audioContext.close();
            this.audioContext = null;
        }
        const overlay = document.getElementById('alarm-overlay');
        if (overlay) overlay.remove();
    },

    showAlert(event) {
        const existing = document.getElementById('alarm-overlay');
        if (existing) existing.remove();

        const div = document.createElement('div');
        div.id = 'alarm-overlay';
        div.innerHTML = `
            <div style="position:fixed;inset:0;background:rgba(220,38,38,0.97);z-index:9999;display:flex;align-items:center;justify-content:center;animation:flash .3s infinite alternate">
                <div style="text-align:center;color:white;padding:20px">
                    <div style="font-size:100px">🚨</div>
                    <div style="font-size:48px;font-weight:bold">${event.event_type}</div>
                    <div style="font-size:28px;margin:10px 0">TERDETEKSI!</div>
                    <div style="font-size:20px;margin-bottom:30px">Lantai ${event.floor} - ${event.device_id}</div>
                    <button onclick="AsramaApp.stopAlarm();location.href='/riwayat/${event.id}'" 
                        style="padding:20px 40px;font-size:18px;background:white;color:#dc2626;border:none;border-radius:10px;font-weight:bold;cursor:pointer">
                        LIHAT DETAIL
                    </button>
                    <div style="margin-top:15px">
                        <button onclick="AsramaApp.stopAlarm()" 
                            style="padding:10px 25px;background:transparent;color:white;border:2px solid white;border-radius:5px;cursor:pointer">
                            Stop Alarm
                        </button>
                    </div>
                </div>
            </div>
            <style>@keyframes flash{from{opacity:1}to{opacity:.85}}</style>
        `;
        document.body.appendChild(div);
    },

    async testAlarm() {
        // Trigger local alarm di web
        this.handleEmergency({
            id: 999,
            event_type: 'FIRE ALARM',
            floor: 1,
            device_id: 'TEST'
        });

        // Trigger alarm ke mobile app via API
        try {
            const res = await fetch('/api/fire', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    device_id: 'WEB-TRIGGER',
                    floor: 1,
                    flame_value: 100,
                    value: 'Manual trigger dari dashboard web'
                })
            });
            const data = await res.json();
            console.log('Mobile alarm triggered:', data);
        } catch (err) {
            console.error('Failed to trigger mobile alarm:', err);
        }
    },

    // ========== PWA INSTALL ==========
    setupInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallButton();
            console.log('📲 Install prompt ready');
        });

        window.addEventListener('appinstalled', () => {
            this.deferredPrompt = null;
            this.isInstalled = true;
            this.hideInstallButton();
            console.log('✅ PWA Installed!');
        });
    },

    async installPWA() {
        if (!this.deferredPrompt) {
            alert('Untuk install:\n\n📱 Mobile: Menu browser → "Add to Home Screen"\n\n💻 Desktop: Klik icon install di address bar');
            return;
        }

        this.deferredPrompt.prompt();
        const result = await this.deferredPrompt.userChoice;
        
        if (result.outcome === 'accepted') {
            console.log('✅ User accepted install');
        }
        this.deferredPrompt = null;
    },

    showInstallButton() {
        const btn = document.getElementById('install-btn');
        if (btn) btn.classList.remove('hidden');
    },

    hideInstallButton() {
        const btn = document.getElementById('install-btn');
        if (btn) btn.classList.add('hidden');
    }
};

// Auto init
document.addEventListener('DOMContentLoaded', () => AsramaApp.init());

window.AsramaApp = AsramaApp;
