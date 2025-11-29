@extends('layouts.app')

@section('title', 'Distribusi Notifikasi')
@section('header', 'Distribusi Notifikasi')

@section('content')
<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-sm text-gray-500">Total</p>
        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-400">
        <p class="text-sm text-green-700">Terkirim</p>
        <p class="text-2xl font-bold text-green-700">{{ $stats['sent'] }}</p>
    </div>
    <div class="bg-red-50 rounded-lg shadow p-4 border-l-4 border-red-400">
        <p class="text-sm text-red-700">Gagal</p>
        <p class="text-2xl font-bold text-red-700">{{ $stats['failed'] }}</p>
    </div>
    <div class="bg-yellow-50 rounded-lg shadow p-4 border-l-4 border-yellow-400">
        <p class="text-sm text-yellow-700">Pending</p>
        <p class="text-2xl font-bold text-yellow-700">{{ $stats['pending'] }}</p>
    </div>
</div>

<!-- Channel Distribution -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <h3 class="text-sm font-medium text-gray-700 mb-3">Distribusi per Channel</h3>
    <div class="flex gap-4">
        @foreach($byChannel as $channel => $count)
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-800">
                @if($channel === 'WHATSAPP')
                    <i class="fab fa-whatsapp mr-1"></i>
                @elseif($channel === 'TELEGRAM')
                    <i class="fab fa-telegram mr-1"></i>
                @elseif($channel === 'EMAIL')
                    <i class="fas fa-envelope mr-1"></i>
                @else
                    <i class="fas fa-globe mr-1"></i>
                @endif
                {{ $channel }}
            </span>
            <span class="text-sm text-gray-600">{{ $count }}</span>
        </div>
        @endforeach
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Channel</label>
            <select name="channel" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="">Semua</option>
                <option value="WEB" {{ request('channel') === 'WEB' ? 'selected' : '' }}>WEB</option>
                <option value="WHATSAPP" {{ request('channel') === 'WHATSAPP' ? 'selected' : '' }}>WhatsApp</option>
                <option value="TELEGRAM" {{ request('channel') === 'TELEGRAM' ? 'selected' : '' }}>Telegram</option>
                <option value="EMAIL" {{ request('channel') === 'EMAIL' ? 'selected' : '' }}>Email</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="">Semua</option>
                <option value="SENT" {{ request('status') === 'SENT' ? 'selected' : '' }}>Terkirim</option>
                <option value="FAILED" {{ request('status') === 'FAILED' ? 'selected' : '' }}>Gagal</option>
                <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Pending</option>
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
            <a href="{{ route('distribusi.index') }}" class="rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kejadian</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Channel</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penerima</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($distribusi as $d)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $d->created_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    @if($d->riwayat)
                    <a href="{{ route('riwayat.show', $d->riwayat) }}" class="text-indigo-600 hover:text-indigo-900">
                        {{ $d->riwayat->event_type }} - Lantai {{ $d->riwayat->floor }}
                    </a>
                    @else
                    <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
                        {{ $d->channel }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $d->recipient ?? $d->kontak->nama ?? '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                        {{ $d->status === 'SENT' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $d->status === 'FAILED' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $d->status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                        {{ $d->status }}
                    </span>
                    @if($d->error_message)
                    <p class="text-xs text-red-500 mt-1">{{ Str::limit($d->error_message, 30) }}</p>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    @if($d->status === 'FAILED')
                    <button onclick="retryNotification({{ $d->id }})" class="text-indigo-600 hover:text-indigo-900">
                        <i class="fas fa-redo"></i> Retry
                    </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-paper-plane text-4xl mb-2"></i>
                    <p>Belum ada data</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="px-6 py-4 border-t border-gray-200">
        {{ $distribusi->withQueryString()->links() }}
    </div>
</div>

@push('scripts')
<script>
function retryNotification(id) {
    fetch(`/api/distribusi/${id}/retry`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(() => location.reload());
}
</script>
@endpush
@endsection
