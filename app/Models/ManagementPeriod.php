<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManagementPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_periode',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'stempel_path',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function officials(): HasMany
    {
        return $this->hasMany(ManagementOfficial::class);
    }

    public function activeOfficials(): HasMany
    {
        return $this->hasMany(ManagementOfficial::class)->where('status', 'active');
    }

    public function printBatches(): HasMany
    {
        return $this->hasMany(PrintBatch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getStempelUrl(): ?string
    {
        if (empty($this->stempel_path)) return null;
        $privatePath = storage_path('app/private/' . $this->stempel_path);
        if (file_exists($privatePath)) return '/media/' . $this->stempel_path;
        $publicPath = storage_path('app/public/' . $this->stempel_path);
        if (file_exists($publicPath)) return '/media/' . $this->stempel_path;
        return null;
    }
}
