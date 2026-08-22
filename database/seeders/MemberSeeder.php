<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Member;
use App\Models\Province;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = Province::with('regencies.districts')->get();

        if ($provinces->isEmpty()) {
            return;
        }

        $sampleMembers = [
            ['nik' => '3275010101900001', 'nama' => 'Budi Santoso', 'jk' => 'Laki-laki'],
            ['nik' => '3275010102900002', 'nama' => 'Siti Rahayu', 'jk' => 'Perempuan'],
            ['nik' => '3275010103900003', 'nama' => 'Ahmad Wijaya', 'jk' => 'Laki-laki'],
            ['nik' => '3275010104900004', 'nama' => 'Dewi Lestari', 'jk' => 'Perempuan'],
            ['nik' => '3275010105900005', 'nama' => 'Eko Prasetyo', 'jk' => 'Laki-laki'],
            ['nik' => '3275010106900006', 'nama' => 'Fitri Handayani', 'jk' => 'Perempuan'],
            ['nik' => '3275010107900007', 'nama' => 'Gunawan Hidayat', 'jk' => 'Laki-laki'],
            ['nik' => '3275010108900008', 'nama' => 'Hesti Wulandari', 'jk' => 'Perempuan'],
            ['nik' => '3275010109900009', 'nama' => 'Irfan Maulana', 'jk' => 'Laki-laki'],
            ['nik' => '3275010110900010', 'nama' => 'Jumiati', 'jk' => 'Perempuan'],
        ];

        $cities = ['Karawang', 'Bekasi', 'Depok', 'Jakarta Pusat', 'Tangerang'];
        $religions = ['Islam', 'Kristen', 'Katolik'];
        $statuses = ['draft', 'ready', 'generated', 'active'];

        foreach ($sampleMembers as $index => $data) {
            $province = $provinces->random();
            $regency = $province->regencies->random();
            $district = $regency->districts->random();

            Member::firstOrCreate(
                ['nik' => $data['nik']],
                [
                    'nama' => $data['nama'],
                    'tempat_lahir' => $cities[array_rand($cities)],
                    'tanggal_lahir' => date('Y-m-d', strtotime('-' . rand(25, 50) . ' years')),
                    'alamat' => 'Jl. Industri No. ' . ($index + 1) . ', RT ' . rand(1, 10) . '/RW ' . rand(1, 5),
                    'province_id' => $province->id,
                    'regency_id' => $regency->id,
                    'district_id' => $district->id,
                    'jenis_kelamin' => $data['jk'],
                    'agama' => $religions[array_rand($religions)],
                    'berlaku_hingga' => date('Y-m-d', strtotime('+' . rand(1, 5) . ' years')),
                    'tanggal_pembuatan' => date('Y-m-d', strtotime('-' . rand(1, 365) . ' days')),
                    'status' => $statuses[array_rand($statuses)],
                    'foto_path' => null,
                ]
            );
        }
    }
}
