<?php

namespace App\Services;

use App\Models\FcmToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private string $projectId = 'bukutamu-a3749';
    private ?string $accessToken = null;

    /**
     * Send notification to all registered devices
     */
    public function sendToAll(string $title, string $body, array $data = []): array
    {
        $tokens = FcmToken::active()->pluck('token')->toArray();
        
        if (empty($tokens)) {
            return ['success' => 0, 'failed' => 0, 'message' => 'No tokens registered'];
        }

        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($tokens as $token) {
            $sent = $this->sendToToken($token, $title, $body, $data);
            if ($sent) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Send notification to specific token using FCM HTTP v1 API
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            // Use legacy FCM API (simpler, no OAuth needed)
            $serverKey = config('services.fcm.server_key');
            
            if (!$serverKey) {
                Log::error('FCM Server Key not configured');
                return false;
            }

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'icon' => '/icons/icon-192x192.png',
                    'click_action' => url('/dashboard'),
                ],
                'data' => $data,
                'priority' => 'high',
                'time_to_live' => 60,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                // Check if token is invalid
                if (isset($result['failure']) && $result['failure'] > 0) {
                    $error = $result['results'][0]['error'] ?? null;
                    if (in_array($error, ['NotRegistered', 'InvalidRegistration'])) {
                        // Remove invalid token
                        FcmToken::where('token', $token)->delete();
                        Log::info('Removed invalid FCM token');
                    }
                    return false;
                }
                
                return true;
            }

            Log::error('FCM send failed: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('FCM error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send fire alert to all devices
     */
    public function sendFireAlert(array $eventData): array
    {
        $title = "🚨 {$eventData['event_type']} TERDETEKSI!";
        $body = "Lantai {$eventData['floor']} - {$eventData['device_id']}";
        
        $data = [
            'id' => (string) ($eventData['id'] ?? 0),
            'event_type' => $eventData['event_type'],
            'floor' => (string) $eventData['floor'],
            'device_id' => $eventData['device_id'],
            'url' => '/riwayat/' . ($eventData['id'] ?? ''),
        ];

        return $this->sendToAll($title, $body, $data);
    }
}
