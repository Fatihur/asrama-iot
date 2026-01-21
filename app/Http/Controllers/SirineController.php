<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SirineLog;
use Illuminate\Http\Request;

class SirineController extends Controller
{
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
        // Bypass cache - get fresh data directly from DB
        $setting = Setting::where('key', 'sirine_mode')->first();
        $mode = $setting ? $setting->value : 'AUTO';
        
        return response($mode, 200)
            ->header('Content-Type', 'text/plain');
    }

    public function setStatus(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:ON,OFF,AUTO,on,off,auto',
        ]);

        $status = strtoupper($validated['status']);
        Setting::setSirineMode($status);
        
        // Clear cache explicitly to ensure fresh data
        \Illuminate\Support\Facades\Cache::forget('setting.sirine_mode');

        SirineLog::log(
            $status,
            $request->has('device_id') ? 'API' : 'MANUAL',
            auth()->id(),
            null,
            $request->input('device_id'),
            $request->input('note', 'Status changed via ' . ($request->has('device_id') ? 'API' : 'Web'))
        );

        // Return the actual saved value from DB
        $savedMode = Setting::where('key', 'sirine_mode')->first()?->value ?? $status;
        
        return response()->json([
            'status' => 'success',
            'message' => 'Status sirine diperbarui',
            'current_status' => $savedMode,
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
