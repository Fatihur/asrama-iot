<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Asrama IoT</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    @if(request()->routeIs('dashboard'))
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @endif
    <style>
        [x-cloak] { display: none !important; }
        .animate-pulse-fast { animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes alarm-flash {
            0%, 100% { background-color: rgb(220 38 38); }
            50% { background-color: rgb(239 68 68); }
        }
        .alarm-flash { animation: alarm-flash 0.5s ease-in-out infinite; }
    </style>
    @stack('styles')
</head>
<body class="h-full" x-data="alarmSystem()" x-init="initAlarm()">
    <!-- Alarm Modal -->
    <div x-show="showAlarm" x-cloak class="fixed inset-0 z-[100] overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 alarm-flash" @click="dismissAlarm()"></div>
            <div class="relative bg-white rounded-lg shadow-2xl max-w-md w-full p-6 transform transition-all"
                 x-show="showAlarm"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-fire text-red-600 text-3xl animate-pulse"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">PERINGATAN DARURAT!</h3>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <p class="text-lg font-semibold text-red-800" x-text="alarmEvent.event_type"></p>
                        <p class="text-gray-700 mt-2">
                            <i class="fas fa-map-marker-alt mr-1"></i> Lantai <span x-text="alarmEvent.floor"></span>
                        </p>
                        <p class="text-gray-600 text-sm mt-1">
                            <i class="fas fa-microchip mr-1"></i> <span x-text="alarmEvent.device_id"></span>
                        </p>
                        <p class="text-gray-500 text-xs mt-2" x-text="alarmEvent.timestamp"></p>
                    </div>
                    <div class="flex gap-3">
                        <button @click="acknowledgeAlarm()" 
                            class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 px-4 rounded-lg transition">
                            <i class="fas fa-check mr-2"></i> Konfirmasi (ACK)
                        </button>
                        <button @click="dismissAlarm()" 
                            class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-lg transition">
                            <i class="fas fa-volume-mute mr-2"></i> Matikan Alarm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio element for alarm sound -->
    <audio id="alarm-audio" preload="auto" loop>
        <source src="data:audio/wav;base64,UklGRl9vT19teleVlZWVlZWVlZWVlZWVlZWVlZWVlZWVlZWVlZWVlZW..." type="audio/wav">
    </audio>

    <div class="min-h-full">
        <!-- Sidebar Mobile -->
        <div x-show="sidebarOpen" class="relative z-50 lg:hidden" x-cloak>
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/80" @click="sidebarOpen = false"></div>

            <div class="fixed inset-0 flex">
                <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-300 transform"
                     x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                     class="relative mr-16 flex w-full max-w-xs flex-1">
                    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-indigo-600 px-6 pb-4">
                        @include('layouts.sidebar-content')
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Desktop -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-indigo-600 px-6 pb-4">
                @include('layouts.sidebar-content')
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:pl-72">
            <!-- Top Bar -->
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <div class="h-6 w-px bg-gray-200 lg:hidden"></div>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1 items-center">
                        <h1 class="text-lg font-semibold text-gray-900">@yield('header', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- Sirine Status Indicator -->
                        <div id="sirine-indicator" class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">Sirine:</span>
                            <span id="sirine-status" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800">
                                Loading...
                            </span>
                        </div>

                        <!-- User Menu -->
                        @auth
                        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200"></div>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 text-sm">
                                <span class="hidden lg:block text-gray-700">{{ auth()->user()->name }}</span>
                                <i class="fas fa-user-circle text-2xl text-gray-400"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                 class="absolute right-0 mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="py-6">
                <div class="px-4 sm:px-6 lg:px-8">
                    @if(session('success'))
                    <div class="mb-4 rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <i class="fas fa-check-circle text-green-400"></i>
                            <p class="ml-3 text-sm text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                            <p class="ml-3 text-sm text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        // Alarm System
        function alarmSystem() {
            return {
                sidebarOpen: false,
                showAlarm: false,
                alarmEvent: { event_type: '', floor: '', device_id: '', timestamp: '', id: null },
                audioContext: null,
                oscillator: null,
                gainNode: null,
                lastEventId: localStorage.getItem('lastEventId') || 0,
                sseConnection: null,

                initAlarm() {
                    this.connectSSE();
                    this.updateSirineStatus();
                    setInterval(() => this.updateSirineStatus(), 15000);
                },

                connectSSE() {
                    if (this.sseConnection) {
                        this.sseConnection.close();
                    }

                    this.sseConnection = new EventSource('/dashboard/sse');
                    
                    this.sseConnection.addEventListener('update', (e) => {
                        const data = JSON.parse(e.data);
                        if (data.latest && data.latest.id > this.lastEventId) {
                            const event = data.latest;
                            const emergencyTypes = ['SMOKE', 'FLAME', 'FIRE', 'FIRE ALARM'];
                            if (emergencyTypes.includes(event.event_type)) {
                                this.triggerAlarm(event);
                            }
                            this.lastEventId = event.id;
                            localStorage.setItem('lastEventId', event.id);
                        }
                    });

                    this.sseConnection.onerror = () => {
                        setTimeout(() => this.connectSSE(), 5000);
                    };
                },

                triggerAlarm(event) {
                    this.alarmEvent = {
                        id: event.id,
                        event_type: event.event_type,
                        floor: event.floor,
                        device_id: event.device_id,
                        timestamp: event.timestamp
                    };
                    this.showAlarm = true;
                    this.playAlarmSound();
                },

                playAlarmSound() {
                    try {
                        if (!this.audioContext) {
                            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        }
                        
                        if (this.oscillator) {
                            this.oscillator.stop();
                        }

                        this.oscillator = this.audioContext.createOscillator();
                        this.gainNode = this.audioContext.createGain();
                        
                        this.oscillator.connect(this.gainNode);
                        this.gainNode.connect(this.audioContext.destination);
                        
                        this.oscillator.frequency.value = 880;
                        this.oscillator.type = 'square';
                        this.gainNode.gain.value = 0.3;
                        
                        this.oscillator.start();
                        
                        // Siren effect
                        const siren = () => {
                            if (!this.showAlarm) return;
                            this.oscillator.frequency.setValueAtTime(880, this.audioContext.currentTime);
                            this.oscillator.frequency.linearRampToValueAtTime(440, this.audioContext.currentTime + 0.5);
                            this.oscillator.frequency.linearRampToValueAtTime(880, this.audioContext.currentTime + 1);
                            setTimeout(siren, 1000);
                        };
                        siren();
                    } catch (e) {
                        console.error('Audio error:', e);
                    }
                },

                stopAlarmSound() {
                    if (this.oscillator) {
                        this.oscillator.stop();
                        this.oscillator = null;
                    }
                },

                dismissAlarm() {
                    this.showAlarm = false;
                    this.stopAlarmSound();
                },

                acknowledgeAlarm() {
                    if (this.alarmEvent.id) {
                        fetch(`/api/riwayat/${this.alarmEvent.id}/ack`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        }).then(() => {
                            this.dismissAlarm();
                        });
                    } else {
                        this.dismissAlarm();
                    }
                },

                updateSirineStatus() {
                    fetch('/api/sirine')
                        .then(r => r.text())
                        .then(status => {
                            const el = document.getElementById('sirine-status');
                            if (!el) return;
                            el.textContent = status;
                            el.className = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' +
                                (status === 'ON' ? 'bg-red-100 text-red-800 animate-pulse-fast' :
                                 status === 'OFF' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800');
                        }).catch(() => {});
                }
            };
        }
    </script>
    @stack('scripts')
</body>
</html>
