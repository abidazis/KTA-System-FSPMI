<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberNumberFormula extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'prefix',
        'separator',
        'include_company_code',
        'sequence_digits',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'include_company_code' => 'boolean',
            'is_active' => 'boolean',
            'sequence_digits' => 'integer',
        ];
    }

    /**
     * Get the user who created this formula.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get sequences for this formula.
     */
    public function sequences(): HasMany
    {
        return $this->hasMany(MemberNumberSequence::class);
    }

    /**
     * Get the currently active formula.
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Scope to get only active formulas.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Activate this formula and deactivate all others.
     */
    public function activate(): void
    {
        // Deactivate all other formulas
        static::where('id', '!=', $this->id)->update(['is_active' => false]);

        // Activate this one
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate this formula.
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Build a sample number for preview.
     */
    public function buildSampleNumber(?string $companyKode = null): string
    {
        $parts = [];

        // Add prefix
        if (!empty($this->prefix)) {
            $parts[] = $this->prefix;
        }

        // Add company code if enabled
        if ($this->include_company_code && !empty($companyKode)) {
            $parts[] = strtoupper($companyKode);
        }

        // Add sequence placeholder
        $parts[] = str_repeat('0', $this->sequence_digits);

        return implode($this->separator, $parts);
    }

    /**
     * Build the actual number with given values.
     */
    public function buildNumber(string $prefixValue, int $sequence): string
    {
        $parts = [];

        if (!empty($this->prefix)) {
            $parts[] = $this->prefix;
        }

        if ($this->include_company_code && !empty($prefixValue)) {
            $parts[] = strtoupper($prefixValue);
        }

        $parts[] = str_pad((string) $sequence, $this->sequence_digits, '0', STR_PAD_LEFT);

        return implode($this->separator, $parts);
    }
}
