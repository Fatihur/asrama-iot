<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Riwayat extends Model
{
    protected $table = 'riwayat';

    protected $fillable = [
        'device_id',
        'floor',
        'event_type',
        'value',
        'image_url',
        'notif_channel',
        'sirine_status',
        'ack_status',
        'resolve_status',
        'ack_by',
        'resolved_by',
        'ack_at',
        'resolved_at',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'ack_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function distribusi(): HasMany
    {
        return $this->hasMany(Distribusi::class);
    }

    public function kamera(): HasMany
    {
        return $this->hasMany(Kamera::class);
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ack_by');
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeOpen($query)
    {
        return $query->where('resolve_status', 'OPEN');
    }

    public function scopePending($query)
    {
        return $query->where('ack_status', 'PENDING');
    }

    public function scopeEmergency($query)
    {
        return $query->whereIn('event_type', ['SMOKE', 'FIRE', 'FIRE ALARM']);
    }

    public function isEmergency(): bool
    {
        return in_array($this->event_type, ['SMOKE', 'FIRE', 'FIRE ALARM']);
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->resolve_status === 'RESOLVED') return 'success';
        if ($this->ack_status === 'ACK') return 'warning';
        return 'danger';
    }
}
