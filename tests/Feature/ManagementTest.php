<?php

namespace Tests\Feature;

use App\Models\ManagementPeriod;
use App\Models\ManagementOfficial;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'pengurus']);
    }

    public function test_can_view_management_periods(): void
    {
        $user = User::factory()->create()->givePermissionTo('pengurus');
        $this->actingAs($user);

        $response = $this->get('/management');
        $response->assertStatus(200);
    }

    public function test_can_create_management_period(): void
    {
        $user = User::factory()->create()->givePermissionTo('pengurus');
        $this->actingAs($user);

        $response = $this->post('/management', [
            'nama_periode' => '2026-2031',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2031-12-31',
        ]);

        $this->assertDatabaseHas('management_periods', ['nama_periode' => '2026-2031']);
    }

    public function test_can_add_official_to_period(): void
    {
        $user = User::factory()->create()->givePermissionTo('pengurus');
        $this->actingAs($user);

        $period = ManagementPeriod::factory()->create();

        $response = $this->post("/management/{$period->id}/officials", [
            'jabatan' => 'Ketua Umum',
            'nama' => 'John Doe',
        ]);

        $this->assertDatabaseHas('management_officials', [
            'jabatan' => 'Ketua Umum',
            'nama' => 'John Doe',
        ]);
    }

    public function test_cannot_have_duplicate_active_ketua_umum(): void
    {
        $user = User::factory()->create()->givePermissionTo('pengurus');
        $this->actingAs($user);

        $period = ManagementPeriod::factory()->create();
        ManagementOfficial::create([
            'management_period_id' => $period->id,
            'jabatan' => 'Ketua Umum',
            'nama' => 'John Doe',
            'status' => 'active',
        ]);

        $response = $this->post("/management/{$period->id}/officials", [
            'jabatan' => 'Ketua Umum',
            'nama' => 'Jane Doe',
        ]);

        // Controller uses ->with('error') not ->withErrors(), so check session flash
        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Ketua Umum sudah ada untuk periode ini.');
    }

    public function test_can_set_active_period(): void
    {
        $user = User::factory()->create()->givePermissionTo('pengurus');
        $this->actingAs($user);

        $period1 = ManagementPeriod::factory()->create(['status' => 'active']);
        $period2 = ManagementPeriod::factory()->create(['status' => 'inactive']);

        $this->post("/management/{$period2->id}/set-active");

        $this->assertDatabaseHas('management_periods', ['id' => $period2->id, 'status' => 'active']);
    }
}
