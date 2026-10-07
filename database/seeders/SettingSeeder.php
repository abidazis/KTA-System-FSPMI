<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // KTA Settings
            [
                'key' => 'kta_validity_years',
                'value' => '5',
                'type' => 'number',
                'group' => 'kta',
                'label' => 'Masa Berlaku KTA (Tahun)',
                'description' => 'Lama masa berlaku kartu anggota dalam tahun. Default: 5 tahun.',
            ],
            [
                'key' => 'kta_card_size',
                'value' => 'credit-card',
                'type' => 'string',
                'group' => 'kta',
                'label' => 'Ukuran KTA',
                'description' => 'Ukuran standar KTA: credit-card (85.6mm x 53.98mm) atau custom.',
            ],
            [
                'key' => 'kta_layout',
                'value' => '5',
                'type' => 'number',
                'group' => 'kta',
                'label' => 'Jumlah KTA Per Halaman',
                'description' => 'Berapa KTA yang dicetak per halaman A4. Default: 5.',
            ],
            [
                'key' => 'max_photo_size_kb',
                'value' => '2048',
                'type' => 'number',
                'group' => 'kta',
                'label' => 'Ukuran Maksimal Foto (KB)',
                'description' => 'Ukuran maksimal file foto upload dalam kilobyte (KB). Default: 2048 KB (2 MB).',
            ],

            // Member Settings
            [
                'key' => 'member_number_format',
                'value' => '1.XX.XX.XXX.XXXX',
                'type' => 'string',
                'group' => 'member',
                'label' => 'Format Nomor Anggota',
                'description' => 'Format nomor anggota: KodeProv.KodeKab.Tahun.Kec.NoUrut',
            ],
            [
                'key' => 'require_photo',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'member',
                'label' => 'Wajib Upload Foto',
                'description' => 'Apakah foto anggota wajib diupload saat pendaftaran.',
            ],
            [
                'key' => 'min_age',
                'value' => '17',
                'type' => 'number',
                'group' => 'member',
                'label' => 'Batas Umur Minimum',
                'description' => 'Umur minimum untuk menjadi anggota (tahun).',
            ],

            // General Settings
            [
                'key' => 'organization_name',
                'value' => 'FSPMI',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Nama Organisasi',
                'description' => 'Nama lengkap organisasi/serikat pekerja.',
            ],
            [
                'key' => 'organization_full_name',
                'value' => 'Federasi Serikat Pekerja Metal Indonesia',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Nama Lengkap Organisasi',
                'description' => 'Nama lengkap organisasi.',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
