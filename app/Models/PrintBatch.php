<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PrintBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'management_period_id',
        'printed_by',
        'tanggal_cetak',
        'jumlah',
        'type',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_cetak' => 'date',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(ManagementPeriod::class, 'management_period_id');
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'print_batch_members')
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('print_batch_members.position');
    }

    public static function generateBatchNumber(): string
    {
        $last = static::orderBy('id', 'desc')->first();
        $number = $last ? ((int) substr($last->batch_number, 4) + 1) : 1;
        return 'BTH-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
