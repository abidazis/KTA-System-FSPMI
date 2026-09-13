<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Member;
use App\Models\MemberNumberFormula;
use App\Models\MemberPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use ZipArchive;

class ImportHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $tempDir = storage_path('app/private/imports/test_harden');
        if (is_dir($tempDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                if ($file->isDir()) { @rmdir($file->getPathname()); } else { @unlink($file->getPathname()); }
            }
        } else {
            mkdir($tempDir, 0755, true);
        }
        Permission::create(['name' => 'import']);

        // Create default active formula
        MemberNumberFormula::create([
            'name' => 'Formula Test',
            'prefix' => 'FSPMI',
            'separator' => '-',
            'include_company_code' => true,
            'sequence_digits' => 4,
            'is_active' => true,
            'created_by' => 1,
        ]);

        // Pre-create the company so it's available during validation preview
        Company::create([
            'kode' => 'ABC',
            'name' => 'PT ABC Indonesia',
            'is_active' => true,
        ]);
    }

    /**
     * Test: valid import with company data succeeds
     */
    public function test_import_with_company_data_succeeds(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->createTestExcel([
                ['nama' => 'Andi Wijaya', 'tempat_lahir' => 'Jakarta',
                 'tanggal_lahir' => '2020-12-16', 'alamat' => 'Jl. Merdeka 1',
                 'provinsi' => 'DKI Jakarta', 'kabupaten_kota' => 'Jakarta Selatan',
                 'kecamatan' => 'Kecamatan', 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01',
                 'kode_perusahaan' => 'ABC', 'nama_perusahaan' => 'PT ABC Indonesia',
                 'foto' => 'ABC-0001.jpg'],
            ]),
            'foto/ABC-0001.jpg' => $this->createTestImage('member_andi'),
        ]);

        // Validate
        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('Validasi Berhasil');

        // Process
        $token = session('import_token');
        $response = $this->post('/import', ['import_token' => $token]);
        $response->assertRedirect('/members');
        $response->assertSessionHas('success');

        // Verify member was created with auto-generated nomor anggota
        $member = Member::where('nama', 'Andi Wijaya')->first();
        $this->assertNotNull($member);
        $this->assertNotNull($member->nik);
        $this->assertEquals('Andi Wijaya', $member->nama);
        // Should match formula pattern: FSPMI-ABC-0001
        $this->assertMatchesRegularExpression('/^FSPMI-ABC-\d{4}$/', $member->nik);

        // Verify company was created
        $company = Company::where('kode', 'ABC')->first();
        $this->assertNotNull($company);
        $this->assertEquals('PT ABC Indonesia', $company->name);

        // Verify member has company
        $this->assertEquals($company->id, $member->company_id);

        // Verify photo hash was stored
        $photo = MemberPhoto::where('member_id', $member->id)->first();
        $this->assertNotNull($photo);
        $this->assertNotNull($photo->photo_hash);

        @unlink($zipPath);
    }

    /**
     * Test: duplicate photo within batch is rejected
     */
    public function test_duplicate_photo_in_batch_is_rejected(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        // Same photo content for two different members (same file content)
        $samePhotoContent = $this->createTestImage('shared_batch_photo');

        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->createTestExcel([
                ['nama' => 'Budi Santoso', 'tempat_lahir' => 'Jakarta',
                 'tanggal_lahir' => '2020-12-16', 'alamat' => 'Jl. Merdeka 1',
                 'provinsi' => '', 'kabupaten_kota' => '', 'kecamatan' => '',
                 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01',
                 'foto' => '256-0001.jpg'],
                ['nama' => 'Siti Rahayu', 'tempat_lahir' => 'Bandung',
                 'tanggal_lahir' => '2020-12-16', 'alamat' => 'Jl. Merdeka 2',
                 'provinsi' => '', 'kabupaten_kota' => '', 'kecamatan' => '',
                 'jenis_kelamin' => 'Perempuan', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01',
                 'foto' => '256-0002.jpg'],
            ]),
            // Both files in ZIP have same content → duplicate detection
            'foto/256-0001.jpg' => $samePhotoContent,
            'foto/256-0002.jpg' => $samePhotoContent,
        ]);

        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        // Should show error about duplicate photo
        $response->assertSeeText('DUPLICATE');

        @unlink($zipPath);
    }

    /**
     * Test: duplicate photo with existing database photo is rejected
     */
    public function test_duplicate_photo_with_existing_is_rejected(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        // Create existing company
        $company = Company::create(['kode' => 'TST', 'name' => 'Test', 'is_active' => true]);

        // Create existing member with existing NIK format
        $existingMember = Member::create([
            'nik' => '3216221612029004',
            'nama' => 'Existing Member',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Alamat',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2025-01-01',
            'status' => 'ready',
            'company_id' => $company->id,
        ]);

        // Use a fixed photo content for both existing and import — same content = same hash
        $sharedPhotoContent = $this->createTestImage('shared_photo_content');

        // Create photo for existing member
        $photoPath = 'members/photos/EXISTING-0001.jpg';
        Storage::disk('local')->put($photoPath, $sharedPhotoContent);
        $photoHash = hash('sha256', $sharedPhotoContent);

        MemberPhoto::create([
            'member_id' => $existingMember->id,
            'photo_path' => $photoPath,
            'photo_hash' => $photoHash,
        ]);

        // Try to import same photo content with different member
        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->createTestExcel([
                ['nama' => 'New Member', 'tempat_lahir' => 'Surabaya',
                 'tanggal_lahir' => '2020-12-16', 'alamat' => 'Jl. Baru 1',
                 'provinsi' => '', 'kabupaten_kota' => '', 'kecamatan' => '',
                 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01',
                 'foto' => 'TST-0001.jpg'],
            ]),
            'foto/TST-0001.jpg' => $sharedPhotoContent, // Same content as existing!
        ]);

        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        // Should show error about duplicate with existing
        $response->assertSeeText('DUPLICATE');
        $response->assertSeeText('3216221612029004'); // Existing member NIK should be shown

        @unlink($zipPath);
    }

    /**
     * Test: company with same kode but different name is rejected
     */
    public function test_company_kode_conflict_is_rejected(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        // Create existing company with kode ABC
        Company::create([
            'kode' => 'ABC',
            'name' => 'PT ABC Indonesia',
            'is_active' => true,
        ]);

        // Try to import with same kode but different name
        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->createTestExcel([
                ['nama' => 'New Member', 'tempat_lahir' => 'Jakarta',
                 'tanggal_lahir' => '2020-12-16', 'alamat' => 'Jl. Baru 1',
                 'provinsi' => '', 'kabupaten_kota' => '', 'kecamatan' => '',
                 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01',
                 'kode_perusahaan' => 'ABC', 'nama_perusahaan' => 'PT ABC Manufacturing',
                 'foto' => 'ABC-0001.jpg'], // Different name!
            ]),
            'foto/ABC-0001.jpg' => $this->createTestImage('member_conflict'),
        ]);

        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        // Should show error about company conflict
        $response->assertSeeText('berbeda');

        @unlink($zipPath);
    }

    /**
     * Test: photo naming uses company code + sequence
     */
    public function test_photo_naming_uses_company_code_and_sequence(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        // Create company with specific kode
        Company::create([
            'kode' => 'XYZ',
            'name' => 'PT XYZ Corporation',
            'is_active' => true,
        ]);

        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->createTestExcel([
                ['nama' => 'Andi', 'tempat_lahir' => 'Jakarta',
                 'tanggal_lahir' => '2020-12-16', 'alamat' => 'Alamat 1',
                 'provinsi' => '', 'kabupaten_kota' => '', 'kecamatan' => '',
                 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01',
                 'kode_perusahaan' => 'XYZ', 'nama_perusahaan' => 'PT XYZ Corporation',
                 'foto' => 'XYZ-0001.jpg'],
                ['nama' => 'Budi', 'tempat_lahir' => 'Bandung',
                 'tanggal_lahir' => '2020-12-16', 'alamat' => 'Alamat 2',
                 'provinsi' => '', 'kabupaten_kota' => '', 'kecamatan' => '',
                 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01',
                 'kode_perusahaan' => 'XYZ', 'nama_perusahaan' => 'PT XYZ Corporation',
                 'foto' => 'XYZ-0002.jpg'],
            ]),
            'foto/XYZ-0001.jpg' => $this->createTestImage('member_1'),
            'foto/XYZ-0002.jpg' => $this->createTestImage('member_2'),
        ]);

        // Validate
        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('Validasi Berhasil');

        // Check preview shows correct photo names
        $response->assertSeeText('XYZ-');
        $response->assertSeeText('.jpg');

        // Check preview shows generated nomor anggota
        $response->assertSeeText('FSPMI-XYZ-');

        @unlink($zipPath);
    }

    /**
     * Test: photo sequence continues from existing photos
     */
    public function test_photo_sequence_continues_from_existing(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        // Create company and existing member with photo
        $company = Company::create([
            'kode' => 'SEQ',
            'name' => 'Sequence Test Company',
            'is_active' => true,
        ]);

        // Create existing member with old format NIK
        $existingMember = Member::create([
            'nik' => '3216221612029009',
            'nama' => 'Existing Member',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Alamat',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2025-01-01',
            'status' => 'ready',
            'company_id' => $company->id,
        ]);

        // Create photo record with sequence number
        $sharedContent = $this->createTestImage('seq_test_photo');
        Storage::disk('local')->put('members/photos/SEQ-0005.jpg', $sharedContent);
        MemberPhoto::create([
            'member_id' => $existingMember->id,
            'photo_path' => 'members/photos/SEQ-0005.jpg',
            'photo_hash' => hash('sha256', $sharedContent),
        ]);

        // Import new member with same company
        $zipPath = $this->createTestZip([
            'data.xlsx' => $this->createTestExcel([
                ['nama' => 'New Member', 'tempat_lahir' => 'Jakarta',
                 'tanggal_lahir' => '2020-12-16', 'alamat' => 'Alamat',
                 'provinsi' => '', 'kabupaten_kota' => '', 'kecamatan' => '',
                 'jenis_kelamin' => 'Laki-laki', 'agama' => 'Islam',
                 'berlaku_hingga' => '2031-12-31', 'tanggal_pembuatan' => '2026-01-01',
                 'kode_perusahaan' => 'SEQ', 'nama_perusahaan' => 'Sequence Test Company',
                 'foto' => 'SEQ-0006.jpg'],
            ]),
            'foto/SEQ-0006.jpg' => $this->createTestImage('seq_test_photo_different'),
        ]);

        // Validate
        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        // Should show SEQ-0006 (continuing from SEQ-0005)
        $response->assertSeeText('SEQ-0006');

        @unlink($zipPath);
    }

    /**
     * Test: partial import saves valid rows only
     */
    public function test_partial_import_saves_valid_rows_only(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        $countBefore = Member::count();

        // Create 99 valid rows + 1 invalid row (missing required field)
        $rows = [];
        for ($i = 0; $i < 99; $i++) {
            $rows[] = [
                'nama' => "Member $i",
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '2020-12-16',
                'alamat' => 'Alamat',
                'provinsi' => '',
                'kabupaten_kota' => '',
                'kecamatan' => '',
                'jenis_kelamin' => 'Laki-laki',
                'agama' => 'Islam',
                'berlaku_hingga' => '2031-12-31',
                'tanggal_pembuatan' => '2026-01-01',
                'kode_perusahaan' => 'ABC',
                'nama_perusahaan' => 'PT ABC Indonesia',
                'foto' => "FOTO-" . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . ".jpg",
            ];
        }

        // Add invalid row (missing required field 'nama')
        $rows[] = [
            'nama' => '', // Empty name - invalid!
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2020-12-16',
            'alamat' => 'Alamat',
            'provinsi' => '',
            'kabupaten_kota' => '',
            'kecamatan' => '',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2031-12-31',
            'tanggal_pembuatan' => '2026-01-01',
            'kode_perusahaan' => 'ABC',
            'nama_perusahaan' => 'PT ABC Indonesia',
            'foto' => 'FOTO-0100.jpg',
        ];

        $files = ['data.xlsx' => $this->createTestExcel($rows)];

        // Add photos for all 100 rows
        for ($i = 0; $i < 100; $i++) {
            $fotoName = $rows[$i]['foto'];
            $files["foto/{$fotoName}"] = $this->createTestImage("row_{$i}");
        }

        $zipPath = $this->createTestZip($files);

        // Validate — should show error
        $response = $this->post('/import/validate', [
            'file' => new UploadedFile($zipPath, 'import.zip', 'application/zip', null, true),
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('Nama kosong');

        // Process import with valid token
        $token = session('import_token');
        $response = $this->post('/import', ['import_token' => $token]);

        // Should succeed and redirect to members
        $response->assertRedirect('/members');

        // 99 valid rows should be saved; 1 invalid row rejected
        $this->assertEquals(99, Member::count() - $countBefore);

        @unlink($zipPath);
    }

    /**
     * Test: commit without valid token is rejected
     */
    public function test_commit_without_valid_token_is_rejected(): void
    {
        $user = User::factory()->create()->givePermissionTo('import');
        $this->actingAs($user);

        $response = $this->post('/import', ['import_token' => 'invalid_token_12345']);

        $response->assertRedirect('/import');
        $response->assertSessionHas('error');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function createTestZip(array $files): string
    {
        $tempDir = storage_path('app/private/imports/test_harden');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $path = $tempDir . '/test_' . uniqid() . '.zip';
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
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

        // Headers without nik (auto-generated)
        $headers = ['nama', 'tempat_lahir', 'tanggal_lahir', 'alamat',
                    'provinsi', 'kabupaten_kota', 'kecamatan', 'jenis_kelamin',
                    'agama', 'berlaku_hingga', 'tanggal_pembuatan', 'foto',
                    'kode_perusahaan', 'nama_perusahaan'];

        $sheet->fromArray($headers, null, 'A1');

        $rowNum = 2;
        foreach ($rows as $row) {
            $sheet->setCellValue('A' . $rowNum, $row['nama']);
            $sheet->setCellValue('B' . $rowNum, $row['tempat_lahir']);
            $sheet->setCellValueExplicit('C' . $rowNum, (string) $row['tanggal_lahir'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $rowNum, $row['alamat']);
            $sheet->setCellValue('E' . $rowNum, $row['provinsi'] ?? '');
            $sheet->setCellValue('F' . $rowNum, $row['kabupaten_kota'] ?? '');
            $sheet->setCellValue('G' . $rowNum, $row['kecamatan'] ?? '');
            $sheet->setCellValue('H' . $rowNum, $row['jenis_kelamin']);
            $sheet->setCellValue('I' . $rowNum, $row['agama']);
            $sheet->setCellValueExplicit('J' . $rowNum, (string) $row['berlaku_hingga'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('K' . $rowNum, (string) $row['tanggal_pembuatan'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('L' . $rowNum, $row['foto'] ?? '');
            $sheet->setCellValue('M' . $rowNum, $row['kode_perusahaan'] ?? '');
            $sheet->setCellValue('N' . $rowNum, $row['nama_perusahaan'] ?? '');
            $rowNum++;
        }

        $tempFile = sys_get_temp_dir() . '/test_excel_' . uniqid() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);
        $content = file_get_contents($tempFile);
        @unlink($tempFile);
        return $content;
    }

    private function createTestImage(string $content = ''): string
    {
        // Create unique content based on input string to avoid accidental duplicates
        return base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8' .
            ($content ? md5($content) : 'z8DwHwAFBQIAX8jx0g') .
            'AAAABJRU5ErkJggg=='
        );
    }
}
