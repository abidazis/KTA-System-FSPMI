<?php

namespace Tests\Unit;

use App\Models\Member;
use App\Models\PrintBatch;
use App\Models\ManagementPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintBatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_print_batch(): void
    {
        $period = ManagementPeriod::create([
            'nama_periode' => '2026-2031',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2031-12-31',
            'status' => 'active',
        ]);

        $member = Member::create([
            'nik' => '3275010101900001',
            'nama' => 'Test Member',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2026-01-01',
            'status' => 'ready',
        ]);

        $batch = PrintBatch::create([
            'batch_number' => PrintBatch::generateBatchNumber(),
            'management_period_id' => $period->id,
            'tanggal_cetak' => now()->toDateString(),
            'jumlah' => 1,
            'type' => 'front',
        ]);

        $batch->members()->attach($member->id, ['position' => 1]);

        $this->assertDatabaseHas('print_batches', ['type' => 'front']);
        $this->assertEquals(1, $batch->members()->count());
    }

    public function test_batch_number_auto_generates(): void
    {
        $period = ManagementPeriod::create([
            'nama_periode' => '2026-2031',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2031-12-31',
            'status' => 'active',
        ]);

        $batchNumber = PrintBatch::generateBatchNumber();

        $this->assertStringStartsWith('BTH-', $batchNumber);
    }

    public function test_batch_numbers_increment(): void
    {
        $period = ManagementPeriod::create([
            'nama_periode' => '2026-2031',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2031-12-31',
            'status' => 'active',
        ]);

        $batch1 = PrintBatch::create([
            'batch_number' => 'BTH-00001',
            'management_period_id' => $period->id,
            'tanggal_cetak' => now()->toDateString(),
            'jumlah' => 1,
            'type' => 'front',
        ]);

        $batch2 = PrintBatch::create([
            'batch_number' => PrintBatch::generateBatchNumber(),
            'management_period_id' => $period->id,
            'tanggal_cetak' => now()->toDateString(),
            'jumlah' => 1,
            'type' => 'front',
        ]);

        $this->assertEquals('BTH-00002', $batch2->batch_number);
    }
}
