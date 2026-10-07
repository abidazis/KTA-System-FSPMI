<?php

namespace Database\Seeders;

use App\Models\KtaSetting;
use Illuminate\Database\Seeder;

class KtaSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'kta_masa_berlaku_tahun',
                'label' => 'Masa Berlaku KTA',
                'value' => '5',
                'type' => 'number',
                'description' => 'Lama masa berlaku KTA dalam tahun',
                'is_active' => true,
            ],
            [
                'key' => 'kta_aktivasi_otomatis',
                'label' => 'Aktivasi Otomatis',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Aktifkan KTA secara otomatis setelah dibuat',
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            KtaSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
