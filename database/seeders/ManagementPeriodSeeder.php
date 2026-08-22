<?php

namespace Database\Seeders;

use App\Models\ManagementOfficial;
use App\Models\ManagementPeriod;
use Illuminate\Database\Seeder;

class ManagementPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $period = ManagementPeriod::firstOrCreate(
            ['nama_periode' => '2026 - 2031'],
            [
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2031-12-31',
                'status' => 'active',
            ]
        );

        ManagementOfficial::firstOrCreate(
            [
                'management_period_id' => $period->id,
                'jabatan' => 'Ketua Umum',
                'nama' => 'Budi Santoso',
            ],
            ['status' => 'active']
        );

        ManagementOfficial::firstOrCreate(
            [
                'management_period_id' => $period->id,
                'jabatan' => 'Sekretaris Umum',
                'nama' => 'Siti Rahayu',
            ],
            ['status' => 'active']
        );

        ManagementOfficial::firstOrCreate(
            [
                'management_period_id' => $period->id,
                'jabatan' => 'Bendahara Umum',
                'nama' => 'Ahmad Wijaya',
            ],
            ['status' => 'active']
        );
    }
}
