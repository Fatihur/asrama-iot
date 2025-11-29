<?php

namespace App\Http\Controllers;

use App\Models\Distribusi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistribusiController extends Controller
{
    public function index(Request $request)
    {
        $query = Distribusi::with(['riwayat', 'kontak']);

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $distribusi = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => Distribusi::count(),
            'sent' => Distribusi::sent()->count(),
            'failed' => Distribusi::failed()->count(),
            'pending' => Distribusi::where('status', 'PENDING')->count(),
        ];

        $byChannel = Distribusi::select('channel', DB::raw('count(*) as total'))
            ->groupBy('channel')
            ->pluck('total', 'channel')
            ->toArray();

        return view('distribusi.index', compact('distribusi', 'stats', 'byChannel'));
    }

    public function show(Distribusi $distribusi)
    {
        $distribusi->load(['riwayat', 'kontak']);
        return view('distribusi.show', compact('distribusi'));
    }

    public function retry(Distribusi $distribusi)
    {
        $distribusi->update([
            'status' => 'PENDING',
            'error_message' => null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi akan dikirim ulang',
        ]);
    }
}
