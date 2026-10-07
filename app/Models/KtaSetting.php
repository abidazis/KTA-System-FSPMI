<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KtaSetting extends Model
{
    protected $fillable = [
        'key',
        'label',
        'value',
        'type',
        'options',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->where('is_active', true)->first();
        return $setting ? $setting->value : $default;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $value = static::get($key, $default);
        return (int) $value;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function boot()
    {
        parent::boot();

        // Seed default settings on first run
        static::created(function ($setting) {
            // Check if this is the first creation (seeding)
        });
    }
}
