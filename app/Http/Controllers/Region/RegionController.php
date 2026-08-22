<?php

namespace App\Http\Controllers\Region;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RegionController extends Controller
{
    public function index(): View
    {
        $provinces = Province::with('regencies.districts')
            ->latest()
            ->paginate(20);

        return view('regions.index', ['provinces' => $provinces]);
    }

    public function create(): View
    {
        return view('regions.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'provinces' => ['required', 'array', 'min:1'],
            'provinces.*.name' => ['required', 'string', 'max:100'],
        ]);

        foreach ($validated['provinces'] as $provinceData) {
            $province = Province::firstOrCreate(
                ['name' => $provinceData['name']],
                ['code' => str_pad(Province::count() + 1, 2, '0', STR_PAD_LEFT)]
            );

            if (!empty($provinceData['regencies'])) {
                foreach ($provinceData['regencies'] as $regencyData) {
                    $regency = Regency::firstOrCreate(
                        ['name' => $regencyData['name'], 'province_id' => $province->id],
                        ['code' => str_pad(Regency::count() + 1, 4, '0', STR_PAD_LEFT)]
                    );

                    if (!empty($regencyData['districts'])) {
                        foreach ($regencyData['districts'] as $districtData) {
                            District::firstOrCreate(
                                ['name' => $districtData['name'], 'regency_id' => $regency->id],
                                ['code' => str_pad(District::count() + 1, 7, '0', STR_PAD_LEFT)]
                            );
                        }
                    }
                }
            }
        }

        return redirect()
            ->route('regions.index')
            ->with('success', 'Data wilayah berhasil disimpan.');
    }

    public function import(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        $import = new \App\Imports\RegionImport();
        Excel::import($import, $request->file('file'));

        return redirect()
            ->route('regions.index')
            ->with('success', 'Data wilayah berhasil diimport.');
    }

    public function show(Province $province): View
    {
        $province->load('regencies.districts');

        return view('regions.show', ['province' => $province]);
    }

    public function destroy(Province $province): \Illuminate\Http\RedirectResponse
    {
        if ($province->members()->exists()) {
            return redirect()
                ->route('regions.index')
                ->with('error', 'Provinsi tidak dapat dihapus karena sudah memiliki anggota.');
        }

        $province->delete();

        return redirect()
            ->route('regions.index')
            ->with('success', 'Provinsi berhasil dihapus.');
    }
}
