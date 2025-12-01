@extends('layouts.app')

@section('title', $kontak ? 'Edit Kontak' : 'Tambah Kontak')
@section('header', $kontak ? 'Edit Kontak' : 'Tambah Kontak')

@section('content')
<div class="max-w-2xl">
    <form action="{{ $kontak ? route('kontak.update', $kontak) : route('kontak.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf
        @if($kontak)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="nama" value="{{ old('nama', $kontak->nama ?? '') }}" required
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('nama')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                <input type="text" name="jabatan" value="{{ old('jabatan', $kontak->jabatan ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon <span class="text-red-500">*</span></label>
                <input type="text" name="nomor" value="{{ old('nomor', $kontak->nomor ?? '') }}" required
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('nomor')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                <input type="text" name="whatsapp" value="{{ old('whatsapp', $kontak->whatsapp ?? '') }}" placeholder="08xxxxxxxxxx"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telegram ID</label>
                <input type="text" name="telegram_id" value="{{ old('telegram_id', $kontak->telegram_id ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $kontak->email ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Template Pesan WhatsApp</label>
                <textarea name="pesan_wa" rows="3" placeholder="Contoh: DARURAT! Terjadi kejadian di Asrama..."
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('pesan_wa', $kontak->pesan_wa ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ikon</label>
                <select name="ikon" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="user" {{ old('ikon', $kontak->ikon ?? 'user') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="user-tie" {{ old('ikon', $kontak->ikon ?? '') === 'user-tie' ? 'selected' : '' }}>Manager</option>
                    <option value="user-shield" {{ old('ikon', $kontak->ikon ?? '') === 'user-shield' ? 'selected' : '' }}>Security</option>
                    <option value="user-md" {{ old('ikon', $kontak->ikon ?? '') === 'user-md' ? 'selected' : '' }}>Medis</option>
                    <option value="fire-extinguisher" {{ old('ikon', $kontak->ikon ?? '') === 'fire-extinguisher' ? 'selected' : '' }}>Pemadam</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                <input type="number" name="urutan" value="{{ old('urutan', $kontak->urutan ?? 0) }}" min="0"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="md:col-span-2 space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="status" value="1" id="status" {{ old('status', $kontak->status ?? true) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="status" class="ml-2 text-sm text-gray-700">Kontak Aktif</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="notify_smoke" value="1" id="notify_smoke" {{ old('notify_smoke', $kontak->notify_smoke ?? true) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="notify_smoke" class="ml-2 text-sm text-gray-700">Notifikasi Smoke (Asap)</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="notify_sos" value="1" id="notify_sos" {{ old('notify_sos', $kontak->notify_sos ?? true) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="notify_sos" class="ml-2 text-sm text-gray-700">Notifikasi Flame (Api)</label>
                </div>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                <i class="fas fa-save mr-1"></i> Simpan
            </button>
            <a href="{{ route('kontak.index') }}" class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
