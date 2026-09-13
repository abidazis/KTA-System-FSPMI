<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\MemberNumberFormula;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberNumberFormulaController extends Controller
{
    /**
     * Display list of formulas.
     */
    public function index(): View
    {
        $formulas = MemberNumberFormula::with('creator')
            ->orderByDesc('is_active')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeFormula = MemberNumberFormula::getActive();

        return view('settings.member-number-formulas.index', [
            'formulas' => $formulas,
            'activeFormula' => $activeFormula,
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        $companies = Company::active()->orderBy('kode')->get();

        return view('settings.member-number-formulas.create', [
            'companies' => $companies,
        ]);
    }

    /**
     * Store new formula.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'prefix' => ['nullable', 'string', 'max:20'],
            'separator' => ['nullable', 'string', 'max:10'],
            'include_company_code' => ['boolean'],
            'sequence_digits' => ['required', 'integer', 'min:1', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        $validated['prefix'] = $validated['prefix'] ?? '';
        $validated['separator'] = $validated['separator'] ?? '-';
        $validated['include_company_code'] = $request->boolean('include_company_code');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['created_by'] = auth()->id();

        // If activating, deactivate others first
        if ($validated['is_active']) {
            MemberNumberFormula::where('is_active', true)->update(['is_active' => false]);
        }

        $formula = MemberNumberFormula::create($validated);

        AuditLog::log('create_member_number_formula', $formula);

        return redirect()
            ->route('member-number-formulas.index')
            ->with('success', 'Formula nomor anggota berhasil dibuat.');
    }

    /**
     * Show edit form.
     */
    public function edit(MemberNumberFormula $formula): View
    {
        $companies = Company::active()->orderBy('kode')->get();

        return view('settings.member-number-formulas.edit', [
            'formula' => $formula,
            'companies' => $companies,
        ]);
    }

    /**
     * Update formula.
     */
    public function update(Request $request, MemberNumberFormula $formula): \Illuminate\Http\RedirectResponse
    {
        $oldData = $formula->toArray();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'prefix' => ['nullable', 'string', 'max:20'],
            'separator' => ['nullable', 'string', 'max:10'],
            'include_company_code' => ['boolean'],
            'sequence_digits' => ['required', 'integer', 'min:1', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        $validated['prefix'] = $validated['prefix'] ?? '';
        $validated['separator'] = $validated['separator'] ?? '-';
        $validated['include_company_code'] = $request->boolean('include_company_code');
        $validated['is_active'] = $request->boolean('is_active');

        // If activating, deactivate others first
        if ($validated['is_active'] && !$formula->is_active) {
            MemberNumberFormula::where('is_active', true)->update(['is_active' => false]);
        }

        $formula->update($validated);

        AuditLog::log('update_member_number_formula', $formula, $oldData, $validated);

        return redirect()
            ->route('member-number-formulas.index')
            ->with('success', 'Formula nomor anggota berhasil diperbarui.');
    }

    /**
     * Toggle formula active status.
     */
    public function toggleActive(MemberNumberFormula $formula): JsonResponse
    {
        $oldStatus = $formula->is_active;

        if ($formula->is_active) {
            // Deactivate
            $formula->deactivate();
        } else {
            // Activate (this will deactivate others)
            $formula->activate();
        }

        AuditLog::log('toggle_member_number_formula', $formula, [
            'is_active' => $oldStatus
        ], [
            'is_active' => $formula->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => $formula->is_active
                ? 'Formula berhasil diaktifkan.'
                : 'Formula berhasil dinonaktifkan.',
            'is_active' => $formula->is_active,
        ]);
    }

    /**
     * Delete formula.
     */
    public function destroy(MemberNumberFormula $formula): \Illuminate\Http\RedirectResponse
    {
        if ($formula->is_active) {
            return redirect()
                ->route('member-number-formulas.index')
                ->with('error', 'Tidak dapat menghapus formula yang sedang aktif.');
        }

        $oldData = $formula->toArray();

        $formula->delete();

        AuditLog::log('delete_member_number_formula', null, $oldData, null);

        return redirect()
            ->route('member-number-formulas.index')
            ->with('success', 'Formula nomor anggota berhasil dihapus.');
    }

    /**
     * Preview formula with sample data.
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prefix' => ['nullable', 'string', 'max:20'],
            'separator' => ['nullable', 'string', 'max:10'],
            'include_company_code' => ['boolean'],
            'sequence_digits' => ['required', 'integer', 'min:1', 'max:10'],
            'company_kode' => ['nullable', 'string', 'max:20'],
        ]);

        $prefix = $validated['prefix'] ?? '';
        $separator = $validated['separator'] ?? '-';
        $includeCompanyCode = $request->boolean('include_company_code');
        $sequenceDigits = (int) $validated['sequence_digits'];
        $companyKode = $validated['company_kode'] ?? null;

        $parts = [];

        if (!empty($prefix)) {
            $parts[] = $prefix;
        }

        if ($includeCompanyCode && !empty($companyKode)) {
            $parts[] = strtoupper($companyKode);
        }

        $parts[] = str_repeat('0', $sequenceDigits);

        $sample = implode($separator, $parts);

        // Generate a few more examples
        $examples = [];
        for ($i = 1; $i <= 3; $i++) {
            $seqParts = $parts;
            array_pop($seqParts); // Remove placeholder
            $seqParts[] = str_pad((string) $i, $sequenceDigits, '0', STR_PAD_LEFT);
            $examples[] = implode($separator, $seqParts);
        }

        return response()->json([
            'sample' => $sample,
            'examples' => $examples,
        ]);
    }
}
