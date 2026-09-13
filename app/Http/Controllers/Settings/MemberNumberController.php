<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Member;
use App\Models\MemberNumber;
use App\Models\MemberNumberFormula;
use App\Services\MemberNumberGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class MemberNumberController extends Controller
{
    private MemberNumberGenerator $generator;

    public function __construct(MemberNumberGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Display the master member numbers list.
     */
    public function index(Request $request): View
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');
        $companyId = $request->get('company_id');

        $query = MemberNumber::with(['member', 'company', 'formula']);

        // Filter by status
        if ($status === 'available') {
            $query->available();
        } elseif ($status === 'used') {
            $query->used();
        }

        // Filter by company
        if ($companyId) {
            $query->forCompany($companyId);
        }

        // Search
        $query->search($search);

        // Order by newest first
        $query->orderByDesc('created_at');

        $memberNumbers = $query->paginate(25)->withQueryString();

        $stats = MemberNumber::getStats();
        $companies = Company::orderBy('name')->get();
        $activeFormula = MemberNumberFormula::getActive();

        return view('settings.member-numbers.index', [
            'memberNumbers' => $memberNumbers,
            'stats' => $stats,
            'companies' => $companies,
            'activeFormula' => $activeFormula,
            'filters' => [
                'status' => $status,
                'search' => $search,
                'company_id' => $companyId,
            ],
        ]);
    }

    /**
     * Generate new member numbers.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'count' => 'required|integer|min:1|max:100',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $count = (int) $request->input('count');
        $companyId = $request->input('company_id');

        try {
            // Generate batch of numbers
            $companyIds = array_fill(0, $count, $companyId);
            $numbers = $this->generator->generateBatch($companyIds);

            // Create MemberNumber records
            $formula = MemberNumberFormula::getActive();
            $created = [];

            foreach ($numbers as $number) {
                // Parse number to get prefix_value and sequence
                $parsed = $this->parseMemberNumber($number, $formula);

                $memberNumber = MemberNumber::create([
                    'number' => $number,
                    'status' => MemberNumber::STATUS_AVAILABLE,
                    'company_id' => $companyId,
                    'formula_id' => $formula?->id,
                    'prefix_value' => $parsed['prefix_value'],
                    'sequence' => $parsed['sequence'],
                ]);

                $created[] = $memberNumber;
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghasilkan {$count} nomor anggota.",
                'count' => count($created),
                'data' => array_map(fn($mn) => [
                    'id' => $mn->id,
                    'number' => $mn->number,
                    'status' => $mn->status,
                ], $created),
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Assign an available number to a member.
     */
    public function assign(Request $request, MemberNumber $memberNumber): JsonResponse
    {
        if (!$memberNumber->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor ini sudah digunakan.',
            ], 400);
        }

        $request->validate([
            'member_id' => 'required|exists:members,id',
        ]);

        $member = Member::find($request->input('member_id'));

        // Check if member already has a number
        if ($member->nik) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota ini sudah memiliki nomor anggota.',
            ], 400);
        }

        // Use transaction to ensure consistency
        \Illuminate\Support\Facades\DB::transaction(function () use ($memberNumber, $member) {
            // Update member with the number
            $member->update(['nik' => $memberNumber->number]);

            // Mark number as used
            $memberNumber->markAsUsed($member);
        });

        return response()->json([
            'success' => true,
            'message' => "Nomor {$memberNumber->number} berhasil diberikan ke {$member->nama}.",
        ]);
    }

    /**
     * Get member details for assignment modal.
     */
    public function getAvailableMembers(Request $request): JsonResponse
    {
        $search = $request->get('search', '');
        $companyId = $request->get('company_id');

        $query = Member::whereNull('nik'); // Only members without number

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($search) {
            $query->where('nama', 'like', "%{$search}%");
        }

        $members = $query->orderBy('nama')->limit(20)->get(['id', 'nama', 'company_id']);

        // Load company names
        $members->load('company');

        return response()->json([
            'success' => true,
            'data' => $members->map(fn($m) => [
                'id' => $m->id,
                'nama' => $m->nama,
                'company' => $m->company?->name,
            ]),
        ]);
    }

    /**
     * Get next available number preview.
     */
    public function previewNext(): JsonResponse
    {
        $nextNumber = $this->generator->peekNext();

        return response()->json([
            'success' => true,
            'next_number' => $nextNumber,
            'has_active_formula' => MemberNumberFormula::getActive() !== null,
        ]);
    }

    /**
     * Parse member number to extract prefix_value and sequence.
     */
    private function parseMemberNumber(string $number, ?MemberNumberFormula $formula): array
    {
        $prefixValue = '';
        $sequence = 0;

        if ($formula) {
            // Extract sequence from the end
            $digits = $formula->sequence_digits;
            $sequence = (int) substr($number, -$digits);

            // Extract prefix value (everything between prefix and sequence)
            $prefix = $formula->prefix;
            $separator = $formula->separator;

            if ($prefix && str_starts_with($number, $prefix . $separator)) {
                $withoutPrefix = substr($number, strlen($prefix . $separator));
                if ($formula->include_company_code) {
                    // Remove sequence from end
                    $withoutSeq = substr($withoutPrefix, 0, -($digits + strlen($separator)));
                    $prefixValue = $withoutSeq;
                } else {
                    $prefixValue = '';
                }
            }
        } else {
            // Fallback: try to extract last number
            preg_match('/(\d+)$/', $number, $matches);
            $sequence = isset($matches[1]) ? (int) $matches[1] : 0;
        }

        return [
            'prefix_value' => $prefixValue,
            'sequence' => $sequence,
        ];
    }
}
