<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MemberNumber extends Model
{
    use HasFactory;

    const STATUS_AVAILABLE = 'available';
    const STATUS_USED = 'used';

    protected $fillable = [
        'number',
        'status',
        'member_id',
        'company_id',
        'formula_id',
        'prefix_value',
        'sequence',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'sequence' => 'integer',
        ];
    }

    /**
     * Get the member using this number.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the company associated with this number.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the formula used to generate this number.
     */
    public function formula(): BelongsTo
    {
        return $this->belongsTo(MemberNumberFormula::class);
    }

    /**
     * Check if this number is available.
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    /**
     * Check if this number is used.
     */
    public function isUsed(): bool
    {
        return $this->status === self::STATUS_USED;
    }

    /**
     * Mark this number as used by a member.
     */
    public function markAsUsed(Member $member): void
    {
        $this->update([
            'status' => self::STATUS_USED,
            'member_id' => $member->id,
        ]);
    }

    /**
     * Scope to get only available numbers.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    /**
     * Scope to get only used numbers.
     */
    public function scopeUsed($query)
    {
        return $query->where('status', self::STATUS_USED);
    }

    /**
     * Scope to get numbers for a specific company.
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to search by number.
     */
    public function scopeSearch($query, ?string $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('number', 'like', "%{$search}%")
              ->orWhereHas('member', function ($mq) use ($search) {
                  $mq->where('nama', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Get statistics.
     */
    public static function getStats(): array
    {
        return [
            'total' => static::count(),
            'available' => static::available()->count(),
            'used' => static::used()->count(),
        ];
    }
}
