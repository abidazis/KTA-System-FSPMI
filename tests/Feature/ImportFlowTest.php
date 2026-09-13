<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Member;
use App\Models\MemberNumberFormula;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use ZipArchive;

class ImportFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $tempDir = storage_path('app/private/imports/test_temp');
        if (is_dir($tempDir)) {
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($it as $file) {
                if ($file->isDir()) { @rmdir($file->getPathname()); } else { @unlink($file->getPathname()); }
            }
            @rmdir($tempDir);
        }
        mkdir($tempDir, 0755, true);
        Permission::create(['name' => 'import']);

        // Create a default active formula for all tests
        Company::create(['kode' => 'ABC', 'name' => 'PT ABC Indonesia', 'is_active' => true]);
        Company::create(['kode' => 'XYZ', 'name' => 'PT XYZ Indonesia', 'is_active' => true]);

        MemberNumberFormula::create([
            'name' => 'Formula Test',
            'prefix' => 'FSPMI',
            'separator' => '-',
            'include_company_code' => true,
            'sequence_digits' => 4,
            'is_active' => true,
            'created_by' => 1,
        ]);
    }

    public function test_validate_import_requires_auth(): void
    {
        $response = $this->post('/import/validate', []);
        $response->assertRedirect('/login');
    }

    public function test_validate_import_shows_error_for_empty_zip(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        $zipPath = storage_path('app/private/imports/test_empty.zip');
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('empty.txt', 'not excel');
        $zip->close();

        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'test.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('File Excel (.xlsx/.xls) tidak ditemukan');
        @unlink($zipPath);
    }

    public function test_validate_import_success_with_valid_excel_and_photo(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        // Create test ZIP: Excel + photo (no nik column - it's auto-generated)
        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->createTestExcel([
                ['nama' => 'Budi Santoso', 'tempat_lahir' => 'Jakarta',
                 'tanggal_lahir' => '2020-12-16', 'alamat' => 'Jl. Merdeka 1',
                 'provinsi' => 'DKI Jakarta', 'kabupaten_kota' => 'Jakarta Selatan',
                 'kecamatan' => 'Kecamatan', 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01',
                 'foto' => 'BUDI-0001.jpg', 'kode_perusahaan' => 'ABC', 'nama_perusahaan' => 'PT ABC Indonesia'],
            ]),
            'foto/BUDI-0001.jpg' => $this->createTestImage(),
        ]);

        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('Validasi Berhasil');
        $response->assertSeeText('1 Valid');

        @unlink($zipPath);
    }

    public function test_validate_rejects_missing_photo(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->createTestExcel([
                ['nama' => 'Siti Rahayu', 'tempat_lahir' => 'Bandung',
                 'tanggal_lahir' => '2021-05-20', 'alamat' => 'Jl. Asia 2',
                 'provinsi' => 'Jawa Barat', 'kabupaten_kota' => 'Bandung',
                 'kecamatan' => 'Kecamatan', 'jenis_kelamin' => 'Perempuan', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01',
                 'foto' => 'SITI-0001.jpg', 'kode_perusahaan' => 'ABC'],
            ]),
            // NO photo
        ]);

        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('FOTO TIDAK DITEMUKAN');
        $response->assertDontSeeText('Validasi Berhasil');
    }

    public function test_validate_rejects_missing_required_columns(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        // Create Excel with data but missing 'nama' column
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Anggota');

        // Headers (WITHOUT 'nama')
        $headers = ['nik', 'tempat_lahir', 'alamat', 'jenis_kelamin', 'agama'];
        $sheet->fromArray([$headers], null, 'A1');

        // Data row (missing 'nama')
        $sheet->fromArray([['123', 'Jakarta', 'Alamat Test', 'Laki-laki', 'Islam']], null, 'A2');

        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->saveSpreadsheet($spreadsheet),
        ]);

        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        $response->assertSeeText("Kolom 'nama' tidak ditemukan dalam Excel");

        @unlink($zipPath);
    }

    public function test_import_without_active_formula_fails(): void
    {
        // Deactivate all formulas
        MemberNumberFormula::query()->update(['is_active' => false]);

        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->createTestExcel([
                ['nama' => 'Test User', 'tempat_lahir' => 'Jakarta',
                 'tanggal_lahir' => '2020-01-01', 'alamat' => 'Alamat',
                 'provinsi' => 'DKI Jakarta', 'kabupaten_kota' => 'Jakarta',
                 'kecamatan' => 'Kecamatan', 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01'],
            ]),
        ]);

        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('Formula nomor anggota belum dikonfigurasi');

        @unlink($zipPath);
    }

    /**
     * Helper: Create a test Excel file with member data (without nik column).
     */
    protected function createTestExcel(array $rows): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Anggota');

        // Headers (without nik - auto-generated)
        $headers = [
            'nama', 'tempat_lahir', 'tanggal_lahir', 'alamat',
            'provinsi', 'kabupaten_kota', 'kecamatan', 'jenis_kelamin',
            'agama', 'berlaku_hingga', 'tanggal_pembuatan', 'foto',
            'kode_perusahaan', 'nama_perusahaan'
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Add data rows
        foreach ($rows as $row) {
            $data = [];
            foreach ($headers as $header) {
                $data[] = $row[$header] ?? '';
            }
            $sheet->fromArray([$data], null, 'A' . ($sheet->getHighestRow() + 1));
        }

        return $this->saveSpreadsheet($spreadsheet);
    }

    /**
     * Helper: Save spreadsheet to temp file and return path.
     */
    protected function saveSpreadsheet(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'excel_') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);
        return $tempPath;
    }

    /**
     * Helper: Create test ZIP file.
     */
    protected function createTestZip(array $files): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'zip_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($files as $path => $content) {
            // Ensure directory exists
            $dir = dirname($path);
            if ($dir !== '.' && !$zip->locateName($dir . '/')) {
                $zip->addEmptyDir($dir);
            }
            // If content is a file path (string), read the file; otherwise use content directly
            if (is_string($content) && file_exists($content)) {
                $zip->addFromString($path, file_get_contents($content));
            } else {
                $zip->addFromString($path, $content);
            }
        }

        $zip->close();
        return $tempPath;
    }

    /**
     * Helper: Create test image.
     */
    protected function createTestImage(string $format = 'jpg'): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'img_') . '.' . $format;

        // Create a simple valid image
        $image = imagecreatetruecolor(100, 120);
        $bg = imagecolorallocate($image, 200, 200, 200);
        $text = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $bg);
        imagestring($image, 3, 10, 50, 'TEST', $text);

        if ($format === 'png') {
            imagepng($image, $tempPath);
        } else {
            imagejpeg($image, $tempPath, 90);
        }
        imagedestroy($image);

        return $tempPath;
    }
}
