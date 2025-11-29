@extends('layouts.app')

@section('title', 'Kontrol Sirine')
@section('header', 'Kontrol Sirine')

@section('content')
<div x-data="sirineControl()">
    <!-- Control Panel -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Status Sirine Saat Ini</h3>
                <p class="text-sm text-gray-500 mt-1">Kontrol status sirine untuk seluruh asrama</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-center">
                    <span :class="{
                        'bg-red-100 text-red-800 border-red-300': currentMode === 'ON',
                        'bg-gray-100 text-gray-800 border-gray-300': currentMode === 'OFF',
                        'bg-blue-100 text-blue-800 border-blue-300': currentMode === 'AUTO'
                    }" class="inline-flex items-center rounded-lg border-2 px-6 py-3 text-2xl font-bold" x-text="currentMode">
                        {{ $currentMode }}
                    </span>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <button @click="setMode('ON')" :disabled="loading"
                    class="flex items-center justify-center gap-2 rounded-lg bg-red-600 px-6 py-4 text-lg font-semibold text-white hover:bg-red-500 disabled:opacity-50 transition-all"
                    :class="{ 'ring-4 ring-red-300': currentMode === 'ON' }">
                <i class="fas fa-volume-up text-2xl"></i>
                <span>NYALAKAN (ON)</span>
            </button>
            <button @click="setMode('OFF')" :disabled="loading"
                    class="flex items-center justify-center gap-2 rounded-lg bg-gray-600 px-6 py-4 text-lg font-semibold text-white hover:bg-gray-500 disabled:opacity-50 transition-all"
                    :class="{ 'ring-4 ring-gray-300': currentMode === 'OFF' }">
                <i class="fas fa-volume-mute text-2xl"></i>
                <span>MATIKAN (OFF)</span>
            </button>
            <button @click="setMode('AUTO')" :disabled="loading"
                    class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-4 text-lg font-semibold text-white hover:bg-blue-500 disabled:opacity-50 transition-all"
                    :class="{ 'ring-4 ring-blue-300': currentMode === 'AUTO' }">
                <i class="fas fa-robot text-2xl"></i>
                <span>OTOMATIS (AUTO)</span>
            </button>
        </div>

        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Keterangan Mode:</h4>
            <ul class="text-sm text-gray-600 space-y-1">
                <li><strong class="text-red-600">ON:</strong> Sirine aktif terus menerus</li>
                <li><strong class="text-gray-600">OFF:</strong> Sirine mati, tidak akan berbunyi meskipun ada kejadian darurat</li>
                <li><strong class="text-blue-600">AUTO:</strong> Sirine otomatis aktif saat terdeteksi SMOKE atau SOS</li>
            </ul>
        </div>
    </div>

    <!-- API Info -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">API Endpoint untuk ESP32</h3>
        <div class="bg-gray-800 rounded-lg p-4 text-sm">
            <p class="text-green-400 mb-2"># Cek Status Sirine</p>
            <code class="text-gray-300">GET {{ url('/api/sirine') }}</code>
            <p class="text-gray-500 mt-1">Response: ON / OFF / AUTO (plain text)</p>

            <p class="text-green-400 mt-4 mb-2"># Set Status Sirine</p>
            <code class="text-gray-300">POST {{ url('/api/sirine') }}</code>
            <p class="text-gray-500 mt-1">Body: {"status": "ON"} atau {"status": "OFF"} atau {"status": "AUTO"}</p>
        </div>
    </div>

    <!-- Log History -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Riwayat Perubahan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sumber</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $log->status === 'ON' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $log->status === 'OFF' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $log->status === 'AUTO' ? 'bg-blue-100 text-blue-800' : '' }}">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ $log->source }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->user->name ?? ($log->device_id ?? 'System') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $log->note ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-bell text-4xl mb-2"></i>
                            <p>Belum ada riwayat</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
function sirineControl() {
    return {
        currentMode: '{{ $currentMode }}',
        loading: false,

        setMode(mode) {
            this.loading = true;
            fetch('/api/sirine', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ status: mode })
            })
            .then(r => r.json())
            .then(data => {
                this.currentMode = data.current_status;
                this.loading = false;
                location.reload();
            })
            .catch(() => {
                this.loading = false;
            });
        }
    };
}
</script>
@endpush
@endsection
