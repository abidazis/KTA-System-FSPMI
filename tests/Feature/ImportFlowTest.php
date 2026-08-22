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
