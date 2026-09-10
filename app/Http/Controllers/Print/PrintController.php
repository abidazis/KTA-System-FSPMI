<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\KtaBackground;
use App\Models\Member;
use App\Models\ManagementPeriod;
use App\Models\PrintBatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PrintController extends Controller
{
    public function index(Request $request): View
    {
        $query = PrintBatch::with(['period', 'printer', 'members']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('members', function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $batches = $query->latest()->paginate(15);

        return view('print.index', ['batches' => $batches]);
    }

    public function create(Request $request): View
    {
        // Tampilkan SEMUA anggota tanpa pagination - user pilih mana yang mau dicetak
        $query = Member::with(['province', 'regency', 'district', 'company']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tampilkan semua tanpa pagination (scroll)
        $members = $query->latest()->get();

        return view('print.create', ['members' => $members]);
    }

    public function preview(Request $request): View
    {
        // Ambil member_ids dari array atau dari JSON
        $memberIds = [];

        if ($request->has('member_ids')) {
            $ids = $request->member_ids;
            if (is_array($ids)) {
                $memberIds = array_map('intval', $ids);
            } elseif (is_string($ids)) {
                $memberIds = array_map('intval', explode(',', $ids));
            }
        } elseif ($request->filled('selected_ids_json')) {
            $decoded = json_decode($request->selected_ids_json, true);
            if (is_array($decoded)) {
                $memberIds = array_map('intval', $decoded);
            }
        }

        if (empty($memberIds)) {
            return redirect()
                ->route('print.create')
                ->with('error', 'Pilih minimal satu anggota.');
        }

        $members = Member::with(['province', 'regency', 'district', 'company'])
            ->whereIn('id', $memberIds)
            ->get();

        $period = ManagementPeriod::where('status', 'active')->first();

        if (!$period) {
            return redirect()
                ->route('print.create')
                ->with('error', 'Periode kepengurusan aktif tidak ditemukan.');
        }

        $officials = $period->activeOfficials()->get();
        $ketua = $officials->firstWhere('jabatan', 'Ketua Umum');
        $sekretaris = $officials->firstWhere('jabatan', 'Sekretaris Umum');

        $ktaData = $members->map(function ($member) use ($ketua, $sekretaris, $period) {
            return [
                'member' => $member,
                'ketua' => $ketua,
                'sekretaris' => $sekretaris,
                'period' => $period,
                'tanggal_cetak' => now()->format('d F Y'),
                'foto_path' => $member->foto_path ? storage_path('app/private/' . $member->foto_path) : null,
                'ttd_ketua_path' => $ketua && $ketua->signature_path ? storage_path('app/private/' . $ketua->signature_path) : null,
                'ttd_sekretaris_path' => $sekretaris && $sekretaris->signature_path ? storage_path('app/private/' . $sekretaris->signature_path) : null,
                'stempel_path' => $period->stempel_path ? storage_path('app/private/' . $period->stempel_path) : null,
            ];
        });

        // Ambil background aktif
        $background = KtaBackground::getActive();

        return view('print.preview', [
            'members' => $ktaData,
            'memberIds' => $request->member_ids,
            'count' => count($members),
            'background' => $background,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        // Ambil member_ids dari array
        $memberIds = [];

        if ($request->has('member_ids')) {
            $ids = $request->member_ids;
            if (is_array($ids)) {
                $memberIds = array_map('intval', $ids);
            } elseif (is_string($ids)) {
                $memberIds = array_map('intval', explode(',', $ids));
            }
        } elseif ($request->filled('selected_ids_json')) {
            $decoded = json_decode($request->selected_ids_json, true);
            if (is_array($decoded)) {
                $memberIds = array_map('intval', $decoded);
            }
        }

        if (empty($memberIds)) {
            return redirect()
                ->route('print.create')
                ->with('error', 'Pilih minimal satu anggota.');
        }

        $members = Member::whereIn('id', $memberIds)->get();

        if ($members->isEmpty()) {
            return redirect()
                ->route('print.create')
                ->with('error', 'Anggota tidak ditemukan.');
        }

        $period = ManagementPeriod::where('status', 'active')->first();

        if (!$period) {
            return redirect()
                ->route('print.create')
                ->with('error', 'Periode kepengurusan aktif tidak ditemukan.');
        }

        // Satu batch merepresentasikan satu kumpulan anggota.
        // Field 'type' tetap disimpan untuk kompatibilitas historis (default 'front').
        $batch = PrintBatch::create([
            'batch_number' => PrintBatch::generateBatchNumber(),
            'management_period_id' => $period->id,
            'printed_by' => auth()->id(),
            'tanggal_cetak' => now()->toDateString(),
            'jumlah' => count($members),
            'type' => 'front',
        ]);

        foreach ($members as $index => $member) {
            $batch->members()->attach($member->id, ['position' => $index + 1]);
            // Status tidak diubah di sini — anggota akan dicetak, bukan otomatis 'printed'.
        }

        AuditLog::log('create_print_batch', $batch, null, [
            'member_count' => count($members),
            'member_ids' => $request->member_ids,
        ]);

        return redirect()
            ->route('print.show', $batch)
            ->with('success', "Batch {$batch->batch_number} berhasil dibuat dengan " . count($members) . " anggota.");
    }

    public function show(PrintBatch $batch): View
    {
        $batch->load(['period', 'printer', 'members.company']);

        $officials = $batch->period->activeOfficials()->get();
        $ketua = $officials->firstWhere('jabatan', 'Ketua Umum');
        $sekretaris = $officials->firstWhere('jabatan', 'Sekretaris Umum');

        $ktaData = $batch->members->map(function ($member) use ($ketua, $sekretaris, $batch) {
            return [
                'member' => $member,
                'ketua' => $ketua,
                'sekretaris' => $sekretaris,
                'period' => $batch->period,
                'tanggal_cetak' => $batch->tanggal_cetak->format('d F Y'),
                'foto_path' => $member->foto_path ? storage_path('app/private/' . $member->foto_path) : null,
                'ttd_ketua_path' => $ketua && $ketua->signature_path ? storage_path('app/private/' . $ketua->signature_path) : null,
                'ttd_sekretaris_path' => $sekretaris && $sekretaris->signature_path ? storage_path('app/private/' . $sekretaris->signature_path) : null,
                'stempel_path' => $batch->period->stempel_path ? storage_path('app/private/' . $batch->period->stempel_path) : null,
            ];
        });

        // Ambil background aktif
        $background = KtaBackground::getActive();

        return view('print.show', [
            'batch' => $batch,
            'members' => $ktaData,
            'ketua' => $ketua,
            'sekretaris' => $sekretaris,
            'background' => $background,
        ]);
    }

    public function downloadPdf(Request $request, PrintBatch $batch): \Symfony\Component\HttpFoundation\Response
    {
        $batch->load(['period', 'printer', 'members.company']);

        $officials = $batch->period->activeOfficials()->get();
        $ketua = $officials->firstWhere('jabatan', 'Ketua Umum');
        $sekretaris = $officials->firstWhere('jabatan', 'Sekretaris Umum');

        // Query parameter: ?duplex=long-edge|short-edge (default long-edge)
        $duplexMode = $request->input('duplex', 'long-edge');
        if (!in_array($duplexMode, ['long-edge', 'short-edge'], true)) {
            $duplexMode = 'long-edge';
        }

        $ktaData = $batch->members->map(function ($member) use ($ketua, $sekretaris, $batch) {
            return [
                'member' => $member,
                'ketua' => $ketua,
                'sekretaris' => $sekretaris,
                'period' => $batch->period,
                'tanggal_cetak' => $batch->tanggal_cetak->format('d F Y'),
                'foto_path' => $member->foto_path ? storage_path('app/private/' . $member->foto_path) : null,
                'ttd_ketua_path' => $ketua && $ketua->signature_path ? storage_path('app/private/' . $ketua->signature_path) : null,
                'ttd_sekretaris_path' => $sekretaris && $sekretaris->signature_path ? storage_path('app/private/' . $sekretaris->signature_path) : null,
                'stempel_path' => $batch->period->stempel_path ? storage_path('app/private/' . $batch->period->stempel_path) : null,
            ];
        });

        // Ambil background aktif
        $background = KtaBackground::getActive();

        $pdf = Pdf::loadView('print.batch-pdf', [
            'members' => $ktaData,
            'batch' => $batch,
            'ketua' => $ketua,
            'sekretaris' => $sekretaris,
            'stempel_path' => $batch->period->stempel_path ? storage_path('app/private/' . $batch->period->stempel_path) : null,
            'duplexMode' => $duplexMode,
            'background' => $background,
        ]);

        // A4 Landscape — 297x210mm — 5 KTA per page (3 top + 2 bottom)
        $pdf->setPaper('a4', 'landscape');

        $filename = $batch->batch_number . '-KTA.pdf';

        AuditLog::log('download_print_batch_pdf', $batch, null, ['duplex' => $duplexMode]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function destroy(PrintBatch $batch): \Illuminate\Http\RedirectResponse
    {
        $oldData = $batch->toArray();

        $batch->members()->detach();
        $batch->delete();

        AuditLog::log('delete_print_batch', null, $oldData, null);

        return redirect()
            ->route('print.index')
            ->with('success', 'Batch cetak berhasil dihapus.');
    }
}
