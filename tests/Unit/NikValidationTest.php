<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\MemberNumberFormula;
use App\Services\ImportService;
use App\Services\MemberNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NikValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test normalizeNik with string values (backward compatibility).
     */
    public function test_normalize_nik_with_string(): void
    {
        // Clean string should return as-is
        $this->assertEquals('3216221612020012', ImportService::normalizeNik('3216221612020012'));
        $this->assertEquals('123456789012345', ImportService::normalizeNik('123456789012345'));

        // String with whitespace should be trimmed
        $this->assertEquals('3216221612020012', ImportService::normalizeNik(' 3216221612020012 '));
        $this->assertEquals('3216221612020012', ImportService::normalizeNik("\t3216221612020012\n"));
    }

    /**
     * Test normalizeNik with float values (Excel numeric).
     */
    public function test_normalize_nik_with_float(): void
    {
        // Float representing a 16-digit NIK
        $floatNik = 3216221612020012.0;
        $this->assertEquals('3216221612020012', ImportService::normalizeNik($floatNik));

        // Float representing a 15-digit NIK
        $floatNik15 = 123456789012345.0;
        $this->assertEquals('123456789012345', ImportService::normalizeNik($floatNik15));
    }

    /**
     * Test normalizeNik with integer values.
     */
    public function test_normalize_nik_with_integer(): void
    {
        $this->assertEquals('3216221612020012', ImportService::normalizeNik(3216221612020012));
        $this->assertEquals('123456789012345', ImportService::normalizeNik(123456789012345));
    }

    /**
     * Test normalizeNik with null/empty values.
     */
    public function test_normalize_nik_with_null_or_empty(): void
    {
        $this->assertEquals('', ImportService::normalizeNik(null));
        $this->assertEquals('', ImportService::normalizeNik(''));
        $this->assertEquals('', ImportService::normalizeNik('   '));
    }

    /**
     * Test that isValidNik now returns true for any value (deprecated).
     * Member numbers are now auto-generated, so no validation is needed.
     */
    public function test_is_valid_nik_returns_true_for_any_value(): void
    {
        // All values return true since member numbers are now auto-generated
        $this->assertTrue(ImportService::isValidNik(''));
        $this->assertTrue(ImportService::isValidNik('   '));
        $this->assertTrue(ImportService::isValidNik('12345678901234')); // 14 digits
        $this->assertTrue(ImportService::isValidNik('1234567890123456')); // 16 digits
        $this->assertTrue(ImportService::isValidNik('FSPMI-ABC-0001')); // New format
        $this->assertTrue(ImportService::isValidNik('ABC123')); // Random string
        $this->assertTrue(ImportService::isValidNik('32162216120200A2')); // With letters
    }

    /**
     * Test isInstructionRow detection for template CATATAN rows.
     * Now checks 'nama' field instead of 'nik'.
     */
    public function test_is_instruction_row_detection(): void
    {
        // Create service instance
        $service = new ImportService();

        // Use reflection to access private method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('isInstructionRow');
        $method->setAccessible(true);

        // CASE 1: CATATAN row with empty fields - should be detected as instruction row
        $catatanRow = [
            'nama' => 'CATATAN: Nomor anggota dibuat otomatis oleh sistem.',
            'tempat_lahir' => '',
            'alamat' => '',
            'jenis_kelamin' => '',
            'agama' => '',
            'berlaku_hingga' => '',
            'tanggal_pembuatan' => '',
            'provinsi' => '',
            'kabupaten_kota' => '',
            'kecamatan' => '',
            'foto' => '',
        ];
        $this->assertTrue($method->invoke($service, $catatanRow), 'CATATAN row with empty fields should be detected as instruction');

        // CASE 2: CATATAN row but with data in other fields - should NOT be instruction row
        $catatanWithData = [
            'nama' => 'CATATAN: some note',
            'tempat_lahir' => 'Jakarta',  // Has actual data
            'alamat' => '',
            'jenis_kelamin' => '',
            'agama' => '',
            'berlaku_hingga' => '',
            'tanggal_pembuatan' => '',
            'provinsi' => '',
            'kabupaten_kota' => '',
            'kecamatan' => '',
            'foto' => '',
        ];
        $this->assertFalse($method->invoke($service, $catatanWithData), 'CATATAN row with actual data should NOT be detected as instruction');

        // CASE 3: Valid data row - should NOT be instruction row
        $validRow = [
            'nama' => 'John Doe',
            'tempat_lahir' => 'Jakarta',
            'alamat' => 'Jl. Test',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2031-12-31',
            'tanggal_pembuatan' => '2026-01-01',
            'provinsi' => 'DKI Jakarta',
            'kabupaten_kota' => 'Jakarta Selatan',
            'kecamatan' => 'Kecamatan',
            'foto' => 'ABC-0001.jpg',
        ];
        $this->assertFalse($method->invoke($service, $validRow), 'Valid data row should NOT be detected as instruction');

        // CASE 4: Case insensitive - lowercase "catatan:"
        $lowercaseCatatan = [
            'nama' => 'catatan: this is a note',
            'tempat_lahir' => '',
            'alamat' => '',
            'jenis_kelamin' => '',
            'agama' => '',
            'berlaku_hingga' => '',
            'tanggal_pembuatan' => '',
            'provinsi' => '',
            'kabupaten_kota' => '',
            'kecamatan' => '',
            'foto' => '',
        ];
        $this->assertTrue($method->invoke($service, $lowercaseCatatan), 'Lowercase "catatan:" should also be detected');

        // CASE 5: Row without CATATAN prefix - should NOT be instruction row
        $notCatatanRow = [
            'nama' => 'John Doe',
            'tempat_lahir' => '',
            'alamat' => '',
            'jenis_kelamin' => '',
            'agama' => '',
            'berlaku_hingga' => '',
            'tanggal_pembuatan' => '',
            'provinsi' => '',
            'kabupaten_kota' => '',
            'kecamatan' => '',
            'foto' => '',
        ];
        $this->assertFalse($method->invoke($service, $notCatatanRow), 'Row without CATATAN prefix should NOT be detected as instruction');
    }

    /**
     * Test that member number generator works correctly.
     */
    public function test_member_number_generator_requires_active_formula(): void
    {
        // Ensure no active formula exists
        MemberNumberFormula::where('is_active', true)->update(['is_active' => false]);

        $generator = new MemberNumberGenerator();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Nomor anggota belum dapat dibuat');

        $generator->generate();
    }

    /**
     * Test member number generation with active formula.
     */
    public function test_member_number_generator_with_active_formula(): void
    {
        // Create a company
        $company = Company::create([
            'kode' => 'ABC',
            'name' => 'Test Company',
            'is_active' => true,
        ]);

        // Create and activate a formula
        $formula = MemberNumberFormula::create([
            'name' => 'Test Formula',
            'prefix' => 'FSPMI',
            'separator' => '-',
            'include_company_code' => true,
            'sequence_digits' => 4,
            'is_active' => true,
            'created_by' => 1,
        ]);

        try {
            $generator = new MemberNumberGenerator();

            // Generate a number
            $number = $generator->generate($company->id);

            // Should match the formula pattern
            $this->assertMatchesRegularExpression('/^FSPMI-[A-Z]+-\d{4}$/', $number);
            $this->assertStringEndsWith('-0001', $number);
        } finally {
            // Cleanup
            $formula->delete();
        }
    }

    /**
     * Test: Member number generator is ONLY source of truth.
     * No other mechanism should generate member numbers.
     */
    public function test_generator_is_single_source_of_truth(): void
    {
        // Create a company
        $company = Company::create([
            'kode' => 'ABC',
            'name' => 'Test Company',
            'is_active' => true,
        ]);

        // Create and activate a formula
        $formula = MemberNumberFormula::create([
            'name' => 'Test Formula',
            'prefix' => 'FSPMI',
            'separator' => '-',
            'include_company_code' => true,
            'sequence_digits' => 4,
            'is_active' => true,
            'created_by' => 1,
        ]);

        $generator = new MemberNumberGenerator();

        // First member
        $num1 = $generator->generate($company->id);
        $this->assertEquals('FSPMI-ABC-0001', $num1);

        // Second member
        $num2 = $generator->generate($company->id);
        $this->assertEquals('FSPMI-ABC-0002', $num2);

        // Batch generation
        $batch = $generator->generateBatch([$company->id, $company->id]);
        $this->assertCount(2, $batch);
        $this->assertEquals('FSPMI-ABC-0003', $batch[0]);
        $this->assertEquals('FSPMI-ABC-0004', $batch[1]);

        // Verify all numbers are unique
        $allNumbers = [$num1, $num2, $batch[0], $batch[1]];
        $this->assertEquals(count($allNumbers), count(array_unique($allNumbers)));
    }

    /**
     * Test: Admin formula determines member number format.
     */
    public function test_admin_formula_controls_format(): void
    {
        // Create a company
        $company = Company::create([
            'kode' => 'XYZ',
            'name' => 'Test Company',
            'is_active' => true,
        ]);

        // Create formula WITHOUT company code
        $formula1 = MemberNumberFormula::create([
            'name' => 'Global Format',
            'prefix' => 'GLOBAL',
            'separator' => '-',
            'include_company_code' => false, // No company code
            'sequence_digits' => 5,
            'is_active' => true,
            'created_by' => 1,
        ]);

        $generator = new MemberNumberGenerator();
        $num1 = $generator->generate(); // No company ID needed
        $this->assertEquals('GLOBAL-00001', $num1);

        // Change to WITH company code
        $formula1->update(['include_company_code' => true]);
        $num2 = $generator->generate($company->id);
        $this->assertEquals('GLOBAL-XYZ-00001', $num2);
    }

    /**
     * Test: Member number is unique (database constraint).
     */
    public function test_member_number_is_unique(): void
    {
        $company = Company::create([
            'kode' => 'ABC',
            'name' => 'Test Company',
            'is_active' => true,
        ]);

        $formula = MemberNumberFormula::create([
            'name' => 'Test Formula',
            'prefix' => 'FSPMI',
            'separator' => '-',
            'include_company_code' => true,
            'sequence_digits' => 4,
            'is_active' => true,
            'created_by' => 1,
        ]);

        // Generate number
        $generator = new MemberNumberGenerator();
        $number = $generator->generate($company->id);

        // Create a member with this number
        $member = \App\Models\Member::create([
            'nik' => $number,
            'nama' => 'Test Member',
            'tempat_lahir' => 'Test City',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2026-01-01',
            'company_id' => $company->id,
        ]);

        // Now check uniqueness
        $this->assertTrue($generator->numberExists($number));
        $this->assertFalse($generator->numberExists('FSPMI-ABC-9999'));
    }
}
