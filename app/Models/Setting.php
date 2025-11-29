<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            if (!$setting) return $default;

            return match ($setting->type) {
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $setting->value,
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    public static function set(string $key, mixed $value, ?string $type = null): void
    {
        $setting = self::firstOrNew(['key' => $key]);
        
        if ($type) $setting->type = $type;
        
        $setting->value = is_array($value) ? json_encode($value) : (string) $value;
        $setting->save();

        Cache::forget("setting.{$key}");
    }

    public static function getSirineMode(): string
    {
        return self::get('sirine_mode', 'AUTO');
    }

    public static function setSirineMode(string $mode): void
    {
        self::set('sirine_mode', strtoupper($mode));
    }
}
