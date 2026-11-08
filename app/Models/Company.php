<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'name',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Find a company by its unique kode (case-insensitive, exact match).
     * Used as the data puller (penarik data) during Excel import.
     */
    public static function findByKode(?string $kode): ?self
    {
        if ($kode === null || trim($kode) === '') {
            return null;
        }

        return static::withoutGlobalScopes()
            ->whereRaw('UPPER(kode) = ?', [mb_strtoupper(trim($kode))])
            ->first();
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }
}
