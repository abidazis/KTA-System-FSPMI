<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KtaBackground extends Model
{
    protected $fillable = [
        'name',
        'front_image',
        'back_image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getActive()
    {
        return static::active()->first();
    }

    public function getFrontImagePath(): ?string
    {
        if (!$this->front_image) {
            return null;
        }
        return storage_path('app/private/' . $this->front_image);
    }

    public function getBackImagePath(): ?string
    {
        if (!$this->back_image) {
            return null;
        }
        return storage_path('app/private/' . $this->back_image);
    }
}
