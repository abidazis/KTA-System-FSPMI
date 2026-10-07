<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
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
