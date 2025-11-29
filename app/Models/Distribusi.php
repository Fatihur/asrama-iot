<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Distribusi extends Model
{
    protected $table = 'distribusi';

    protected $fillable = [
        'riwayat_id',
        'kontak_id',
        'channel',
        'recipient',
        'message',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function riwayat(): BelongsTo
    {
        return $this->belongsTo(Riwayat::class);
    }

    public function kontak(): BelongsTo
    {
        return $this->belongsTo(Kontak::class);
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'SENT');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'FAILED');
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'SENT',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'FAILED',
            'error_message' => $error,
        ]);
    }
}
