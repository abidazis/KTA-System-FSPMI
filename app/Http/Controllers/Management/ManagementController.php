<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ManagementOfficial;
use App\Models\ManagementPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ManagementController extends Controller
{
    public function index(): View
    {
        $periods = ManagementPeriod::with('officials')
            ->latest()
            ->paginate(10);

        return view('management.index', ['periods' => $periods]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'nama_periode' => ['required', 'string', 'max:100'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
        ]);

        if ($request->has('set_active')) {
            ManagementPeriod::where('status', 'active')->update(['status' => 'inactive']);
            $validated['status'] = 'active';
        }

        $period = ManagementPeriod::create($validated);

        AuditLog::log('create_management_period', $period);

        return redirect()
            ->route('management.show', $period)
            ->with('success', 'Periode kepengurusan berhasil dibuat.');
    }

    public function show(ManagementPeriod $period): View
    {
        $period->load('officials');

        return view('management.show', ['period' => $period]);
    }

    public function update(Request $request, ManagementPeriod $period): \Illuminate\Http\RedirectResponse
    {
        $oldData = $period->toArray();

        $validated = $request->validate([
            'nama_periode' => ['required', 'string', 'max:100'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
        ]);

        $period->update($validated);

        AuditLog::log('update_management_period', $period, $oldData, $validated);

        return redirect()
            ->route('management.show', $period)
            ->with('success', 'Periode kepengurusan berhasil diperbarui.');
    }

    public function setActive(ManagementPeriod $period): \Illuminate\Http\RedirectResponse
    {
        ManagementPeriod::where('status', 'active')->update(['status' => 'inactive']);

        $period->update(['status' => 'active']);

        AuditLog::log('set_active_management_period', $period);

        return redirect()
            ->route('management.index')
            ->with('success', 'Periode aktif berhasil diubah.');
    }

    public function destroy(ManagementPeriod $period): \Illuminate\Http\RedirectResponse
    {
        if ($period->printBatches()->exists()) {
            return redirect()
                ->route('management.index')
                ->with('error', 'Periode tidak dapat dihapus karena sudah memiliki riwayat cetak.');
        }

        $oldData = $period->toArray();

        foreach ($period->officials as $official) {
            if ($official->signature_path) {
                Storage::disk('local')->delete($official->signature_path);
            }
        }

        $period->delete();

        AuditLog::log('delete_management_period', null, $oldData, null);

        return redirect()
            ->route('management.index')
            ->with('success', 'Periode berhasil dihapus.');
    }

    public function storeOfficial(Request $request, ManagementPeriod $period): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'jabatan' => ['required', 'string', 'max:100'],
            'nama' => ['required', 'string', 'max:100'],
            'signature' => ['nullable', 'image', 'mimes:png', 'max:512'],
        ]);

        $ketuaExists = ManagementOfficial::where('management_period_id', $period->id)
            ->where('jabatan', 'Ketua Umum')
            ->where('status', 'active')
            ->exists();

        if ($validated['jabatan'] === 'Ketua Umum' && $ketuaExists) {
            return redirect()
                ->back()
                ->with('error', 'Ketua Umum sudah ada untuk periode ini.');
        }

        if ($request->hasFile('signature')) {
            $path = $request->file('signature')->store('members/signatures', 'local');
            $validated['signature_path'] = $path;
        }

        $validated['management_period_id'] = $period->id;
        $validated['status'] = 'active';

        $official = ManagementOfficial::create($validated);

        AuditLog::log('create_management_official', $official);

        return redirect()
            ->route('management.show', $period)
            ->with('success', 'Pengurus berhasil ditambahkan.');
    }

    public function updateOfficial(Request $request, ManagementPeriod $period, ManagementOfficial $official): \Illuminate\Http\RedirectResponse
    {
        $oldData = $official->toArray();

        $validated = $request->validate([
            'jabatan' => ['required', 'string', 'max:100'],
            'nama' => ['required', 'string', 'max:100'],
            'signature' => ['nullable', 'image', 'mimes:png', 'max:512'],
        ]);

        if ($validated['jabatan'] === 'Ketua Umum') {
            $ketuaExists = ManagementOfficial::where('management_period_id', $period->id)
                ->where('jabatan', 'Ketua Umum')
                ->where('id', '!=', $official->id)
                ->where('status', 'active')
                ->exists();

            if ($ketuaExists) {
                return redirect()
                    ->back()
                    ->with('error', 'Ketua Umum sudah ada untuk periode ini.');
            }
        }

        if ($request->hasFile('signature')) {
            if ($official->signature_path) {
                Storage::disk('local')->delete($official->signature_path);
            }
            $path = $request->file('signature')->store('members/signatures', 'local');
            $validated['signature_path'] = $path;
        }

        $official->update($validated);

        AuditLog::log('update_management_official', $official, $oldData, $validated);

        return redirect()
            ->route('management.show', $period)
            ->with('success', 'Pengurus berhasil diperbarui.');
    }

    public function destroyOfficial(ManagementPeriod $period, ManagementOfficial $official): \Illuminate\Http\RedirectResponse
    {
        $oldData = $official->toArray();

        if ($official->signature_path) {
            Storage::disk('local')->delete($official->signature_path);
        }

        $official->delete();

        AuditLog::log('delete_management_official', null, $oldData, null);

        return redirect()
            ->route('management.show', $period)
            ->with('success', 'Pengurus berhasil dihapus.');
    }
}
