@extends('layouts.app')

@section('title', 'Kamera')
@section('header', 'Monitoring Kamera')

@section('content')
<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Device</label>
            <select name="device_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="">Semua</option>
                @foreach($devices as $device)
                <option value="{{ $device }}" {{ request('device_id') === $device ? 'selected' : '' }}>{{ $device }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
            <select name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="">Semua</option>
                <option value="SCHEDULED" {{ request('type') === 'SCHEDULED' ? 'selected' : '' }}>Scheduled</option>
                <option value="EVENT" {{ request('type') === 'EVENT' ? 'selected' : '' }}>Event</option>
                <option value="MANUAL" {{ request('type') === 'MANUAL' ? 'selected' : '' }}>Manual</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
            <input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            <a href="{{ route('kamera.index') }}" class="rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

<!-- Image Grid -->
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
    @forelse($images as $image)
    <div class="bg-white rounded-lg shadow overflow-hidden group cursor-pointer" 
         onclick="showImage('{{ $image->image_url }}', '{{ $image->device_id }}', '{{ $image->captured_at->format('d/m/Y H:i') }}')">
        <div class="aspect-square relative">
            <img src="{{ $image->image_url }}" alt="Camera" class="w-full h-full object-cover">
            @if($image->riwayat)
            <div class="absolute top-2 right-2">
                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                    {{ $image->riwayat->event_type }}
                </span>
            </div>
            @endif
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center">
                <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 text-2xl"></i>
            </div>
        </div>
        <div class="p-2">
            <p class="text-xs font-medium text-gray-900 truncate">{{ $image->device_id }}</p>
            <p class="text-xs text-gray-500">{{ $image->captured_at->format('d/m H:i') }}</p>
        </div>
    </div>
    @empty
    <div class="col-span-full py-12 text-center text-gray-500">
        <i class="fas fa-camera text-4xl mb-2"></i>
        <p>Belum ada gambar</p>
    </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $images->withQueryString()->links() }}
</div>

<!-- Modal -->
<div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80" onclick="closeModal()">
    <div class="max-w-4xl max-h-[90vh] p-4" onclick="event.stopPropagation()">
        <img id="modalImage" src="" alt="Image" class="max-w-full max-h-[80vh] rounded-lg">
        <div class="mt-2 text-white text-center">
            <p id="modalDevice" class="font-medium"></p>
            <p id="modalTime" class="text-sm text-gray-300"></p>
        </div>
    </div>
    <button onclick="closeModal()" class="absolute top-4 right-4 text-white text-2xl">
        <i class="fas fa-times"></i>
    </button>
</div>

@push('scripts')
<script>
function showImage(url, device, time) {
    document.getElementById('modalImage').src = url;
    document.getElementById('modalDevice').textContent = device;
    document.getElementById('modalTime').textContent = time;
    document.getElementById('imageModal').classList.remove('hidden');
    document.getElementById('imageModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.getElementById('imageModal').classList.remove('flex');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
@endpush
@endsection
