<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Member;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Services\MemberNumberGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MembersExport;
use RuntimeException;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $query = Member::with(['province', 'regency', 'district', 'company']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        if ($request->filled('regency_id')) {
            $query->where('regency_id', $request->regency_id);
        }

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }

        if ($request->filled('agama')) {
            $query->where('agama', $request->agama);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('masa_berlaku')) {
            switch ($request->masa_berlaku) {
                case 'expired':
                    $query->where('berlaku_hingga', '<', now()->toDateString());
                    break;
                case 'expiring':
                    $query->whereBetween('berlaku_hingga', [now()->toDateString(), now()->addDays(30)->toDateString()]);
                    break;
                case 'active':
                    $query->where('berlaku_hingga', '>', now()->addDays(30)->toDateString());
                    break;
            }
        }

        $members = $query->latest()->paginate(25)->withQueryString();

        $provinces = Province::orderBy('name')->get();
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
        $statuses = ['draft', 'ready', 'generated', 'printed', 'active', 'expired', 'inactive'];
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('members.index', [
            'members' => $members,
            'provinces' => $provinces,
            'religions' => $religions,
            'statuses' => $statuses,
            'companies' => $companies,
        ]);
    }

    public function create(): View
    {
        $provinces = Province::orderBy('name')->get();
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        // Pre-load regencies and districts if coming from validation error
        $regencies = collect();
        $districts = collect();

        if (old('regency_id')) {
            $regencies = Regency::where('province_id', old('province_id'))
                ->orderBy('name')->get();
        }

        if (old('district_id')) {
            $districts = District::where('regency_id', old('regency_id'))
                ->orderBy('name')->get();
        }

        return view('members.create', [
            'provinces' => $provinces,
            'religions' => $religions,
            'companies' => $companies,
            'regencies' => $regencies,
            'districts' => $districts,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'alamat' => ['required', 'string'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'regency_id' => ['nullable', 'exists:regencies,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'],
            'agama' => ['required', 'string', 'max:50'],
            'berlaku_hingga' => ['required', 'date', 'after:today'],
            'tanggal_pembuatan' => ['required', 'date'],
            'company_id' => ['required', 'exists:companies,id'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        // Generate member number automatically
        try {
            $generator = new MemberNumberGenerator();
            $nomorAnggota = $generator->generate($validated['company_id']);
        } catch (RuntimeException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('members/photos', 'local');
            $validated['foto_path'] = $path;
        }

        $validated['nik'] = $nomorAnggota;
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'draft';

        $member = Member::create($validated);

        AuditLog::log('create_member', $member);

        if ($member->hasPhoto()) {
            $member->update(['status' => 'ready']);
        }

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Anggota berhasil ditambahkan. No. Anggota: ' . $nomorAnggota);
    }

    public function show(Member $member): View
    {
        $member->load(['province', 'regency', 'district', 'company', 'printBatches.period']);

        return view('members.show', ['member' => $member]);
    }

    public function edit(Member $member): View
    {
        $provinces = Province::orderBy('name')->get();
        $regencies = Regency::where('province_id', $member->province_id)->orderBy('name')->get();
        $districts = District::where('regency_id', $member->regency_id)->orderBy('name')->get();
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('members.edit', [
            'member' => $member,
            'provinces' => $provinces,
            'regencies' => $regencies,
            'districts' => $districts,
            'religions' => $religions,
            'companies' => $companies,
        ]);
    }

    public function update(Request $request, Member $member): \Illuminate\Http\RedirectResponse
    {
        $oldData = $member->toArray();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'alamat' => ['required', 'string'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'regency_id' => ['nullable', 'exists:regencies,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'],
            'agama' => ['required', 'string', 'max:50'],
            'berlaku_hingga' => ['required', 'date'],
            'tanggal_pembuatan' => ['required', 'date'],
            'company_id' => ['required', 'exists:companies,id'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            if ($member->foto_path) {
                Storage::disk('local')->delete($member->foto_path);
            }
            $path = $request->file('foto')->store('members/photos', 'local');
            $validated['foto_path'] = $path;
        }

        // nik is NOT editable - preserve existing value
        $member->update($validated);

        AuditLog::log('update_member', $member, $oldData, $validated);

        if ($member->hasPhoto() && $member->status === 'draft') {
            $member->update(['status' => 'ready']);
        }

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Member $member): \Illuminate\Http\RedirectResponse
    {
        $oldData = $member->toArray();

        if ($member->foto_path) {
            Storage::disk('local')->delete($member->foto_path);
        }

        $member->delete();

        AuditLog::log('delete_member', null, $oldData, null);

        return redirect()
            ->route('members.index')
            ->with('success', 'Anggota berhasil dihapus.');
    }

    public function getRegencies($provinceId): JsonResponse
    {
        $regencies = Regency::where('province_id', $provinceId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($regencies);
    }

    public function getDistricts($regencyId): JsonResponse
    {
        $districts = District::where('regency_id', $regencyId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($districts);
    }

    public function updateStatus(Request $request, Member $member): JsonResponse
    {
        // Handle status update
        if ($request->has('status')) {
            $validated = $request->validate([
                'status' => ['required', 'in:draft,ready,generated,printed,active,inactive'],
            ]);

            $oldStatus = $member->status;
            $member->update(['status' => $validated['status']]);

            AuditLog::log('update_member_status', $member, ['status' => $oldStatus], ['status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah',
                'status' => $member->status,
            ]);
        }

        // Handle company_id update
        if ($request->has('company_id')) {
            $validated = $request->validate([
                'company_id' => ['nullable', 'exists:companies,id'],
            ]);

            $oldCompanyId = $member->company_id;
            $member->update(['company_id' => $validated['company_id'] ?: null]);

            AuditLog::log('update_member_company', $member, ['company_id' => $oldCompanyId], ['company_id' => $validated['company_id']]);

            $member->load('company');

            return response()->json([
                'success' => true,
                'message' => 'Perusahaan berhasil diubah',
                'company_name' => $member->company?->name ?? '-',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada data yang diubah.'
        ], 422);
    }

    /**
     * Bulk action handler - handles update_status, edit, and delete
     * Returns JSON for JavaScript fetch API
     */
    public function bulkAction(Request $request): JsonResponse
    {
        // Validate required fields
        $validated = $request->validate([
            'member_ids' => ['required', 'array', 'min:1'],
            'action' => ['required', 'in:delete,update_status,edit'],
        ]);

        $count = count($validated['member_ids']);

        try {
            switch ($validated['action']) {
                case 'delete':
                    $members = Member::whereIn('id', $validated['member_ids'])->get();
                    foreach ($members as $member) {
                        if ($member->foto_path) {
                            Storage::disk('local')->delete($member->foto_path);
                        }
                        AuditLog::log('delete_member', null, $member->toArray(), null);
                        $member->delete();
                    }
                    return response()->json([
                        'success' => true,
                        'message' => $count . ' anggota berhasil dihapus.',
                        'count' => $count
                    ]);

                case 'update_status':
                    $newStatus = $request->input('new_status');
                    if (!$newStatus) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Status baru wajib dipilih.'
                        ], 422);
                    }
                    $validStatuses = ['draft', 'ready', 'generated', 'printed', 'active', 'inactive'];
                    if (!in_array($newStatus, $validStatuses)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Status tidak valid.'
                        ], 422);
                    }
                    $updated = Member::whereIn('id', $validated['member_ids'])->update(['status' => $newStatus]);
                    AuditLog::log('bulk_update_member_status', null, ['ids' => $validated['member_ids']], ['status' => $newStatus, 'count' => $updated]);
                    return response()->json([
                        'success' => true,
                        'message' => $count . ' anggota berhasil diperbarui.',
                        'count' => $updated
                    ]);

                case 'edit':
                    $updateData = [];

                    // Check bulk_ prefix fields first, then fallback to direct names
                    $provinceId = $request->input('bulk_province_id') ?: $request->input('province_id');
                    $regencyId = $request->input('bulk_regency_id') ?: $request->input('regency_id');
                    $districtId = $request->input('bulk_district_id') ?: $request->input('district_id');
                    $berlaku = $request->input('bulk_berlaku_hingga') ?: $request->input('berlaku_hingga');

                    if ($provinceId && $provinceId !== '') {
                        $updateData['province_id'] = $provinceId;
                    }
                    if ($regencyId && $regencyId !== '') {
                        $updateData['regency_id'] = $regencyId;
                    }
                    if ($districtId && $districtId !== '') {
                        $updateData['district_id'] = $districtId;
                    }
                    if ($berlaku && $berlaku !== '') {
                        $updateData['berlaku_hingga'] = $berlaku;
                    }

                    if (empty($updateData)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Tidak ada data yang diubah.'
                        ], 422);
                    }

                    $updated = Member::whereIn('id', $validated['member_ids'])->update($updateData);
                    AuditLog::log('bulk_edit_member', null, ['ids' => $validated['member_ids']], array_merge($updateData, ['count' => $updated]));
                    return response()->json([
                        'success' => true,
                        'message' => $count . ' anggota berhasil diperbarui.',
                        'count' => $updated
                    ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => false,
            'message' => 'Aksi tidak valid.'
        ], 422);
    }

    /**
     * Export members to Excel
     * Uses the same filters as the index method
     */
    public function export(Request $request)
    {
        // Collect filters from query string (same as index page)
        $filters = $request->only([
            'search',
            'province_id',
            'regency_id',
            'district_id',
            'jenis_kelamin',
            'agama',
            'status',
            'masa_berlaku',
        ]);

        // Generate filename with date
        $date = now()->format('Y-m-d');
        $filename = "data-anggota-fspmi-{$date}.xlsx";

        // If there are active filters, add them to filename for clarity
        if (!empty($filters)) {
            // Build a short filter descriptor
            $filterParts = [];
            if (!empty($filters['search'])) {
                $filterParts[] = 'srch';
            }
            if (!empty($filters['province_id'])) {
                $filterParts[] = 'prov';
            }
            if (!empty($filters['regency_id'])) {
                $filterParts[] = 'kab';
            }
            if (!empty($filters['district_id'])) {
                $filterParts[] = 'kec';
            }
            if (!empty($filters['status'])) {
                $filterParts[] = $filters['status'];
            }
            if (!empty($filterParts)) {
                $filename = "data-anggota-fspmi-{$date}(" . implode('-', $filterParts) . ").xlsx";
            }
        }

        return Excel::download(new MembersExport($filters), $filename);
    }
}
