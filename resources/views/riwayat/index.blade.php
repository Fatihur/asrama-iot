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
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $r->device_id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $r->floor }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                            {{ $r->event_type === 'SMOKE' ? 'bg-gray-200 text-gray-800' : '' }}
                            {{ $r->event_type === 'FLAME' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $r->event_type === 'FIRE' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $r->event_type === 'FIRE ALARM' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $r->event_type === 'SENSOR' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ !in_array($r->event_type, ['SMOKE', 'FLAME', 'FIRE', 'FIRE ALARM', 'SENSOR']) ? 'bg-gray-100 text-gray-800' : '' }}">
                            @if($r->event_type === 'SMOKE')<i class="fas fa-smog mr-1"></i>@endif
                            @if($r->event_type === 'FLAME')<i class="fas fa-fire-alt mr-1 text-orange-500"></i>@endif
                            @if($r->event_type === 'FIRE')<i class="fas fa-fire mr-1"></i>@endif
                            @if($r->event_type === 'FIRE ALARM')<i class="fas fa-bell mr-1"></i>@endif
                            @if($r->event_type === 'SENSOR')<i class="fas fa-microchip mr-1"></i>@endif
                            {{ $r->event_type }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                        @php
                            $value = $r->value ?? '-';
                            $jsonData = json_decode($value, true);
                        @endphp
                        @if($jsonData && is_array($jsonData))
                            <div class="text-xs">
                                @if(isset($jsonData['mq2_value']))
                                    <span class="inline-block bg-gray-100 rounded px-1 mr-1">MQ2: {{ $jsonData['mq2_value'] }}</span>
                                @endif
                                @if(isset($jsonData['mq2_ppm']))
                                    <span class="inline-block bg-gray-100 rounded px-1 mr-1">PPM: {{ $jsonData['mq2_ppm'] }}</span>
                                @endif
                                @if(isset($jsonData['flame_detected']))
                                    <span class="inline-block {{ $jsonData['flame_detected'] ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }} rounded px-1">
                                        Api: {{ $jsonData['flame_detected'] ? 'Ya' : 'Tidak' }}
                                    </span>
                                @endif
                            </div>
                        @else
                            {{ Str::limit($value, 30) }}
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
