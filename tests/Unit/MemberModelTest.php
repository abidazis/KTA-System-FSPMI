<?php

namespace Tests\Unit;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_member(): void
    {
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
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('members', ['nik' => '3275010101900001']);
    }

    public function test_nik_must_be_unique(): void
    {
        Member::create([
            'nik' => '3275010101900001',
            'nama' => 'Test Member',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2026-01-01',
            'status' => 'draft',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Member::create([
            'nik' => '3275010101900001',
            'nama' => 'Another Member',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1991-01-01',
            'alamat' => 'Another Address',
            'jenis_kelamin' => 'Perempuan',
            'agama' => 'Kristen',
            'berlaku_hingga' => '2031-01-01',
            'tanggal_pembuatan' => '2026-01-01',
            'status' => 'draft',
        ]);
    }

    public function test_member_can_be_soft_deleted(): void
    {
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
            'status' => 'draft',
        ]);

        $member->delete();

        $this->assertSoftDeleted('members', ['id' => $member->id]);
    }

    public function test_member_is_expired(): void
    {
        $member = Member::create([
            'nik' => '3275010101900001',
            'nama' => 'Test Member',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2020-01-01',
            'tanggal_pembuatan' => '2015-01-01',
            'status' => 'active',
        ]);

        $this->assertTrue($member->isExpired());
    }

    public function test_member_is_not_expired_when_valid(): void
    {
        $member = Member::create([
            'nik' => '3275010101900001',
            'nama' => 'Test Member',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => now()->addYear()->format('Y-m-d'),
            'tanggal_pembuatan' => '2026-01-01',
            'status' => 'active',
        ]);

        $this->assertFalse($member->isExpired());
    }
}
