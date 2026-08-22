<?php

namespace App\Imports;

use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RegionImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['provinsi']) && empty($row['kabupaten_kota']) && empty($row['kecamatan'])) {
            return null;
        }

        $province = Province::firstOrCreate(
            ['name' => $row['provinsi'] ?? 'Unknown'],
            ['code' => str_pad(Province::max('id') + 1, 2, '0', STR_PAD_LEFT)]
        );

        if (!empty($row['kabupaten_kota'])) {
            $regency = Regency::firstOrCreate(
                ['name' => $row['kabupaten_kota'], 'province_id' => $province->id],
                ['code' => str_pad(Regency::max('id') + 1, 4, '0', STR_PAD_LEFT)]
            );

            if (!empty($row['kecamatan'])) {
                District::firstOrCreate(
                    ['name' => $row['kecamatan'], 'regency_id' => $regency->id],
                    ['code' => str_pad(District::max('id') + 1, 7, '0', STR_PAD_LEFT)]
                );
            }
        }

        return null;
    }
}
