<?php

namespace Tests\Unit;

use App\Exports\MemberTemplateExport;
use Tests\TestCase;

class MemberTemplateExportTest extends TestCase
{
    /**
     * Test: Member template does NOT have a NIK column.
     * The member number column is named nomor_anggota and is optional.
     */
    public function test_template_has_no_nik_column(): void
    {
        $export = new MemberTemplateExport();
        $headings = $export->headings();

        // nik should NOT be in headings
        $this->assertNotContains('nik', $headings);
        $this->assertNotContains('NIK', $headings);
    }

    /**
     * Test: Member template has nomor_anggota column so users can supply
     * existing numbers without editing each member manually afterwards.
     */
    public function test_template_has_nomor_anggota_column(): void
    {
        $export = new MemberTemplateExport();
        $headings = $export->headings();

        $this->assertContains('nomor_anggota', $headings);
        // Should be the first column so it is easy to find
        $this->assertSame('nomor_anggota', $headings[0]);
    }

    /**
     * Test: nomor_anggota example follows the X.XX.XX.XXX.XXXX format.
     */
    public function test_template_nomor_anggota_example_is_valid(): void
    {
        $export = new MemberTemplateExport();
        $firstRow = $export->collection()->first();

        $this->assertMatchesRegularExpression(
            '/^[0-9]{1,2}\.[0-9]{2}\.[0-9]{2}\.[0-9]{3}\.[0-9]{4}$/',
            $firstRow['nomor_anggota']
        );
    }

    /**
     * Test: Member template has correct columns for data input.
     */
    public function test_template_has_required_columns(): void
    {
        $export = new MemberTemplateExport();
        $headings = $export->headings();

        // Required data columns
        $requiredColumns = [
            'nama',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat',
            'jenis_kelamin',
            'agama',
            'berlaku_hingga',
            'tanggal_pembuatan',
            'foto',
        ];

        foreach ($requiredColumns as $column) {
            $this->assertContains($column, $headings, "Missing required column: {$column}");
        }
    }

    /**
     * Test: Member template has company columns.
     */
    public function test_template_has_company_columns(): void
    {
        $export = new MemberTemplateExport();
        $headings = $export->headings();

        $this->assertContains('kode_perusahaan', $headings);
        $this->assertContains('nama_perusahaan', $headings);
    }

    /**
     * Test: Member template example uses correct photo filename format.
     * Photo should use company code + sequence, not nik.
     */
    public function test_template_uses_correct_photo_format(): void
    {
        $export = new MemberTemplateExport();
        $collection = $export->collection();
        $firstRow = $collection->first();

        // Example should NOT use old NIK format for photo
        $this->assertNotEquals('3275010101900001.jpg', $firstRow['foto']);

        // Should use company-based format
        $this->assertStringContainsString('ABC', $firstRow['foto']);
        $this->assertStringContainsString('.jpg', $firstRow['foto']);
    }
}
