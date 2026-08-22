<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Member;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $query = Member::with(['province', 'regency', 'district']);

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

        return view('members.index', [
            'members' => $members,
            'provinces' => $provinces,
            'religions' => $religions,
            'statuses' => $statuses,
        ]);
    }

    public function create(): View
    {
        $provinces = Province::orderBy('name')->get();
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

        return view('members.create', [
            'provinces' => $provinces,
            'religions' => $religions,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:20', 'unique:members,nik'],
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
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('members/photos', 'local');
            $validated['foto_path'] = $path;
        }

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'draft';

        $member = Member::create($validated);

        AuditLog::log('create_member', $member);

        if ($member->hasPhoto()) {
            $member->update(['status' => 'ready']);
        }

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function show(Member $member): View
    {
        $member->load(['province', 'regency', 'district', 'printBatches.period']);

        return view('members.show', ['member' => $member]);
    }

    public function edit(Member $member): View
    {
        $provinces = Province::orderBy('name')->get();
        $regencies = Regency::where('province_id', $member->province_id)->orderBy('name')->get();
        $districts = District::where('regency_id', $member->regency_id)->orderBy('name')->get();
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

        return view('members.edit', [
            'member' => $member,
            'provinces' => $provinces,
            'regencies' => $regencies,
            'districts' => $districts,
            'religions' => $religions,
        ]);
    }

    public function update(Request $request, Member $member): \Illuminate\Http\RedirectResponse
    {
        $oldData = $member->toArray();

        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:20', 'unique:members,nik,' . $member->id],
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
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            if ($member->foto_path) {
                Storage::disk('local')->delete($member->foto_path);
            }
            $path = $request->file('foto')->store('members/photos', 'local');
            $validated['foto_path'] = $path;
        }

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

    public function getRegencies(Request $request): Response
    {
        $regencies = Regency::where('province_id', $request->province_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($regencies);
    }

    public function getDistricts(Request $request): Response
    {
        $districts = District::where('regency_id', $request->regency_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($districts);
    }

    public function bulkAction(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'member_ids' => ['required', 'array', 'min:1'],
            'action' => ['required', 'in:delete,update_status,export'],
        ]);

        $members = Member::whereIn('id', $validated['member_ids']);

        switch ($validated['action']) {
            case 'delete':
                foreach ($members->get() as $member) {
                    if ($member->foto_path) {
                        Storage::disk('local')->delete($member->foto_path);
                    }
                    AuditLog::log('delete_member', null, $member->toArray(), null);
                    $member->delete();
                }
                return redirect()->back()->with('success', count($validated['member_ids']) . ' anggota berhasil dihapus.');

            case 'update_status':
                $request->validate(['new_status' => ['required', 'in:draft,ready,generated,active,inactive']]);
                $members->update(['status' => $request->new_status]);
                return redirect()->back()->with('success', count($validated['member_ids']) . ' anggota berhasil diperbarui.');
        }

        return redirect()->back();
    }
}
