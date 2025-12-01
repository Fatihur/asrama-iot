<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use App\Services\FcmService;
use Illuminate\Http\Request;

class FcmController extends Controller
{
    /**
     * Register FCM token
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|max:500',
            'device_name' => 'nullable|string|max:100',
        ]);

        FcmToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'device_name' => $validated['device_name'] ?? 'Unknown',
                'user_agent' => $request->userAgent(),
                'active' => true,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Token registered successfully',
        ]);
    }

    /**
     * Unregister FCM token
     */
    public function unregister(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        FcmToken::where('token', $validated['token'])->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Token removed successfully',
        ]);
    }

    /**
     * Send test notification to all devices
     */
    public function test(FcmService $fcmService)
    {
        $result = $fcmService->sendToAll(
            '🔔 Test Notifikasi',
            'Ini adalah test notifikasi dari Asrama IoT',
            ['test' => 'true']
        );

        return response()->json($result);
    }

    /**
     * List registered tokens
     */
    public function tokens()
    {
        $tokens = FcmToken::active()
            ->select('id', 'device_name', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($tokens);
    }
}
