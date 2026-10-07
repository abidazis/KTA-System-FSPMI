@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('header', 'Pengaturan Sistem')

@section('content')
<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-header">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="background: var(--primary); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            </div>
            <div>
                <h3 style="margin: 0;">Pengaturan Sistem</h3>
                <small style="color: var(--gray-500);">Kelola konfigurasi aplikasi</small>
            </div>
        </div>
    </div>

    <form action="{{ route('settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div style="padding: 1.5rem;">
            {{-- KTA Settings --}}
            <div style="margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <div style="background: var(--primary); color: white; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.875rem;">K</div>
                    <h4 style="margin: 0; color: var(--gray-700);">Pengaturan KTA</h4>
                </div>

                <div style="display: grid; gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                            Masa Berlaku KTA (Tahun)
                        </label>
                        <input type="number" name="settings[kta_validity_years]" class="form-control"
                            value="{{ $settings->where('key', 'kta_validity_years')->first()?->value ?? 5 }}"
                            min="1" max="10" style="max-width: 150px;">
                        <small style="color: var(--gray-500); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                            Lama masa berlaku kartu anggota dalam tahun. Default: 5 tahun.
                        </small>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                            Jumlah KTA Per Halaman
                        </label>
                        <input type="number" name="settings[kta_layout]" class="form-control"
                            value="{{ $settings->where('key', 'kta_layout')->first()?->value ?? 5 }}"
                            min="1" max="10" style="max-width: 150px;">
                        <small style="color: var(--gray-500); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                            Berapa KTA yang dicetak per halaman A4.
                        </small>
                    </div>
                </div>
            </div>

            {{-- Member Settings --}}
            <div style="margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <div style="background: var(--primary); color: white; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.875rem;">M</div>
                    <h4 style="margin: 0; color: var(--gray-700);">Pengaturan Anggota</h4>
                </div>

                <div style="display: grid; gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                            Format Nomor Anggota
                        </label>
                        <input type="text" name="settings[member_number_format]" class="form-control"
                            value="{{ $settings->where('key', 'member_number_format')->first()?->value ?? '1.XX.XX.XXX.XXXX' }}"
                            style="max-width: 250px;">
                        <small style="color: var(--gray-500); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                            Format: KodeProv.KodeKab.Tahun.Kec.NoUrut
                        </small>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                            Batas Umur Minimum
                        </label>
                        <input type="number" name="settings[min_age]" class="form-control"
                            value="{{ $settings->where('key', 'min_age')->first()?->value ?? 17 }}"
                            min="1" max="65" style="max-width: 150px;">
                        <small style="color: var(--gray-500); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                            Umur minimum untuk menjadi anggota (dalam tahun).
                        </small>
                    </div>
                </div>
            </div>

            {{-- General Settings --}}
            <div style="margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <div style="background: var(--primary); color: white; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.875rem;">G</div>
                    <h4 style="margin: 0; color: var(--gray-700);">Pengaturan Umum</h4>
                </div>

                <div style="display: grid; gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                            Nama Singkatan Organisasi
                        </label>
                        <input type="text" name="settings[organization_name]" class="form-control"
                            value="{{ $settings->where('key', 'organization_name')->first()?->value ?? 'FSPMI' }}"
                            style="max-width: 250px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                            Nama Lengkap Organisasi
                        </label>
                        <input type="text" name="settings[organization_full_name]" class="form-control"
                            value="{{ $settings->where('key', 'organization_full_name')->first()?->value ?? 'Federasi Serikat Pekerja Metal Indonesia' }}">
                    </div>
                </div>
            </div>

            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-200); display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
