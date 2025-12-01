<?php

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushService
{
    protected WebPush $webPush;

    public function __construct()
    {
        $auth = [
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => config('services.vapid.public_key'),
                'privateKey' => config('services.vapid.private_key'),
            ],
        ];

        $this->webPush = new WebPush($auth);
    }

    public function sendNotification(PushSubscription $subscription, array $payload): bool
    {
        $webPushSub = Subscription::create([
            'endpoint' => $subscription->endpoint,
            'publicKey' => $subscription->p256dh_key,
            'authToken' => $subscription->auth_token,
        ]);

        $result = $this->webPush->sendOneNotification(
            $webPushSub,
            json_encode($payload)
        );

        if ($result->isSuccess()) {
            return true;
        }

        // If subscription expired, deactivate it
        if ($result->isSubscriptionExpired()) {
            $subscription->update(['active' => false]);
        }

        return false;
    }

    public function sendToAll(array $payload): array
    {
        $subscriptions = PushSubscription::active()->get();
        $results = ['success' => 0, 'failed' => 0];

        foreach ($subscriptions as $subscription) {
            $webPushSub = Subscription::create([
                'endpoint' => $subscription->endpoint,
                'publicKey' => $subscription->p256dh_key,
                'authToken' => $subscription->auth_token,
            ]);

            $this->webPush->queueNotification($webPushSub, json_encode($payload));
        }

        foreach ($this->webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $results['success']++;
            } else {
                $results['failed']++;
                
                // Deactivate expired subscriptions
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $report->getEndpoint())
                        ->update(['active' => false]);
                }
            }
        }

        return $results;
    }

    public function sendFireAlert(array $eventData): array
    {
        $payload = [
            'title' => "🚨 {$eventData['event_type']} TERDETEKSI!",
            'body' => "Lantai {$eventData['floor']} - {$eventData['device_id']}",
            'icon' => '/favicon.ico',
            'badge' => '/favicon.ico',
            'tag' => 'fire-alarm-' . ($eventData['id'] ?? time()),
            'requireInteraction' => true,
            'data' => [
                'url' => '/riwayat/' . ($eventData['id'] ?? ''),
                'event_type' => $eventData['event_type'],
                'floor' => $eventData['floor'],
                'timestamp' => $eventData['timestamp'] ?? now()->toIso8601String(),
            ],
        ];

        return $this->sendToAll($payload);
    }
}
