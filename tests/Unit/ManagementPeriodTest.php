<?php

namespace Tests\Unit;

use App\Models\ManagementPeriod;
use App\Models\ManagementOfficial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_management_period(): void
    {
        $period = ManagementPeriod::create([
            'nama_periode' => '2026-2031',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2031-12-31',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('management_periods', ['nama_periode' => '2026-2031']);
    }

    public function test_can_add_official_to_period(): void
    {
        $period = ManagementPeriod::create([
            'nama_periode' => '2026-2031',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2031-12-31',
            'status' => 'active',
        ]);

        $official = ManagementOfficial::create([
            'management_period_id' => $period->id,
            'jabatan' => 'Ketua Umum',
            'nama' => 'John Doe',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('management_officials', ['jabatan' => 'Ketua Umum']);
    }

    public function test_period_can_have_multiple_officials(): void
    {
        $period = ManagementPeriod::create([
            'nama_periode' => '2026-2031',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2031-12-31',
            'status' => 'active',
        ]);

        ManagementOfficial::create([
            'management_period_id' => $period->id,
            'jabatan' => 'Ketua Umum',
            'nama' => 'John Doe',
            'status' => 'active',
        ]);

        ManagementOfficial::create([
            'management_period_id' => $period->id,
            'jabatan' => 'Sekretaris Umum',
            'nama' => 'Jane Doe',
            'status' => 'active',
        ]);

        $this->assertEquals(2, $period->officials()->count());
    }
}
