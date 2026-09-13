<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Member;
use App\Models\MemberNumberFormula;
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

        // Create a default active formula
        MemberNumberFormula::create([
            'name' => 'Test Formula',
            'prefix' => 'FSPMI',
            'separator' => '-',
            'include_company_code' => true,
            'sequence_digits' => 4,
            'is_active' => true,
            'created_by' => 1,
        ]);
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

    public function test_can_create_member_with_auto_generated_number(): void
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

        // Should redirect to member show page
        $response->assertRedirect();

        // Member should be created with auto-generated nik
        $member = Member::where('nama', 'Test Member')->first();
        $this->assertNotNull($member);
        $this->assertEquals('FSPMI-TST-0001', $member->nik);
    }

    public function test_cannot_create_member_without_formula(): void
    {
        // Deactivate all formulas
        MemberNumberFormula::query()->update(['is_active' => false]);

        $company = Company::create([
            'kode' => 'TST',
            'name' => 'Test Company',
            'is_active' => true,
        ]);

        $user = User::factory()->create()->givePermissionTo(['anggota-view', 'anggota-create']);
        $this->actingAs($user);

        $response = $this->post('/members', [
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

        // Should redirect back with error
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionHas('error', 'Nomor anggota belum dapat dibuat karena formula nomor anggota belum dikonfigurasi oleh administrator.');

        // Member should NOT be created
        $this->assertDatabaseMissing('members', ['nama' => 'Test Member']);
    }

    public function test_cannot_create_member_without_company_when_formula_requires_it(): void
    {
        $user = User::factory()->create()->givePermissionTo(['anggota-view', 'anggota-create']);
        $this->actingAs($user);

        $response = $this->post('/members', [
            'nama' => 'Test Member',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2026-01-01',
            'company_id' => '', // No company
        ]);

        // Should fail validation
        $response->assertSessionHasErrors('company_id');
    }

    public function test_can_view_member_detail(): void
    {
        $user = User::factory()->create()->givePermissionTo('anggota-view');
        $this->actingAs($user);

        $company = Company::create([
            'kode' => 'TST',
            'name' => 'Test Company',
            'is_active' => true,
        ]);

        $member = Member::create([
            'nik' => 'FSPMI-TST-0001',
            'nama' => 'Test Member',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2026-01-01',
            'company_id' => $company->id,
            'status' => 'active',
        ]);

        $response = $this->get('/members/' . $member->id);
        $response->assertStatus(200);
        $response->assertSee('Test Member');
    }

    public function test_can_delete_member(): void
    {
        $user = User::factory()->create()->givePermissionTo(['anggota-view', 'anggota-delete']);
        $this->actingAs($user);

        $company = Company::create([
            'kode' => 'TST',
            'name' => 'Test Company',
            'is_active' => true,
        ]);

        $member = Member::factory()->create([
            'company_id' => $company->id,
            'nik' => 'FSPMI-TST-0001',
        ]);
        $response = $this->delete("/members/{$member->id}");
        $response->assertRedirect('/members');
        $this->assertSoftDeleted('members', ['id' => $member->id]);
    }
}
