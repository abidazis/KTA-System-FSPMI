<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'photo_path',
        'photo_hash',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the member that owns this photo.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Find an existing photo by its hash.
     */
    public static function findByHash(string $hash): ?self
    {
        return static::where('photo_hash', $hash)->first();
    }

    /**
     * Check if a hash already exists in the database.
     */
    public static function hashExists(string $hash): bool
    {
        return static::where('photo_hash', $hash)->exists();
    }

    /**
     * Get the next sequence number for a company code.
     * Scans existing photo filenames to find the highest sequence.
     */
    public static function getNextSequenceForCompany(string $companyKode): int
    {
        $prefix = strtoupper($companyKode) . '-';

        // Get all existing photos for this company
        $photos = static::where('photo_path', 'like', "members/photos/{$prefix}%")->get();

        $maxSequence = 0;
        foreach ($photos as $photo) {
            $filename = basename($photo->photo_path);
            // Extract sequence number: ABC-0001.jpg -> 0001
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)\./', $filename, $matches)) {
                $seq = (int) $matches[1];
                if ($seq > $maxSequence) {
                    $maxSequence = $seq;
                }
            }
        }

        return $maxSequence + 1;
    }
}
