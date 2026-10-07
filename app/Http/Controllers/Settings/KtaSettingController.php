<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\KtaSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KtaSettingController extends Controller
{
    public function index(): View
    {
        $settings = KtaSetting::orderBy('label')->get();
        return view('settings.kta.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:100|unique:kta_settings,key',
            'label' => 'required|string|max:255',
            'value' => 'nullable|string',
            'type' => 'required|in:text,number,select,boolean',
            'options' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $setting = KtaSetting::create($validated);
        $setting->options = $request->options;
        $setting->save();

        return redirect()
            ->route('settings.kta.index')
            ->with('success', 'Pengaturan berhasil ditambahkan.');
    }

    public function update(Request $request, KtaSetting $setting)
    {
        $validated = $request->validate([
            'value' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $setting->update($validated);

        return redirect()
            ->route('settings.kta.index')
            ->with('success', 'Pengaturan "' . $setting->label . '" berhasil diperbarui.');
    }

    public function destroy(KtaSetting $setting)
    {
        $setting->delete();

        return redirect()
            ->route('settings.kta.index')
            ->with('success', 'Pengaturan berhasil dihapus.');
    }

    public function toggle(KtaSetting $setting)
    {
        $setting->update(['is_active' => !$setting->is_active]);

        $status = $setting->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()
            ->route('settings.kta.index')
            ->with('success', 'Pengaturan "' . $setting->label . '" berhasil ' . $status . '.');
    }
}
