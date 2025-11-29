<?php

namespace App\Http\Controllers;

use App\Models\Kontak;
use Illuminate\Http\Request;

class KontakController extends Controller
{
    public function index()
    {
        $kontaks = Kontak::ordered()->paginate(20);
        return view('kontak.index', compact('kontaks'));
    }

    public function create()
    {
        return view('kontak.form', ['kontak' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'jabatan' => 'nullable|string|max:100',
            'nomor' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'pesan_wa' => 'nullable|string|max:500',
            'telegram_id' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'ikon' => 'nullable|string|max:50',
            'status' => 'boolean',
            'notify_smoke' => 'boolean',
            'notify_sos' => 'boolean',
            'urutan' => 'nullable|integer|min:0',
        ]);

        $validated['status'] = $request->boolean('status', true);
        $validated['notify_smoke'] = $request->boolean('notify_smoke', true);
        $validated['notify_sos'] = $request->boolean('notify_sos', true);

        Kontak::create($validated);

        return redirect()->route('kontak.index')
            ->with('success', 'Kontak berhasil ditambahkan');
    }

    public function edit(Kontak $kontak)
    {
        return view('kontak.form', compact('kontak'));
    }

    public function update(Request $request, Kontak $kontak)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'jabatan' => 'nullable|string|max:100',
            'nomor' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'pesan_wa' => 'nullable|string|max:500',
            'telegram_id' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'ikon' => 'nullable|string|max:50',
            'status' => 'boolean',
            'notify_smoke' => 'boolean',
            'notify_sos' => 'boolean',
            'urutan' => 'nullable|integer|min:0',
        ]);

        $validated['status'] = $request->boolean('status', true);
        $validated['notify_smoke'] = $request->boolean('notify_smoke', true);
        $validated['notify_sos'] = $request->boolean('notify_sos', true);

        $kontak->update($validated);

        return redirect()->route('kontak.index')
            ->with('success', 'Kontak berhasil diperbarui');
    }

    public function destroy(Kontak $kontak)
    {
        $kontak->delete();

        return redirect()->route('kontak.index')
            ->with('success', 'Kontak berhasil dihapus');
    }
}
