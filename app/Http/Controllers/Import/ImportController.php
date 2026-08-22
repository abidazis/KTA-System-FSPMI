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
            'zipNames' => $allZipNames,
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
        $total = max(0, count($rows) - 1);
        $photosFound = [];

        // NIK normalization helper — strip leading zeros and normalize to 15 digits
        // Excel float loses last digit, so all NIKs become 15 digits
        $normalizeNik = fn(string $nik): string => preg_replace('/^0+/', '', $nik) ?: '0';

        // Get all NIKs already in DB, keyed by normalized form
        $allDbNiks = Member::pluck('nik')->toArray();
        $dbNiksByNormalized = [];
        $dbNiksLast15 = [];
        foreach ($allDbNiks as $dbNik) {
            $norm = $normalizeNik($dbNik);
            $dbNiksByNormalized[$norm] = $dbNik;
            $dbNiksLast15[$norm] = substr($norm, -15);
        }
        $existingNiks = array_flip($allDbNiks);
        // Track NIKs within this Excel for duplicate detection
        $seenNiks = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowNum = $i + 1;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Map headers to values
            $rowData = [];
            foreach ($headers as $index => $header) {
                if (isset($row[$index])) {
                    $val = $row[$index];
                    // Convert PhpSpreadsheet numeric dates to string
                    if (is_numeric($val) && in_array($header, ['tanggal_lahir', 'berlaku_hingga', 'tanggal_pembuatan'])) {
                        $val = $this->excelSerialToDate($val);
                    }
                    $rowData[$header] = trim((string) $val);
                }
            }

            $rowErrors = $this->validateRow(
                $rowData, $existingNiks, $seenNiks, $dbNiksByNormalized,
                $normalizeNik, $extractPath, $rowNum, $photosFound
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
     *
     * @param array $row Row data from Excel
     * @param array $existingNiks Map of exact NIKs in DB (for exact match)
     * @param array &$seenNiks Map of NIKs seen within this Excel (by-ref, modified)
     * @param array $dbNiksByNormalized Map of normalized NIK -> original DB NIK (for fuzzy check)
     * @param callable $normalizeNik Function to normalize a NIK string
     * @param string $extractPath Path to extracted ZIP contents
     * @param int $rowNum Excel row number (for error reporting)
     * @param array &$photosFound Map: Excel-NIK -> ['nik' => confirmed-NIK, 'path' => photo-path] (by-ref, modified)
     * @return array List of error strings (empty = valid)
     */
    private function validateRow(array $row, array $existingNiks, array &$seenNiks, array $dbNiksByNormalized, callable $normalizeNik, string $extractPath, int $rowNum, array &$photosFound): array
    {
        $rowErrors = [];
        $nik = trim((string) ($row['nik'] ?? ''));

        // NIK validation
        if (empty($nik)) {
            $rowErrors[] = 'NIK kosong';
        } elseif (!preg_match('/^\d{15,16}$/', $nik)) {
            $rowErrors[] = 'NIK harus terdiri dari 15 atau 16 digit angka';
        } else {
            // Excel float precision: NIK stored as number may lose last digit
            // e.g. "3275010101900001" → "3275010101900000" (15 digits, lost trailing 1)
            $nikNorm = $normalizeNik($nik);

            // Check exact match in DB
            if (isset($existingNiks[$nik])) {
                $rowErrors[] = "NIK $nik sudah terdaftar di database";
            }
            // Check duplicate within same Excel
            if (isset($seenNiks[$nikNorm])) {
                $rowErrors[] = "NIK $nik duplikat dalam file Excel yang sama";
            }

            // Fuzzy check: if this NIK (after normalization) matches last 15 digits of any DB NIK,
            // it likely lost a digit due to Excel float precision — reject it
            foreach ($dbNiksByNormalized as $normDb => $originalDb) {
                if (substr($normDb, -15) === substr($nikNorm, -15)) {
                    $rowErrors[] = "NIK $nik terlalu mirip dengan NIK {$originalDb} yang sudah terdaftar "
                        . "(periksa apakah NIK di Excel disimpan sebagai TEXT, bukan ANGKA)";
                    break;
                }
            }
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
        if (!empty($nik)) {
            $photoResult = $this->findPhotoByNik($extractPath, $nik);
            if ($photoResult) {
                $confirmedNik = $photoResult['nik'];
                $photoPath = $photoResult['path'];
                // Use the confirmed NIK from filename for storage
                $photosFound[$nik] = ['nik' => $confirmedNik, 'path' => $photoPath];
                $seenNiks[$normalizeNik($confirmedNik)] = true;
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

        if (!preg_match('/^\d{15,16}$/', $nik)) {
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
