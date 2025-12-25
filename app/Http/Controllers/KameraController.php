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
                'status' => 'empty',
                'message' => 'Tidak ada gambar',
                'data' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $latest,
        ]);
    }

    public function store(Request $request)
    {
        // Flexible validation untuk ESP32-CAM
        $validated = $request->validate([
            'device_id' => 'nullable|string|max:50',
            'floor' => 'nullable|integer|min:1',
            'lokasi' => 'nullable|string|max:100',
            'image_url' => 'nullable|url|max:500',
            'image' => 'nullable|max:5120',
            'imageFile' => 'nullable|max:5120',
            'riwayat_id' => 'nullable|exists:riwayat,id',
            'type' => 'nullable|in:SCHEDULED,EVENT,MANUAL',
            'captured_at' => 'nullable|date',
        ]);

        $imageUrl = $validated['image_url'] ?? null;
        $imagePath = null;

        // Handle file upload dari form-data (image atau imageFile)
        $imageFile = $request->file('image') ?? $request->file('imageFile');
        if ($imageFile) {
            $filename = 'esp32_' . date('Ymd_His') . '_' . uniqid() . '.' . ($imageFile->getClientOriginalExtension() ?: 'jpg');
            $path = $imageFile->storeAs('kamera', $filename, 'public');
            $imageUrl = Storage::url($path);
            $imagePath = $path;
        }
        
        // Handle raw binary image dari ESP32-CAM
        if (!$imageFile && !$imageUrl && $request->getContent()) {
            $content = $request->getContent();
            // Check if it's image data (starts with JPEG magic bytes or has significant size)
            if (strlen($content) > 100) {
                $filename = 'esp32_' . date('Ymd_His') . '_' . uniqid() . '.jpg';
                Storage::disk('public')->put('kamera/' . $filename, $content);
                $imagePath = 'kamera/' . $filename;
                $imageUrl = Storage::url($imagePath);
            }
        }

        if (!$imageUrl) {
            return response()->json([
                'status' => 'error',
                'message' => 'No image provided',
            ], 400);
        }

        // Tentukan captured_at: prioritas dari request > riwayat > now
        $capturedAt = $validated['captured_at'] ?? null;
        $riwayatId = $validated['riwayat_id'] ?? null;
        
        // Jika tidak ada riwayat_id, cari event terakhir dalam 30 detik terakhir dari device yang sama
        if (!$riwayatId) {
            $deviceId = $validated['device_id'] ?? $request->header('X-Device-ID', 'ESP32-CAM');
            $floor = $validated['floor'] ?? $request->header('X-Floor', 1);
            
            $recentEvent = \App\Models\Riwayat::where('floor', $floor)
                ->whereIn('event_type', ['FIRE', 'SMOKE', 'FLAME'])
                ->where('timestamp', '>=', now()->subSeconds(30))
                ->orderBy('timestamp', 'desc')
                ->first();
            
            if ($recentEvent) {
                $riwayatId = $recentEvent->id;
                $capturedAt = $capturedAt ?? $recentEvent->timestamp;
            }
        }
        
        // Jika ada riwayat_id tapi belum ada captured_at, ambil dari riwayat
        if (!$capturedAt && $riwayatId) {
            $riwayat = \App\Models\Riwayat::find($riwayatId);
            $capturedAt = $riwayat?->timestamp;
        }
        
        $capturedAt = $capturedAt ?? now();

        $kamera = Kamera::create([
            'device_id' => $validated['device_id'] ?? $request->header('X-Device-ID', 'ESP32-CAM'),
            'floor' => $validated['floor'] ?? $request->header('X-Floor', 1),
            'lokasi' => $validated['lokasi'] ?? null,
            'image_url' => $imageUrl,
            'image_path' => $imagePath,
            'riwayat_id' => $riwayatId,
            'type' => $validated['type'] ?? 'EVENT',
            'captured_at' => $capturedAt,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Gambar berhasil disimpan',
            'data' => $kamera,
        ], 201);
    }

    public function destroy(Kamera $kamera)
    {
        // Hapus file gambar jika ada
        if ($kamera->image_path && Storage::disk('public')->exists($kamera->image_path)) {
            Storage::disk('public')->delete($kamera->image_path);
        }

        $kamera->delete();

        return redirect()->route('kamera.index')->with('success', 'Gambar berhasil dihapus');
    }
}
