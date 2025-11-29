<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SirineLog extends Model
{
    protected $table = 'sirine_logs';

    protected $fillable = [
        'status',
        'source',
        'user_id',
        'riwayat_id',
        'device_id',
        'note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function riwayat(): BelongsTo
    {
        return $this->belongsTo(Riwayat::class);
    }

    public static function log(string $status, string $source, ?int $userId = null, ?int $riwayatId = null, ?string $deviceId = null, ?string $note = null): self
    {
        return self::create([
            'status' => $status,
            'source' => $source,
            'user_id' => $userId,
            'riwayat_id' => $riwayatId,
            'device_id' => $deviceId,
            'note' => $note,
        ]);
    }
}
