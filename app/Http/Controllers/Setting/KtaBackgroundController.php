<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\KtaBackground;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class KtaBackgroundController extends Controller
{
    public function index(): View
    {
        $backgrounds = KtaBackground::latest()->get();
        return view('settings.kta-background.index', compact('backgrounds'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'front_image' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
            'back_image' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
        ]);

        $frontPath = $request->file('front_image')->store('kta/backgrounds', 'local');
        $backPath = $request->file('back_image')->store('kta/backgrounds', 'local');

        // Jika ini template aktif pertama, set is_active = true
        $isActive = KtaBackground::count() === 0;

        // Jika user meminta set aktif
        if ($request->boolean('is_active')) {
            KtaBackground::query()->update(['is_active' => false]);
            $isActive = true;
        }

        KtaBackground::create([
            'name' => $request->name,
            'front_image' => $frontPath,
            'back_image' => $backPath,
            'is_active' => $isActive,
        ]);

        return redirect()
            ->route('settings.kta-background.index')
            ->with('success', 'Background KTA berhasil diimport.');
    }

    public function setActive(KtaBackground $background): RedirectResponse
    {
        KtaBackground::query()->update(['is_active' => false]);
        $background->update(['is_active' => true]);

        return redirect()
            ->route('settings.kta-background.index')
            ->with('success', 'Background KTA "' . $background->name . '" ditetapkan sebagai aktif.');
    }

    public function destroy(KtaBackground $background): RedirectResponse
    {
        // Hapus file gambar
        if ($background->front_image) {
            Storage::disk('local')->delete($background->front_image);
        }
        if ($background->back_image) {
            Storage::disk('local')->delete($background->back_image);
        }

        $wasActive = $background->is_active;
        $background->delete();

        // Jika yang dihapus adalah yang aktif, set yang terbaru sebagai aktif
        if ($wasActive) {
            $latest = KtaBackground::latest('id')->first();
            if ($latest) {
                $latest->update(['is_active' => true]);
            }
        }

        return redirect()
            ->route('settings.kta-background.index')
            ->with('success', 'Background KTA berhasil dihapus.');
    }
}
