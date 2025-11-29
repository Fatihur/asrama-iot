@extends('layouts.app')

@section('title', 'Kontak Darurat')
@section('header', 'Kontak Darurat')

@section('content')
<div class="mb-4 flex justify-between items-center">
    <p class="text-sm text-gray-600">Daftar kontak yang akan dihubungi saat terjadi kejadian darurat</p>
    <a href="{{ route('kontak.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
        <i class="fas fa-plus mr-1"></i> Tambah Kontak
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nomor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">WhatsApp</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notifikasi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($kontaks as $kontak)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100">
                                <i class="fas fa-{{ $kontak->ikon ?? 'user' }} text-indigo-600"></i>
                            </span>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ $kontak->nama }}</div>
                            @if($kontak->email)
                            <div class="text-sm text-gray-500">{{ $kontak->email }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $kontak->jabatan ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $kontak->nomor }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    @if($kontak->whatsapp)
                    <a href="{{ $kontak->whatsapp_link }}" target="_blank" class="text-green-600 hover:text-green-800">
                        <i class="fab fa-whatsapp mr-1"></i>{{ $kontak->whatsapp }}
                    </a>
                    @else
                    <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-1">
                        @if($kontak->notify_smoke)
                        <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                            <i class="fas fa-fire mr-1"></i>Smoke
                        </span>
                        @endif
                        @if($kontak->notify_sos)
                        <span class="inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-800">
                            <i class="fas fa-exclamation mr-1"></i>SOS
                        </span>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $kontak->status ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $kontak->status ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <a href="{{ route('kontak.edit', $kontak) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('kontak.destroy', $kontak) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kontak ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-address-book text-4xl mb-2"></i>
                    <p>Belum ada kontak</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="px-6 py-4 border-t border-gray-200">
        {{ $kontaks->links() }}
    </div>
</div>
@endsection
