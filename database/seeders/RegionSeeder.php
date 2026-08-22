<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            [
                'name' => 'Jawa Barat',
                'regencies' => [
                    [
                        'name' => 'Karawang',
                        'districts' => ['Karangtaruna', 'Telagasari', 'Klari', ' Cikampek', 'Purwasari', 'Tegalwaru', 'Cilebar', 'Rawamerta', 'Tempuran', 'Mekarbuana'],
                    ],
                    [
                        'name' => 'Bekasi',
                        'districts' => ['Harapan Baru', 'Bekasi Barat', 'Bekasi Timur', 'Bekasi Utara', 'Medansatria', 'Pondokgede', 'Jatiasih', 'Pondokmelati'],
                    ],
                    [
                        'name' => 'Depok',
                        'districts' => ['Beji', 'Cimanggis', 'Cinere', 'Cipayung', 'Limo', 'Pancoran Mas', 'Sawangan', 'Sukmajaya', 'Tapos'],
                    ],
                ],
            ],
            [
                'name' => 'DKI Jakarta',
                'regencies' => [
                    [
                        'name' => 'Jakarta Pusat',
                        'districts' => ['Cempaka Putih', 'Gambir', 'Johar Baru', 'Kemayoran', 'Menteng', 'Sawah Besar'],
                    ],
                    [
                        'name' => 'Jakarta Utara',
                        'districts' => ['Cilincing', 'Kelapa Gading', 'Koja', 'Pademangan', 'Penjaringan', 'Tanjung Priok'],
                    ],
                ],
            ],
            [
                'name' => 'Banten',
                'regencies' => [
                    [
                        'name' => 'Tangerang',
                        'districts' => ['Cipondoh', 'Benda', 'Pinang', 'Tangerang', 'Karawaci', 'Neglasari', 'Periuk', 'Batuceper'],
                    ],
                    [
                        'name' => 'Tangerang Selatan',
                        'districts' => ['Serpong', 'Serpong Utara', 'Pondok Aren', 'Ciputat', 'Ciputat Timur', 'Setu'],
                    ],
                ],
            ],
        ];

        foreach ($provinces as $pIndex => $provinceData) {
            $province = Province::firstOrCreate(
                ['name' => $provinceData['name']],
                ['code' => str_pad($pIndex + 1, 2, '0', STR_PAD_LEFT)]
            );

            foreach ($provinceData['regencies'] as $rIndex => $regencyData) {
                $regency = Regency::firstOrCreate(
                    ['name' => $regencyData['name'], 'province_id' => $province->id],
                    ['code' => str_pad(($pIndex * 10) + $rIndex + 1, 4, '0', STR_PAD_LEFT)]
                );

                foreach ($regencyData['districts'] as $dIndex => $districtName) {
                    District::firstOrCreate(
                        ['name' => trim($districtName), 'regency_id' => $regency->id],
                        ['code' => str_pad(($pIndex * 100) + ($rIndex * 10) + $dIndex + 1, 7, '0', STR_PAD_LEFT)]
                    );
                }
            }
        }
    }
}
