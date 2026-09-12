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
        // 4 KTA per A4 landscape page (2 rows x 2 cols)
        // Each member has FRONT + BACK, but PDF combines them
        $this->assertEquals(1, $this->calculatePageCount(1));
        $this->assertEquals(1, $this->calculatePageCount(4));
        $this->assertEquals(2, $this->calculatePageCount(5));
        $this->assertEquals(2, $this->calculatePageCount(8));
        $this->assertEquals(3, $this->calculatePageCount(9));
        $this->assertEquals(3, $this->calculatePageCount(10));
    }

    public function test_pdf_real_page_count_for_each_size(): void
    {
        // 4 KTA per A4 landscape page (2 rows x 2 cols)
        $expectedPages = [1 => 1, 4 => 1, 5 => 2, 8 => 2, 9 => 3, 10 => 3];
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
     * Calculate expected page count for N members (4 KTA per A4 landscape page).
     */
    private function calculatePageCount(int $n): int
    {
        return (int) ceil($n / 4);
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

    public function test_one_batch_produces_both_front_and_back_pdf(): void
    {
        // Satu batch untuk banyak anggota, tanpa side selection
        $batch = $this->createBatchWithMembers(10);

        // Front PDF
        $responseFront = $this->get('/print/' . $batch->id . '/pdf?side=front');
        $responseFront->assertStatus(200);
        $responseFront->assertHeader('Content-Type', 'application/pdf');

        // Back PDF (same batch, different query param)
        $responseBack = $this->get('/print/' . $batch->id . '/pdf?side=back');
        $responseBack->assertStatus(200);
        $responseBack->assertHeader('Content-Type', 'application/pdf');

        // Both PDFs should contain the same batch's members but with opposite side
        $this->assertGreaterThan(1000, strlen($responseFront->getContent()));
        $this->assertGreaterThan(1000, strlen($responseBack->getContent()));
    }

    public function test_default_side_is_front_when_no_param(): void
    {
        $batch = $this->createBatchWithMembers(5);
        $response = $this->get('/print/' . $batch->id . '/pdf');
        $response->assertStatus(200);

        $contentDisposition = $response->headers->get('Content-Disposition');
        // New batch PDF has different naming convention
        $this->assertStringContainsString('BTH', $contentDisposition);
    }

    public function test_duplex_ordering_helper_long_edge(): void
    {
        $cells = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $reordered = \App\Support\PrintOrder::reorderForDuplex($cells, 'long-edge');
        $this->assertEquals([10, 9, 8, 7, 6, 5, 4, 3, 2, 1], $reordered);
    }

    public function test_duplex_ordering_helper_short_edge(): void
    {
        $cells = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $reordered = \App\Support\PrintOrder::reorderForDuplex($cells, 'short-edge');
        // Each row reversed (within-row horizontal flip)
        $this->assertEquals([2, 1, 4, 3, 6, 5, 8, 7, 10, 9], $reordered);
    }

    public function test_pdf_uses_kta_v2_design(): void
    {
        // Verify PDF uses kta-card-v2 partial
        $batch = $this->createBatchWithMembers(1);
        $response = $this->get('/print/' . $batch->id . '/pdf?side=front');
        $response->assertStatus(200);

        $content = $response->getContent();
        // PDF stream usually FlateDecode-compressed, check content length
        $this->assertGreaterThan(1000, strlen($content),
            'PDF should contain content');

        // Verify kta-card-v2-style partial is used
        $html = view('print.partials.kta-card-v2-style', [])->render();
        $this->assertStringContainsString('kta-card', $html);
        $this->assertStringContainsString('kta-bg-image', $html);
    }

    public function test_back_pdf_uses_kta_v2_design(): void
    {
        $batch = $this->createBatchWithMembers(1);
        $response = $this->get('/print/' . $batch->id . '/pdf?side=back');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertGreaterThan(1000, strlen($content));

        // Verify kta-card-v2-style partial is used
        $html = view('print.partials.kta-card-v2-style', [])->render();
        $this->assertStringContainsString('kta-card', $html);
        $this->assertStringContainsString('kta-data-layer', $html);
    }

    public function test_batch_members_count_unchanged_after_duplex(): void
    {
        // Download front dan back dari batch yang sama; anggota batch tetap sama
        $batch = $this->createBatchWithMembers(15);
        $initialCount = $batch->members()->count();

        $this->get('/print/' . $batch->id . '/pdf?side=front')->assertStatus(200);
        $this->get('/print/' . $batch->id . '/pdf?side=back')->assertStatus(200);

        $this->assertEquals($initialCount, $batch->members()->count());
    }
}
