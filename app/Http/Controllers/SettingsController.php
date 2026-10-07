<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index(): View
    {
        $settings = Setting::orderBy('group')->orderBy('label')->get();

        // Group settings for display
        $groupedSettings = $settings->groupBy('group');

        return view('settings.index', [
            'settings' => $settings,
            'groupedSettings' => $groupedSettings,
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable'],
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => is_numeric($value) ? 'number' : 'string',
                ]
            );
        }

        return redirect()
            ->back()
            ->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * Get a specific setting value
     */
    public static function get(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }

    /**
     * Get KTA validity years
     */
    public static function getKtaValidityYears(): int
    {
        return (int) Setting::get('kta_validity_years', 5);
    }
}
