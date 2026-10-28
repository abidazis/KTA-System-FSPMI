<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    /**
     * Get setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function set($key, $value, $type = 'string')
    {
        // label wajib terisi (NOT NULL). Saat baris belum ada, pakai default
        // agar firstOrCreate tidak gagal di instalasi baru.
        $setting = static::firstOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'label' => Str::headline($key),
                'group' => 'general',
            ]
        );

        // Jangan menimpa label/group yang sudah diatur lewat seeder/pengaturan.
        if (! $setting->wasRecentlyCreated) {
            $setting->update(['value' => $value, 'type' => $type]);
        }

        return $setting;
    }

    /**
     * Get settings by group
     */
    public static function getByGroup($group)
    {
        return static::where('group', $group)->get();
    }

    /**
     * Get KTA validity period in years
     */
    public static function getKtaValidityYears(): int
    {
        return (int) static::get('kta_validity_years', 5);
    }

    /**
     * Get max photo size in KB
     */
    public static function getMaxPhotoSizeKb(): int
    {
        return (int) static::get('max_photo_size_kb', 2048);
    }

    /**
     * Get max photo size in kilobytes for validation rule
     */
    public static function getMaxPhotoSizeForValidation(): int
    {
        return static::getMaxPhotoSizeKb();
    }
}
