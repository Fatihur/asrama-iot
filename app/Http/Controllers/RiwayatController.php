<?php

namespace App\Http\Controllers;

use App\Models\Riwayat;
use App\Models\Distribusi;
use App\Models\Kontak;
use App\Models\Setting;
use App\Models\SirineLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $query = Riwayat::select('id', 'device_id', 'floor', 'event_type', 'value', 'sirine_status', 'ack_status', 'resolve_status', 'timestamp');

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }
        if ($request->filled('status')) {
            $query->where('resolve_status', $request->status);
        }
        if ($request->filled('floor')) {
            $query->where('floor', $request->floor);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('timestamp', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('timestamp', '<=', $request->date_to);
        }

        $riwayat = $query->orderBy('timestamp', 'desc')->paginate(20);

        $eventTypes = Cache::remember('event_types', 300, fn() => Riwayat::distinct()->pluck('event_type'));
        $floors = Cache::remember('floors', 300, fn() => Riwayat::distinct()->pluck('floor')->sort());

        return view('riwayat.index', compact('riwayat', 'eventTypes', 'floors'));
    }

    public function show(Riwayat $riwayat)
    {
        $riwayat->load(['distribusi.kontak', 'kamera', 'acknowledgedBy', 'resolvedByUser']);
        return view('riwayat.show', compact('riwayat'));
    }

    public function apiIndex(Request $request)
    {
        $query = Riwayat::query();

        if ($request->filled('limit')) {
            $query->limit($request->limit);
        }
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        return response()->json($query->orderBy('timestamp', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:50',
            'floor' => 'required|integer|min:1|max:100',
            'event_type' => 'required|string|max:20',
            'value' => 'nullable|string|max:255',
            'image_url' => 'nullable|url|max:500',
        ]);

        $isEmergency = in_array(strtoupper($validated['event_type']), ['SMOKE', 'SOS']);
        $sirineMode = Setting::getSirineMode();

        $riwayat = Riwayat::create([
            'device_id' => $validated['device_id'],
            'floor' => $validated['floor'],
            'event_type' => strtoupper($validated['event_type']),
            'value' => $validated['value'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'notif_channel' => 'WEB, API',
            'sirine_status' => ($isEmergency && $sirineMode !== 'OFF') ? 'ON' : 'OFF',
            'timestamp' => now(),
        ]);

        if ($isEmergency && $sirineMode !== 'OFF') {
            SirineLog::log('ON', 'AUTO', null, $riwayat->id, $validated['device_id'], 'Auto triggered by ' . $validated['event_type']);
        }

        $this->sendNotifications($riwayat);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil disimpan',
            'data' => $riwayat,
            'sirine' => $riwayat->sirine_status,
        ], 201);
    }

    public function acknowledge(Request $request, Riwayat $riwayat)
    {
        $riwayat->update([
            'ack_status' => 'ACK',
            'ack_by' => auth()->id(),
            'ack_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Kejadian telah dikonfirmasi',
        ]);
    }

    public function resolve(Request $request, Riwayat $riwayat)
    {
        $riwayat->update([
            'resolve_status' => 'RESOLVED',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
            'sirine_status' => 'OFF',
        ]);

        if ($riwayat->sirine_status === 'ON') {
            SirineLog::log('OFF', 'MANUAL', auth()->id(), $riwayat->id, null, 'Resolved by user');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Kejadian telah diselesaikan',
        ]);
    }

    protected function sendNotifications(Riwayat $riwayat): void
    {
        $contacts = Kontak::active()->forEvent($riwayat->event_type)->get();

        foreach ($contacts as $contact) {
            Distribusi::create([
                'riwayat_id' => $riwayat->id,
                'kontak_id' => $contact->id,
                'channel' => 'WEB',
                'recipient' => $contact->nama,
                'message' => "Alert: {$riwayat->event_type} di Lantai {$riwayat->floor}",
                'status' => 'SENT',
                'sent_at' => now(),
            ]);
        }
    }

    public function storeSensor(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:50',
            'floor' => 'required|integer|min:1|max:100',
            'value' => 'nullable|string|max:255',
            'temperature' => 'nullable|numeric',
            'humidity' => 'nullable|numeric',
            'smoke' => 'nullable|numeric',
        ]);

        $value = $validated['value'] ?? json_encode([
            'temperature' => $validated['temperature'] ?? null,
            'humidity' => $validated['humidity'] ?? null,
            'smoke' => $validated['smoke'] ?? null,
        ]);

        $riwayat = Riwayat::create([
            'device_id' => $validated['device_id'],
            'floor' => $validated['floor'],
            'event_type' => 'SENSOR',
            'value' => $value,
            'notif_channel' => 'API',
            'sirine_status' => 'OFF',
            'timestamp' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data sensor berhasil disimpan',
            'data' => $riwayat,
        ], 201);
    }

    public function getSensorData(Request $request)
    {
        $query = Riwayat::where('event_type', 'SENSOR');

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }
        if ($request->filled('floor')) {
            $query->where('floor', $request->floor);
        }
        if ($request->filled('limit')) {
            $query->limit($request->limit);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('timestamp', 'desc')->get(),
        ]);
    }

    public function storeFire(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:50',
            'floor' => 'required|integer|min:1|max:100',
            'value' => 'nullable|string|max:255',
            'image_url' => 'nullable|url|max:500',
        ]);

        $sirineMode = Setting::getSirineMode();

        $riwayat = Riwayat::create([
            'device_id' => $validated['device_id'],
            'floor' => $validated['floor'],
            'event_type' => 'FIRE',
            'value' => $validated['value'] ?? 'Fire detected',
            'image_url' => $validated['image_url'] ?? null,
            'notif_channel' => 'WEB, API',
            'sirine_status' => $sirineMode !== 'OFF' ? 'ON' : 'OFF',
            'timestamp' => now(),
        ]);

        if ($sirineMode !== 'OFF') {
            SirineLog::log('ON', 'AUTO', null, $riwayat->id, $validated['device_id'], 'Auto triggered by FIRE');
        }

        $this->sendNotifications($riwayat);

        return response()->json([
            'status' => 'success',
            'message' => 'Fire event berhasil disimpan',
            'data' => $riwayat,
            'sirine' => $riwayat->sirine_status,
        ], 201);
    }

    public function getFireEvents(Request $request)
    {
        $query = Riwayat::where('event_type', 'FIRE');

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }
        if ($request->filled('floor')) {
            $query->where('floor', $request->floor);
        }
        if ($request->filled('limit')) {
            $query->limit($request->limit);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('timestamp', 'desc')->get(),
        ]);
    }
}
