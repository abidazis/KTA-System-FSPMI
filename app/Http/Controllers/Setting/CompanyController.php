<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CompanyController extends Controller
{
    public function index(): View
    {
        $companies = Company::orderBy('name')->paginate(10);
        return view('settings.companies.index', compact('companies'));
    }

    public function create(): View
    {
        return view('settings.companies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Company::create($validated);

        return redirect()
            ->route('settings.companies.index')
            ->with('success', 'Perusahaan berhasil ditambahkan.');
    }

    public function show(Company $company): View
    {
        return view('settings.companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        return view('settings.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $company->update($validated);

        return redirect()
            ->route('settings.companies.index')
            ->with('success', 'Perusahaan berhasil diperbarui.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        // Cek apakah perusahaan masih punya anggota
        if ($company->members()->exists()) {
            return redirect()
                ->route('settings.companies.index')
                ->with('error', 'Perusahaan tidak dapat dihapus karena masih memiliki anggota.');
        }

        $company->delete();

        return redirect()
            ->route('settings.companies.index')
            ->with('success', 'Perusahaan berhasil dihapus.');
    }
}
