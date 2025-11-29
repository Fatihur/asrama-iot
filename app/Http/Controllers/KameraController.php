<?php

namespace App\Http\Controllers;

use App\Models\Kamera;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KameraController extends Controller
{
    public function index(Request $request)
    {
        $query = Kamera::with('riwayat');

        if ($request->filled('floor')) {
            $query->byFloor($request->floor);
        }
        if ($request->filled('device_id')) {
            $query->byDevice($request->device_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date')) {
            $query->whereDate('captured_at', $request->date);
        }

        $images = $query->latest()->paginate(24);
        $floors = Kamera::distinct()->pluck('floor')->sort();
        $devices = Kamera::distinct()->pluck('device_id');

        return view('kamera.index', compact('images', 'floors', 'devices'));
    }

    public function show(Kamera $kamera)
    {
        $kamera->load('riwayat');
        return view('kamera.show', compact('kamera'));
    }

    public function latestImage(Request $request)
    {
        $query = Kamera::query();

        if ($request->filled('floor')) {
            $query->byFloor($request->floor);
        }
        if ($request->filled('device_id')) {
            $query->byDevice($request->device_id);
        }

        $latest = $query->latest()->first();

        if (!$latest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada gambar',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $latest,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:50',
            'floor' => 'required|integer|min:1',
            'lokasi' => 'nullable|string|max:100',
            'image_url' => 'required_without:image|url|max:500',
            'image' => 'required_without:image_url|image|max:5120',
            'riwayat_id' => 'nullable|exists:riwayat,id',
            'type' => 'nullable|in:SCHEDULED,EVENT,MANUAL',
        ]);

        $imageUrl = $validated['image_url'] ?? null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('kamera', 'public');
            $imageUrl = Storage::url($path);
            $validated['image_path'] = $path;
        }

        $kamera = Kamera::create([
            'device_id' => $validated['device_id'],
            'floor' => $validated['floor'],
            'lokasi' => $validated['lokasi'] ?? null,
            'image_url' => $imageUrl,
            'image_path' => $validated['image_path'] ?? null,
            'riwayat_id' => $validated['riwayat_id'] ?? null,
            'type' => $validated['type'] ?? 'SCHEDULED',
            'captured_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Gambar berhasil disimpan',
            'data' => $kamera,
        ], 201);
    }
}
