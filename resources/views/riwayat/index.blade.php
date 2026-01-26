@extends('layouts.app')

@section('title', 'Riwayat Kejadian')
@section('header', 'Riwayat Kejadian')

@section('content')
<div class="bg-white rounded-lg shadow">
    <!-- Filters -->
    <div class="p-4 border-b border-gray-200">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kejadian</label>
                <select name="event_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">Semua</option>
                    @foreach($eventTypes as $type)
                    <option value="{{ $type }}" {{ request('event_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">Semua</option>
                    <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>Open</option>
                    <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>Resolved</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lantai</label>
                <select name="floor" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">Semua</option>
                    @foreach($floors as $floor)
                    <option value="{{ $floor }}" {{ request('floor') == $floor ? 'selected' : '' }}>Lantai {{ $floor }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <button type="submit" formaction="{{ route('riwayat.export.pdf') }}" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500" title="Export PDF">
                    <i class="fas fa-file-pdf"></i>
                </button>
                <a href="{{ route('riwayat.index') }}" class="rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
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
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($riwayat as $r)
                <tr class="{{ $r->isEmergency() && $r->resolve_status === 'OPEN' ? 'bg-red-50' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $r->timestamp->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">ESP32 Main</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $r->floor }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                            {{ $r->event_type === 'SMOKE' ? 'bg-gray-200 text-gray-800' : '' }}
                            {{ $r->event_type === 'FIRE' ? 'bg-red-100 text-red-800' : '' }}
                            {{ !in_array($r->event_type, ['SMOKE', 'FIRE']) ? 'bg-gray-100 text-gray-800' : '' }}">
                            @if($r->event_type === 'SMOKE')<i class="fas fa-smog mr-1"></i>@endif
                            @if($r->event_type === 'FIRE')<i class="fas fa-fire mr-1"></i>@endif
                            {{ $r->event_type }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @php
                            $value = $r->value ?? '-';
                            $jsonData = is_string($value) ? json_decode($value, true) : (is_array($value) ? $value : null);
                            $numericValue = is_numeric($value) ? (int)$value : null;
                        @endphp
                        @if($jsonData && is_array($jsonData))
                            <div class="space-y-1">
                                @if(isset($jsonData['mq2_value']) || isset($jsonData['mq2_ppm']))
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-smog text-gray-400 w-4"></i>
                                    <span class="text-xs">
                                        @if(isset($jsonData['mq2_value']))
                                            <span class="inline-flex items-center bg-gray-100 rounded px-2 py-0.5">
                                                MQ2: <strong class="ml-1">{{ $jsonData['mq2_value'] }}</strong>
                                            </span>
                                        @endif
                                        @if(isset($jsonData['mq2_ppm']))
                                            <span class="inline-flex items-center bg-blue-50 text-blue-700 rounded px-2 py-0.5 ml-1">
                                                {{ $jsonData['mq2_ppm'] }} PPM
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                @endif
                                @if(isset($jsonData['flame_detected']) || isset($jsonData['flame_value']))
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-fire-alt {{ isset($jsonData['flame_detected']) && $jsonData['flame_detected'] ? 'text-red-500' : 'text-gray-400' }} w-4"></i>
                                    <span class="text-xs">
                                        @if(isset($jsonData['flame_detected']))
                                            <span class="inline-flex items-center {{ $jsonData['flame_detected'] ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }} rounded px-2 py-0.5">
                                                <i class="fas {{ $jsonData['flame_detected'] ? 'fa-exclamation-triangle' : 'fa-check' }} mr-1"></i>
                                                {{ $jsonData['flame_detected'] ? 'Api Terdeteksi' : 'Aman' }}
                                            </span>
                                        @endif
                                        @if(isset($jsonData['flame_value']))
                                            <span class="inline-flex items-center bg-gray-100 rounded px-2 py-0.5 ml-1">
                                                Nilai: {{ $jsonData['flame_value'] }}
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                @endif
                            </div>
                        @elseif($numericValue !== null && $r->event_type === 'SMOKE')
                            @php
                                $smokeLevel = $numericValue >= 3000 ? 'Tinggi' : ($numericValue >= 1500 ? 'Sedang' : 'Rendah');
                                $smokeColor = $numericValue >= 3000 ? 'bg-red-100 text-red-700' : ($numericValue >= 1500 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700');
                            @endphp
                            <div class="flex items-center gap-2">
                                <i class="fas fa-smog {{ $numericValue >= 3000 ? 'text-red-500' : ($numericValue >= 1500 ? 'text-yellow-500' : 'text-gray-400') }}"></i>
                                <div class="text-xs space-y-1">
                                    <span class="inline-flex items-center {{ $smokeColor }} rounded px-2 py-0.5">
                                        <i class="fas {{ $numericValue >= 3000 ? 'fa-exclamation-triangle' : ($numericValue >= 1500 ? 'fa-exclamation' : 'fa-check') }} mr-1"></i>
                                        {{ $smokeLevel }}
                                    </span>
                                    <span class="inline-flex items-center bg-gray-100 rounded px-2 py-0.5 ml-1">
                                        Nilai: <strong class="ml-1">{{ number_format($numericValue) }}</strong>
                                    </span>
                                </div>
                            </div>
                        @elseif($numericValue !== null && $r->event_type === 'FLAME')
                            @php
                                $flameDetected = $numericValue < 500;
                            @endphp
                            <div class="flex items-center gap-2">
                                <i class="fas fa-fire-alt {{ $flameDetected ? 'text-red-500' : 'text-gray-400' }}"></i>
                                <div class="text-xs">
                                    <span class="inline-flex items-center {{ $flameDetected ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }} rounded px-2 py-0.5">
                                        <i class="fas {{ $flameDetected ? 'fa-exclamation-triangle' : 'fa-check' }} mr-1"></i>
                                        {{ $flameDetected ? 'Api Terdeteksi' : 'Aman' }}
                                    </span>
                                    <span class="inline-flex items-center bg-gray-100 rounded px-2 py-0.5 ml-1">
                                        Nilai: <strong class="ml-1">{{ number_format($numericValue) }}</strong>
                                    </span>
                                </div>
                            </div>
                        @elseif($numericValue !== null)
                            <div class="flex items-center gap-2">
                                <i class="fas fa-chart-line text-gray-400"></i>
                                <span class="inline-flex items-center bg-gray-100 rounded px-2 py-0.5 text-xs">
                                    Nilai: <strong class="ml-1">{{ number_format($numericValue) }}</strong>
                                </span>
                            </div>
                        @else
                            {{ $value !== '-' ? Str::limit($value, 50) : '-' }}
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $r->sirine_status === 'ON' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $r->sirine_status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($r->resolve_status === 'RESOLVED')
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                <i class="fas fa-check mr-1"></i> Resolved
                            </span>
                        @elseif($r->ack_status === 'ACK')
                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                <i class="fas fa-eye mr-1"></i> ACK
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                <i class="fas fa-exclamation mr-1"></i> Pending
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('riwayat.show', $r) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($r->resolve_status !== 'RESOLVED')
                            @if($r->ack_status !== 'ACK')
                            <button onclick="acknowledgeEvent({{ $r->id }})" class="text-yellow-600 hover:text-yellow-900 mr-2">
                                <i class="fas fa-check"></i>
                            </button>
                            @endif
                            <button onclick="resolveEvent({{ $r->id }})" class="text-green-600 hover:text-green-900">
                                <i class="fas fa-check-double"></i>
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>Tidak ada data</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $riwayat->withQueryString()->links() }}
    </div>
</div>

@push('scripts')
<script>
function acknowledgeEvent(id) {
    fetch(`/api/riwayat/${id}/ack`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(() => location.reload());
}

function resolveEvent(id) {
    fetch(`/api/riwayat/${id}/resolve`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(() => location.reload());
}
</script>
@endpush
@endsection
