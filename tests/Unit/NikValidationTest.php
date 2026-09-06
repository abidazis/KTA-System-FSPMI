<?php

namespace Tests\Unit;

use App\Http\Controllers\Import\ImportController;
use Tests\TestCase;

class NikValidationTest extends TestCase
{
    /**
     * Test normalizeNik with string values.
     */
    public function test_normalize_nik_with_string(): void
    {
        // Clean string should return as-is
        $this->assertEquals('3216221612020012', ImportController::normalizeNik('3216221612020012'));
        $this->assertEquals('123456789012345', ImportController::normalizeNik('123456789012345'));

        // String with whitespace should be trimmed
        $this->assertEquals('3216221612020012', ImportController::normalizeNik(' 3216221612020012 '));
        $this->assertEquals('3216221612020012', ImportController::normalizeNik("\t3216221612020012\n"));
    }

    /**
     * Test normalizeNik with float values (Excel numeric).
     */
    public function test_normalize_nik_with_float(): void
    {
        // Float representing a 16-digit NIK
        $floatNik = 3216221612020012.0;
        $this->assertEquals('3216221612020012', ImportController::normalizeNik($floatNik));

        // Float representing a 15-digit NIK
        $floatNik15 = 123456789012345.0;
        $this->assertEquals('123456789012345', ImportController::normalizeNik($floatNik15));
    }

    /**
     * Test normalizeNik with integer values.
     */
    public function test_normalize_nik_with_integer(): void
    {
        $this->assertEquals('3216221612020012', ImportController::normalizeNik(3216221612020012));
        $this->assertEquals('123456789012345', ImportController::normalizeNik(123456789012345));
    }

    /**
     * Test normalizeNik with null/empty values.
     */
    public function test_normalize_nik_with_null_or_empty(): void
    {
        $this->assertEquals('', ImportController::normalizeNik(null));
        $this->assertEquals('', ImportController::normalizeNik(''));
        $this->assertEquals('', ImportController::normalizeNik('   '));
    }

    /**
     * Test isValidNik with valid NIKs.
     */
    public function test_is_valid_nik_with_valid_values(): void
    {
        // Valid 16-digit NIKs
        $this->assertTrue(ImportController::isValidNik('3216221612020012'));
        $this->assertTrue(ImportController::isValidNik('1234567890123456'));

        // Valid 15-digit NIKs
        $this->assertTrue(ImportController::isValidNik('123456789012345'));
        $this->assertTrue(ImportController::isValidNik('000123456789012')); // Leading zeros are valid
    }

    /**
     * Test isValidNik with invalid NIKs.
     */
    public function test_is_valid_nik_with_invalid_values(): void
    {
        // Empty values
        $this->assertFalse(ImportController::isValidNik(''));
        $this->assertFalse(ImportController::isValidNik('   '));

        // Wrong length - too short
        $this->assertFalse(ImportController::isValidNik('12345678901234')); // 14 digits
        $this->assertFalse(ImportController::isValidNik('1234567890')); // 10 digits

        // Wrong length - too long
        $this->assertFalse(ImportController::isValidNik('12345678901234567')); // 17 digits

        // Contains letters
        $this->assertFalse(ImportController::isValidNik('32162216120200A2'));

        // Contains spaces
        $this->assertFalse(ImportController::isValidNik('3216 2216 1202 0012'));

        // Scientific notation (should be rejected)
        $this->assertFalse(ImportController::isValidNik('3.21622161202001E+15'));
    }

    /**
     * Test that 16-digit NIK that would be stored as float in Excel is properly normalized.
     * This is the key test case for the reported bug.
     */
    public function test_sixteen_digit_nik_from_excel_float(): void
    {
        // Simulate what Excel does - stores large numbers as float
        // In real Excel, 3216221612020012 would be stored as float
        // PhpSpreadsheet would return it as float

        $nikFromExcel = 3216221612020012.0; // Float representation

        // Normalize should convert to proper string
        $normalized = ImportController::normalizeNik($nikFromExcel);

        // Should be 16 digits, all numeric
        $this->assertEquals(16, strlen($normalized));
        $this->assertMatchesRegularExpression('/^\d{16}$/', $normalized);
        $this->assertEquals('3216221612020012', $normalized);

        // And it should pass validation
        $this->assertTrue(ImportController::isValidNik($normalized));
    }

    /**
     * Test that the full pipeline works correctly.
     */
    public function test_full_normalize_and_validate_pipeline(): void
    {
        $testCases = [
            // [input, expected_length, expected_valid]
            ['3216221612020012', 16, true],   // 16 digit - the reported bug case
            ['123456789012345', 15, true],     // 15 digit
            ['3216221612020012.0', 16, true],  // Float 16 digit
            ['123456789012345.0', 15, true],   // Float 15 digit
            [3216221612020012.0, 16, true],    // Float (not string)
            [123456789012345, 15, true],       // Integer
        ];

        foreach ($testCases as [$input, $expectedLength, $expectedValid]) {
            $normalized = ImportController::normalizeNik($input);
            $this->assertEquals(
                $expectedLength,
                strlen($normalized),
                "Input " . json_encode($input) . " should normalize to length $expectedLength, got " . strlen($normalized)
            );
            $this->assertMatchesRegularExpression('/^\d+$/', $normalized);
            $this->assertEquals(
                $expectedValid,
                ImportController::isValidNik($normalized),
                "Input " . json_encode($input) . " normalized to '$normalized' should " . ($expectedValid ? "be valid" : "be invalid")
            );
        }
    }

    /**
     * Test that empty/whitespace values are properly handled.
     */
    public function test_empty_and_whitespace_handling(): void
    {
        // All these should normalize to empty string
        $emptyCases = [null, '', '   ', "\t", "\n", "  \t\n  "];
        foreach ($emptyCases as $input) {
            $normalized = ImportController::normalizeNik($input);
            $this->assertEquals('', $normalized, "Input " . json_encode($input) . " should normalize to empty string");
            $this->assertFalse(ImportController::isValidNik($normalized));
        }
    }

    /**
     * Test isInstructionRow detection for template CATATAN rows.
     */
    public function test_is_instruction_row_detection(): void
    {
        // Test using reflection to access private method
        $controller = new \App\Http\Controllers\Import\ImportController();

        // CASE 1: CATATAN row with empty fields - should be detected as instruction row
        $catatanRow = [
            'nik' => 'CATATAN: Kolom NIK harus berisi 16 digit angka. Jangan rubah format sel.',
            'nama' => '',
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
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('isInstructionRow');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke($controller, $catatanRow), 'CATATAN row with empty fields should be detected as instruction');

        // CASE 2: CATATAN row but with data in other fields - should NOT be instruction row
        $catatanWithData = [
            'nik' => 'CATATAN: some note',
            'nama' => 'John Doe',  // Has actual data
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
        $this->assertFalse($method->invoke($controller, $catatanWithData), 'CATATAN row with actual data should NOT be detected as instruction');

        // CASE 3: Valid NIK - should NOT be instruction row
        $validRow = [
            'nik' => '3216221612020012',
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
            'foto' => '3216221612020012.jpg',
        ];
        $this->assertFalse($method->invoke($controller, $validRow), 'Valid NIK row should NOT be detected as instruction');

        // CASE 4: Case insensitive - lowercase "catatan:"
        $lowercaseCatatan = [
            'nik' => 'catatan: this is a note',
            'nama' => '',
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
        $this->assertTrue($method->invoke($controller, $lowercaseCatatan), 'Lowercase "catatan:" should also be detected');

        // CASE 5: Mixed case "Catatan:"
        $mixedCatatan = [
            'nik' => 'Catatan: instruction text',
            'nama' => '',
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
        $this->assertTrue($method->invoke($controller, $mixedCatatan), 'Mixed case "Catatan:" should also be detected');

        // CASE 6: Row with numeric-like NIK but no actual data - should NOT be instruction row
        $numericNikNoData = [
            'nik' => '1234567890123456',
            'nama' => '',
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
        $this->assertFalse($method->invoke($controller, $numericNikNoData), 'Numeric NIK with empty fields should NOT be detected as instruction (invalid member data)');
    }
}
