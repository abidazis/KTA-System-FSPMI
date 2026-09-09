<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\KtaBackground;
use App\Models\Member;
use App\Models\ManagementPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KtaController extends Controller
{
    public function generate(Request $request, Member $member): \Illuminate\Http\RedirectResponse
    {
        if (!$member->hasPhoto()) {
            return redirect()
                ->back()
                ->with('error', 'Tidak dapat generate KTA. Foto anggota belum tersedia.');
        }

        $period = ManagementPeriod::where('status', 'active')->first();

        if (!$period) {
            return redirect()
                ->back()
                ->with('error', 'Tidak dapat generate KTA. Periode kepengurusan aktif tidak ditemukan.');
        }

        $member->update([
            'status' => 'generated',
            'generated_at' => now(),
            'management_period_id' => $period->id,
        ]);

        return redirect()
            ->route('members.kta.preview', $member)
            ->with('success', 'KTA berhasil di-generate.');
    }

    public function preview(Request $request, Member $member): View
    {
        $member->load('company');

        $period = ManagementPeriod::where('status', 'active')->first();
        $officials = $period ? $period->activeOfficials()->get() : collect();

        $ketua = $officials->firstWhere('jabatan', 'Ketua Umum');
        $sekretaris = $officials->firstWhere('jabatan', 'Sekretaris Umum');

        $ktaData = [
            'member' => $member,
            'period' => $period,
            'ketua' => $ketua,
            'sekretaris' => $sekretaris,
            'tanggal_cetak' => now()->format('d F Y'),
            'logo_path' => public_path('images/logo-kta.png'),
            'ttd_ketua_path' => $ketua && $ketua->signature_path && Storage::disk('local')->exists($ketua->signature_path)
                ? Storage::disk('local')->path($ketua->signature_path)
                : null,
            'ttd_sekretaris_path' => $sekretaris && $sekretaris->signature_path && Storage::disk('local')->exists($sekretaris->signature_path)
                ? Storage::disk('local')->path($sekretaris->signature_path)
                : null,
            'foto_path' => $member->foto_path && Storage::disk('local')->exists($member->foto_path)
                ? Storage::disk('local')->path($member->foto_path)
                : null,
            'stempel_path' => $period && $period->stempel_path && Storage::disk('local')->exists($period->stempel_path)
                ? Storage::disk('local')->path($period->stempel_path)
                : null,
        ];

        $background = KtaBackground::getActive();

        return view('members.kta-preview', array_merge($ktaData, [
            'background' => $background,
        ]));
    }

    public function download(Request $request, Member $member): Response
    {
        $member->load('company');

        $period = ManagementPeriod::where('status', 'active')->first();
        $officials = $period ? $period->activeOfficials()->get() : collect();

        $ketua = $officials->firstWhere('jabatan', 'Ketua Umum');
        $sekretaris = $officials->firstWhere('jabatan', 'Sekretaris Umum');

        $ktaData = [
            'member' => $member,
            'period' => $period,
            'ketua' => $ketua,
            'sekretaris' => $sekretaris,
            'tanggal_cetak' => now()->format('d F Y'),
            'is_pdf' => true,
            'foto_path' => $member->foto_path && Storage::disk('local')->exists($member->foto_path)
                ? Storage::disk('local')->path($member->foto_path)
                : null,
            'ttd_ketua_path' => $ketua && $ketua->signature_path && Storage::disk('local')->exists($ketua->signature_path)
                ? Storage::disk('local')->path($ketua->signature_path)
                : null,
            'ttd_sekretaris_path' => $sekretaris && $sekretaris->signature_path && Storage::disk('local')->exists($sekretaris->signature_path)
                ? Storage::disk('local')->path($sekretaris->signature_path)
                : null,
            'stempel_path' => $period && $period->stempel_path && Storage::disk('local')->exists($period->stempel_path)
                ? Storage::disk('local')->path($period->stempel_path)
                : null,
        ];

        $background = KtaBackground::getActive();

        $pdf = Pdf::loadView('members.kta-pdf', array_merge($ktaData, [
            'background' => $background,
        ]));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'KTA-' . $member->nik . '-' . now()->format('Ymd') . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
