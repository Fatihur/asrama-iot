<?php

namespace App\Http\Controllers;

use App\Models\Riwayat;
use App\Models\Kontak;
use App\Models\Kamera;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('dashboard_stats', 30, function () {
            return [
                'total_events' => Riwayat::count(),
                'open_events' => Riwayat::where('resolve_status', 'OPEN')->count(),
                'pending_events' => Riwayat::where('ack_status', 'PENDING')->count(),
                'today_events' => Riwayat::whereDate('timestamp', today())->count(),
                'emergency_today' => Riwayat::whereIn('event_type', ['SMOKE', 'SOS'])->whereDate('timestamp', today())->count(),
            ];
        });

        $eventsByType = Cache::remember('events_by_type', 60, function () {
            return Riwayat::select('event_type', DB::raw('count(*) as total'))
                ->groupBy('event_type')
                ->pluck('total', 'event_type')
                ->toArray();
        });

        $recentEvents = Riwayat::select('id', 'device_id', 'floor', 'event_type', 'resolve_status', 'ack_status', 'sirine_status', 'timestamp')
            ->orderBy('timestamp', 'desc')
            ->limit(10)
            ->get();

        $latestCamera = Cache::remember('latest_camera', 60, fn() => Kamera::orderBy('captured_at', 'desc')->first());
        $sirineMode = Setting::getSirineMode();

        $chartData = Cache::remember('chart_data', 120, function () {
            return Riwayat::select(DB::raw('DATE(timestamp) as date'), DB::raw('count(*) as total'))
                ->where('timestamp', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        });

        return view('dashboard.index', compact('stats', 'eventsByType', 'recentEvents', 'latestCamera', 'sirineMode', 'chartData'));
    }

    public function sse()
    {
        return response()->stream(function () {
            $lastId = 0;
            while (true) {
                $latest = Riwayat::select('id', 'device_id', 'floor', 'event_type', 'sirine_status', 'timestamp')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($latest && $latest->id !== $lastId) {
                    $lastId = $latest->id;
                    $data = [
                        'latest' => $latest,
                        'pending_count' => Riwayat::where('ack_status', 'PENDING')->count(),
                        'sirine_mode' => Setting::getSirineMode(),
                    ];
                    echo "event: update\ndata: " . json_encode($data) . "\n\n";
                    ob_flush();
                    flush();
                }

                if (connection_aborted()) break;
                sleep(5);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
