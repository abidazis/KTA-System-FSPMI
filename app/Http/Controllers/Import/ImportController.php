<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\AuditLog;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ZipArchive;

class ImportController extends Controller
{
    public function index(): View
    {
        // Clear any stale import session
        session()->forget('import_token');
        return view('imports.index');
    }

    public function downloadTemplate()
    {
        $templatePath = storage_path('app/private/imports/template_import.xlsx');
        $this->generateTemplateExcel($templatePath);
        return response()->download($templatePath, 'template_import_anggota.xlsx');
    }

    /**
     * Generate a pre-formatted Excel template where NIK column is stored as TEXT.
     * This prevents Excel from converting 16-digit NIKs to floating-point numbers.
     */
    private function generateTemplateExcel(string $path): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Anggota');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(22); // nik (TEXT)
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(12);
        $sheet->getColumnDimension('K')->setWidth(16);
        $sheet->getColumnDimension('L')->setWidth(16);
        $sheet->getColumnDimension('M')->setWidth(22);

        // Header row styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '1e40af']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

        // Headers
        $headers = ['nik', 'nama', 'tempat_lahir', 'tanggal_lahir', 'alamat',
                    'provinsi', 'kabupaten_kota', 'kecamatan', 'jenis_kelamin',
                    'agama', 'berlaku_hingga', 'tanggal_pembuatan', 'foto'];
        $sheet->fromArray($headers, null, 'A1');

        // Example data row — NIK stored as TEXT with apostrophe prefix
        // This ensures Excel treats it as text, not number
        $sheet->setCellValueExplicit('A2', "'3275010101900001", \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B2', 'Nama Lengkap');
        $sheet->setCellValue('C2', 'Kota');
        $sheet->setCellValueExplicit('D2', '1990-01-01', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('E2', 'Alamat Lengkap');
        $sheet->setCellValue('F2', 'Jawa Barat');
        $sheet->setCellValue('G2', 'Karawang');
        $sheet->setCellValue('H2', 'Kecamatan');
        $sheet->setCellValue('I2', 'Laki-laki');
        $sheet->setCellValue('J2', 'Islam');
        $sheet->setCellValueExplicit('K2', '2031-12-31', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('L2', '2026-01-01', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('M2', '3275010101900001.jpg');

        // Add note about NIK in cell A4
        $sheet->setCellValue('A4', 'CATATAN: Kolom NIK harus berisi 16 digit angka. Jangan rubah format sel.');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => 'dc2626'], 'size' => 10],
        ]);
        $sheet->mergeCells('A4:M4');

        // Freeze header row
        $sheet->freezePane('A2');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($path);
    }

    public function validateImport(Request $request): View
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:zip', 'max:51200'],
        ]);

        // Step 1: Store ZIP to private temp storage
        $zipFile = $request->file('file');
        $token = uniqid('imp_');
        $tempDir = storage_path('app/private/imports/temp/' . $token);
        mkdir($tempDir, 0755, true);
        $zipPath = $tempDir . '/upload.zip';
        $zipFile->move($tempDir, 'upload.zip');

        // Step 2: Extract ZIP
        $extractPath = $tempDir . '/extracted';
        mkdir($extractPath, 0755, true);

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            $this->cleanupTempDir($tempDir);
            return view('imports.validation', [
                'result' => $this->formatError('Tidak dapat membuka file ZIP.'),
                'importToken' => null,
            ]);
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Step 3: Find Excel file using ZipArchive names (handles spaces/special chars)
        $excelFile = null;
        $allZipNames = [];

        $zip = new ZipArchive;
        if ($zip->open($zipPath) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                $allZipNames[] = $name;
                // Normalize path: convert forward slashes to OS-appropriate separator
                $normalizedName = str_replace('/', DIRECTORY_SEPARATOR, $name);
                $fullPath = $extractPath . DIRECTORY_SEPARATOR . $normalizedName;
                $baseName = pathinfo($normalizedName, PATHINFO_BASENAME);
                $ext = strtolower(pathinfo($baseName, PATHINFO_EXTENSION));
                if (in_array($ext, ['xlsx', 'xls']) && $excelFile === null) {
                    $excelFile = $fullPath;
                }
            }
            $zip->close();
        }

        if (!$excelFile) {
            $this->cleanupTempDir($tempDir);
            return view('imports.validation', [
                'result' => $this->formatError('File Excel (.xlsx/.xls) tidak ditemukan dalam ZIP.'),
                'importToken' => null,
            ]);
        }

        // Step 4: Validate Excel data
        $validationResult = $this->validateExcelData($excelFile, $extractPath, $allZipNames);

        // Step 5: Store token + paths in session
        session([
            'import_token' => $token,
            'import_temp_dir' => $tempDir,
            'import_extract_path' => $extractPath,
            'import_validation_result' => $validationResult,
            'import_zip_names' => $allZipNames,
        ]);

        return view('imports.validation', [
            'result' => $validationResult,
            'importToken' => $token,
        ]);
    }

    public function processImport(Request $request): \Illuminate\Http\RedirectResponse
    {
        $token = $request->input('import_token');
        $sessionToken = session('import_token');
        $tempDir = session('import_temp_dir');

        if (!$token || $token !== $sessionToken || !$tempDir) {
            return redirect()
                ->route('imports.index')
                ->with('error', 'Sesi import tidak valid atau sudah kadaluarsa. Silakan upload ulang.');
        }

        $validationResult = session('import_validation_result', []);

        if (empty($validationResult['data']) || $validationResult['valid'] === 0) {
            return redirect()
                ->route('imports.index')
                ->with('error', 'Tidak ada data valid untuk diimport.');
        }

        $zipPath = $tempDir . '/upload.zip';
        $extractPath = $tempDir . '/extracted';

        if (!file_exists($zipPath)) {
            $this->cleanupTempDir($tempDir);
            return redirect()
                ->route('imports.index')
                ->with('error', 'File ZIP tidak ditemukan.');
        }

        $importResult = $this->importMembers(
            $validationResult['data'],
            $extractPath,
            $validationResult['photos_found'] ?? []
        );

        $this->cleanupTempDir($tempDir);
        session()->forget(['import_token', 'import_temp_dir', 'import_extract_path', 'import_validation_result']);

        if ($importResult['success']) {
            AuditLog::log('import_members', null, null, [
                'total' => $importResult['total'],
                'imported' => $importResult['imported'],
                'errors' => count($importResult['errors']),
            ]);

            return redirect()
                ->route('members.index')
                ->with('success', "Berhasil mengimport {$importResult['imported']} anggota.");
        }

        return redirect()
            ->route('imports.index')
            ->with('error', 'Import gagal: ' . ($importResult['errors'][0]['errors'][0] ?? 'Kesalahan tidak diketahui.'));
    }

    // ─── VALIDATION ─────────────────────────────────────────────────────────────

    private function validateExcelData(string $excelFile, string $extractPath, array $allZipNames): array
    {
        try {
            $spreadsheet = IOFactory::load($excelFile);
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

        // Normalize headers to lowercase
        $headers = array_map(fn($h) => strtolower(trim((string) ($h ?? ''))), $rows[0]);

        $requiredHeaders = [
            'nik', 'nama', 'tempat_lahir', 'tanggal_lahir',
            'alamat', 'jenis_kelamin', 'agama',
            'berlaku_hingga', 'tanggal_pembuatan',
        ];

        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $headers)) {
                return $this->formatError("Kolom '$required' tidak ditemukan dalam Excel.");
            }
        }

        $validData = [];
        $errors = [];
        $photosFound = [];

        // Get all NIKs already in DB for duplicate checking
        $allDbNiks = Member::pluck('nik')->toArray();
        $existingNiks = array_flip($allDbNiks);

        // Track NIKs within this Excel for duplicate detection
        $seenNiks = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowNum = $i + 1;

            // Skip completely empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Map headers to values with proper NIK normalization
            $rowData = [];
            foreach ($headers as $index => $header) {
                if (isset($row[$index])) {
                    $val = $row[$index];

                    // NIK column: use proper normalization to handle Excel float issues
                    if ($header === 'nik') {
                        $rowData[$header] = self::normalizeNik($val);
                    }
                    // Date columns: convert PhpSpreadsheet numeric dates to string
                    elseif (is_numeric($val) && in_array($header, ['tanggal_lahir', 'berlaku_hingga', 'tanggal_pembuatan'])) {
                        $rowData[$header] = $this->excelSerialToDate($val);
                    }
                    // Other columns: simple trim to string
                    else {
                        $rowData[$header] = trim((string) $val);
                    }
                }
            }

            // Skip instruction/note rows (e.g., template CATATAN rows)
            if ($this->isInstructionRow($rowData)) {
                continue;
            }

            $rowErrors = $this->validateRow(
                $rowData, $existingNiks, $seenNiks, $allDbNiks,
                $extractPath, $rowNum, $photosFound
            );

            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $rowNum,
                    'nik' => $rowData['nik'] ?? '-',
                    'errors' => $rowErrors,
                ];
            } else {
                // Use the filename-NIK (confirmed by photo match) for data storage
                $nikFromExcel = $rowData['nik'] ?? '';
                $confirmedNik = $photosFound[$nikFromExcel]['nik'] ?? $nikFromExcel;
                $rowData['nik'] = $confirmedNik;
                $validData[] = $rowData;
            }
        }

        // Total = valid + errors (actual member data rows, excluding empty/instruction rows)
        $total = count($validData) + count($errors);

        return [
            'success' => empty($errors),
            'total' => $total,
            'valid' => count($validData),
            'data' => $validData,
            'errors' => $errors,
            'photos_found' => $photosFound,
        ];
    }

    /**
     * Validate a single row from the Excel import.
     * NIK should already be normalized before calling this method.
     *
     * @param array $row Row data from Excel (nik already normalized)
     * @param array $existingNiks Map of exact NIKs in DB (for exact match)
     * @param array &$seenNiks Map of NIKs seen within this Excel (by-ref, modified)
     * @param array $allDbNiks All NIKs in DB (for fuzzy precision check)
     * @param string $extractPath Path to extracted ZIP contents
     * @param int $rowNum Excel row number (for error reporting)
     * @param array &$photosFound Map: Excel-NIK -> ['nik' => confirmed-NIK, 'path' => photo-path] (by-ref, modified)
     * @return array List of error strings (empty = valid)
     */
    private function validateRow(array $row, array $existingNiks, array &$seenNiks, array $allDbNiks, string $extractPath, int $rowNum, array &$photosFound): array
    {
        $rowErrors = [];

        // NIK is already normalized by validateExcelData, so just use it directly
        $nik = $row['nik'] ?? '';

        // NIK validation
        if (empty($nik)) {
            $rowErrors[] = 'NIK kosong';
        } elseif (!self::isValidNik($nik)) {
            $rowErrors[] = 'NIK harus terdiri dari 15 atau 16 digit angka';
        } else {
            // NIK is valid format, now check for duplicates

            // Check exact match in DB
            if (isset($existingNiks[$nik])) {
                $rowErrors[] = "NIK $nik sudah terdaftar di database";
            }

            // Check duplicate within same Excel (using normalized comparison)
            $nikNorm = ltrim($nik, '0') ?: $nik;
            if (isset($seenNiks[$nikNorm])) {
                $rowErrors[] = "NIK $nik duplikat dalam file Excel yang sama";
            }

            // Fuzzy precision check: only reject if NIK is exactly the same as a DB NIK
            // (This catches true duplicates, not "similar" NIKs)
            // We don't do aggressive 15-digit matching anymore as it causes false positives
        }

        if (empty($row['nama'])) {
            $rowErrors[] = 'Nama kosong';
        }
        if (empty($row['tempat_lahir'])) {
            $rowErrors[] = 'Tempat lahir kosong';
        }
        if (empty($row['tanggal_lahir'])) {
            $rowErrors[] = 'Tanggal lahir kosong';
        } elseif (!$this->isValidDate($row['tanggal_lahir'])) {
            $rowErrors[] = 'Format tanggal lahir tidak valid';
        }
        if (empty($row['alamat'])) {
            $rowErrors[] = 'Alamat kosong';
        }
        if (empty($row['jenis_kelamin'])) {
            $rowErrors[] = 'Jenis kelamin kosong';
        } elseif (!in_array($row['jenis_kelamin'], ['Laki-laki', 'Perempuan'])) {
            $rowErrors[] = "Jenis kelamin harus 'Laki-laki' atau 'Perempuan'";
        }
        if (empty($row['agama'])) {
            $rowErrors[] = 'Agama kosong';
        }
        if (empty($row['berlaku_hingga'])) {
            $rowErrors[] = 'Berlaku hingga kosong';
        } elseif (!$this->isValidDate($row['berlaku_hingga'])) {
            $rowErrors[] = 'Format berlaku hingga tidak valid';
        }
        if (empty($row['tanggal_pembuatan'])) {
            $rowErrors[] = 'Tanggal pembuatan kosong';
        } elseif (!$this->isValidDate($row['tanggal_pembuatan'])) {
            $rowErrors[] = 'Format tanggal pembuatan tidak valid';
        }

        // Photo is REQUIRED — find by NIK, using filename as source of truth
        // Only search for photo if NIK is valid
        if (!empty($nik) && self::isValidNik($nik)) {
            $photoResult = $this->findPhotoByNik($extractPath, $nik);
            if ($photoResult) {
                $confirmedNik = $photoResult['nik'];
                $photoPath = $photoResult['path'];
                // Use the confirmed NIK from filename for storage
                $photosFound[$nik] = ['nik' => $confirmedNik, 'path' => $photoPath];
                // Track normalized NIK for duplicate detection
                $seenNiks[ltrim($confirmedNik, '0') ?: $confirmedNik] = true;
            } else {
                $rowErrors[] = "FOTO TIDAK DITEMUKAN (cari: {$nik}.jpg / {$nik}.jpeg / {$nik}.png) — "
                    . 'pastikan format sel NIK di Excel adalah TEXT, bukan ANGKA';
            }
        }

        return $rowErrors;
    }

    // ─── PHOTO SEARCH ───────────────────────────────────────────────────────────

    /**
     * Recursively search for a photo matching the given NIK.
     * Returns ['nik' => confirmed-NIK, 'path' => absolute-path] on success.
     * Handles Excel floating-point precision loss by matching last N digits.
     */
    private function findPhotoByNik(string $extractPath, string $nik): ?array
    {
        $nik = trim((string) $nik);

        // Use static validation method for consistency
        if (!self::isValidNik($nik)) {
            return null;
        }

        $extensions = ['jpg', 'jpeg', 'png'];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $extractPath,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
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

            // Exact match
            if ($baseName === $nik) {
                return ['nik' => $baseName, 'path' => $file->getPathname()];
            }

            // Strip leading zeros from both for fair comparison
            $fnClean = ltrim($baseName, '0') ?: $baseName;
            $nkClean = ltrim($nik, '0') ?: $nik;

            // Exact match after stripping zeros
            if ($fnClean === $nkClean) {
                return ['nik' => $baseName, 'path' => $file->getPathname()];
            }

            // Fuzzy match: compare last N digits (handles Excel float precision loss)
            // e.g. file "3275010102000001" vs Excel NIK "3275010102000000"
            $compareLen = min(strlen($fnClean), strlen($nkClean));
            if ($compareLen >= 14) {
                $fileLast = substr($fnClean, -$compareLen);
                $nikLast = substr($nkClean, -$compareLen);
                if ($fileLast === $nikLast) {
                    return ['nik' => $baseName, 'path' => $file->getPathname()];
                }
            }
        }

        return null;
    }

    // ─── IMPORT ────────────────────────────────────────────────────────────────

    private function importMembers(array $data, string $extractPath, array $photosFound): array
    {
        $imported = 0;
        $errors = [];

        \DB::beginTransaction();
        try {
            foreach ($data as $row) {
                $nik = $row['nik'];
                $nik = trim((string) $nik);

                // Find/create province
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

                // Copy photo to private storage — use confirmed NIK from filename
                $fotoPath = null;
                $sourcePhotoData = $photosFound[$nik] ?? null;

                if ($sourcePhotoData && file_exists($sourcePhotoData['path'])) {
                    $ext = strtolower(pathinfo($sourcePhotoData['path'], PATHINFO_EXTENSION));
                    $destPath = 'members/photos/' . $nik . '.' . $ext;
                    Storage::disk('local')->put($destPath, file_get_contents($sourcePhotoData['path']));
                    $fotoPath = $destPath;
                }

                Member::create([
                    'nik' => $nik,
                    'nama' => $row['nama'],
                    'tempat_lahir' => $row['tempat_lahir'],
                    'tanggal_lahir' => $this->parseDate($row['tanggal_lahir']),
                    'alamat' => $row['alamat'],
                    'province_id' => $provinceId,
                    'regency_id' => $regencyId,
                    'district_id' => $districtId,
                    'jenis_kelamin' => $row['jenis_kelamin'],
                    'agama' => $row['agama'],
                    'berlaku_hingga' => $this->parseDate($row['berlaku_hingga']),
                    'tanggal_pembuatan' => $this->parseDate($row['tanggal_pembuatan']),
                    'foto_path' => $fotoPath,
                    'status' => $fotoPath ? 'ready' : 'draft',
                    'created_by' => auth()->id(),
                ]);

                $imported++;
            }

            \DB::commit();

            return [
                'success' => true,
                'total' => count($data),
                'imported' => $imported,
                'errors' => [],
            ];
        } catch (\Exception $e) {
            \DB::rollBack();

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

    // ─── HELPERS ─────────────────────────────────────────────────────────────

    private function formatError(string $message): array
    {
        return [
            'success' => false,
            'total' => 0,
            'valid' => 0,
            'data' => [],
            'errors' => [['row' => '-', 'nik' => '-', 'errors' => [$message]]],
            'photos_found' => [],
        ];
    }

    /**
     * Normalize a NIK value for consistent processing.
     * Handles Excel float precision issues by converting to integer string.
     *
     * @param mixed $value Raw value from Excel cell
     * @return string Normalized NIK as 15-16 digit string, or empty string for invalid
     */
    public static function normalizeNik(mixed $value): string
    {
        // Handle null/empty
        if ($value === null || $value === '') {
            return '';
        }

        // Handle numeric values (float from Excel)
        // This is critical: Excel stores large numbers as floats, which may be in scientific notation
        // e.g. 3216221612020012.0 stored as float, cast to string gives "3.21622161202001E+15"
        if (is_float($value) || is_int($value)) {
            // Convert to integer string without decimals or scientific notation
            return number_format($value, 0, '', '');
        }

        // Handle string values
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return '';
            }

            // Check if string looks like a numeric value (from Excel storing as TEXT but value is numeric)
            // e.g. "3216221612020012.0" should become "3216221612020012"
            if (preg_match('/^\d+\.0*$/', $value)) {
                // Remove trailing .0, .00, etc.
                return preg_replace('/\.0+$/', '', $value);
            }

            // Already a clean string - return as-is
            return $value;
        }

        // Handle other types by converting to string and trimming
        return trim((string) $value);
    }

    /**
     * Validate a NIK string (should be normalized first).
     *
     * @param string $nik Normalized NIK string
     * @return bool True if valid (15 or 16 digits, all numeric)
     */
    public static function isValidNik(string $nik): bool
    {
        if (empty($nik)) {
            return false;
        }
        return preg_match('/^\d{15,16}$/', $nik) === 1;
    }

    /**
     * Check if a row is an instruction/note row that should be skipped.
     *
     * An instruction row is identified by:
     * - NIK column contains text starting with "CATATAN:" (case-insensitive)
     * - All other required data columns are empty
     *
     * This handles template files that include instructional notes like:
     * "CATATAN: Kolom NIK harus berisi 16 digit angka. Jangan rubah format sel."
     *
     * @param array $rowData Row data mapped from headers
     * @return bool True if this is an instruction row to skip
     */
    private function isInstructionRow(array $rowData): bool
    {
        $nik = $rowData['nik'] ?? '';

        // Check if NIK column contains an instruction/note pattern
        if (!empty($nik) && is_string($nik)) {
            $nikLower = strtolower(trim($nik));
            if (str_starts_with($nikLower, 'catatan:')) {
                // This is an instruction row - verify other fields are empty
                // Instruction rows have the note in NIK column but no actual data
                $requiredFields = ['nama', 'tempat_lahir', 'alamat', 'jenis_kelamin', 'agama'];

                foreach ($requiredFields as $field) {
                    $value = $rowData[$field] ?? '';
                    if (!empty(trim((string) $value))) {
                        // There's actual data in other columns - this is NOT an instruction row
                        return false;
                    }
                }

                // All required fields are empty - this is an instruction row
                return true;
            }
        }

        return false;
    }

    private function isValidDate(string $date): bool
    {
        if (empty($date)) return false;
        if (is_numeric($date)) return true;
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $fmt) {
            $p = \DateTime::createFromFormat($fmt, $date);
            if ($p && $p->format($fmt) === $date) return true;
        }
        return strtotime($date) !== false;
    }

    private function parseDate(string $date): ?string
    {
        if (empty($date)) return null;

        // Numeric Excel serial
        if (is_numeric($date)) {
            return $this->excelSerialToDate($date);
        }

        // Try explicit formats
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd F Y'] as $fmt) {
            $p = \DateTime::createFromFormat($fmt, $date);
            if ($p && $p->format($fmt) === $date) {
                return $p->format('Y-m-d');
            }
        }

        // Fallback to strtotime
        $ts = strtotime($date);
        if ($ts) return date('Y-m-d', $ts);

        return null;
    }

    private function excelSerialToDate($serial): string
    {
        // Excel serial: days since 1900-01-00 (with 1900 leap year bug)
        $unix = ((float) $serial - 25569) * 86400;
        return gmdate('Y-m-d', (int) $unix);
    }

    private function cleanupTempDir(string $path): void
    {
        if (!is_dir($path)) return;
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            if ($file->isDir()) {
                @rmdir($file->getPathname());
            } else {
                @unlink($file->getPathname());
            }
        }
        @rmdir($path);
    }
}
