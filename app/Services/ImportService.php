<?php

namespace App\Services;

use App\Models\Company;
use App\Models\District;
use App\Models\Member;
use App\Models\MemberPhoto;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportService
{
    /**
     * Validate all import data before any database write.
     * Returns array with validation results.
     */
    public function validateImport(string $excelFile, string $extractPath, array $allZipNames): array
    {
        $errors = [];
        $validData = [];
        $photosData = [];
        $companyData = [];
        $processedNikHashes = []; // Track NIK hashes in current batch

        // Parse Excel
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelFile);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = [];

            foreach ($sheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $rows[] = $rowData;
            }
        } catch (\Exception $e) {
            return $this->formatError('File Excel tidak valid: ' . $e->getMessage());
        }

        if (count($rows) < 2) {
            return $this->formatError('File Excel kosong atau tidak memiliki data.');
        }

        // Normalize headers
        $headers = array_map(fn($h) => strtolower(trim((string) ($h ?? ''))), $rows[0]);

        // Check required headers (including new company columns)
        $requiredHeaders = [
            'nik', 'nama', 'tempat_lahir', 'tanggal_lahir',
            'alamat', 'jenis_kelamin', 'agama',
            'berlaku_hingga', 'tanggal_pembuatan',
        ];
        // kode_perusahaan and foto_perusahaan are optional

        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $headers)) {
                return $this->formatError("Kolom '$required' tidak ditemukan dalam Excel.");
            }
        }

        // Get existing data for validation
        $existingNikMap = Member::pluck('nik')->map(fn($nik) => $nik)->flip()->toArray();
        $existingPhotoHashes = MemberPhoto::pluck('photo_hash', 'photo_hash')->filter()->flip()->toArray();

        // Track NIKs in current batch
        $batchNikMap = [];

        // First pass: Parse all data and validate without database writes
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowNum = $i + 1;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Map headers to values
            $rowData = $this->mapRowData($headers, $row);

            // Skip instruction rows
            if ($this->isInstructionRow($rowData)) {
                continue;
            }

            // Validate this row
            $rowErrors = $this->validateRowData(
                $rowData,
                $rowNum,
                $existingNikMap,
                $batchNikMap,
                $existingPhotoHashes,
                $processedNikHashes,
                $extractPath,
                $photosData,
                $companyData
            );

            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $rowNum,
                    'nik' => $rowData['nik'] ?? '-',
                    'nama' => $rowData['nama'] ?? '-',
                    'perusahaan' => $rowData['kode_perusahaan'] ?? $rowData['nama_perusahaan'] ?? '-',
                    'errors' => $rowErrors,
                ];
            } else {
                // Add to valid data
                $batchNikMap[$rowData['nik']] = true;
                $validData[] = $rowData;
            }
        }

        // Generate preview data (photo names, company assignments)
        $previewData = $this->generatePreviewData($validData, $companyData, $photosData);

        $total = count($validData) + count($errors);

        return [
            'success' => empty($errors),
            'total' => $total,
            'valid' => count($validData),
            'errors' => $errors,
            'data' => $validData,
            'preview' => $previewData,
            'company_summary' => $this->generateCompanySummary($companyData),
        ];
    }

    /**
     * Process the validated import with atomic transaction.
     */
    public function processImport(array $data, array $previewData, string $extractPath): array
    {
        $imported = 0;
        $createdMembers = [];

        try {
            DB::beginTransaction();

            foreach ($data as $index => $row) {
                $nik = $row['nik'];
                $preview = $previewData[$index] ?? null;

                // Find or create company
                $company = $this->resolveCompany($row, $preview['company_kode'] ?? null);

                // Find or create region
                $regionIds = $this->resolveRegions($row);

                // Determine final photo path and hash
                $fotoPath = null;
                $photoHash = null;

                // Get photo source path from preview
                if (!empty($preview['photo_source_path']) && file_exists($preview['photo_source_path'])) {
                    $sourcePath = $preview['photo_source_path'];
                    $ext = $preview['photo_extension'] ?? 'jpg';

                    // Calculate hash
                    $photoHash = hash_file('sha256', $sourcePath);

                    // Copy to final storage with auto-generated name
                    $destPath = 'members/photos/' . $preview['photo_name'];

                    // Read and store
                    $photoContent = file_get_contents($sourcePath);
                    Storage::disk('local')->put($destPath, $photoContent);

                    $fotoPath = $destPath;
                }

                // Create member
                $member = Member::create([
                    'nik' => $nik,
                    'nama' => $row['nama'],
                    'tempat_lahir' => $row['tempat_lahir'],
                    'tanggal_lahir' => $this->parseDate($row['tanggal_lahir']),
                    'alamat' => $row['alamat'],
                    'province_id' => $regionIds['province_id'],
                    'regency_id' => $regionIds['regency_id'],
                    'district_id' => $regionIds['district_id'],
                    'jenis_kelamin' => $row['jenis_kelamin'],
                    'agama' => $row['agama'],
                    'berlaku_hingga' => $this->parseDate($row['berlaku_hingga']),
                    'tanggal_pembuatan' => $this->parseDate($row['tanggal_pembuatan']),
                    'foto_path' => $fotoPath,
                    'status' => $fotoPath ? 'ready' : 'draft',
                    'company_id' => $company?->id,
                    'created_by' => auth()->id(),
                ]);

                $createdMembers[] = $member;

                // Create photo record with hash
                if ($fotoPath && $photoHash) {
                    MemberPhoto::create([
                        'member_id' => $member->id,
                        'photo_path' => $fotoPath,
                        'photo_hash' => $photoHash,
                    ]);
                }

                $imported++;
            }

            DB::commit();

            return [
                'success' => true,
                'total' => count($data),
                'imported' => $imported,
                'errors' => [],
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            // Cleanup any photos that were created
            foreach ($createdMembers as $member) {
                if ($member->foto_path && Storage::disk('local')->exists($member->foto_path)) {
                    Storage::disk('local')->delete($member->foto_path);
                }
                $member->forceDelete();
            }

            return [
                'success' => false,
                'total' => count($data),
                'imported' => 0,
                'errors' => [
                    ['row' => '-', 'nik' => '-', 'errors' => ['Terjadi kesalahan saat import: ' . $e->getMessage()]],
                ],
            ];
        }
    }

    /**
     * Map Excel row data to associative array.
     */
    private function mapRowData(array $headers, array $row): array
    {
        $data = [];
        foreach ($headers as $index => $header) {
            if (isset($row[$index])) {
                $val = $row[$index];

                if ($header === 'nik') {
                    $data[$header] = self::normalizeNik($val);
                } elseif (is_numeric($val) && in_array($header, ['tanggal_lahir', 'berlaku_hingga', 'tanggal_pembuatan'])) {
                    $data[$header] = $this->excelSerialToDate($val);
                } else {
                    $data[$header] = trim((string) $val);
                }
            }
        }

        // Map company columns (support both old and new format)
        $data['kode_perusahaan'] = $data['kode_perusahaan'] ?? null;
        $data['nama_perusahaan'] = $data['nama_perusahaan'] ?? $data['perusahaan'] ?? null;

        return $data;
    }

    /**
     * Validate a single row of data.
     */
    private function validateRowData(
        array $row,
        int $rowNum,
        array $existingNikMap,
        array $batchNikMap,
        array $existingPhotoHashes,
        array &$processedNikHashes,
        string $extractPath,
        array &$photosData,
        array &$companyData
    ): array {
        $errors = [];
        $nik = $row['nik'] ?? '';

        // NIK validation
        if (empty($nik)) {
            $errors[] = 'NIK kosong';
        } elseif (!self::isValidNik($nik)) {
            $errors[] = "NIK \"$nik\" tidak valid. NIK harus terdiri dari 15 atau 16 digit angka.";
        } else {
            // Check duplicate in database
            if (isset($existingNikMap[$nik])) {
                $errors[] = "NIK $nik sudah terdaftar di database";
            }

            // Check duplicate in batch
            if (isset($batchNikMap[$nik])) {
                $errors[] = "NIK $nik duplikat dalam file Excel yang sama";
            }
        }

        // Other field validations
        if (empty($row['nama'])) {
            $errors[] = 'Nama kosong';
        }
        if (empty($row['tempat_lahir'])) {
            $errors[] = 'Tempat lahir kosong';
        }
        if (empty($row['tanggal_lahir'])) {
            $errors[] = 'Tanggal lahir kosong';
        } elseif (!$this->isValidDate($row['tanggal_lahir'])) {
            $errors[] = 'Format tanggal lahir tidak valid';
        }
        if (empty($row['alamat'])) {
            $errors[] = 'Alamat kosong';
        }
        if (empty($row['jenis_kelamin'])) {
            $errors[] = 'Jenis kelamin kosong';
        } elseif (!in_array($row['jenis_kelamin'], ['Laki-laki', 'Perempuan'])) {
            $errors[] = "Jenis kelamin harus 'Laki-laki' atau 'Perempuan'";
        }
        if (empty($row['agama'])) {
            $errors[] = 'Agama kosong';
        }
        if (empty($row['berlaku_hingga'])) {
            $errors[] = 'Berlaku hingga kosong';
        } elseif (!$this->isValidDate($row['berlaku_hingga'])) {
            $errors[] = 'Format berlaku hingga tidak valid';
        }
        if (empty($row['tanggal_pembuatan'])) {
            $errors[] = 'Tanggal pembuatan kosong';
        } elseif (!$this->isValidDate($row['tanggal_pembuatan'])) {
            $errors[] = 'Format tanggal pembuatan tidak valid';
        }

        // Company validation
        $companyValidation = $this->validateCompany($row, $rowNum);
        if (!empty($companyValidation['errors'])) {
            $errors = array_merge($errors, $companyValidation['errors']);
        }
        if (isset($companyValidation['data'])) {
            $companyData[$rowNum] = $companyValidation['data'];
        }

        // Photo validation (REQUIRED)
        if (!empty($nik) && self::isValidNik($nik)) {
            $photoResult = $this->findAndValidatePhoto($extractPath, $nik, $rowNum, $existingPhotoHashes, $processedNikHashes);

            if (!empty($photoResult['errors'])) {
                $errors = array_merge($errors, $photoResult['errors']);
            }
            if (isset($photoResult['data'])) {
                $photosData[$nik] = $photoResult['data'];
                $processedNikHashes[$photoResult['data']['hash']] = $photoResult['data'];
                $row['_source_photo'] = $photoResult['data'];
            }
        }

        return $errors;
    }

    /**
     * Validate company data.
     */
    private function validateCompany(array $row, int $rowNum): array
    {
        $errors = [];
        $companyData = [];

        $kode = !empty($row['kode_perusahaan']) ? strtoupper(trim($row['kode_perusahaan'])) : null;
        $nama = !empty($row['nama_perusahaan']) ? trim($row['nama_perusahaan']) : null;

        if (empty($kode) && empty($nama)) {
            // No company specified - that's fine
            return ['errors' => [], 'data' => ['kode' => null, 'name' => null, 'is_new' => false]];
        }

        // Find existing company by kode
        $existingByKode = null;
        if ($kode) {
            $existingByKode = Company::where('kode', $kode)->first();
        }

        if ($existingByKode) {
            // Company exists - check if data matches
            if ($nama && $existingByKode->name !== $nama) {
                $errors[] = "Kode perusahaan \"$kode\" sudah terdaftar dengan nama \"{$existingByKode->name}\". "
                    . "Nama yang diimport \"$nama\" berbeda dengan data yang tersimpan. "
                    . "Silakan periksa kembali data perusahaan.";
            }
            $companyData = [
                'id' => $existingByKode->id,
                'kode' => $existingByKode->kode,
                'name' => $existingByKode->name,
                'is_new' => false,
            ];
        } else {
            // Company doesn't exist - can be created on import
            if ($kode && Company::where('name', $nama)->exists()) {
                // Name exists but kode is different - this is a conflict
                $existingByName = Company::where('name', $nama)->first();
                $errors[] = "Nama perusahaan \"$nama\" sudah terdaftar dengan kode \"{$existingByName->kode}\". "
                    . "Silakan gunakan kode yang sesuai atau hubungi administrator.";
            } else {
                $companyData = [
                    'kode' => $kode,
                    'name' => $nama,
                    'is_new' => true,
                ];
            }
        }

        return ['errors' => $errors, 'data' => $companyData];
    }

    /**
     * Find photo by NIK and validate it.
     */
    private function findAndValidatePhoto(
        string $extractPath,
        string $nik,
        int $rowNum,
        array $existingPhotoHashes,
        array $processedNikHashes
    ): array {
        $extensions = ['jpg', 'jpeg', 'png'];
        $foundFile = null;
        $foundHash = null;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extractPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $extension = strtolower($file->getExtension());
            if (!in_array($extension, $extensions, true)) {
                continue;
            }

            $baseName = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            // Check if filename matches NIK (with various normalization)
            $fnClean = ltrim($baseName, '0') ?: $baseName;
            $nkClean = ltrim($nik, '0') ?: $nik;

            if ($fnClean === $nkClean || $baseName === $nik) {
                // Calculate hash
                $hash = hash_file('sha256', $file->getPathname());

                // Check duplicate with existing database photos
                if (isset($existingPhotoHashes[$hash])) {
                    $existingPhoto = MemberPhoto::where('photo_hash', $hash)->with('member')->first();
                    return [
                        'errors' => [
                            "FOTO DUPLICATE: Foto yang diupload memiliki isi yang sama dengan foto anggota lain. "
                            . "NIK: {$existingPhoto->member->nik}, Nama: {$existingPhoto->member->nama}, File: " . basename($existingPhoto->photo_path)
                        ],
                    ];
                }

                // Check duplicate within current batch
                if (isset($processedNikHashes[$hash])) {
                    return [
                        'errors' => [
                            "FOTO DUPLICATE DALAM BATCH: Foto pada baris $rowNum memiliki isi yang sama dengan foto pada "
                            . "baris lain dalam file yang sama. Gunakan foto yang berbeda."
                        ],
                    ];
                }

                // Valid photo found
                $foundFile = [
                    'nik' => $baseName,
                    'path' => $file->getPathname(),
                    'hash' => $hash,
                    'extension' => $extension,
                ];
                $foundHash = $hash;
                break;
            }
        }

        if (!$foundFile) {
            return [
                'errors' => [
                    "FOTO TIDAK DITEMUKAN (cari: {$nik}.jpg / {$nik}.jpeg / {$nik}.png) — "
                    . 'Pastikan file foto tersedia di folder foto/ dan nama file sesuai dengan NIK.'
                ],
            ];
        }

        // Validate image is actually a valid image
        $imageInfo = @getimagesize($foundFile['path']);
        if (!$imageInfo) {
            return [
                'errors' => [
                    "FOTO TIDAK VALID: File {$nik}.{$foundFile['extension']} bukan file gambar yang valid."
                ],
            ];
        }

        return ['data' => $foundFile];
    }

    /**
     * Generate preview data including photo names.
     */
    private function generatePreviewData(array $validData, array $companyData, array $photosData): array
    {
        $preview = [];
        $companySequenceCounters = [];

        foreach ($validData as $index => $row) {
            $nik = $row['nik'];
            $rowNum = $index + 2; // Excel row number
            $companyInfo = $companyData[$rowNum] ?? null;
            $photoData = $photosData[$nik] ?? null;

            // Determine company code
            $companyKode = 'UNK';
            if ($companyInfo) {
                if (!empty($companyInfo['kode'])) {
                    $companyKode = $companyInfo['kode'];
                } elseif (!empty($companyInfo['name'])) {
                    // Generate kode from company name (first 3 letters uppercase)
                    $companyKode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $companyInfo['name']), 0, 3));
                }
            }

            // Get next sequence for this company
            if (!isset($companySequenceCounters[$companyKode])) {
                $companySequenceCounters[$companyKode] = MemberPhoto::getNextSequenceForCompany($companyKode);
            }

            $sequence = $companySequenceCounters[$companyKode]++;
            $photoName = sprintf('%s-%04d.%s', $companyKode, $sequence, $photoData['extension'] ?? 'jpg');

            $preview[] = [
                'nik' => $nik,
                'nama' => $row['nama'],
                'company_kode' => $companyKode,
                'company_name' => $companyInfo['name'] ?? null,
                'photo_name' => $photoName,
                'photo_hash' => $photoData['hash'] ?? null,
                'photo_source_path' => $photoData['path'] ?? null,
                'photo_extension' => $photoData['extension'] ?? 'jpg',
                'is_new_company' => $companyInfo['is_new'] ?? false,
            ];
        }

        return $preview;
    }

    /**
     * Generate company summary for preview.
     */
    private function generateCompanySummary(array $companyData): array
    {
        $summary = [];
        $seen = [];

        foreach ($companyData as $rowNum => $data) {
            if (empty($data['kode']) && empty($data['name'])) {
                continue;
            }

            $key = ($data['kode'] ?? '') . '|' . ($data['name'] ?? '');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $summary[] = [
                'kode' => $data['kode'] ?? '-',
                'nama' => $data['name'] ?? '-',
                'is_new' => $data['is_new'] ?? false,
                'action' => ($data['is_new'] ?? false) ? 'Akan dibuat baru' : 'Menggunakan data existing',
            ];
        }

        return $summary;
    }

    /**
     * Resolve company - find existing or create new.
     */
    private function resolveCompany(array $row, ?string $kode = null): ?Company
    {
        $kode = $kode ?? (!empty($row['kode_perusahaan']) ? strtoupper(trim($row['kode_perusahaan'])) : null);
        $nama = !empty($row['nama_perusahaan']) ? trim($row['nama_perusahaan']) : null;

        if (empty($kode) && empty($nama)) {
            return null;
        }

        // Find by kode
        if ($kode) {
            $company = Company::where('kode', $kode)->first();
            if ($company) {
                return $company;
            }
        }

        // Create new company
        if ($kode && $nama) {
            return Company::create([
                'kode' => $kode,
                'name' => $nama,
                'is_active' => true,
            ]);
        }

        return null;
    }

    /**
     * Resolve region IDs.
     */
    private function resolveRegions(array $row): array
    {
        $provinceId = null;
        $regencyId = null;
        $districtId = null;

        if (!empty($row['provinsi'])) {
            $province = Province::firstOrCreate(
                ['name' => $row['provinsi']],
                ['code' => str_pad((Province::max('id') ?? 0) + 1, 2, '0', STR_PAD_LEFT)]
            );
            $provinceId = $province->id;
        }

        if (!empty($row['kabupaten_kota']) && $provinceId) {
            $regency = Regency::firstOrCreate(
                ['name' => $row['kabupaten_kota'], 'province_id' => $provinceId],
                ['code' => str_pad((Regency::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT)]
            );
            $regencyId = $regency->id;
        }

        if (!empty($row['kecamatan']) && $regencyId) {
            $district = District::firstOrCreate(
                ['name' => $row['kecamatan'], 'regency_id' => $regencyId],
                ['code' => str_pad((District::max('id') ?? 0) + 1, 7, '0', STR_PAD_LEFT)]
            );
            $districtId = $district->id;
        }

        return [
            'province_id' => $provinceId,
            'regency_id' => $regencyId,
            'district_id' => $districtId,
        ];
    }

    /**
     * Format error response.
     */
    private function formatError(string $message): array
    {
        return [
            'success' => false,
            'total' => 0,
            'valid' => 0,
            'errors' => [['row' => '-', 'nik' => '-', 'errors' => [$message]]],
            'data' => [],
            'preview' => [],
            'company_summary' => [],
        ];
    }

    /**
     * Check if a row is an instruction row.
     */
    private function isInstructionRow(array $rowData): bool
    {
        $nik = $rowData['nik'] ?? '';
        if (!empty($nik) && is_string($nik)) {
            $nikLower = strtolower(trim($nik));
            if (str_starts_with($nikLower, 'catatan:')) {
                $requiredFields = ['nama', 'tempat_lahir', 'alamat', 'jenis_kelamin', 'agama'];
                foreach ($requiredFields as $field) {
                    $value = $rowData[$field] ?? '';
                    if (!empty(trim((string) $value))) {
                        return false;
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Normalize NIK value.
     */
    public static function normalizeNik(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_float($value) || is_int($value)) {
            return number_format($value, 0, '', '');
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return '';
            }
            if (preg_match('/^\d+\.0*$/', $value)) {
                return preg_replace('/\.0+$/', '', $value);
            }
            return $value;
        }

        return trim((string) $value);
    }

    /**
     * Validate NIK format.
     */
    public static function isValidNik(string $nik): bool
    {
        if (empty($nik)) {
            return false;
        }
        return preg_match('/^\d{15,16}$/', $nik) === 1;
    }

    /**
     * Validate date format.
     */
    private function isValidDate(string $date): bool
    {
        if (empty($date)) {
            return false;
        }
        if (is_numeric($date)) {
            return true;
        }
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $fmt) {
            $p = \DateTime::createFromFormat($fmt, $date);
            if ($p && $p->format($fmt) === $date) {
                return true;
            }
        }
        return strtotime($date) !== false;
    }

    /**
     * Parse date string.
     */
    private function parseDate(string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        if (is_numeric($date)) {
            return $this->excelSerialToDate($date);
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd F Y'] as $fmt) {
            $p = \DateTime::createFromFormat($fmt, $date);
            if ($p && $p->format($fmt) === $date) {
                return $p->format('Y-m-d');
            }
        }

        $ts = strtotime($date);
        if ($ts) {
            return date('Y-m-d', $ts);
        }

        return null;
    }

    /**
     * Convert Excel serial date to Y-m-d.
     */
    private function excelSerialToDate($serial): string
    {
        $unix = ((float) $serial - 25569) * 86400;
        return gmdate('Y-m-d', (int) $unix);
    }
}
