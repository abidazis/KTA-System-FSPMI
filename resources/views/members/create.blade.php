@extends('layouts.app')

@section('title', 'Tambah Anggota')
@section('header', 'Tambah Anggota Baru')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Form Data Anggota</h3>
        <a href="{{ route('members.index') }}" class="btn btn-sm btn-outline">Kembali</a>
    </div>

    <form action="{{ route('members.store') }}" method="POST" enctype="multipart/form-data" id="member-form">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label for="nik">NIK *</label>
                <input type="text" id="nik" name="nik" value="{{ old('nik') }}" required>
                @error('nik') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="nama">Nama Lengkap *</label>
                <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required>
                @error('nama') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="tempat_lahir">Tempat Lahir *</label>
                <input type="text" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required>
                @error('tempat_lahir') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir *</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required>
                @error('tanggal_lahir') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="alamat">Alamat *</label>
            <textarea id="alamat" name="alamat" rows="2" required>{{ old('alamat') }}</textarea>
            @error('alamat') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="province_id">Provinsi *</label>
                <select id="province_id" name="province_id" required>
                    <option value="">-- Pilih Provinsi --</option>
                    @foreach($provinces as $province)
                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="regency_id">Kabupaten/Kota</label>
                <select id="regency_id" name="regency_id" disabled>
                    <option value="">-- Pilih Kabupaten/Kota --</option>
                    @if(old('regency_id'))
                        @foreach($regencies as $regency)
                        <option value="{{ $regency->id }}" selected>{{ $regency->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="form-group">
                <label for="district_id">Kecamatan</label>
                <select id="district_id" name="district_id" disabled>
                    <option value="">-- Pilih Kecamatan --</option>
                    @if(old('district_id'))
                        @foreach($districts as $district)
                        <option value="{{ $district->id }}" selected>{{ $district->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="jenis_kelamin">Jenis Kelamin *</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('jenis_kelamin') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="agama">Agama *</label>
                <select id="agama" name="agama" required>
                    <option value="">-- Pilih --</option>
                    @foreach($religions as $religion)
                    <option value="{{ $religion }}" {{ old('agama') == $religion ? 'selected' : '' }}>{{ $religion }}</option>
                    @endforeach
                </select>
                @error('agama') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="company_id">Perusahaan *</label>
                <select id="company_id" name="company_id" required>
                    <option value="">-- Pilih Perusahaan --</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
                @error('company_id') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="tanggal_pembuatan">Tanggal Pembuatan KTA *</label>
                <input type="date" id="tanggal_pembuatan" name="tanggal_pembuatan" value="{{ old('tanggal_pembuatan', now()->format('Y-m-d')) }}" required>
                @error('tanggal_pembuatan') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="berlaku_hingga">Berlaku Hingga *</label>
                <input type="date" id="berlaku_hingga" name="berlaku_hingga" value="{{ old('berlaku_hingga') }}" required>
                @error('berlaku_hingga') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="foto">Foto Anggota</label>
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png">
            <small style="color:#64748b;">Format: JPG, PNG. Maks: 2MB</small>
            <div id="photo-preview" style="margin-top:1rem;"></div>
            @error('foto') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div style="display:flex;gap:1rem;margin-top:1.5rem;">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('members.index') }}" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
@endsection
