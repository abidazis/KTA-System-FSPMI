<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MemberTemplateExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'nama',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat',
            'provinsi',
            'kabupaten_kota',
            'kecamatan',
            'jenis_kelamin',
            'agama',
            'berlaku_hingga',
            'tanggal_pembuatan',
            'foto',
            'kode_perusahaan',
            'nama_perusahaan',
        ];
    }

    public function collection(): Collection
    {
        return collect([
            [
                'nama' => 'Nama Lengkap',
                'tempat_lahir' => 'Kota',
                'tanggal_lahir' => '1990-01-01',
                'alamat' => 'Alamat Lengkap',
                'provinsi' => 'Jawa Barat',
                'kabupaten_kota' => 'Karawang',
                'kecamatan' => 'Kecamatan',
                'jenis_kelamin' => 'Laki-laki',
                'agama' => 'Islam',
                'berlaku_hingga' => '2031-12-31',
                'tanggal_pembuatan' => '2026-01-01',
                'foto' => 'ABC-0001.jpg',
                'kode_perusahaan' => 'ABC',
                'nama_perusahaan' => 'PT ABC Indonesia',
            ],
        ]);
    }
}
