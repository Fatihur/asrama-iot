@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard Monitoring')

@section('content')
<div x-data="dashboardData()" x-init="init()">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Total Kejadian</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['total_events'] }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Kejadian Hari Ini</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-indigo-600">{{ $stats['today_events'] }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-yellow-50 px-4 py-5 shadow sm:p-6 border-l-4 border-yellow-400">
            <dt class="truncate text-sm font-medium text-yellow-700">Menunggu Konfirmasi</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yellow-700" x-text="pendingCount">{{ $stats['pending_events'] }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-red-50 px-4 py-5 shadow sm:p-6 border-l-4 border-red-400">
            <dt class="truncate text-sm font-medium text-red-700">Kebakaran Hari Ini</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-red-700">{{ $stats['emergency_today'] }}</dd>
        </div>
    </div>

    <!-- Test Panel -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-flask mr-2 text-indigo-600"></i>Panel Tes
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Test Sirine -->
            <div class="border rounded-lg p-4">
                <h4 class="font-medium text-gray-700 mb-2">Tes Sirine</h4>
                <p class="text-sm text-gray-500 mb-3">Nyalakan sirine selama 3 detik untuk tes</p>
                <button @click="testSirine()" :disabled="testingSirine"
                    class="w-full rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500 disabled:opacity-50">
                    <i class="fas fa-bell mr-2"></i>
                    <span x-text="testingSirine ? 'Testing...' : 'Tes Sirine'">Tes Sirine</span>
                </button>
                <p x-show="sirineTestResult" x-text="sirineTestResult" class="mt-2 text-sm" :class="sirineTestSuccess ? 'text-green-600' : 'text-red-600'"></p>
            </div>

            <!-- Test SSE -->
            <div class="border rounded-lg p-4">
                <h4 class="font-medium text-gray-700 mb-2">Tes SSE</h4>
                <p class="text-sm text-gray-500 mb-3">Tes koneksi Server-Sent Events</p>
                <button @click="testSSE()" :disabled="testingSSE"
                    class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 disabled:opacity-50">
                    <i class="fas fa-satellite-dish mr-2"></i>
                    <span x-text="testingSSE ? 'Mendengarkan...' : 'Tes SSE'">Tes SSE</span>
                </button>
                <p x-show="sseTestResult" x-text="sseTestResult" class="mt-2 text-sm" :class="sseTestSuccess ? 'text-green-600' : 'text-red-600'"></p>
            </div>

            <!-- Test Camera -->
            <div class="border rounded-lg p-4">
                <h4 class="font-medium text-gray-700 mb-2">Tes Kamera</h4>
                <p class="text-sm text-gray-500 mb-3">Ambil gambar terakhir dari kamera</p>
                <button @click="testCamera()" :disabled="testingCamera"
                    class="w-full rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500 disabled:opacity-50">
                    <i class="fas fa-camera mr-2"></i>
                    <span x-text="testingCamera ? 'Loading...' : 'Tes Kamera'">Tes Kamera</span>
                </button>
                <p x-show="cameraTestResult" x-text="cameraTestResult" class="mt-2 text-sm" :class="cameraTestSuccess ? 'text-green-600' : 'text-red-600'"></p>
                <img x-show="cameraTestImage" :src="cameraTestImage" class="mt-2 w-full h-32 object-cover rounded" alt="Test Camera">
            </div>
        </div>

        <!-- Additional Test Panels -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <!-- Test Sensor -->
            <div class="border rounded-lg p-4">
                <h4 class="font-medium text-gray-700 mb-2">Tes Sensor</h4>
                <p class="text-sm text-gray-500 mb-3">Kirim data sensor dummy</p>
                <button @click="testSensor()" :disabled="testingSensor"
                    class="w-full rounded-md bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 disabled:opacity-50">
                    <i class="fas fa-thermometer-half mr-2"></i>
                    <span x-text="testingSensor ? 'Sending...' : 'Tes Sensor'">Tes Sensor</span>
                </button>
                <p x-show="sensorTestResult" x-text="sensorTestResult" class="mt-2 text-sm" :class="sensorTestSuccess ? 'text-green-600' : 'text-red-600'"></p>
            </div>

            <!-- Test Fire -->
            <div class="border rounded-lg p-4">
                <h4 class="font-medium text-gray-700 mb-2">Tes Fire</h4>
                <p class="text-sm text-gray-500 mb-3">Simulasi event kebakaran</p>
                <button @click="testFire()" :disabled="testingFire"
                    class="w-full rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-500 disabled:opacity-50">
                    <i class="fas fa-fire mr-2"></i>
                    <span x-text="testingFire ? 'Sending...' : 'Tes Fire'">Tes Fire</span>
                </button>
                <p x-show="fireTestResult" x-text="fireTestResult" class="mt-2 text-sm" :class="fireTestSuccess ? 'text-green-600' : 'text-red-600'"></p>
            </div>

            <!-- Test Event -->
            <div class="border rounded-lg p-4">
                <h4 class="font-medium text-gray-700 mb-2">Tes Event</h4>
                <p class="text-sm text-gray-500 mb-3">Kirim event generic</p>
                <button @click="testEvent()" :disabled="testingEvent"
                    class="w-full rounded-md bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-500 disabled:opacity-50">
                    <i class="fas fa-bolt mr-2"></i>
                    <span x-text="testingEvent ? 'Sending...' : 'Tes Event'">Tes Event</span>
                </button>
                <p x-show="eventTestResult" x-text="eventTestResult" class="mt-2 text-sm" :class="eventTestSuccess ? 'text-green-600' : 'text-red-600'"></p>
            </div>
        </div>
    </div>

    <!-- API Documentation -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-code mr-2 text-indigo-600"></i>API Endpoints (ESP32-CAM + MQ2 + Flame Sensor)
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <div class="bg-gray-50 rounded p-3">
                <code class="text-indigo-600 font-medium">POST /api/sensor</code>
                <p class="text-gray-500 mt-1">Data MQ2 & Flame sensor</p>
                <p class="text-xs text-gray-400 mt-1">mq2_value, mq2_ppm, flame_detected, flame_value</p>
            </div>
            <div class="bg-gray-50 rounded p-3">
                <code class="text-indigo-600 font-medium">POST /api/fire</code>
                <p class="text-gray-500 mt-1">Event api terdeteksi (auto sirine)</p>
                <p class="text-xs text-gray-400 mt-1">flame_value, mq2_value</p>
            </div>
            <div class="bg-gray-50 rounded p-3">
                <code class="text-indigo-600 font-medium">POST /api/capture</code>
                <p class="text-gray-500 mt-1">Upload gambar ESP32-CAM</p>
                <p class="text-xs text-gray-400 mt-1">image (file), device_id, floor</p>
            </div>
            <div class="bg-gray-50 rounded p-3">
                <code class="text-indigo-600 font-medium">POST /api/event</code>
                <p class="text-gray-500 mt-1">Kirim event umum</p>
                <p class="text-xs text-gray-400 mt-1">event_type: SMOKE, FIRE, FIRE ALARM</p>
            </div>
            <div class="bg-gray-50 rounded p-3">
                <code class="text-indigo-600 font-medium">GET /api/sirine</code>
                <p class="text-gray-500 mt-1">Cek status sirine</p>
                <p class="text-xs text-gray-400 mt-1">Response: ON / OFF / AUTO</p>
            </div>
            <div class="bg-gray-50 rounded p-3">
                <code class="text-indigo-600 font-medium">POST /api/upload</code>
                <p class="text-gray-500 mt-1">Alias upload gambar</p>
                <p class="text-xs text-gray-400 mt-1">image (file) atau image_url</p>
            </div>
        </div>
        
        <!-- ESP32 Code Example -->
        <details class="mt-4">
            <summary class="cursor-pointer text-sm font-medium text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-code mr-1"></i> Contoh Kode ESP32-CAM
            </summary>
            <div class="mt-2 bg-gray-800 rounded-lg p-4 text-xs overflow-x-auto">
                <pre class="text-green-400"><code>// ESP32-CAM Upload Image
const char* serverUrl = "https://{{ request()->getHost() }}/api/capture";

HTTPClient http;
http.begin(serverUrl);
http.addHeader("Content-Type", "image/jpeg");
http.addHeader("X-Device-ID", "ESP32-CAM-001");
http.addHeader("X-Floor", "1");

// Capture and send image
camera_fb_t *fb = esp_camera_fb_get();
int httpCode = http.POST(fb->buf, fb->len);

if (httpCode == 201) {
    Serial.println("Upload success!");
} else {
    Serial.println("Upload failed: " + String(httpCode));
}
esp_camera_fb_return(fb);
http.end();</code></pre>
            </div>
        </details>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Sirine Control -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-bell mr-2 text-indigo-600"></i>Kontrol Sirine
            </h3>
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-gray-500">Status Saat Ini:</span>
                <span :class="{
                    'bg-red-100 text-red-800': sirineMode === 'ON',
                    'bg-gray-100 text-gray-800': sirineMode === 'OFF',
                    'bg-blue-100 text-blue-800': sirineMode === 'AUTO'
                }" class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium" x-text="sirineMode">
                    {{ $sirineMode }}
                </span>
            </div>
            <div class="flex gap-2">
                <button @click="setSirine('ON')" class="flex-1 rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">
                    <i class="fas fa-volume-up mr-1"></i> ON
                </button>
                <button @click="setSirine('OFF')" class="flex-1 rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-500">
                    <i class="fas fa-volume-mute mr-1"></i> OFF
                </button>
                <button @click="setSirine('AUTO')" class="flex-1 rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-500">
                    <i class="fas fa-robot mr-1"></i> AUTO
                </button>
            </div>
        </div>

        <!-- Event Type Distribution -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-chart-pie mr-2 text-indigo-600"></i>Jenis Kejadian
            </h3>
            <div class="space-y-3">
                @foreach($eventsByType as $type => $count)
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        @if($type === 'SMOKE')
                            <i class="fas fa-smog text-gray-600"></i>
                        @elseif($type === 'FIRE')
                            <i class="fas fa-fire text-orange-500"></i>
                        @elseif($type === 'FIRE ALARM')
                            <i class="fas fa-bell text-red-600"></i>
                        @else
                            <i class="fas fa-info-circle text-gray-500"></i>
                        @endif
                        <span class="text-sm text-gray-600">{{ $type }}</span>
                    </span>
                    <span class="text-sm font-medium text-gray-900">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Latest Camera -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-camera mr-2 text-indigo-600"></i>Kamera Terakhir
            </h3>
            @if($latestCamera)
            <div class="relative">
                <img src="{{ $latestCamera->image_url }}" alt="Latest Camera" class="w-full h-40 object-cover rounded-lg">
                <div class="absolute bottom-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded">
                    {{ $latestCamera->captured_at->diffForHumans() }}
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">{{ $latestCamera->device_id }} - Lantai {{ $latestCamera->floor }}</p>
            @else
            <div class="flex items-center justify-center h-40 bg-gray-100 rounded-lg">
                <span class="text-gray-400">Belum ada gambar</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-chart-line mr-2 text-indigo-600"></i>Grafik Kejadian 7 Hari Terakhir
        </h3>
        <canvas id="eventsChart" height="100"></canvas>
    </div>

    <!-- Recent Events -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-history mr-2 text-indigo-600"></i>Kejadian Terbaru
            </h3>
            <a href="{{ route('riwayat.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lantai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nilai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sirine</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="events-tbody">
                    @foreach($recentEvents as $event)
                    <tr class="{{ $event->isEmergency() && $event->resolve_status === 'OPEN' ? 'bg-red-50' : '' }}" id="event-{{ $event->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $event->timestamp->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $event->device_id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $event->floor }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $event->event_type === 'SMOKE' ? 'bg-gray-200 text-gray-800' : '' }}
                                {{ $event->event_type === 'FLAME' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $event->event_type === 'FIRE' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $event->event_type === 'FIRE ALARM' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $event->event_type === 'SENSOR' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ !in_array($event->event_type, ['SMOKE', 'FLAME', 'FIRE', 'FIRE ALARM', 'SENSOR']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                @if($event->event_type === 'SMOKE')<i class="fas fa-smog mr-1"></i>@endif
                                @if($event->event_type === 'FLAME')<i class="fas fa-fire-alt mr-1"></i>@endif
                                @if($event->event_type === 'FIRE')<i class="fas fa-fire mr-1"></i>@endif
                                @if($event->event_type === 'FIRE ALARM')<i class="fas fa-bell mr-1"></i>@endif
                                @if($event->event_type === 'SENSOR')<i class="fas fa-microchip mr-1"></i>@endif
                                {{ $event->event_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                            @php
                                $value = $event->value ?? '-';
                                $jsonData = json_decode($value, true);
                            @endphp
                            @if($jsonData && is_array($jsonData))
                                <div class="text-xs">
                                    @if(isset($jsonData['mq2_value']))
                                        <span class="inline-block bg-gray-100 rounded px-1 mr-1">MQ2: {{ $jsonData['mq2_value'] }}</span>
                                    @endif
                                    @if(isset($jsonData['flame_detected']))
                                        <span class="inline-block {{ $jsonData['flame_detected'] ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }} rounded px-1">
                                            Api: {{ $jsonData['flame_detected'] ? 'Ya' : 'Tidak' }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                {{ Str::limit($value, 20) }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $event->sirine_status === 'ON' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $event->sirine_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($event->resolve_status === 'RESOLVED')
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                    <i class="fas fa-check mr-1"></i> Resolved
                                </span>
                            @elseif($event->ack_status === 'ACK')
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                    <i class="fas fa-eye mr-1"></i> ACK
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 animate-pulse">
                                    <i class="fas fa-exclamation mr-1"></i> Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($event->resolve_status !== 'RESOLVED')
                                @if($event->ack_status !== 'ACK')
                                <button onclick="acknowledgeEvent({{ $event->id }})" class="text-yellow-600 hover:text-yellow-900 mr-2">
                                    <i class="fas fa-check"></i> ACK
                                </button>
                                @endif
                                <button onclick="resolveEvent({{ $event->id }})" class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-check-double"></i> Resolve
                                </button>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function dashboardData() {
    return {
        sirineMode: '{{ $sirineMode }}',
        pendingCount: {{ $stats['pending_events'] }},

        // Test states
        testingSirine: false,
        sirineTestResult: '',
        sirineTestSuccess: false,
        testingSSE: false,
        sseTestResult: '',
        sseTestSuccess: false,
        sseEventSource: null,
        testingCamera: false,
        cameraTestResult: '',
        cameraTestSuccess: false,
        cameraTestImage: '',
        testingSensor: false,
        sensorTestResult: '',
        sensorTestSuccess: false,
        testingFire: false,
        fireTestResult: '',
        fireTestSuccess: false,
        testingEvent: false,
        eventTestResult: '',
        eventTestSuccess: false,

        init() {
            setInterval(() => this.refresh(), 10000);
        },

        refresh() {
            fetch('/api/sirine').then(r => r.text()).then(s => this.sirineMode = s).catch(() => {});
        },

        setSirine(status) {
            fetch('/api/sirine', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ status })
            }).then(r => r.json()).then(data => this.sirineMode = data.current_status);
        },

        testSirine() {
            this.testingSirine = true;
            this.sirineTestResult = '';
            const originalMode = this.sirineMode;

            fetch('/api/sirine', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ status: 'ON', note: 'Test sirine dari dashboard' })
            })
            .then(r => r.json())
            .then(data => {
                this.sirineMode = data.current_status;
                this.sirineTestResult = 'Sirine aktif! Mematikan dalam 3 detik...';
                this.sirineTestSuccess = true;

                setTimeout(() => {
                    fetch('/api/sirine', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ status: originalMode, note: 'Auto off setelah test' })
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.sirineMode = data.current_status;
                        this.sirineTestResult = 'Tes selesai. Sirine kembali ke mode ' + originalMode;
                        this.testingSirine = false;
                    });
                }, 3000);
            })
            .catch(err => {
                this.sirineTestResult = 'Gagal: ' + err.message;
                this.sirineTestSuccess = false;
                this.testingSirine = false;
            });
        },

        testSSE() {
            if (this.sseEventSource) {
                this.sseEventSource.close();
                this.sseEventSource = null;
                this.testingSSE = false;
                this.sseTestResult = 'Koneksi SSE ditutup';
                return;
            }

            this.testingSSE = true;
            this.sseTestResult = 'Menghubungkan ke SSE...';

            try {
                this.sseEventSource = new EventSource('{{ route("dashboard.sse") }}');

                this.sseEventSource.onopen = () => {
                    this.sseTestResult = 'Terhubung! Menunggu event...';
                    this.sseTestSuccess = true;
                };

                this.sseEventSource.addEventListener('update', (e) => {
                    const data = JSON.parse(e.data);
                    this.sseTestResult = 'Event diterima! Pending: ' + data.pending_count + ', Sirine: ' + data.sirine_mode;
                    this.sseTestSuccess = true;
                });

                this.sseEventSource.onerror = () => {
                    this.sseTestResult = 'Koneksi SSE terputus';
                    this.sseTestSuccess = false;
                    this.testingSSE = false;
                    this.sseEventSource.close();
                    this.sseEventSource = null;
                };

                setTimeout(() => {
                    if (this.sseEventSource) {
                        this.sseEventSource.close();
                        this.sseEventSource = null;
                        this.testingSSE = false;
                        if (this.sseTestSuccess) {
                            this.sseTestResult = 'Tes SSE berhasil! Koneksi ditutup otomatis.';
                        }
                    }
                }, 15000);
            } catch (err) {
                this.sseTestResult = 'Gagal: ' + err.message;
                this.sseTestSuccess = false;
                this.testingSSE = false;
            }
        },

        testCamera() {
            this.testingCamera = true;
            this.cameraTestResult = '';
            this.cameraTestImage = '';

            fetch('/api/kamera/latest')
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success' && data.data) {
                    this.cameraTestResult = 'Gambar ditemukan: ' + data.data.device_id + ' (Lantai ' + data.data.floor + ')';
                    this.cameraTestSuccess = true;
                    this.cameraTestImage = data.data.image_url;
                } else {
                    this.cameraTestResult = 'Tidak ada gambar tersedia';
                    this.cameraTestSuccess = false;
                }
                this.testingCamera = false;
            })
            .catch(err => {
                this.cameraTestResult = 'Gagal: ' + (err.message || 'Tidak ada gambar');
                this.cameraTestSuccess = false;
                this.testingCamera = false;
            });
        },

        testSensor() {
            this.testingSensor = true;
            this.sensorTestResult = '';

            fetch('/api/sensor', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    device_id: 'ESP32-MQ2-001',
                    floor: 1,
                    mq2_value: 150,
                    mq2_ppm: 45,
                    flame_detected: false,
                    flame_value: 1000
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    this.sensorTestResult = 'Data sensor berhasil dikirim! ID: ' + data.data.id;
                    this.sensorTestSuccess = true;
                } else {
                    this.sensorTestResult = 'Gagal: ' + (data.message || 'Unknown error');
                    this.sensorTestSuccess = false;
                }
                this.testingSensor = false;
            })
            .catch(err => {
                this.sensorTestResult = 'Gagal: ' + err.message;
                this.sensorTestSuccess = false;
                this.testingSensor = false;
            });
        },

        testFire() {
            this.testingFire = true;
            this.fireTestResult = '';

            fetch('/api/fire', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    device_id: 'ESP32-FLAME-001',
                    floor: 1,
                    flame_value: 100,
                    value: 'Flame detected by IR sensor'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    this.fireTestResult = 'Fire event dikirim! Sirine: ' + data.sirine;
                    this.fireTestSuccess = true;
                } else {
                    this.fireTestResult = 'Gagal: ' + (data.message || 'Unknown error');
                    this.fireTestSuccess = false;
                }
                this.testingFire = false;
            })
            .catch(err => {
                this.fireTestResult = 'Gagal: ' + err.message;
                this.fireTestSuccess = false;
                this.testingFire = false;
            });
        },

        testEvent() {
            this.testingEvent = true;
            this.eventTestResult = '';

            fetch('/api/event', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    device_id: 'ESP32-CAM-001',
                    floor: 1,
                    event_type: 'SMOKE',
                    mq2_value: 350,
                    value: 'Smoke detected by MQ2 sensor'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    this.eventTestResult = 'Event berhasil dikirim! ID: ' + data.data.id;
                    this.eventTestSuccess = true;
                } else {
                    this.eventTestResult = 'Gagal: ' + (data.message || 'Unknown error');
                    this.eventTestSuccess = false;
                }
                this.testingEvent = false;
            })
            .catch(err => {
                this.eventTestResult = 'Gagal: ' + err.message;
                this.eventTestSuccess = false;
                this.testingEvent = false;
            });
        }
    };
}

function acknowledgeEvent(id) {
    fetch(`/api/riwayat/${id}/ack`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(() => location.reload());
}

function resolveEvent(id) {
    fetch(`/api/riwayat/${id}/resolve`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(() => location.reload());
}

// Chart
const chartData = @json($chartData);
const ctx = document.getElementById('eventsChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartData.map(d => d.date),
        datasets: [{
            label: 'Jumlah Kejadian',
            data: chartData.map(d => d.total),
            borderColor: 'rgb(79, 70, 229)',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
</script>
@endpush
@endsection
