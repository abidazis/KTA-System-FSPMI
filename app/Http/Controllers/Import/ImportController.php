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
        return response()->download($templatePath, 'template_import_anggota.xlsx');
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
        $sheet->getColumnDimension('A')->setWidth(22); // nik
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
        $sheet->getColumnDimension('N')->setWidth(15); // kode_perusahaan (NEW)
        $sheet->getColumnDimension('O')->setWidth(30); // nama_perusahaan (NEW)

        // Header row styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '1e40af']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);

        // Headers (including new company columns)
        $headers = [
            'nik', 'nama', 'tempat_lahir', 'tanggal_lahir', 'alamat',
            'provinsi', 'kabupaten_kota', 'kecamatan', 'jenis_kelamin',
            'agama', 'berlaku_hingga', 'tanggal_pembuatan', 'foto',
            'kode_perusahaan', 'nama_perusahaan'
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Example data row
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
        $sheet->setCellValue('N2', 'ABC'); // kode_perusahaan (NEW)
        $sheet->setCellValue('O2', 'PT ABC Indonesia'); // nama_perusahaan (NEW)

        // Add note about NIK in cell A4
        $sheet->setCellValue('A4', 'CATATAN: Kolom NIK harus berisi 16 digit angka. Jangan rubah format sel. Kolom kode_perusahaan dan nama_perusahaan opsional.');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => 'dc2626'], 'size' => 10],
        ]);
        $sheet->mergeCells('A4:O4');

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

        // Step 3: Find Excel file
        $excelFile = null;
        $allZipNames = [];

        $zip = new ZipArchive;
        if ($zip->open($zipPath) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                $allZipNames[] = $name;
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

        // Step 4: Validate using ImportService
        $validationResult = $this->importService->validateImport($excelFile, $extractPath, $allZipNames);

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
