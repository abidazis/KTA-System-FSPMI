<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Member;
use App\Models\MemberNumber;
use App\Models\MemberNumberFormula;
use App\Services\MemberNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberNumberMasterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create active formula
        Company::create([
            'kode' => 'ABC',
            'name' => 'Test Company',
            'is_active' => true,
        ]);

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

    /**
     * Test: MemberNumberGenerator creates MemberNumber records.
     */
    public function test_generator_creates_member_number_records(): void
    {
        $generator = new MemberNumberGenerator();

        // Generate a single number
        $number = $generator->generate(1);

        // Check that MemberNumber record was created
        $this->assertDatabaseHas('member_numbers', [
            'number' => $number,
            'status' => 'available',
        ]);

        $memberNumber = MemberNumber::where('number', $number)->first();
        $this->assertNotNull($memberNumber);
        $this->assertTrue($memberNumber->isAvailable());
    }

    /**
     * Test: generateBatch creates multiple MemberNumber records.
     */
    public function test_batch_generation_creates_member_number_records(): void
    {
        $generator = new MemberNumberGenerator();

        // Generate batch
        $numbers = $generator->generateBatch([1, 1, 1]);

        // Check all numbers were created
        $this->assertCount(3, $numbers);
        $this->assertEquals(3, MemberNumber::count());

        // All should be available
        foreach ($numbers as $number) {
            $this->assertDatabaseHas('member_numbers', [
                'number' => $number,
                'status' => 'available',
            ]);
        }
    }

    /**
     * Test: MemberNumber status changes to used when assigned to member.
     */
    public function test_member_number_status_changes_to_used(): void
    {
        $generator = new MemberNumberGenerator();
        $number = $generator->generate(1);

        $memberNumber = MemberNumber::where('number', $number)->first();
        $this->assertTrue($memberNumber->isAvailable());

        // Create a member and assign the number
        $member = Member::create([
            'nik' => $number,
            'nama' => 'Test Member',
            'tempat_lahir' => 'Test City',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2026-01-01',
            'company_id' => 1,
        ]);

        $memberNumber->markAsUsed($member);

        $this->assertTrue($memberNumber->isUsed());
        $this->assertEquals('used', $memberNumber->status);
        $this->assertEquals($member->id, $memberNumber->member_id);
    }

    /**
     * Test: MemberNumber unique constraint prevents duplicates.
     */
    public function test_member_number_number_is_unique(): void
    {
        $generator = new MemberNumberGenerator();
        $number = $generator->generate(1);

        // Try to create duplicate directly
        $this->expectException(\Illuminate\Database\QueryException::class);

        MemberNumber::create([
            'number' => $number,
            'status' => 'available',
            'company_id' => 1,
            'formula_id' => 1,
        ]);
    }

    /**
     * Test: MemberNumber scopes work correctly.
     */
    public function test_member_number_scopes(): void
    {
        $generator = new MemberNumberGenerator();
        $numbers = $generator->generateBatch([1, 1]);

        // Get first number and mark as used
        $first = MemberNumber::where('number', $numbers[0])->first();
        $member = Member::create([
            'nik' => $numbers[0],
            'nama' => 'Test Member',
            'tempat_lahir' => 'Test City',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2026-01-01',
            'company_id' => 1,
        ]);
        $first->markAsUsed($member);

        // Check scopes
        $this->assertEquals(2, MemberNumber::count());
        $this->assertEquals(1, MemberNumber::available()->count());
        $this->assertEquals(1, MemberNumber::used()->count());
    }

    /**
     * Test: MemberNumber getStats returns correct counts.
     */
    public function test_member_number_stats(): void
    {
        $generator = new MemberNumberGenerator();
        $numbers = $generator->generateBatch([1, 1, 1]);

        // Mark one as used
        $first = MemberNumber::where('number', $numbers[0])->first();
        $member = Member::create([
            'nik' => $numbers[0],
            'nama' => 'Test Member',
            'tempat_lahir' => 'Test City',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2026-01-01',
            'company_id' => 1,
        ]);
        $first->markAsUsed($member);

        $stats = MemberNumber::getStats();

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(2, $stats['available']);
        $this->assertEquals(1, $stats['used']);
    }

    /**
     * Test: MemberNumber search scope works.
     */
    public function test_member_number_search_scope(): void
    {
        $generator = new MemberNumberGenerator();
        $numbers = $generator->generateBatch([1, 1]);

        // Mark first as used
        $first = MemberNumber::where('number', $numbers[0])->first();
        $member = Member::create([
            'nik' => $numbers[0],
            'nama' => 'John Doe',
            'tempat_lahir' => 'Test City',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2026-01-01',
            'company_id' => 1,
        ]);
        $first->markAsUsed($member);

        // Search by number
        $found = MemberNumber::search($numbers[0])->first();
        $this->assertNotNull($found);
        $this->assertEquals($numbers[0], $found->number);

        // Search by member name
        $found = MemberNumber::search('John')->first();
        $this->assertNotNull($found);
        $this->assertEquals('John Doe', $found->member->nama);
    }

    /**
     * Test: USED number cannot be deleted via cascade.
     * The unique constraint on member_id should prevent deletion cascade.
     */
    public function test_used_number_cannot_be_deleted_easily(): void
    {
        $generator = new MemberNumberGenerator();
        $number = $generator->generate(1);

        $memberNumber = MemberNumber::where('number', $number)->first();
        $member = Member::create([
            'nik' => $number,
            'nama' => 'Test Member',
            'tempat_lahir' => 'Test City',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2026-01-01',
            'company_id' => 1,
        ]);
        $memberNumber->markAsUsed($member);

        // Try to delete member (should fail due to FK constraint on member_id unique)
        $this->expectException(\Illuminate\Database\QueryException::class);
        $member->forceDelete();
    }

    /**
     * Test: Member can only have one number.
     */
    public function test_member_has_unique_nik_constraint(): void
    {
        $generator = new MemberNumberGenerator();
        $number = $generator->generate(1);

        // Create first member with this number
        Member::create([
            'nik' => $number,
            'nama' => 'Member 1',
            'tempat_lahir' => 'Test City',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2026-01-01',
            'company_id' => 1,
        ]);

        // Try to create another member with same number
        $this->expectException(\Illuminate\Database\QueryException::class);

        Member::create([
            'nik' => $number,
            'nama' => 'Member 2',
            'tempat_lahir' => 'Test City',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Test Address',
            'jenis_kelamin' => 'Perempuan',
            'agama' => 'Islam',
            'berlaku_hingga' => '2030-01-01',
            'tanggal_pembuatan' => '2026-01-01',
            'company_id' => 1,
        ]);
    }
}
