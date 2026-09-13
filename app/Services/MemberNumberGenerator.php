<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Member;
use App\Models\MemberNumber;
use App\Models\MemberNumberFormula;
use App\Models\MemberNumberSequence;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MemberNumberGenerator
{
    private const MAX_RETRIES = 5;

    /**
     * Generate a new member number.
     *
     * @throws RuntimeException if no active formula exists or generation fails
     */
    public function generate(?int $companyId = null): string
    {
        // Get active formula
        $formula = MemberNumberFormula::getActive();

        if (!$formula) {
            throw new RuntimeException(
                'Nomor anggota belum dapat dibuat karena formula nomor anggota belum dikonfigurasi oleh administrator.'
            );
        }

        // If formula requires company code, ensure company exists
        $company = null;
        $prefixValue = '';

        if ($formula->include_company_code) {
            if (!$companyId) {
                throw new RuntimeException(
                    'Perusahaan wajib dipilih karena formula menggunakan kode perusahaan.'
                );
            }

            $company = Company::find($companyId);
            if (!$company) {
                throw new RuntimeException('Perusahaan tidak ditemukan.');
            }

            $prefixValue = $company->kode;
        }

        return $this->generateWithLocking($formula, $companyId, $prefixValue);
    }

    /**
     * Generate multiple member numbers for batch import.
     *
     * @param array $companyIds Array of company IDs (one per row)
     * @return array Array of generated member numbers
     * @throws RuntimeException
     */
    public function generateBatch(array $companyIds): array
    {
        $formula = MemberNumberFormula::getActive();

        if (!$formula) {
            throw new RuntimeException(
                'Nomor anggota belum dapat dibuat karena formula nomor anggota belum dikonfigurasi oleh administrator.'
            );
        }

        return DB::transaction(function () use ($formula, $companyIds) {
            $numbers = [];
            $sequenceCache = [];

            foreach ($companyIds as $index => $companyId) {
                $prefixValue = '';

                if ($formula->include_company_code && $companyId) {
                    $company = Company::find($companyId);
                    if (!$company) {
                        throw new RuntimeException("Perusahaan tidak ditemukan untuk baris #{$index}");
                    }
                    $prefixValue = $company->kode;
                }

                // Check cache first
                $cacheKey = ($formula->id . '-' . ($companyId ?? 'null') . '-' . $prefixValue);

                if (!isset($sequenceCache[$cacheKey])) {
                    // Get or create sequence with locking
                    $sequence = $this->getOrCreateSequenceWithLock($formula->id, $companyId, $prefixValue);
                } else {
                    // Increment cached sequence
                    $sequence = $sequenceCache[$cacheKey];
                }

                // Always increment to get next number
                $nextNumber = $sequence->incrementSequence();
                $sequenceCache[$cacheKey] = $sequence;

                $memberNumber = $formula->buildNumber($prefixValue, $nextNumber);
                $numbers[] = $memberNumber;

                // Create MemberNumber record for master tracking
                $this->createMemberNumberRecord($memberNumber, $formula, $companyId, $prefixValue, $nextNumber);
            }

            return $numbers;
        });
    }

    /**
     * Create a MemberNumber record for master tracking.
     */
    private function createMemberNumberRecord(
        string $number,
        MemberNumberFormula $formula,
        ?int $companyId,
        string $prefixValue,
        int $sequence
    ): MemberNumber {
        return MemberNumber::create([
            'number' => $number,
            'status' => MemberNumber::STATUS_AVAILABLE,
            'company_id' => $companyId,
            'formula_id' => $formula->id,
            'prefix_value' => $prefixValue,
            'sequence' => $sequence,
        ]);
    }

    /**
     * Reserve numbers for preview (without incrementing actual sequence).
     * Returns array of [index => ['company_id' => x, 'prefix_value' => y, 'preview_number' => z]]
     */
    public function reserveForPreview(array $companyIds): array
    {
        $formula = MemberNumberFormula::getActive();

        if (!$formula) {
            return [];
        }

        $reservations = [];
        $sequenceCache = [];

        foreach ($companyIds as $index => $companyId) {
            $prefixValue = '';

            if ($formula->include_company_code && $companyId) {
                $company = Company::find($companyId);
                if ($company) {
                    $prefixValue = $company->kode;
                }
            }

            $cacheKey = ($formula->id . '-' . ($companyId ?? 'null') . '-' . $prefixValue);

            if (!isset($sequenceCache[$cacheKey])) {
                // Get next sequence without incrementing
                $sequence = $this->getNextSequence($formula, $companyId, $prefixValue);
                $sequenceCache[$cacheKey] = $sequence;
            } else {
                // Move to next in sequence
                $sequenceCache[$cacheKey]++;
            }

            $reservations[$index] = [
                'company_id' => $companyId,
                'prefix_value' => $prefixValue,
                'preview_number' => $formula->buildNumber($prefixValue, $sequenceCache[$cacheKey]),
            ];
        }

        return $reservations;
    }

    /**
     * Generate with database locking to prevent race conditions.
     */
    private function generateWithLocking(MemberNumberFormula $formula, ?int $companyId, string $prefixValue): string
    {
        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                return DB::transaction(function () use ($formula, $companyId, $prefixValue) {
                    // Lock the sequence row for update
                    $sequence = $this->getOrCreateSequenceWithLock($formula->id, $companyId, $prefixValue);

                    // Increment sequence
                    $newSequence = $sequence->incrementSequence();

                    // Build the member number
                    $memberNumber = $formula->buildNumber($prefixValue, $newSequence);

                    // Double-check uniqueness in database
                    if (Member::where('nik', $memberNumber)->exists()) {
                        throw new RuntimeException(
                            "Nomor anggota {$memberNumber} sudah ada. Silakan coba lagi."
                        );
                    }

                    // Create MemberNumber record for master tracking
                    $this->createMemberNumberRecord($memberNumber, $formula, $companyId, $prefixValue, $newSequence);

                    return $memberNumber;
                });
            } catch (RuntimeException $e) {
                // If it's a uniqueness error, retry
                if (str_contains($e->getMessage(), 'sudah ada') && $attempt < self::MAX_RETRIES) {
                    // Clear any cached state and retry
                    continue;
                }
                throw $e;
            }
        }

        throw new RuntimeException('Gagal menghasilkan nomor anggota setelah ' . self::MAX_RETRIES . ' percobaan.');
    }

    /**
     * Get or create sequence with pessimistic locking.
     */
    private function getOrCreateSequenceWithLock(int $formulaId, ?int $companyId, string $prefixValue): MemberNumberSequence
    {
        // Try to find existing sequence with lock
        $sequence = MemberNumberSequence::where('formula_id', $formulaId)
            ->where('company_id', $companyId)
            ->lockForUpdate()
            ->first();

        if (!$sequence) {
            // Create new sequence
            $sequence = MemberNumberSequence::create([
                'formula_id' => $formulaId,
                'company_id' => $companyId,
                'last_sequence' => 0,
                'prefix_value' => $prefixValue,
            ]);
        } else {
            // Update prefix value if changed
            if ($sequence->prefix_value !== $prefixValue) {
                $sequence->update(['prefix_value' => $prefixValue]);
            }
        }

        return $sequence;
    }

    /**
     * Get next sequence number without incrementing.
     */
    private function getNextSequence(MemberNumberFormula $formula, ?int $companyId, string $prefixValue): int
    {
        $sequence = MemberNumberSequence::where('formula_id', $formula->id)
            ->where('company_id', $companyId)
            ->first();

        if (!$sequence) {
            return 1;
        }

        return $sequence->last_sequence + 1;
    }

    /**
     * Check if a member number already exists.
     */
    public function numberExists(string $number): bool
    {
        return Member::where('nik', $number)->exists();
    }

    /**
     * Get the next sequence for a specific company without generating.
     */
    public function peekNext(?int $companyId = null): ?string
    {
        $formula = MemberNumberFormula::getActive();

        if (!$formula) {
            return null;
        }

        $prefixValue = '';
        if ($formula->include_company_code && $companyId) {
            $company = Company::find($companyId);
            if ($company) {
                $prefixValue = $company->kode;
            }
        }

        $nextSeq = $this->getNextSequence($formula, $companyId, $prefixValue);

        return $formula->buildNumber($prefixValue, $nextSeq);
    }
}
