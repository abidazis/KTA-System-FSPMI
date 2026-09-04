@extends('layouts.app')

@section('title', 'Tambah Perusahaan')
@section('header', 'Tambah Perusahaan Baru')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Form Tambah Perusahaan</h3>
        <a href="{{ route('settings.companies.index') }}" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Kembali
        </a>
    </div>

    <form action="{{ route('settings.companies.store') }}" method="POST">
        @csrf

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;">
            <div class="form-group">
                <label for="name">Nama Perusahaan *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Nama perusahaan">
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="email@perusahaan.com">
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="address">Alamat</label>
            <textarea id="address" name="address" rows="2" placeholder="Alamat lengkap">{{ old('address') }}</textarea>
            @error('address') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;">
            <div class="form-group">
                <label for="city">Kota</label>
                <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="Nama kota">
                @error('city') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="province">Provinsi</label>
                <input type="text" id="province" name="province" value="{{ old('province') }}" placeholder="Nama provinsi">
                @error('province') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="postal_code">Kode Pos</label>
                <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" placeholder="12345">
                @error('postal_code') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;">
            <div class="form-group">
                <label for="phone">Telepon</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="021-1234567">
                @error('phone') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="is_active">Status</label>
                <select id="is_active" name="is_active">
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @error('is_active') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="display:flex;gap:0.75rem;margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--gray-200);">
            <button type="submit" class="btn btn-primary btn-lg">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Simpan
            </button>
            <button type="reset" class="btn btn-outline btn-lg">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                Reset
            </button>
        </div>
    </form>
</div>
@endsection
