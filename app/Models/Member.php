<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nik',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'province_id',
        'regency_id',
        'district_id',
        'jenis_kelamin',
        'agama',
        'berlaku_hingga',
        'tanggal_pembuatan',
        'foto_path',
        'status',
        'company_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'berlaku_hingga' => 'date',
            'tanggal_pembuatan' => 'date',
        ];
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function printBatches(): BelongsToMany
    {
        return $this->belongsToMany(PrintBatch::class, 'print_batch_members')
            ->withPivot('position')
            ->withTimestamps();
    }

    public function isExpired(): bool
    {
        return $this->berlaku_hingga->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->berlaku_hingga->between(now(), now()->addDays($days));
    }

    public function hasPhoto(): bool
    {
        if (empty($this->foto_path)) return false;
        // Check in private disk (storage/app/private/) - where 'local' disk stores files
        $privatePath = storage_path('app/private/' . $this->foto_path);
        if (file_exists($privatePath)) return true;
        // Check in local disk root (storage/app/) - fallback
        $localPath = storage_path('app/' . $this->foto_path);
        return file_exists($localPath);
    }

    public function getPhotoUrl(): ?string
    {
        if (!$this->hasPhoto()) return null;
        return '/media/' . $this->foto_path;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('berlaku_hingga', '<', now()->toDateString());
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereBetween('berlaku_hingga', [
            now()->toDateString(),
            now()->addDays($days)->toDateString()
        ]);
    }

    public function scopeReadyToPrint($query)
    {
        return $query->whereNotNull('foto_path')
            ->whereIn('status', ['ready', 'generated']);
    }
}
