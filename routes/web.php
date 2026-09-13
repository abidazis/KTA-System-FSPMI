<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Import\ImportController;
use App\Http\Controllers\Management\ManagementController;
use App\Http\Controllers\Member\KtaController;
use App\Http\Controllers\Member\MemberController;
use App\Http\Controllers\Print\PrintController;
use App\Http\Controllers\Region\RegionController;
use App\Http\Controllers\Setting\CompanyController;
use App\Http\Controllers\Settings\MemberNumberFormulaController;
use App\Http\Controllers\Settings\MemberNumberController;
use App\Http\Controllers\Setting\KtaBackgroundController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Public routes
Route::get('/', fn() => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password/{token}', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('password/edit', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Members
    Route::middleware('permission:anggota-view')->group(function () {
        Route::get('members', [MemberController::class, 'index'])->name('members.index');
        Route::get('members/export', [MemberController::class, 'export'])->name('members.export');
        Route::get('members/create', [MemberController::class, 'create'])->name('members.create');
        Route::post('members', [MemberController::class, 'store'])->name('members.store');
        Route::post('members/bulk-action', [MemberController::class, 'bulkAction'])->name('members.bulk-action');
        Route::get('members/{member}', [MemberController::class, 'show'])->name('members.show');
        Route::get('members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
        Route::put('members/{member}', [MemberController::class, 'update'])->name('members.update');
        Route::delete('members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');

        // KTA
        Route::get('members/{member}/kta/preview', [KtaController::class, 'preview'])->name('members.kta.preview');
        Route::post('members/{member}/kta/generate', [KtaController::class, 'generate'])->name('members.kta.generate');
        Route::get('members/{member}/kta/download', [KtaController::class, 'download'])->name('members.kta.download');

        // Inline Status Update
        Route::patch('members/{member}/status', [MemberController::class, 'updateStatus'])->name('members.status.update');
    });

    // AJAX - Wilayah (di luar permission agar dropdown berfungsi di semua halaman)
    Route::get('api/regencies/{provinceId}', [MemberController::class, 'getRegencies'])->name('api.regencies');
    Route::get('api/districts/{regencyId}', [MemberController::class, 'getDistricts'])->name('api.districts');

    // Import
    Route::middleware('permission:import')->group(function () {
        Route::get('import', [ImportController::class, 'index'])->name('imports.index');
        Route::get('import/template', [ImportController::class, 'downloadTemplate'])->name('imports.template');
        Route::post('import/validate', [ImportController::class, 'validateImport'])->name('imports.validate');
        Route::post('import', [ImportController::class, 'processImport'])->name('imports.process');
    });

    // Print
    Route::middleware('permission:cetak')->group(function () {
        Route::get('print', [PrintController::class, 'index'])->name('print.index');
        Route::get('print/create', [PrintController::class, 'create'])->name('print.create');
        Route::post('print/preview', [PrintController::class, 'preview'])->name('print.preview');
        Route::post('print', [PrintController::class, 'store'])->name('print.store');
        Route::get('print/{batch}', [PrintController::class, 'show'])->name('print.show');
        Route::get('print/{batch}/pdf', [PrintController::class, 'downloadPdf'])->name('print.pdf');
        Route::delete('print/{batch}', [PrintController::class, 'destroy'])->name('print.destroy');
    });

    // Settings - KTA Background
    Route::middleware('permission:cetak')->group(function () {
        Route::get('settings/kta-background', [KtaBackgroundController::class, 'index'])->name('settings.kta-background.index');
        Route::post('settings/kta-background', [KtaBackgroundController::class, 'store'])->name('settings.kta-background.store');
        Route::post('settings/kta-background/{background}/set-active', [KtaBackgroundController::class, 'setActive'])->name('settings.kta-background.set-active');
        Route::delete('settings/kta-background/{background}', [KtaBackgroundController::class, 'destroy'])->name('settings.kta-background.destroy');

        // Master Perusahaan
        Route::get('settings/companies', [CompanyController::class, 'index'])->name('settings.companies.index');
        Route::get('settings/companies/create', [CompanyController::class, 'create'])->name('settings.companies.create');
        Route::post('settings/companies', [CompanyController::class, 'store'])->name('settings.companies.store');
        Route::get('settings/companies/{company}', [CompanyController::class, 'edit'])->name('settings.companies.edit');
        Route::put('settings/companies/{company}', [CompanyController::class, 'update'])->name('settings.companies.update');
        Route::delete('settings/companies/{company}', [CompanyController::class, 'destroy'])->name('settings.companies.destroy');

        // Formulasi Nomor Anggota
        Route::middleware('permission:formulasi-nomor-anggota')->group(function () {
            Route::get('settings/member-number-formulas', [MemberNumberFormulaController::class, 'index'])->name('member-number-formulas.index');
            Route::get('settings/member-number-formulas/create', [MemberNumberFormulaController::class, 'create'])->name('member-number-formulas.create');
            Route::post('settings/member-number-formulas', [MemberNumberFormulaController::class, 'store'])->name('member-number-formulas.store');
            Route::get('settings/member-number-formulas/{formula}/edit', [MemberNumberFormulaController::class, 'edit'])->name('member-number-formulas.edit');
            Route::put('settings/member-number-formulas/{formula}', [MemberNumberFormulaController::class, 'update'])->name('member-number-formulas.update');
            Route::delete('settings/member-number-formulas/{formula}', [MemberNumberFormulaController::class, 'destroy'])->name('member-number-formulas.destroy');
            Route::post('settings/member-number-formulas/{formula}/toggle-active', [MemberNumberFormulaController::class, 'toggleActive'])->name('member-number-formulas.toggle-active');
        });

        // Master Kelola Nomor Anggota
        Route::middleware('permission:formulasi-nomor-anggota')->group(function () {
            Route::get('settings/member-numbers', [MemberNumberController::class, 'index'])->name('member-numbers.index');
            Route::post('settings/member-numbers/generate', [MemberNumberController::class, 'generate'])->name('member-numbers.generate');
            Route::post('settings/member-numbers/{memberNumber}/assign', [MemberNumberController::class, 'assign'])->name('member-numbers.assign');
            Route::get('settings/member-numbers/available-members', [MemberNumberController::class, 'getAvailableMembers'])->name('member-numbers.available-members');
            Route::get('settings/member-numbers/preview-next', [MemberNumberController::class, 'previewNext'])->name('member-numbers.preview-next');
        });
    });

    // Management Periods
    Route::middleware('permission:pengurus')->group(function () {
        Route::get('management', [ManagementController::class, 'index'])->name('management.index');
        Route::post('management', [ManagementController::class, 'store'])->name('management.store');
        Route::get('management/{period}', [ManagementController::class, 'show'])->name('management.show');
        Route::put('management/{period}', [ManagementController::class, 'update'])->name('management.update');
        Route::put('management/{period}/stempel', [ManagementController::class, 'updateStempel'])->name('management.stempel');
        Route::post('management/{period}/set-active', [ManagementController::class, 'setActive'])->name('management.set-active');
        Route::delete('management/{period}', [ManagementController::class, 'destroy'])->name('management.destroy');

        // Officials
        Route::post('management/{period}/officials', [ManagementController::class, 'storeOfficial'])->name('management.officials.store');
        Route::put('management/{period}/officials/{official}', [ManagementController::class, 'updateOfficial'])->name('management.officials.update');
        Route::put('management/{period}/officials/{official}/signature', [ManagementController::class, 'updateSignature'])->name('management.officials.signature');
        Route::delete('management/{period}/officials/{official}', [ManagementController::class, 'destroyOfficial'])->name('management.officials.destroy');
    });

    // Regions
    Route::middleware('permission:wilayah')->group(function () {
        Route::get('regions', [RegionController::class, 'index'])->name('regions.index');
        Route::get('regions/create', [RegionController::class, 'create'])->name('regions.create');
        Route::post('regions', [RegionController::class, 'store'])->name('regions.store');
        Route::post('regions/import', [RegionController::class, 'import'])->name('regions.import');
        Route::get('regions/{province}', [RegionController::class, 'show'])->name('regions.show');
        Route::delete('regions/{province}', [RegionController::class, 'destroy'])->name('regions.destroy');
    });

    // Media - serve files from local storage
    Route::get('media/{path}', [MediaController::class, 'show'])
        ->where('path', '.*')
        ->name('media.show');

    // Users
    Route::middleware('permission:user-view')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
