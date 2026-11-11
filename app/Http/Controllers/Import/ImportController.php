<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use ZipArchive;

class ImportController extends Controller
{
    protected ImportService $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    public function index(): View
    {
        // Clear any stale import session
        session()->forget(['import_token', 'import_temp_dir', 'import_extract_path', 'import_validation_result']);
        return view('imports.index');
    }

    public function downloadTemplate()
    {
        $templatePath = storage_path('app/private/imports/template_import.xlsx');
        $this->generateTemplateExcel($templatePath);

        // Always regenerate, and tell the browser not to reuse a previously
        // downloaded copy of the template (same filename every time).
        return response()->download($templatePath, 'template_import_anggota.xlsx', [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Generate a pre-formatted Excel template.
     */
    private function generateTemplateExcel(string $path): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Anggota');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(20); // nomor_anggota
        $sheet->getColumnDimension('B')->setWidth(25); // nama
        $sheet->getColumnDimension('C')->setWidth(18); // tempat_lahir
        $sheet->getColumnDimension('D')->setWidth(14); // tanggal_lahir
        $sheet->getColumnDimension('E')->setWidth(30); // alamat
        $sheet->getColumnDimension('F')->setWidth(18); // provinsi
        $sheet->getColumnDimension('G')->setWidth(18); // kabupaten_kota
        $sheet->getColumnDimension('H')->setWidth(18); // kecamatan
        $sheet->getColumnDimension('I')->setWidth(15); // jenis_kelamin
        $sheet->getColumnDimension('J')->setWidth(12); // agama
        $sheet->getColumnDimension('K')->setWidth(16); // berlaku_hingga
        $sheet->getColumnDimension('L')->setWidth(16); // tanggal_pembuatan
        $sheet->getColumnDimension('M')->setWidth(22); // foto
        $sheet->getColumnDimension('N')->setWidth(15); // kode_perusahaan

        // Header row styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '1e40af']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

        // Headers (nomor_anggota optional - filled = used as-is, empty = auto-generated)
        $headers = [
            'nomor_anggota', 'nama', 'tempat_lahir', 'tanggal_lahir', 'alamat',
            'provinsi', 'kabupaten_kota', 'kecamatan', 'jenis_kelamin',
            'agama', 'berlaku_hingga', 'tanggal_pembuatan', 'foto',
            'kode_perusahaan'
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Example data row
        $sheet->setCellValueExplicit('A2', '1.02.01.038.0123', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B2', 'Nama Lengkap');
        $sheet->setCellValue('C2', 'Kota');
        $sheet->setCellValueExplicit('D2', '1990-01-01', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('E2', 'Alamat Lengkap');
        $sheet->setCellValue('F2', 'Jawa Barat');
        $sheet->setCellValue('G2', 'Karawang');
        $sheet->setCellValue('H2', 'Kecamatan');
        $sheet->setCellValueExplicit('I2', '1', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('J2', 'Islam');
        $sheet->setCellValueExplicit('K2', '2031-12-31', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('L2', '2026-01-01', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('M2', 'ABC-0001'); // foto: name only, extension is auto-detected
        $sheet->setCellValue('N2', 'ABC'); // kode_perusahaan

        // Dropdown 1/2 for the jenis_kelamin column.
        $genderValidation = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $genderValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $genderValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $genderValidation->setAllowBlank(true);
        $genderValidation->setShowDropDown(true);
        $genderValidation->setFormula1('"1,2"');
        for ($row = 2; $row <= 1000; $row++) {
            $sheet->getCell('I' . $row)->setDataValidation(clone $genderValidation);
        }

        // Note row
        $sheet->setCellValue('A4', 'CATATAN: Kolom nomor_anggota opsional — isi jika nomor sudah ada (cth: 1.02.01.038.0123), kosongkan agar dibuat otomatis oleh sistem. Jenis kelamin cukup diisi 1 = Laki-laki atau 2 = Perempuan. Kolom foto cukup diisi nama filenya saja (cth: ABC-0001) — sistem otomatis mengenali .jpg/.jpeg/.png tanpa membedakan huruf besar/kecil. Perusahaan cukup diisi kode_perusahaan; kode baru akan dibuat otomatis. Kolom provinsi, kabupaten_kota, kecamatan & kode_perusahaan opsional.');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => 'dc2626'], 'size' => 10],
        ]);
        $sheet->mergeCells('A4:N4');

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

        if (!$zip->extractTo($extractPath)) {
            $zip->close();
            $this->cleanupTempDir($tempDir);
            return view('imports.validation', [
                'result' => $this->formatError('Gagal mengekstrak file ZIP. Pastikan file tidak rusak lalu upload ulang.'),
                'importToken' => null,
            ]);
        }

        // Step 3: Collect every spreadsheet candidate in the ZIP.
        // Office lock files ("~$data.xlsx"), macOS metadata and hidden files are
        // skipped, and shallow paths are preferred so the real workbook wins.
        $allZipNames = [];
        $excelCandidates = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false) {
                continue;
            }

            $allZipNames[] = $name;

            if ($this->isIgnoredZipEntry($name)) {
                continue;
            }

            $normalizedName = str_replace('/', DIRECTORY_SEPARATOR, $name);
            $fullPath = $extractPath . DIRECTORY_SEPARATOR . $normalizedName;
            $baseName = pathinfo($normalizedName, PATHINFO_BASENAME);
            $ext = strtolower(pathinfo($baseName, PATHINFO_EXTENSION));

            if (!in_array($ext, ['xlsx', 'xls', 'xlsm', 'xltx', 'ods'], true)) {
                continue;
            }

            $excelCandidates[] = [
                'name' => $name,
                'path' => $fullPath,
                'depth' => substr_count($name, '/'),
            ];
        }
        $zip->close();

        usort($excelCandidates, fn($a, $b) => [$a['depth'], $a['name']] <=> [$b['depth'], $b['name']]);

        if (empty($excelCandidates)) {
            $this->cleanupTempDir($tempDir);
            return view('imports.validation', [
                'result' => $this->formatError(
                    'File Excel (.xlsx/.xls) tidak ditemukan dalam ZIP.' . $this->zipContentHint($allZipNames)
                ),
                'importToken' => null,
            ]);
        }

        // Step 4: Validate using ImportService
        $validationResult = $this->importService->validateImport(
            array_column($excelCandidates, 'path'),
            $extractPath,
            $allZipNames
        );

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

        // Process import using ImportService
        $importResult = $this->importService->processImport(
            $validationResult['data'],
            $validationResult['preview'] ?? [],
            $extractPath
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

    /**
     * Entries that must never be treated as the data workbook.
     * Office keeps a "~$name.xlsx" lock file next to an open workbook and macOS
     * adds "._name.xlsx" metadata; both are common when a folder is zipped.
     */
    private function isIgnoredZipEntry(string $name): bool
    {
        $normalized = str_replace('\\', '/', $name);
        $baseName = pathinfo($normalized, PATHINFO_BASENAME);

        if ($baseName === '' || $baseName === '.DS_Store' || $baseName === 'Thumbs.db') {
            return true;
        }

        if (str_starts_with($baseName, '~$') || str_starts_with($baseName, '._')) {
            return true;
        }

        return str_contains($normalized, '__MACOSX/');
    }

    /**
     * Show what the ZIP actually contained when no workbook was found.
     */
    private function zipContentHint(array $allZipNames): string
    {
        $visible = array_values(array_filter(
            $allZipNames,
            fn($name) => !str_ends_with((string) $name, '/')
        ));

        if (empty($visible)) {
            return ' ZIP tidak berisi file apa pun.';
        }

        $sample = array_slice($visible, 0, 15);
        $hint = ' Isi ZIP: ' . implode(', ', $sample);

        if (count($visible) > count($sample)) {
            $hint .= ', ... (' . count($visible) . ' file)';
        }

        return $hint . '.';
    }

    /**
     * Format error for view.
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
     * Cleanup temporary directory.
     */
    private function cleanupTempDir(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
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
