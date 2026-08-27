<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KtaFieldPosition extends Model
{
    protected $fillable = [
        'kta_background_id',
        'field_key',
        'left_mm',
        'top_mm',
        'font_size',
        'font_weight',
        'color',
        'width_mm',
    ];

    public function background()
    {
        return $this->belongsTo(KtaBackground::class);
    }

    public static function getDefaults()
    {
        return [
            'nik' => ['left_mm' => 22, 'top_mm' => 17, 'font_size' => 2.15, 'font_weight' => '500', 'color' => '#000'],
            'nama' => ['left_mm' => 22, 'top_mm' => 23, 'font_size' => 2.15, 'font_weight' => '500', 'color' => '#000'],
            'ttl' => ['left_mm' => 22, 'top_mm' => 29, 'font_size' => 2.15, 'font_weight' => '500', 'color' => '#000'],
            'alamat' => ['left_mm' => 22, 'top_mm' => 35, 'font_size' => 2.15, 'font_weight' => '500', 'color' => '#000', 'width_mm' => 25],
            'jk' => ['left_mm' => 22, 'top_mm' => 43, 'font_size' => 2.15, 'font_weight' => '500', 'color' => '#000'],
            'agama' => ['left_mm' => 40, 'top_mm' => 43, 'font_size' => 2.15, 'font_weight' => '500', 'color' => '#000'],
            'berlaku' => ['left_mm' => 22, 'top_mm' => 49, 'font_size' => 2.15, 'font_weight' => '500', 'color' => '#000'],
            'tanggal_ttd' => ['left_mm' => 3, 'top_mm' => 63, 'font_size' => 1.95, 'font_weight' => '400', 'color' => '#000'],
            'nama_ketua' => ['left_mm' => 46, 'top_mm' => 71, 'font_size' => 1.55, 'font_weight' => '600', 'color' => '#000'],
            'nama_sekretaris' => ['left_mm' => 3, 'top_mm' => 71, 'font_size' => 1.55, 'font_weight' => '600', 'color' => '#000'],
            'ttd_ketua' => ['left_mm' => 46, 'top_mm' => 55, 'font_size' => 0, 'font_weight' => '400', 'color' => '#000'],
            'ttd_sekretaris' => ['left_mm' => 3, 'top_mm' => 55, 'font_size' => 0, 'font_weight' => '400', 'color' => '#000'],
            'ttd_seal' => ['left_mm' => 26, 'top_mm' => 52, 'font_size' => 0, 'font_weight' => '400', 'color' => '#000'],
            'foto' => ['left_mm' => 4, 'top_mm' => 12, 'font_size' => 0, 'font_weight' => '400', 'color' => '#000'],
        ];
    }
}
