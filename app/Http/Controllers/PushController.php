<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|max:500',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $subscription = PushSubscription::updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            [
                'p256dh_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'user_agent' => $request->userAgent(),
                'device_name' => $validated['device_name'] ?? null,
                'active' => true,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Push subscription berhasil disimpan',
            'id' => $subscription->id,
        ]);
    }

    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        PushSubscription::where('endpoint', $validated['endpoint'])->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Push subscription berhasil dihapus',
        ]);
    }

    public function vapidPublicKey()
    {
        return response()->json([
            'publicKey' => config('services.vapid.public_key'),
        ]);
    }

    public function test(Request $request)
    {
        $webPush = new WebPushService();
        
        $results = $webPush->sendFireAlert([
            'id' => 0,
            'event_type' => 'TEST ALARM',
            'floor' => 1,
            'device_id' => 'TEST-DEVICE',
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Test notification sent',
            'results' => $results,
        ]);
    }

    public function subscriptions()
    {
        $subs = PushSubscription::active()->get(['id', 'device_name', 'user_agent', 'created_at']);
        
        return response()->json([
            'status' => 'success',
            'count' => $subs->count(),
            'subscriptions' => $subs,
        ]);
    }
}
