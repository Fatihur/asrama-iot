<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kontak extends Model
{
    protected $table = 'kontaks';

    protected $fillable = [
        'nama',
        'jabatan',
        'nomor',
        'whatsapp',
        'pesan_wa',
        'telegram_id',
        'email',
        'ikon',
        'status',
        'notify_smoke',
        'notify_sos',
        'urutan',
    ];

    protected $casts = [
        'status' => 'boolean',
        'notify_smoke' => 'boolean',
        'notify_sos' => 'boolean',
    ];

    public function distribusi(): HasMany
    {
        return $this->hasMany(Distribusi::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }

    public function scopeForEvent($query, string $eventType)
    {
        return match (strtoupper($eventType)) {
            'SMOKE' => $query->where('notify_smoke', true),
            'SOS' => $query->where('notify_sos', true),
            default => $query,
        };
    }

    public function getWhatsappLinkAttribute(): ?string
    {
        if (!$this->whatsapp) return null;
        $number = preg_replace('/[^0-9]/', '', $this->whatsapp);
        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }
        $message = urlencode($this->pesan_wa ?? '');
        return "https://wa.me/{$number}?text={$message}";
    }
}
