<?php

namespace App\Exports;

use App\Models\Member;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MembersExport implements FromCollection, WithHeadings, WithColumnFormatting, WithTitle
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Data Anggota';
    }

    public function headings(): array
    {
        return [
            'No',
            'NIK',
            'Nama',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat',
            'Provinsi',
            'Kab/Kota',
            'Kecamatan',
            'Jenis Kelamin',
            'Agama',
            'Perusahaan',
            'Status',
            'Berlaku Hingga',
            'Tanggal Pembuatan',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,  // No
            'B' => NumberFormat::FORMAT_TEXT,  // NIK - TEXT to prevent scientific notation
            'C' => NumberFormat::FORMAT_TEXT,  // Nama
            'D' => NumberFormat::FORMAT_TEXT,  // Tempat Lahir
            'E' => NumberFormat::FORMAT_TEXT,  // Tanggal Lahir
            'F' => NumberFormat::FORMAT_TEXT,  // Alamat
            'G' => NumberFormat::FORMAT_TEXT,  // Provinsi
            'H' => NumberFormat::FORMAT_TEXT,  // Kab/Kota
            'I' => NumberFormat::FORMAT_TEXT,  // Kecamatan
            'J' => NumberFormat::FORMAT_TEXT,  // Jenis Kelamin
            'K' => NumberFormat::FORMAT_TEXT,  // Agama
            'L' => NumberFormat::FORMAT_TEXT,  // Perusahaan
            'M' => NumberFormat::FORMAT_TEXT,  // Status
            'N' => NumberFormat::FORMAT_TEXT,  // Berlaku Hingga
            'O' => NumberFormat::FORMAT_TEXT,  // Tanggal Pembuatan
        ];
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $query = Member::with(['province', 'regency', 'district', 'company']);

        // Apply the same filters as MemberController@index
        $this->applyFilters($query);

        $members = $query->latest()->get();

        // Transform data for export
        $data = $members->map(function ($member, $index) {
            return [
                'no' => (string) ($index + 1),
                'nik' => (string) $member->nik,
                'nama' => $member->nama ?? '',
                'tempat_lahir' => $member->tempat_lahir ?? '',
                'tanggal_lahir' => $this->formatDate($member->tanggal_lahir),
                'alamat' => $member->alamat ?? '',
                'provinsi' => $member->province?->name ?? '-',
                'kabupaten' => $member->regency?->name ?? '-',
                'kecamatan' => $member->district?->name ?? '-',
                'jenis_kelamin' => $member->jenis_kelamin ?? '',
                'agama' => $member->agama ?? '',
                'perusahaan' => $member->company?->name ?? '-',
                'status' => $this->formatStatus($member->status),
                'berlaku_hingga' => $this->formatDate($member->berlaku_hingga),
                'tanggal_pembuatan' => $this->formatDate($member->tanggal_pembuatan),
            ];
        });

        return $data;
    }

    /**
     * Format date as dd/mm/yyyy string
     */
    protected function formatDate($date): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Apply the same filters used in MemberController@index
     */
    protected function applyFilters($query): void
    {
        // Search filter (NIK or nama)
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        // Province filter
        if (!empty($this->filters['province_id'])) {
            $query->where('province_id', $this->filters['province_id']);
        }

        // Regency/Kabupaten filter
        if (!empty($this->filters['regency_id'])) {
            $query->where('regency_id', $this->filters['regency_id']);
        }

        // District/Kecamatan filter
        if (!empty($this->filters['district_id'])) {
            $query->where('district_id', $this->filters['district_id']);
        }

        // Gender filter
        if (!empty($this->filters['jenis_kelamin'])) {
            $query->where('jenis_kelamin', $this->filters['jenis_kelamin']);
        }

        // Religion filter
        if (!empty($this->filters['agama'])) {
            $query->where('agama', $this->filters['agama']);
        }

        // Status filter
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        // Masa berlaku filter
        if (!empty($this->filters['masa_berlaku'])) {
            switch ($this->filters['masa_berlaku']) {
                case 'expired':
                    $query->where('berlaku_hingga', '<', now()->toDateString());
                    break;
                case 'expiring':
                    $query->whereBetween('berlaku_hingga', [now()->toDateString(), now()->addDays(30)->toDateString()]);
                    break;
                case 'active':
                    $query->where('berlaku_hingga', '>', now()->addDays(30)->toDateString());
                    break;
            }
        }
    }

    /**
     * Format status to readable Indonesian text
     */
    protected function formatStatus(string $status): string
    {
        $statusMap = [
            'draft' => 'Draft',
            'ready' => 'Ready',
            'generated' => 'Generated',
            'printed' => 'Printed',
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            'expired' => 'Kedaluwarsa',
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }
}
