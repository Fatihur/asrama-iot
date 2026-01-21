<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SirineLog;
use App\Models\Riwayat;
use Illuminate\Http\Request;

class SirineController extends Controller
{
    /**
     * Get actual sirine output state for ESP32
     * Returns "ON" or "OFF" based on mode and active emergencies
     */
    public function getOutput()
    {
        $mode = Setting::getSirineMode();
        
        // Direct mode: return as-is
        if ($mode === 'ON') {
            return response('ON', 200)->header('Content-Type', 'text/plain');
        }
        
        if ($mode === 'OFF') {
            return response('OFF', 200)->header('Content-Type', 'text/plain');
        }
        
        // AUTO mode: check for active emergencies
        $hasActiveEmergency = Riwayat::emergency()
            ->where('resolve_status', '!=', 'RESOLVED')
            ->exists();
        
        $output = $hasActiveEmergency ? 'ON' : 'OFF';
        
        return response($output, 200)->header('Content-Type', 'text/plain');
    }

    public function index()
    {
        $currentMode = Setting::getSirineMode();
        $logs = SirineLog::with(['user', 'riwayat'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('sirine.index', compact('currentMode', 'logs'));
    }

    public function getStatus()
    {
        return response(Setting::getSirineMode(), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function setStatus(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:ON,OFF,AUTO,on,off,auto',
        ]);

        $status = strtoupper($validated['status']);
        Setting::setSirineMode($status);

        SirineLog::log(
            $status,
            $request->has('device_id') ? 'API' : 'MANUAL',
            auth()->id(),
            null,
            $request->input('device_id'),
            $request->input('note', 'Status changed via ' . ($request->has('device_id') ? 'API' : 'Web'))
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Status sirine diperbarui',
            'current_status' => $status,
        ]);
    }

    public function toggle()
    {
        $current = Setting::getSirineMode();
        $new = $current === 'ON' ? 'OFF' : 'ON';

        Setting::setSirineMode($new);

        SirineLog::log($new, 'MANUAL', auth()->id(), null, null, 'Toggled from ' . $current);

        return response()->json([
            'status' => 'success',
            'previous' => $current,
            'current' => $new,
        ]);
    }

    public function setAuto()
    {
        Setting::setSirineMode('AUTO');

        SirineLog::log('AUTO', 'MANUAL', auth()->id(), null, null, 'Set to AUTO mode');

        return response()->json([
            'status' => 'success',
            'message' => 'Sirine dalam mode AUTO',
        ]);
    }
}
