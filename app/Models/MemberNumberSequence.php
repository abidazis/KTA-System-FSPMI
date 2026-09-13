<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberNumberSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'formula_id',
        'company_id',
        'last_sequence',
        'prefix_value',
    ];

    protected function casts(): array
    {
        return [
            'last_sequence' => 'integer',
        ];
    }

    /**
     * Get the formula for this sequence.
     */
    public function formula(): BelongsTo
    {
        return $this->belongsTo(MemberNumberFormula::class);
    }

    /**
     * Get the company for this sequence.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the next sequence number without incrementing.
     */
    public function getNextNumber(): int
    {
        return $this->last_sequence + 1;
    }

    /**
     * Increment and save the sequence.
     */
    public function incrementSequence(): int
    {
        $this->last_sequence++;
        $this->save();
        return $this->last_sequence;
    }
}
