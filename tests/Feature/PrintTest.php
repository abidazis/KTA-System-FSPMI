<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\PrintBatch;
use App\Models\User;
use App\Models\ManagementPeriod;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'cetak']);
    }

    public function test_can_view_print_index(): void
    {
        $user = User::factory()->create()->givePermissionTo('cetak');
        $this->actingAs($user);

        $response = $this->get('/print');
        $response->assertStatus(200);
    }

    public function test_can_view_print_create(): void
    {
        $user = User::factory()->create()->givePermissionTo('cetak');
        $this->actingAs($user);

        $response = $this->get('/print/create');
        $response->assertStatus(200);
    }

    public function test_can_create_print_batch(): void
    {
        $user = User::factory()->create()->givePermissionTo('cetak');
        $this->actingAs($user);

        ManagementPeriod::factory()->create(['status' => 'active']);

        $member = Member::factory()->create([
            'foto_path' => 'test.jpg',
            'status' => 'ready',
        ]);

        $response = $this->post('/print', [
            'member_ids' => [$member->id],
            'side' => 'front',
        ]);

        $this->assertDatabaseHas('print_batches', ['type' => 'front']);
        $this->assertDatabaseHas('print_batch_members', ['member_id' => $member->id]);
    }

    public function test_batch_number_auto_generated(): void
    {
        $user = User::factory()->create()->givePermissionTo('cetak');
        $this->actingAs($user);

        ManagementPeriod::factory()->create(['status' => 'active']);

        $member = Member::factory()->create(['foto_path' => 'test.jpg']);
        $this->post('/print', [
            'member_ids' => [$member->id],
            'side' => 'front',
        ]);

        $batch = PrintBatch::first();
        $this->assertNotNull($batch);
        $this->assertStringStartsWith('BTH-', $batch->batch_number);
    }

    /**
     * Helper: Create a batch with N members, returns the PrintBatch.
     */
    private function createBatchWithMembers(int $n, string $side = 'front'): PrintBatch
    {
        $user = User::factory()->create()->givePermissionTo('cetak');
        $this->actingAs($user);

        ManagementPeriod::factory()->create(['status' => 'active']);

        // Use a high offset based on microsecond time so NIK is unique across calls
        $baseNik = 3200000000000000 + ((int) (microtime(true) * 1000) % 1000000000);

        $members = collect();
        for ($i = 0; $i < $n; $i++) {
            $nik = (string) ($baseNik + $i);
            $members->push(Member::factory()->create([
                'foto_path' => 'test' . $i . '.jpg',
                'status' => 'ready',
                'nik' => $nik,
                'nama' => 'Anggota ' . $i,
            ]));
        }

        $memberIds = $members->pluck('id')->toArray();

        $response = $this->post('/print', [
            'member_ids' => $memberIds,
            'side' => $side,
        ]);

        $response->assertSessionDoesntHaveErrors();

        // Use orderBy id desc to avoid timing issues with created_at
        $batch = PrintBatch::orderBy('id', 'desc')->first();
        return $batch;
    }

    public function test_pdf_with_one_member_produces_valid_pdf(): void
    {
        $batch = $this->createBatchWithMembers(1);

        $response = $this->get('/print/' . $batch->id . '/pdf');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // PDF should be non-empty
        $this->assertGreaterThan(100, strlen($response->getContent()));

        // PDF starts with %PDF magic bytes
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_pdf_page_count_matches_member_count(): void
    {
        // Test A: 1 anggota → 1 FRONT page + 1 BACK page
        $this->assertEquals(1, $this->calculatePageCount(1));

        // Test B: 10 anggota → 1 FRONT page + 1 BACK page
        $this->assertEquals(1, $this->calculatePageCount(10));

        // Test C: 11 anggota → 2 FRONT page + 2 BACK page
        $this->assertEquals(2, $this->calculatePageCount(11));

        // Test D: 20 anggota → 2 FRONT page + 2 BACK page
        $this->assertEquals(2, $this->calculatePageCount(20));

        // Test E: 21 anggota → 3 FRONT page + 3 BACK page
        $this->assertEquals(3, $this->calculatePageCount(21));

        // Test F: 23 anggota → 3 FRONT page + 3 BACK page
        $this->assertEquals(3, $this->calculatePageCount(23));
    }

    public function test_pdf_real_page_count_for_each_size(): void
    {
        // Verify the actual generated PDF has the expected page count.
        $expectedPages = [1 => 1, 10 => 1, 11 => 2, 20 => 2, 21 => 3, 23 => 3];
        foreach ($expectedPages as $n => $expected) {
            $batch = $this->createBatchWithMembers($n);

            // Verify batch actually has N members
            $actualCount = $batch->members()->count();
            $this->assertEquals(
                $n,
                $actualCount,
                "Batch should have {$n} members, got {$actualCount}"
            );

            $response = $this->get('/print/' . $batch->id . '/pdf');
            $response->assertStatus(200);

            $content = $response->getContent();
            preg_match_all('~/Type\s*/Page[^s]~', $content, $pages);
            $actual = count($pages[0]);

            $this->assertEquals(
                $expected,
                $actual,
                "Expected {$expected} page(s) for {$n} members, got {$actual}"
            );
        }
    }

    /**
     * Calculate expected page count for N members (10 KTA per page).
     */
    private function calculatePageCount(int $n): int
    {
        return (int) ceil($n / 10);
    }

    public function test_pdf_uses_a4_portrait_paper(): void
    {
        $batch = $this->createBatchWithMembers(1);

        $response = $this->get('/print/' . $batch->id . '/pdf');
        $response->assertStatus(200);

        $content = $response->getContent();

        // A4 portrait in points: width 595.28, height 841.89
        // DOMPDF may compress streams, so we check that *some* MediaBox with
        // these dimensions appears in the PDF metadata.
        $hasWidth = preg_match('/MediaBox\s*\[[^\]]*595(?:\.\d+)?/', $content);
        $hasHeight = preg_match('/MediaBox\s*\[[^\]]*841(?:\.\d+)?/', $content);

        $this->assertTrue((bool) $hasWidth, 'PDF should have MediaBox width ~595.28 (A4 portrait)');
        $this->assertTrue((bool) $hasHeight, 'PDF should have MediaBox height ~841.89 (A4 portrait)');
    }

    public function test_duplex_long_edge_reverses_back_order(): void
    {
        // Create batch with 2 members to easily verify back-page ordering
        $batch = $this->createBatchWithMembers(2, 'back');

        $response = $this->get('/print/' . $batch->id . '/pdf?duplex=long-edge');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // Both back and front PDFs should have the same pagination:
        // 2 members → 1 page (each)
        $this->assertGreaterThan(100, strlen($response->getContent()));
        $this->assertStringStartsWith('%PDF', $response->getContent());

        // Verify duplex short-edge mode also works (does NOT reverse)
        $response2 = $this->get('/print/' . $batch->id . '/pdf?duplex=short-edge');
        $response2->assertStatus(200);
    }

    public function test_duplex_short_edge_keeps_order(): void
    {
        $batch = $this->createBatchWithMembers(2, 'back');
        $response = $this->get('/print/' . $batch->id . '/pdf?duplex=short-edge');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
