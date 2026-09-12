<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Member;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'anggota-view']);
        Permission::create(['name' => 'anggota-create']);
        Permission::create(['name' => 'anggota-edit']);
        Permission::create(['name' => 'anggota-delete']);
    }

    public function test_can_view_members_list(): void
    {
        $response = $this->get('/members');
        $response->assertStatus(302);
    }

    public function test_unauthenticated_user_redirects_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_members(): void
    {
        $user = User::factory()->create()->givePermissionTo('anggota-view');
        $this->actingAs($user);

        $response = $this->get('/members');
        $response->assertStatus(200);
    }

    public function test_can_create_member(): void
    {
        // Create a company first
        $company = Company::create([
            'kode' => 'TST',
            'name' => 'Test Company',
            'is_active' => true,
        ]);

        $user = User::factory()->create()->givePermissionTo(['anggota-view', 'anggota-create']);
        $this->actingAs($user);

        $response = $this->post('/members', [
            'nik' => '3275010101900001',
            'nama' => 'Test Member',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2026-01-01',
            'company_id' => $company->id,
        ]);

        $this->assertDatabaseHas('members', ['nik' => '3275010101900001']);
    }

    public function test_nik_must_be_unique(): void
    {
        $user = User::factory()->create()->givePermissionTo(['anggota-view', 'anggota-create']);
        $this->actingAs($user);

        Member::factory()->create(['nik' => '3275010101900001']);

        $response = $this->post('/members', [
            'nik' => '3275010101900001',
            'nama' => 'Test Member',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2026-01-01',
        ]);

        $response->assertSessionHasErrors('nik');
    }

    public function test_can_view_member_detail(): void
    {
        $user = User::factory()->create()->givePermissionTo('anggota-view');
        $this->actingAs($user);

        $member = Member::factory()->create();
        $response = $this->get("/members/{$member->id}");
        $response->assertStatus(200);
    }

    public function test_can_delete_member(): void
    {
        $user = User::factory()->create()->givePermissionTo(['anggota-view', 'anggota-delete']);
        $this->actingAs($user);

        $member = Member::factory()->create();
        $response = $this->delete("/members/{$member->id}");
        $response->assertRedirect('/members');
        $this->assertSoftDeleted('members', ['id' => $member->id]);
    }
}
