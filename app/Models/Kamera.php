<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kamera extends Model
{
    protected $table = 'kamera';

    protected $fillable = [
        'device_id',
        'floor',
        'lokasi',
        'image_url',
        'image_path',
        'riwayat_id',
        'type',
        'event_type',
        'captured_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
    ];

    public function riwayat(): BelongsTo
    {
        return $this->belongsTo(Riwayat::class);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('captured_at', 'desc');
    }

    public function scopeByDevice($query, string $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    public function scopeByFloor($query, int $floor)
    {
        return $query->where('floor', $floor);
    }

    public function getImageFullUrlAttribute(): string
    {
        if (str_starts_with($this->image_url, 'http')) {
            return $this->image_url;
        }
        return asset('storage/' . $this->image_url);
    }
}
