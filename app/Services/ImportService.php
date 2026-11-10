<?php

namespace App\Services;

use App\Models\Company;
use App\Models\District;
use App\Models\Member;
use App\Models\MemberNumber;
use App\Models\MemberNumberFormula;
use App\Models\MemberNumberSequence;
use App\Models\MemberPhoto;
use App\Models\Province;
use App\Models\Regency;
use App\Services\MemberNumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImportService
{
    /**
     * Columns the import cannot run without.
     */
    private const REQUIRED_HEADERS = [
        'nama', 'tempat_lahir', 'tanggal_lahir',
        'alamat', 'jenis_kelamin', 'agama',
        'berlaku_hingga', 'tanggal_pembuatan',
    ];

    /**
     * Accepted spellings for each column. Raw header cells are normalized
     * (lowercase, separators -> space) before being matched here.
     */
    private const HEADER_ALIASES = [
        'nama' => ['nama', 'nama lengkap', 'nama anggota', 'nama karyawan', 'nama pegawai', 'name', 'full name', 'fullname'],
        'tempat_lahir' => ['tempat lahir', 'tempatlahir', 'birth place', 'birthplace'],
        'tanggal_lahir' => ['tanggal lahir', 'tgl lahir', 'tgllahir', 'birth date', 'birthdate', 'dob'],
        'alamat' => ['alamat', 'alamat lengkap', 'address'],
        'provinsi' => ['provinsi', 'propinsi', 'province'],
        'kabupaten_kota' => ['kabupaten kota', 'kabupaten', 'kota', 'kab kota', 'kabupatenkota', 'regency', 'city'],
        'kecamatan' => ['kecamatan', 'district'],
        'jenis_kelamin' => ['jenis kelamin', 'kelamin', 'gender', 'jk', 'l p'],
        'agama' => ['agama', 'religion'],
        'berlaku_hingga' => ['berlaku hingga', 'berlaku sampai', 'berlaku', 'valid until', 'masa berlaku', 'masa berlaku hingga'],
        'tanggal_pembuatan' => ['tanggal pembuatan', 'tgl pembuatan', 'tanggal buat', 'tgl buat', 'tanggal cetak', 'tgl cetak', 'issue date', 'tanggal terbit'],
        'foto' => ['foto', 'photo', 'file foto', 'nama foto', 'nama file foto', 'photo file'],
        'kode_perusahaan' => ['kode perusahaan', 'kode pt', 'kode company', 'kode kantor', 'company code', 'kode'],
        'nama_perusahaan' => ['nama perusahaan', 'nama pt', 'perusahaan', 'nama kantor', 'company', 'company name'],
        'nomor_anggota' => ['nomor anggota', 'no anggota', 'nomor kartu', 'member number'],
    ];

    /**
     * Validate all import data before any database write.
     * Returns array with validation results.
     *
     * @param string|array $excelFiles One Excel path, or a list of candidate paths from the ZIP.
     */
    public function validateImport(string|array $excelFiles, string $extractPath, array $allZipNames = []): array
    {
        $errors = [];
        $validData = [];
        $photosData = [];
        $companyData = [];
        $processedNikHashes = []; // Track photo hashes in current batch
        $seenNomorAnggota = []; // Track manual member numbers in current batch

        // Parse Excel. Several files may be present in the ZIP (Office lock files,
        // a stray copy, etc.), so every candidate is tried and the best match wins.
        $parsed = $this->readSpreadsheet((array) $excelFiles);

        if (!$parsed['ok']) {
            return $this->formatError($parsed['message']);
        }

        $headers = $parsed['headers'];
        $rows = $parsed['rows'];

        // nomor_anggota is optional - add validation if present
        $hasNomorAnggota = in_array('nomor_anggota', $headers, true);

        $missingHeaders = [];
        foreach (self::REQUIRED_HEADERS as $required) {
            if (!in_array($required, $headers, true)) {
                $missingHeaders[] = $required;
            }
        }

        if (!empty($missingHeaders)) {
            return $this->formatError($this->missingHeadersMessage($missingHeaders, $parsed));
        }

        if (empty($rows)) {
            return $this->formatError('File Excel kosong atau tidak memiliki data.');
        }

        // Formula is only needed when at least one row has no manual nomor anggota.
        $formula = MemberNumberFormula::getActive();
        if (!$formula) {
            foreach ($rows as $row) {
                $mapped = $this->mapRowData($headers, $row);
                if ($this->isInstructionRow($mapped) || $this->isBlankDataRow($mapped)) {
                    continue;
                }
                if (trim((string) ($mapped['nomor_anggota'] ?? '')) === '') {
                    return $this->formatError(
                        'Formula nomor anggota belum dikonfigurasi. Buka menu Pengaturan → Nomor Anggota untuk membuat formula aktif, '
                        . 'atau isi kolom nomor_anggota di Excel agar nomor dipakai apa adanya.'
                    );
                }
            }
        }

        // Get existing photo hashes for duplicate detection
        $existingPhotoHashes = MemberPhoto::pluck('photo_hash', 'photo_hash')->filter()->flip()->toArray();

        // First pass: Parse all data and validate without database writes
        // Keys are the real Excel row numbers, so the reported row always matches the file.
        foreach ($rows as $rowNum => $row) {
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

            // Skip rows that hold no member data (leftover notes, stray formatting)
            if ($this->isBlankDataRow($rowData)) {
                continue;
            }

            // Validate this row
            $rowErrors = $this->validateRowData(
                $rowData,
                $rowNum,
                $existingPhotoHashes,
                $processedNikHashes,
                $extractPath,
                $photosData,
                $companyData,
                $hasNomorAnggota,
                $seenNomorAnggota
            );

            // Set company_id from companyData (validateRowData doesn't modify $rowData)
            $companyInfo = $companyData[$rowNum] ?? null;
            if ($companyInfo && isset($companyInfo['id'])) {
                $rowData['company_id'] = $companyInfo['id'];
            }

            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $rowNum,
                    'nik' => '-',
                    'nama' => ($rowData['nama'] ?? '') ?: '-',
                    'perusahaan' => ($rowData['kode_perusahaan'] ?? '') ?: (($rowData['nama_perusahaan'] ?? '') ?: '-'),
                    'errors' => $rowErrors,
                ];
            } else {
                // Keep the original Excel row number - skipped rows shift the index
                $rowData['_row_num'] = $rowNum;
                $validData[] = $rowData;
            }
        }

        // Pass hasNomorAnggota to preview generation
        $previewData = $this->generatePreviewData($validData, $companyData, $photosData, $hasNomorAnggota);

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
     * Read the first spreadsheet among $files that contains a usable header row.
     *
     * A ZIP often carries more than one spreadsheet-looking entry (Excel lock
     * files "~$data.xlsx", a stray copy, a file in a sub-folder). Instead of
     * blindly taking the first one, every candidate is loaded and the sheet with
     * the best header match is used.
     *
     * @param array<int, string|array{path?: string}> $files
     */
    private function readSpreadsheet(array $files): array
    {
        $loadErrors = [];
        $best = null;
        $loaded = false;

        foreach ($files as $file) {
            $path = is_array($file) ? (string) ($file['path'] ?? '') : (string) $file;

            if ($path === '' || !is_file($path)) {
                continue;
            }

            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            } catch (\Throwable $e) {
                $loadErrors[] = basename($path) . ' — ' . $e->getMessage();
                continue;
            }

            $loaded = true;

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetRows = $this->worksheetToArray($sheet);
                if (empty($sheetRows)) {
                    continue;
                }

                $candidate = $this->detectHeaderRow($sheetRows);
                if ($candidate === null) {
                    continue;
                }

                $candidate['file'] = basename($path);
                $candidate['sheet'] = $sheet->getTitle();

                if ($best === null || $candidate['score'] > $best['score']) {
                    $best = $candidate;
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            if ($best !== null && $best['score'] >= count(self::REQUIRED_HEADERS)) {
                break;
            }
        }

        if ($best === null) {
            if (!$loaded) {
                $detail = empty($loadErrors) ? '' : ' (' . implode(' | ', array_slice($loadErrors, 0, 3)) . ')';

                return ['ok' => false, 'message' => 'File Excel tidak dapat dibaca' . $detail];
            }

            return ['ok' => false, 'message' => 'File Excel kosong atau tidak memiliki data.'];
        }

        return ['ok' => true] + $best;
    }

    /**
     * Convert a worksheet to a map of [excel row number => cell values].
     */
    private function worksheetToArray(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        // getHighestDataRow()/Column() only count real data, so a template with
        // formatted-but-empty rows does not make us read a million blank rows.
        $highestRow = method_exists($sheet, 'getHighestDataRow')
            ? $sheet->getHighestDataRow()
            : $sheet->getHighestRow();

        if ($highestRow < 1) {
            return [];
        }

        $highestColumn = method_exists($sheet, 'getHighestDataColumn')
            ? $sheet->getHighestDataColumn()
            : $sheet->getHighestColumn();

        $range = 'A1:' . $highestColumn . $highestRow;
        $values = $sheet->rangeToArray($range, null, true, false, false);

        $rows = [];
        foreach ($values as $index => $rowValues) {
            $rows[$index + 1] = $rowValues;
        }

        return $rows;
    }

    /**
     * Find the header row inside a worksheet (typically row 1, but users often
     * leave a blank/title row above it). Returns the best scoring candidate.
     */
    private function detectHeaderRow(array $sheetRows): ?array
    {
        $best = null;
        $fallback = null;

        $rowNumbers = array_slice(array_keys($sheetRows), 0, 20);

        foreach ($rowNumbers as $rowNum) {
            $rawHeaders = $sheetRows[$rowNum];
            $headers = array_map(fn($value) => $this->canonicalHeader($value), $rawHeaders);

            if (empty(array_filter($headers))) {
                continue;
            }

            $score = 0;
            foreach (self::REQUIRED_HEADERS as $required) {
                if (in_array($required, $headers, true)) {
                    $score++;
                }
            }

            $candidate = [
                'score' => $score,
                'headers' => $headers,
                'raw_headers' => $rawHeaders,
                'header_row' => $rowNum,
            ];

            if (!in_array('nama', $headers, true)) {
                if ($fallback === null || $score > $fallback['score']) {
                    $fallback = $candidate;
                }
                continue;
            }

            if ($best === null || $score > $best['score']) {
                $best = $candidate;
            }
        }

        $chosen = $best ?? $fallback;

        if ($chosen === null) {
            return null;
        }

        // Every row below the header is data; real Excel row numbers are kept.
        $dataRows = [];
        foreach ($sheetRows as $dataRowNumber => $values) {
            if ($dataRowNumber > $chosen['header_row']) {
                $dataRows[$dataRowNumber] = $values;
            }
        }

        $chosen['rows'] = $dataRows;

        return $chosen;
    }

    /**
     * Turn a raw header cell into its canonical column name.
     * "Jenis Kelamin", "JENIS_KELAMIN", "Jenis Kelamin (L/P)" all become "jenis_kelamin".
     */
    private function canonicalHeader(mixed $raw): string
    {
        $header = strtolower(trim((string) ($raw ?? '')));

        if ($header === '') {
            return '';
        }

        $header = str_replace(['_', '.', '-', '/', '\\'], ' ', $header);
        $header = trim((string) preg_replace('/\s+/u', ' ', $header));
        $header = trim((string) preg_replace('/\s*\([^)]*\)\s*/u', ' ', $header));

        foreach (self::HEADER_ALIASES as $canonical => $aliases) {
            if (in_array($header, $aliases, true)) {
                return $canonical;
            }
        }

        return str_replace(' ', '_', $header);
    }

    /**
     * Build a message that shows what the system actually read from the file.
     */
    private function missingHeadersMessage(array $missing, array $parsed): string
    {
        $message = "Kolom '{$missing[0]}' tidak ditemukan dalam Excel.";

        if (count($missing) > 1) {
            $message .= ' Kolom lain yang juga tidak ditemukan: ' . implode(', ', array_slice($missing, 1)) . '.';
        }

        $detected = array_values(array_filter(
            array_map(fn($value) => trim((string) ($value ?? '')), $parsed['raw_headers'] ?? []),
            fn($value) => $value !== ''
        ));

        if (!empty($detected)) {
            $message .= ' Header terbaca di baris ' . $parsed['header_row'] . ' pada sheet "'
                . $parsed['sheet'] . '": ' . implode(', ', array_slice($detected, 0, 20)) . '.';
        }

        $message .= ' Gunakan template resmi dari menu Import (Download Template Excel) agar nama kolom sesuai.';

        return $message;
    }

    /**
     * Process the validated import with atomic transaction.
     */
    public function processImport(array $data, array $previewData, string $extractPath): array
    {
        $imported = 0;
        $createdMembers = [];

        // Only needed for auto-generated numbers and manual-number registration.
        // Rows that already carry a nomor anggota can be imported without a formula.
        $formula = MemberNumberFormula::getActive();

        try {
            DB::beginTransaction();

            // Only generate numbers for rows without a manual nomor_anggota
            $companyIds = [];
            $rowIndexes = [];
            foreach ($data as $index => $row) {
                if (trim($row['nomor_anggota'] ?? '') !== '') {
                    continue;
                }
                $companyIds[] = $row['company_id'] ?? null;
                $rowIndexes[] = $index;
            }

            $generator = new MemberNumberGenerator();

            $generatedNumbers = [];
            if (!empty($companyIds)) {
                try {
                    $generatedNumbers = $generator->generateBatch($companyIds);
                } catch (RuntimeException $e) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'total' => count($data),
                        'imported' => 0,
                        'errors' => [
                            ['row' => '-', 'nik' => '-', 'errors' => ['Gagal generate nomor anggota: ' . $e->getMessage()]],
                        ],
                    ];
                }
            }

            $memberNumbers = [];
            foreach ($rowIndexes as $i => $index) {
                $memberNumbers[$index] = $generatedNumbers[$i] ?? null;
            }

            foreach ($data as $index => $row) {
                $preview = $previewData[$index] ?? null;

                // Use manual nomor from preview if available, otherwise use auto-generated
                $manualNomor = trim($row['nomor_anggota'] ?? '');
                if (!empty($manualNomor)) {
                    $nomorAnggota = $manualNomor;
                } else {
                    $nomorAnggota = $memberNumbers[$index] ?? null;
                }

                if (!$nomorAnggota) {
                    throw new RuntimeException("Nomor anggota tidak dapat dibuat untuk baris #" . ($index + 1));
                }

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

                // Create member with generated nomor anggota
                $member = Member::create([
                    'nik' => $nomorAnggota,
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

                // Register manual nomor in master (auto-generated ones already registered by the generator)
                if (!empty($manualNomor)) {
                    $this->registerManualNumber($manualNomor, $member, $formula, $company?->id);
                }

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

                if (is_numeric($val) && in_array($header, ['tanggal_lahir', 'berlaku_hingga', 'tanggal_pembuatan'])) {
                    $data[$header] = $this->excelSerialToDate($val);
                } else {
                    $data[$header] = trim((string) $val);
                }
            }
        }

        // Map company columns (support both old and new format)
        $data['kode_perusahaan'] = $data['kode_perusahaan'] ?? null;
        $data['nama_perusahaan'] = $data['nama_perusahaan'] ?? $data['perusahaan'] ?? null;

        // Normalize manual member number (Excel may store it as a number)
        if (isset($data['nomor_anggota'])) {
            $data['nomor_anggota'] = trim($this->normalizeMemberNumber($data['nomor_anggota']));
        }

        return $data;
    }

    /**
     * Normalize member number value coming from Excel.
     * Numeric cells may arrive as float (e.g. 1.02010380123E+12) or with trailing .0.
     */
    private function normalizeMemberNumber(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_float($value)) {
            // Whole numbers (e.g. 1020103800123) must not become "1.02E+12"
            if (fmod($value, 1.0) === 0.0) {
                return sprintf('%.0F', $value);
            }

            return (string) $value;
        }

        $value = trim((string) $value);

        // Strip Excel's ".0" suffix on whole numbers
        if (preg_match('/^\d+\.0+$/', $value)) {
            return preg_replace('/\.0+$/', '', $value);
        }

        return $value;
    }

    /**
     * Validate a single row of data.
     */
    private function validateRowData(
        array $row,
        int $rowNum,
        array $existingPhotoHashes,
        array &$processedNikHashes,
        string $extractPath,
        array &$photosData,
        array &$companyData,
        bool $hasNomorAnggota = false,
        array &$seenNomorAnggota = []
    ): array {
        $errors = [];

        // Validate nomor anggota if provided
        if ($hasNomorAnggota && !empty($row['nomor_anggota'])) {
            $nomorAnggota = trim($row['nomor_anggota']);

            // Check duplicate against database (including soft-deleted members,
            // since the unique index on nik still covers them)
            if (Member::withTrashed()->where('nik', $nomorAnggota)->exists()) {
                $errors[] = "Nomor anggota '$nomorAnggota' sudah terdaftar";
            } elseif (MemberNumber::where('number', $nomorAnggota)
                ->where('status', MemberNumber::STATUS_USED)
                ->exists()) {
                $errors[] = "Nomor anggota '$nomorAnggota' sudah dipakai anggota lain";
            } elseif (isset($seenNomorAnggota[$nomorAnggota])) {
                $errors[] = "Nomor anggota '$nomorAnggota' duplikat dengan baris {$seenNomorAnggota[$nomorAnggota]}";
            } else {
                $seenNomorAnggota[$nomorAnggota] = $rowNum;
            }

            // Validate format, same rule as the member create/edit form
            if (!preg_match('/^[0-9]{1,2}\.[0-9]{2}\.[0-9]{2}\.[0-9]{3}\.[0-9]{4}$/', $nomorAnggota)) {
                $errors[] = "Format nomor anggota tidak valid (contoh: 1.02.01.038.0123)";
            }
        } elseif ($hasNomorAnggota && empty($row['nomor_anggota'])) {
            // nomor_anggota column exists but empty - that's OK, will auto-generate
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
            $row['company_id'] = $companyValidation['data']['id'] ?? null;
        }

        // Photo validation (REQUIRED for import)
        $fotoColumn = $row['foto'] ?? null;
        $searchKey = $fotoColumn ?? $rowNum; // Use foto column or row number as key
        $photoResult = $this->findAndValidatePhoto($extractPath, $searchKey, $fotoColumn, $rowNum, $existingPhotoHashes, $processedNikHashes);

        if (!empty($photoResult['errors'])) {
            $errors = array_merge($errors, $photoResult['errors']);
        }
        if (isset($photoResult['data'])) {
            $photosData[$searchKey] = $photoResult['data'];
            $processedNikHashes[$photoResult['data']['hash']] = $photoResult['data'];
            $row['_source_photo'] = $photoResult['data'];
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

        // Find existing company by kode (case-insensitive)
        $existingByKode = Company::findByKode($kode);

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
     * Find photo by foto column and validate it.
     */
    private function findAndValidatePhoto(
        string $extractPath,
        string $searchKey,
        ?string $fotoColumn,
        int $rowNum,
        array $existingPhotoHashes,
        array $processedNikHashes
    ): array {
        $extensions = ['jpg', 'jpeg', 'png'];
        $foundFile = null;
        $foundHash = null;

        // Determine search filename: use foto column value if provided, otherwise use search key
        $searchFileName = !empty($fotoColumn) ? trim($fotoColumn) : $searchKey;

        // Remove extension from search filename for matching
        $searchBaseName = pathinfo($searchFileName, PATHINFO_FILENAME);

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

            // Match against foto column filename or search key
            if (strcasecmp($baseName, $searchBaseName) === 0 || strcasecmp($baseName, $searchKey) === 0) {
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
                    'source_name' => $file->getFilename(), // original filename in ZIP
                    'path' => $file->getPathname(),
                    'hash' => $hash,
                    'extension' => $extension,
                ];
                $foundHash = $hash;
                break;
            }
        }

        if (!$foundFile) {
            $searchHint = !empty($fotoColumn)
                ? $searchFileName
                : "{$searchKey}.jpg / {$searchKey}.jpeg / {$searchKey}.png";
            return [
                'errors' => [
                    "FOTO TIDAK DITEMUKAN (cari: {$searchHint}) — "
                    . 'Pastikan file foto tersedia di folder foto/ dan nama file sesuai dengan kolom foto.'
                ],
            ];
        }

        // Validate image is actually a valid image
        $imageInfo = @getimagesize($foundFile['path']);
        if (!$imageInfo) {
            return [
                'errors' => [
                    "FOTO TIDAK VALID: File {$searchBaseName}.{$foundFile['extension']} bukan file gambar yang valid."
                ],
            ];
        }

        return ['data' => $foundFile];
    }

    /**
     * Generate preview data including generated member numbers and photo names.
     */
    private function generatePreviewData(array $validData, array $companyData, array $photosData, bool $hasNomorAnggota = false): array
    {
        $preview = [];
        $companySequenceCounters = [];

        // First pass: build company id list for rows WITHOUT manual nomor
        $companyIds = [];
        $autoRowIndexes = [];
        foreach ($validData as $index => $row) {
            $rowNum = $row['_row_num'] ?? ($index + 2); // Excel row number
            $companyInfo = $companyData[$rowNum] ?? null;

            if (trim($row['nomor_anggota'] ?? '') !== '') {
                continue; // manual number, no reservation needed
            }

            $companyIds[] = $companyInfo['id'] ?? null;
            $autoRowIndexes[] = $index;
        }

        // Reserve numbers only for rows without manual nomor
        $generator = new MemberNumberGenerator();
        $generatedReservations = $generator->reserveForPreview($companyIds);

        $reservations = [];
        foreach ($autoRowIndexes as $i => $index) {
            $reservations[$index] = $generatedReservations[$i] ?? null;
        }

        foreach ($validData as $index => $row) {
            $rowNum = $row['_row_num'] ?? ($index + 2); // Excel row number
            $companyInfo = $companyData[$rowNum] ?? null;
            $reservation = $reservations[$index] ?? null;

            // Use manual nomor if provided, otherwise use reserved number
            $manualNomor = trim($row['nomor_anggota'] ?? '');
            if (!empty($manualNomor)) {
                $nomorAnggota = $manualNomor; // Use manual nomor from Excel
            } else {
                // Use reserved number for preview
                $nomorAnggota = $reservation['preview_number'] ?? 'PENDING';
            }

            // Get photo data
            $fotoColumn = $row['foto'] ?? null;
            $searchKey = $fotoColumn ?? $rowNum;
            $photoData = $photosData[$searchKey] ?? null;

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

            // Get next sequence for this company (for photo naming)
            if (!isset($companySequenceCounters[$companyKode])) {
                $companySequenceCounters[$companyKode] = MemberPhoto::getNextSequenceForCompany($companyKode);
            }

            $sequence = $companySequenceCounters[$companyKode]++;
            $photoName = sprintf('%s-%04d.%s', $companyKode, $sequence, $photoData['extension'] ?? 'jpg');

            $preview[] = [
                'nomor_anggota' => $nomorAnggota,
                'is_manual' => !empty($manualNomor),
                'nama' => $row['nama'],
                'company_kode' => $companyKode,
                'company_name' => $companyInfo['name'] ?? null,
                'company_id' => $companyInfo['id'] ?? null,
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
     * Register a manually supplied member number in the member_numbers master.
     * If the number already exists and is still available, mark it as used.
     */
    private function registerManualNumber(
        string $number,
        Member $member,
        ?MemberNumberFormula $formula,
        ?int $companyId
    ): void {
        $existing = MemberNumber::where('number', $number)->first();

        if ($existing) {
            if ($existing->isAvailable()) {
                $existing->markAsUsed($member);
            }

            if (!$existing->company_id && $companyId) {
                $existing->update(['company_id' => $companyId]);
            }

            return;
        }

        $sequence = 0;
        if ($formula) {
            $sequence = (int) substr($number, -$formula->sequence_digits);
        }

        MemberNumber::create([
            'number' => $number,
            'status' => MemberNumber::STATUS_USED,
            'member_id' => $member->id,
            'company_id' => $companyId,
            'formula_id' => $formula?->id,
            'prefix_value' => '',
            'sequence' => $sequence,
        ]);
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

        // Find by kode (case-insensitive) - kode acts as the data puller
        $company = Company::findByKode($kode);
        if ($company) {
            return $company;
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
     * Check if a row is an instruction row (e.g. the CATATAN note in the template).
     * The note may sit in any column, so every cell is checked instead of only 'nama'.
     */
    private function isInstructionRow(array $rowData): bool
    {
        $catatanKey = null;

        foreach ($rowData as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            if (str_starts_with(strtolower(ltrim($value)), 'catatan')) {
                $catatanKey = $key;
                break;
            }
        }

        if ($catatanKey === null) {
            return false;
        }

        // Verify this is truly an instruction row (no real member data besides the note itself)
        foreach ($rowData as $field => $value) {
            if ($field === $catatanKey) {
                continue;
            }

            if (!empty(trim((string) $value))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a row carries no member data at all.
     * Such rows (stray formatting) should be skipped, not rejected.
     * A row with any filled cell is kept so the user still sees what is missing.
     */
    private function isBlankDataRow(array $rowData): bool
    {
        foreach ($rowData as $value) {
            if (!empty(trim((string) $value))) {
                return false;
            }
        }

        return true;
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
     * Validate NIK format (DEPRECATED - kept for backward compatibility).
     * Now member numbers are auto-generated, this method always returns true.
     */
    public static function isValidNik(string $nik): bool
    {
        // Member numbers are now auto-generated, validation is not needed
        // Return true to allow any format
        return true;
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
