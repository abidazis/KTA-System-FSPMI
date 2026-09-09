<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagementOfficial extends Model
{
    use HasFactory;

    protected $fillable = [
        'management_period_id',
        'jabatan',
        'nama',
        'signature_path',
        'status',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(ManagementPeriod::class, 'management_period_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByJabatan($query, $jabatan)
    {
        return $query->where('jabatan', $jabatan);
    }

    public function getSignatureUrl(): ?string
    {
        if (empty($this->signature_path)) return null;
        $privatePath = storage_path('app/private/' . $this->signature_path);
        if (file_exists($privatePath)) return '/media/' . $this->signature_path;
        $publicPath = storage_path('app/public/' . $this->signature_path);
        if (file_exists($publicPath)) return '/media/' . $this->signature_path;
        return null;
    }
}
