@extends('layouts.app')

@section('title', 'Detail Kejadian')
@section('header', 'Detail Kejadian #' . $riwayat->id)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Info -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Kejadian</h3>
            <dl class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Device ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $riwayat->device_id }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Lantai</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $riwayat->floor }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Jenis Kejadian</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                            {{ $riwayat->event_type === 'SMOKE' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $riwayat->event_type === 'SOS' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ !in_array($riwayat->event_type, ['SMOKE', 'SOS']) ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ $riwayat->event_type }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Waktu</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $riwayat->timestamp->format('d/m/Y H:i:s') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nilai</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $riwayat->value ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status Sirine</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $riwayat->sirine_status === 'ON' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $riwayat->sirine_status }}
                        </span>
                    </dd>
                </div>
            </dl>

            @if($riwayat->image_url)
            <div class="mt-6">
                <dt class="text-sm font-medium text-gray-500 mb-2">Gambar</dt>
                <img src="{{ $riwayat->image_url }}" alt="Event Image" class="rounded-lg max-h-64 object-cover">
            </div>
            @endif
        </div>

        <!-- Distribution Log -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Log Notifikasi</h3>
            @if($riwayat->distribusi->count() > 0)
            <div class="space-y-3">
                @foreach($riwayat->distribusi as $d)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-800">
                            {{ $d->channel }}
                        </span>
                        <span class="text-sm text-gray-900">{{ $d->recipient }}</span>
                    </div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                        {{ $d->status === 'SENT' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $d->status === 'FAILED' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $d->status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                        {{ $d->status }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-sm">Belum ada notifikasi</p>
            @endif
        </div>
    </div>

    <!-- Status & Actions -->
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Status</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Konfirmasi</span>
                    @if($riwayat->ack_status === 'ACK')
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                            <i class="fas fa-check mr-1"></i> Dikonfirmasi
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                            Pending
                        </span>
                    @endif
                </div>
                @if($riwayat->ack_at)
                <div class="text-xs text-gray-500">
                    {{ $riwayat->ack_at->format('d/m/Y H:i') }} oleh {{ $riwayat->acknowledgedBy->name ?? '-' }}
                </div>
                @endif

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Penyelesaian</span>
                    @if($riwayat->resolve_status === 'RESOLVED')
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                            <i class="fas fa-check-double mr-1"></i> Selesai
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                            Open
                        </span>
                    @endif
                </div>
                @if($riwayat->resolved_at)
                <div class="text-xs text-gray-500">
                    {{ $riwayat->resolved_at->format('d/m/Y H:i') }} oleh {{ $riwayat->resolvedByUser->name ?? '-' }}
                </div>
                @endif
            </div>

            @if($riwayat->resolve_status !== 'RESOLVED')
            <div class="mt-6 space-y-2">
                @if($riwayat->ack_status !== 'ACK')
                <button onclick="acknowledgeEvent({{ $riwayat->id }})" class="w-full rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white hover:bg-yellow-500">
                    <i class="fas fa-check mr-1"></i> Konfirmasi (ACK)
                </button>
                @endif
                <button onclick="resolveEvent({{ $riwayat->id }})" class="w-full rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-500">
                    <i class="fas fa-check-double mr-1"></i> Selesaikan
                </button>
            </div>
            @endif
        </div>

        <a href="{{ route('riwayat.index') }}" class="block w-full text-center rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
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
