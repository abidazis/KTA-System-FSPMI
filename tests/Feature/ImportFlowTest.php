<?php

namespace Tests\Feature;

use App\Models\Member;
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

        // Create test ZIP: Excel + photo
        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->createTestExcel([
                ['nik' => '3216221612020001', 'nama' => 'Budi Santoso', 'tempat_lahir' => 'Jakarta',
                 'tanggal_lahir' => '2020-12-16', 'alamat' => 'Jl. Merdeka 1',
                 'provinsi' => 'DKI Jakarta', 'kabupaten_kota' => 'Jakarta Selatan',
                 'kecamatan' => 'Kecamatan', 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01'],
            ]),
            'foto/3216221612020001.jpg' => $this->createTestImage(),
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
                ['nik' => '3216221612020002', 'nama' => 'Siti Rahayu', 'tempat_lahir' => 'Bandung',
                 'tanggal_lahir' => '2021-05-20', 'alamat' => 'Jl. Asia 2',
                 'provinsi' => 'Jawa Barat', 'kabupaten_kota' => 'Bandung',
                 'kecamatan' => 'Kecamatan', 'jenis_kelamin' => 'Perempuan', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01'],
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

    public function test_validate_rejects_duplicate_nik_in_database(): void
    {
        // Pre-create member with same NIK
        Member::create([
            'nik' => '3216221612020003',
            'nama' => 'Existing Member',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Alamat',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2025-01-01',
            'status' => 'active',
        ]);

        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->createTestExcel([
                ['nik' => '3216221612020003', 'nama' => 'New Member', 'tempat_lahir' => 'Surabaya',
                 'tanggal_lahir' => '1990-01-01', 'alamat' => 'Alamat',
                 'provinsi' => '', 'kabupaten_kota' => '', 'kecamatan' => '',
                 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01'],
            ]),
            'foto/3216221612020003.png' => $this->createTestImage('png'),
        ]);

        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('sudah terdaftar');

        @unlink($zipPath);
    }

    public function test_full_import_flow_saves_member_with_photo(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->createTestExcel([
                ['nik' => '3216221612020004', 'nama' => 'Ahmad Wijaya', 'tempat_lahir' => 'Semarang',
                 'tanggal_lahir' => '2020-12-16', 'alamat' => 'Jl. Pahlawan 10',
                 'provinsi' => 'Jawa Tengah', 'kabupaten_kota' => 'Semarang',
                 'kecamatan' => 'Kecamatan', 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01'],
            ]),
            'foto/3216221612020004.jpg' => $this->createTestImage('jpg'),
        ]);

        // Step 1: Validate
        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('Validasi Berhasil');
        $response->assertSeeText('Simpan');
        $response->assertSeeText('1 Data');

        // Step 2: Get token from session and process
        $token = session('import_token');
        $this->assertNotNull($token, 'Import token should be set in session');

        $response = $this->post('/import', [
            'import_token' => $token,
        ]);

        $response->assertRedirect('/members');
        $response->assertSessionHas('success', 'Berhasil mengimport 1 anggota.');

        // Step 3: Verify member was created
        $member = Member::where('nik', '3216221612020004')->first();
        $this->assertNotNull($member);
        $this->assertEquals('Ahmad Wijaya', $member->nama);
        $this->assertEquals('ready', $member->status);
        $this->assertNotNull($member->foto_path);
        $this->assertTrue(Storage::disk('local')->exists($member->foto_path));

        // Cleanup
        $member->delete();
        if ($member->foto_path && Storage::disk('local')->exists($member->foto_path)) {
            Storage::disk('local')->delete($member->foto_path);
        }
        @unlink($zipPath);
    }

    public function test_process_import_requires_valid_token(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        $response = $this->post('/import', [
            'import_token' => 'invalid_token_12345',
        ]);

        $response->assertRedirect('/import');
        $response->assertSessionHas('error');
    }

    public function test_jpeg_and_png_photos_work(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        $nikBase = '321622161202';
        foreach (['jpg', 'jpeg', 'png'] as $idx => $ext) {
            $nik = $nikBase . str_pad($idx + 6, 4, '0', STR_PAD_LEFT);
            $fileName = $nik . '.' . $ext;

            $zipPath = $this->createTestZip([
                'data.xlsx' => $this->createTestExcel([[
                    'nik' => $nik, 'nama' => 'Test ' . $ext,
                    'tempat_lahir' => 'Kota', 'tanggal_lahir' => '2020-01-01',
                    'alamat' => 'Alamat', 'provinsi' => '', 'kabupaten_kota' => '', 'kecamatan' => '',
                    'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                    'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01',
                ]]),
                'foto/' . $fileName => $this->createTestImage($ext === 'jpeg' ? 'jpeg' : $ext),
            ]);

            $response = $this->post('/import/validate', [
                'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
            ]);

            $response->assertStatus(200);
            $response->assertSeeText('1 Valid', 'Photo format .' . $ext . ' should be recognized');

            @unlink($zipPath);
        }
    }

    /**
     * Test the specific bug case: 16-digit NIK stored as NUMBER in Excel.
     * This was causing false "NIK HARUS TERDIRI DARI 15 ATAU 16 DIGIT ANGKA" errors.
     */
    public function test_sixteen_digit_nik_stored_as_number_in_excel(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        // The specific NIK from the bug report
        $nik = '3216221612020012';

        // Create Excel with NIK stored as NUMBER (not TEXT) - this is what Excel does by default
        $zipPath = $this->createTestZipWithNumericNik([
            ['nik' => $nik, 'nama' => 'Test Bug Case', 'tempat_lahir' => 'Kota',
             'tanggal_lahir' => '2020-12-16', 'alamat' => 'Jl. Test 1',
             'provinsi' => 'DKI Jakarta', 'kabupaten_kota' => 'Jakarta Selatan',
             'kecamatan' => 'Kecamatan', 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
             'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01'],
        ], $nik);

        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        // The NIK should be VALID, not rejected with "NIK HARUS TERDIRI DARI 15 ATAU 16 DIGIT ANGKA"
        $response->assertSeeText('1 Valid');
        $response->assertDontSeeText('NIK harus terdiri dari 15 atau 16 digit angka');

        @unlink($zipPath);
    }

    /**
     * Test that template CATATAN/instruction row is skipped and not treated as member data.
     * This tests the exact scenario from the bug report.
     */
    public function test_template_catan_row_is_skipped(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        $nik = '3216221612020012';

        // Create Excel matching the actual template structure:
        // Row 1: headers
        // Row 2: member data
        // Row 3: blank
        // Row 4: CATATAN note
        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->createTestExcelWithTemplateStructure([
                ['nik' => $nik, 'nama' => 'Test Member', 'tempat_lahir' => 'Kota',
                 'tanggal_lahir' => '2020-12-16', 'alamat' => 'Jl. Test 1',
                 'provinsi' => 'DKI Jakarta', 'kabupaten_kota' => 'Jakarta Selatan',
                 'kecamatan' => 'Kecamatan', 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01'],
            ], $nik),
            'foto/' . $nik . '.jpg' => $this->createTestImage('jpg'),
        ]);

        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);

        // The CATATAN row should NOT appear in errors
        $response->assertDontSeeText('CATATAN: Kolom NIK harus berisi 16 digit angka');

        // Should show 1 valid member, not "3 Total / 1 Valid / 1 Error"
        $response->assertSeeText('1 Valid');
        $response->assertDontSeeText('NIK harus terdiri dari 15 atau 16 digit angka');

        @unlink($zipPath);
    }

    /**
     * Test that instruction-like NIK with actual member data is NOT silently skipped.
     */
    public function test_invalid_nik_with_member_data_is_rejected(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        // Create Excel with invalid NIK but valid other fields
        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->createTestExcelWithNumericNik([
                ['nik' => 'ABC123', 'nama' => 'Test Member', 'tempat_lahir' => 'Kota',
                 'tanggal_lahir' => '2020-12-16', 'alamat' => 'Jl. Test 1',
                 'provinsi' => 'DKI Jakarta', 'kabupaten_kota' => 'Jakarta Selatan',
                 'kecamatan' => 'Kecamatan', 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01'],
            ], 'ABC123'),
            'foto/ABC123.jpg' => $this->createTestImage('jpg'),
        ]);

        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        // Invalid NIK should be rejected
        $response->assertSeeText('NIK harus terdiri dari 15 atau 16 digit angka');

        @unlink($zipPath);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function createTestZip(array $files): string
    {
        // Write to Laravel's private storage so UploadedFile can access it reliably
        $tempDir = storage_path('app/private/imports/test_temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $path = $tempDir . '/test_' . uniqid() . '.zip';
        $zip = new ZipArchive();
        $result = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($result !== true) {
            throw new \RuntimeException("Cannot create ZIP at $path (error $result)");
        }
        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();
        return $path;
    }

    private function createTestExcel(array $rows): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['nik', 'nama', 'tempat_lahir', 'tanggal_lahir', 'alamat',
                    'provinsi', 'kabupaten_kota', 'kecamatan', 'jenis_kelamin',
                    'agama', 'berlaku_hingga', 'tanggal_pembuatan', 'foto'];

        $sheet->fromArray($headers, null, 'A1');

        $rowNum = 2;
        foreach ($rows as $row) {
            $sheet->setCellValueExplicit('A' . $rowNum, (string) $row['nik'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $rowNum, $row['nama']);
            $sheet->setCellValue('C' . $rowNum, $row['tempat_lahir']);
            $sheet->setCellValueExplicit('D' . $rowNum, (string) $row['tanggal_lahir'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowNum, $row['alamat']);
            $sheet->setCellValue('F' . $rowNum, $row['provinsi']);
            $sheet->setCellValue('G' . $rowNum, $row['kabupaten_kota']);
            $sheet->setCellValue('H' . $rowNum, $row['kecamatan']);
            $sheet->setCellValue('I' . $rowNum, $row['jenis_kelamin']);
            $sheet->setCellValue('J' . $rowNum, $row['agama']);
            $sheet->setCellValueExplicit('K' . $rowNum, (string) $row['berlaku_hingga'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('L' . $rowNum, (string) $row['tanggal_pembuatan'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('M' . $rowNum, $row['nik'] . '.jpg');
            $rowNum++;
        }

        $tempFile = sys_get_temp_dir() . '/test_excel_' . uniqid() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);
        $content = file_get_contents($tempFile);
        @unlink($tempFile);
        return $content;
    }

    /**
     * Create test Excel with NIK stored as NUMBER (not TEXT).
     * This simulates Excel's default behavior when user enters a long number.
     */
    private function createTestExcelWithNumericNik(array $rows): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['nik', 'nama', 'tempat_lahir', 'tanggal_lahir', 'alamat',
                    'provinsi', 'kabupaten_kota', 'kecamatan', 'jenis_kelamin',
                    'agama', 'berlaku_hingga', 'tanggal_pembuatan', 'foto'];

        $sheet->fromArray($headers, null, 'A1');

        $rowNum = 2;
        foreach ($rows as $row) {
            // Store NIK as NUMBER - this is the key difference!
            // In real Excel, if user types a long number without apostrophe prefix,
            // Excel stores it as a float, and PhpSpreadsheet reads it as float
            $sheet->setCellValue('A' . $rowNum, $row['nik']); // This stores as NUMBER
            $sheet->setCellValue('B' . $rowNum, $row['nama']);
            $sheet->setCellValue('C' . $rowNum, $row['tempat_lahir']);
            $sheet->setCellValueExplicit('D' . $rowNum, (string) $row['tanggal_lahir'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowNum, $row['alamat']);
            $sheet->setCellValue('F' . $rowNum, $row['provinsi']);
            $sheet->setCellValue('G' . $rowNum, $row['kabupaten_kota']);
            $sheet->setCellValue('H' . $rowNum, $row['kecamatan']);
            $sheet->setCellValue('I' . $rowNum, $row['jenis_kelamin']);
            $sheet->setCellValue('J' . $rowNum, $row['agama']);
            $sheet->setCellValueExplicit('K' . $rowNum, (string) $row['berlaku_hingga'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('L' . $rowNum, (string) $row['tanggal_pembuatan'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('M' . $rowNum, $row['nik'] . '.jpg');
            $rowNum++;
        }

        $tempFile = sys_get_temp_dir() . '/test_excel_' . uniqid() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);
        $content = file_get_contents($tempFile);
        @unlink($tempFile);
        return $content;
    }

    /**
     * Create test ZIP with Excel containing NIK stored as NUMBER.
     */
    private function createTestZipWithNumericNik(array $rows, string $nik): string
    {
        $tempDir = storage_path('app/private/imports/test_temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $path = $tempDir . '/test_' . uniqid() . '.zip';
        $zip = new ZipArchive();
        $result = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($result !== true) {
            throw new \RuntimeException("Cannot create ZIP at $path (error $result)");
        }

        // Add Excel with NIK as NUMBER (simulating Excel default behavior)
        $zip->addFromString('data.xlsx', $this->createTestExcelWithNumericNik($rows));

        // Add photo with NIK as filename
        $zip->addFromString('foto/' . $nik . '.jpg', $this->createTestImage('jpg'));

        $zip->close();
        return $path;
    }

    /**
     * Create test Excel with template structure including CATATAN row.
     * This matches the actual template Excel structure:
     * Row 1: Headers
     * Row 2: Member data
     * Row 3: Blank
     * Row 4: CATATAN instruction
     */
    private function createTestExcelWithTemplateStructure(array $rows): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['nik', 'nama', 'tempat_lahir', 'tanggal_lahir', 'alamat',
                    'provinsi', 'kabupaten_kota', 'kecamatan', 'jenis_kelamin',
                    'agama', 'berlaku_hingga', 'tanggal_pembuatan', 'foto'];

        $sheet->fromArray($headers, null, 'A1');

        $rowNum = 2;

        // Row 2: Member data
        foreach ($rows as $row) {
            $sheet->setCellValueExplicit('A' . $rowNum, (string) $row['nik'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $rowNum, $row['nama']);
            $sheet->setCellValue('C' . $rowNum, $row['tempat_lahir']);
            $sheet->setCellValueExplicit('D' . $rowNum, (string) $row['tanggal_lahir'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowNum, $row['alamat']);
            $sheet->setCellValue('F' . $rowNum, $row['provinsi']);
            $sheet->setCellValue('G' . $rowNum, $row['kabupaten_kota']);
            $sheet->setCellValue('H' . $rowNum, $row['kecamatan']);
            $sheet->setCellValue('I' . $rowNum, $row['jenis_kelamin']);
            $sheet->setCellValue('J' . $rowNum, $row['agama']);
            $sheet->setCellValueExplicit('K' . $rowNum, (string) $row['berlaku_hingga'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('L' . $rowNum, (string) $row['tanggal_pembuatan'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('M' . $rowNum, $row['nik'] . '.jpg');
            $rowNum++;
        }

        // Row 3: Blank row (this will be skipped by empty row check)

        // Row 4: CATATAN instruction row - same as actual template
        $rowNum = 4;
        $sheet->setCellValueExplicit('A' . $rowNum, 'CATATAN: Kolom NIK harus berisi 16 digit angka. Jangan rubah format sel.', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->getStyle('A' . $rowNum)->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => 'dc2626'], 'size' => 10],
        ]);
        $sheet->mergeCells('A' . $rowNum . ':M' . $rowNum);

        $tempFile = sys_get_temp_dir() . '/test_excel_' . uniqid() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);
        $content = file_get_contents($tempFile);
        @unlink($tempFile);
        return $content;
    }

    private function createTestImage(string $format = 'jpg'): string
    {
        // Minimal 1x1 PNG
        if ($format === 'png' || $format === 'jpeg' || $format === 'jpg') {
            return base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8Dw' .
                'HwAFBQIAX8jx0gAAAABJRU5ErkJggg=='
            );
        }
        return base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8Dw' .
            'HwAFBQIAX8jx0gAAAABJRU5ErkJggg=='
        );
    }
}
