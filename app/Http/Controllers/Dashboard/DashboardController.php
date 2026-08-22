<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\ManagementPeriod;
use App\Models\PrintBatch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'total_anggota' => Member::count(),
            'kta_aktif' => Member::active()->where('status', '!=', 'expired')->where('berlaku_hingga', '>=', now()->toDateString())->count(),
            'kta_belum_cetak' => Member::whereIn('status', ['draft', 'ready'])->whereNotNull('foto_path')->count(),
            'kta_kadaluarsa' => Member::where('berlaku_hingga', '<', now()->toDateString())->count(),
            'kta_akan_kadaluarsa' => Member::whereBetween('berlaku_hingga', [
                now()->toDateString(),
                now()->addDays(30)->toDateString()
            ])->count(),
            'total_cetak' => PrintBatch::sum('jumlah'),
            'periode_aktif' => ManagementPeriod::where('status', 'active')->count(),
        ];

        $recentMembers = Member::with(['province', 'regency', 'district'])
            ->latest()
            ->take(10)
            ->get();

        $membersByDistrict = Member::with('district:id,name')
            ->whereNotNull('district_id')
            ->selectRaw('district_id, count(*) as total')
            ->groupBy('district_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $membersByGender = Member::selectRaw('jenis_kelamin, count(*) as total')
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin');

        $membersByStatus = Member::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('dashboard.index', [
            'stats' => $stats,
            'recentMembers' => $recentMembers,
            'membersByDistrict' => $membersByDistrict,
            'membersByGender' => $membersByGender,
            'membersByStatus' => $membersByStatus,
        ]);
    }
}
