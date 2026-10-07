<?php

namespace App\Http\Controllers\Region;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Member;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;

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
            'name' => ['required', 'string', 'max:100'],
            'regencies' => ['required', 'array', 'min:1'],
            'regencies.*.name' => ['required', 'string', 'max:100'],
            'regencies.*.districts' => ['array'],
            'regencies.*.districts.*.name' => ['required', 'string', 'max:100'],
        ]);

        $province = Province::firstOrCreate(
            ['name' => $validated['name']],
            ['code' => str_pad(Province::count() + 1, 2, '0', STR_PAD_LEFT)]
        );

        foreach ($validated['regencies'] as $regencyData) {
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

    public function downloadTemplate()
    {
        $templateData = [
            ['provinsi', 'kabupaten_kota', 'kecamatan'],
            ['Jawa Barat', 'Bandung', 'Cibeunying Kaler'],
            ['Jawa Barat', 'Bandung', 'Cibeunying Kidul'],
            ['Jawa Barat', 'Bekasi', 'Bekasi Barat'],
            ['DKI Jakarta', 'Jakarta Pusat', 'Cempaka Putih'],
        ];

        return Excel::download(new class($templateData) implements FromArray {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }
        }, 'template_import_wilayah.xlsx');
    }

    public function show(Province $province): View
    {
        $province->load('regencies.districts');

        return view('regions.show', ['province' => $province]);
    }

    public function edit(Province $province): View
    {
        $province->load('regencies.districts');

        return view('regions.edit', ['province' => $province]);
    }

    public function update(Request $request, Province $province): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'regencies' => ['required', 'array', 'min:1'],
            'regencies.*.name' => ['required', 'string', 'max:100'],
            'regencies.*.districts' => ['array'],
            'regencies.*.districts.*.name' => ['required', 'string', 'max:100'],
        ]);

        // Update nama provinsi
        $province->update(['name' => $validated['name']]);

        // Get existing regency IDs from database
        $existingRegencyIds = $province->regencies()->pluck('id')->toArray();

        // Get regency IDs from request
        $requestRegencyIds = [];
        $requestRegencyNames = [];

        foreach ($validated['regencies'] as $index => $regencyData) {
            // Find or create regency
            $regency = Regency::where('province_id', $province->id)
                ->where('name', $regencyData['name'])
                ->first();

            if (!$regency) {
                $regency = Regency::create([
                    'name' => $regencyData['name'],
                    'province_id' => $province->id,
                    'code' => str_pad(Regency::count() + 1, 4, '0', STR_PAD_LEFT),
                ]);
            }

            $requestRegencyIds[] = $regency->id;
            $requestRegencyNames[$regency->id] = $regencyData['name'];

            // Get existing district IDs
            $existingDistrictIds = $regency->districts()->pluck('id')->toArray();

            // Process districts
            if (!empty($regencyData['districts'])) {
                $requestDistrictIds = [];

                foreach ($regencyData['districts'] as $districtData) {
                    $district = District::where('regency_id', $regency->id)
                        ->where('name', $districtData['name'])
                        ->first();

                    if (!$district) {
                        $district = District::create([
                            'name' => $districtData['name'],
                            'regency_id' => $regency->id,
                            'code' => str_pad(District::count() + 1, 7, '0', STR_PAD_LEFT),
                        ]);
                    }

                    $requestDistrictIds[] = $district->id;
                }

                // Delete districts that were removed
                $districtsToDelete = array_diff($existingDistrictIds, $requestDistrictIds);
                if (!empty($districtsToDelete)) {
                    // Check if any member is associated with these districts
                    $membersInDistricts = Member::whereIn('district_id', $districtsToDelete)->exists();
                    if ($membersInDistricts) {
                        return redirect()
                            ->route('regions.edit', $province)
                            ->with('error', 'Tidak dapat menghapus kecamatan yang sudah memiliki anggota.');
                    }
                    District::whereIn('id', $districtsToDelete)->delete();
                }
            } else {
                // All districts removed - check if any member is associated
                $membersInExistingDistricts = Member::whereIn('district_id', $existingDistrictIds)->exists();
                if ($membersInExistingDistricts) {
                    return redirect()
                        ->route('regions.edit', $province)
                        ->with('error', 'Tidak dapat menghapus semua kecamatan karena sudah memiliki anggota.');
                }
                District::whereIn('id', $existingDistrictIds)->delete();
            }
        }

        // Delete regencies that were removed
        $regenciesToDelete = array_diff($existingRegencyIds, $requestRegencyIds);
        if (!empty($regenciesToDelete)) {
            // Check if any member is associated with these regencies
            $membersInRegencies = Member::whereIn('regency_id', $regenciesToDelete)->exists();
            if ($membersInRegencies) {
                return redirect()
                    ->route('regions.edit', $province)
                    ->with('error', 'Tidak dapat menghapus kabupaten/kota yang sudah memiliki anggota.');
            }
            // Delete districts of those regencies first
            $districtsToDelete = District::whereIn('regency_id', $regenciesToDelete)->pluck('id');
            District::whereIn('id', $districtsToDelete)->delete();
            Regency::whereIn('id', $regenciesToDelete)->delete();
        }

        return redirect()
            ->route('regions.index')
            ->with('success', 'Data wilayah berhasil diperbarui.');
    }

    public function destroy(Province $province): \Illuminate\Http\RedirectResponse
    {
        // Check if province has members
        if (Member::where('province_id', $province->id)->exists()) {
            return redirect()
                ->route('regions.index')
                ->with('error', 'Provinsi tidak dapat dihapus karena sudah memiliki anggota.');
        }

        // Check if any regency in this province has members
        $regencyIds = $province->regencies()->pluck('id');
        if (Member::whereIn('regency_id', $regencyIds)->exists()) {
            return redirect()
                ->route('regions.index')
                ->with('error', 'Kabupaten/Kota dalam provinsi ini tidak dapat dihapus karena sudah memiliki anggota.');
        }

        // Check if any district in this province has members
        $districtIds = District::whereIn('regency_id', $regencyIds)->pluck('id');
        if (Member::whereIn('district_id', $districtIds)->exists()) {
            return redirect()
                ->route('regions.index')
                ->with('error', 'Kecamatan dalam provinsi ini tidak dapat dihapus karena sudah memiliki anggota.');
        }

        // Safe to delete - delete from bottom to top
        // Delete all districts
        District::whereIn('regency_id', $regencyIds)->delete();

        // Delete all regencies
        $province->regencies()->delete();

        // Delete province
        $province->delete();

        return redirect()
            ->route('regions.index')
            ->with('success', 'Provinsi beserta seluruh data di dalamnya berhasil dihapus.');
    }
}
