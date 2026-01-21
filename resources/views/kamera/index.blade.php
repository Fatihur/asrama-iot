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
    <div class="bg-white rounded-lg shadow overflow-hidden group relative">
        <div class="aspect-square relative cursor-pointer" 
             onclick="showImage('{{ $image->image_url }}', '{{ $image->device_id }}', '{{ $image->captured_at->format('d/m/Y H:i') }}', {{ $image->id }})">
            <img src="{{ $image->image_url }}" alt="Camera" class="w-full h-full object-cover">
            @php
                $eventType = $image->event_type ?? ($image->riwayat->event_type ?? null);
            @endphp
            @if($eventType)
            <div class="absolute top-2 right-2">
                @if($eventType === 'FIRE')
                <span class="inline-flex items-center rounded-md bg-red-500 px-2 py-1 text-xs font-semibold text-white shadow">
                    FIRE
                </span>
                @elseif($eventType === 'SMOKE')
                <span class="inline-flex items-center rounded-md bg-yellow-500 px-2 py-1 text-xs font-semibold text-white shadow">
                    SMOKE
                </span>
                @endif
            </div>
            @endif
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center">
                <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 text-2xl"></i>
            </div>
        </div>
        <div class="p-2 flex justify-between items-start">
            <div>
                <p class="text-xs font-medium text-gray-900 truncate">{{ $image->device_id }}</p>
                <p class="text-xs text-gray-500">{{ $image->captured_at->format('d/m H:i') }}</p>
            </div>
            <button onclick="confirmDelete({{ $image->id }}, '{{ $image->device_id }}')" 
                    class="text-red-500 hover:text-red-700 p-1" title="Hapus">
                <i class="fas fa-trash text-xs"></i>
            </button>
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

<!-- Flash Message -->
@if(session('success'))
<div id="flashMessage" class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
    <div class="flex items-center gap-2">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80" onclick="closeModal()">
    <div class="max-w-4xl max-h-[90vh] p-4" onclick="event.stopPropagation()">
        <img id="modalImage" src="" alt="Image" class="max-w-full max-h-[80vh] rounded-lg">
        <div class="mt-2 text-white text-center">
            <p id="modalDevice" class="font-medium"></p>
            <p id="modalTime" class="text-sm text-gray-300"></p>
        </div>
        <div class="mt-4 flex justify-center">
            <button id="modalDeleteBtn" onclick="confirmDeleteFromModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fas fa-trash"></i> Hapus Gambar
            </button>
        </div>
    </div>
    <button onclick="closeModal()" class="absolute top-4 right-4 text-white text-2xl">
        <i class="fas fa-times"></i>
    </button>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg p-6 max-w-sm mx-4" onclick="event.stopPropagation()">
        <div class="text-center">
            <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Hapus</h3>
            <p class="text-gray-600 mb-4">Apakah Anda yakin ingin menghapus gambar dari <strong id="deleteDeviceName"></strong>?</p>
        </div>
        <form id="deleteForm" method="POST" class="flex gap-3">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                Batal
            </button>
            <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Hapus
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
let currentImageId = null;

function showImage(url, device, time, id) {
    currentImageId = id;
    document.getElementById('modalImage').src = url;
    document.getElementById('modalDevice').textContent = device;
    document.getElementById('modalTime').textContent = time;
    document.getElementById('imageModal').classList.remove('hidden');
    document.getElementById('imageModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.getElementById('imageModal').classList.remove('flex');
    currentImageId = null;
}

function confirmDelete(id, deviceName) {
    document.getElementById('deleteDeviceName').textContent = deviceName;
    document.getElementById('deleteForm').action = '/kamera/' + id;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteModal').classList.add('flex');
}

function confirmDeleteFromModal() {
    if (currentImageId) {
        const deviceName = document.getElementById('modalDevice').textContent;
        closeModal();
        confirmDelete(currentImageId, deviceName);
    }
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteModal').classList.remove('flex');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        closeDeleteModal();
    }
});

// Auto hide flash message
const flashMessage = document.getElementById('flashMessage');
if (flashMessage) {
    setTimeout(() => {
        flashMessage.style.opacity = '0';
        flashMessage.style.transition = 'opacity 0.5s';
        setTimeout(() => flashMessage.remove(), 500);
    }, 3000);
}
</script>
@endpush
@endsection
