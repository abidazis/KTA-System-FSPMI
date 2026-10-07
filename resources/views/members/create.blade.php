@extends('layouts.app')

@section('title', 'Tambah Anggota')
@section('header', 'Tambah Anggota Baru')

@section('content')
<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-header">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="background: var(--primary); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
            </div>
            <div>
                <h3 style="margin: 0;">Form Data Anggota</h3>
                <small style="color: var(--gray-500);">Lengkapi semua data dengan benar</small>
            </div>
        </div>
        <a href="{{ route('members.index') }}" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Kembali
        </a>
    </div>

    <form action="{{ route('members.store') }}" method="POST" enctype="multipart/form-data" id="member-form">
        @csrf

        {{-- Section 1: Identitas --}}
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--gray-200);">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
                <div style="background: var(--primary); color: white; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.875rem;">1</div>
                <h4 style="margin: 0; color: var(--gray-700);">Identitas Anggota</h4>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="nik" style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        No. Anggota <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="nik" name="nik" value="{{ old('nik') }}" required
                        placeholder="1.02.01.038.0123"
                        pattern="[0-9]{1,2}\.[0-9]{2}\.[0-9]{2}\.[0-9]{3}\.[0-9]{4}"
                        title="Format: 1.02.01.038.0123"
                        style="font-family: 'Courier New', monospace; letter-spacing: 1px; font-size: 1.1rem; font-weight: 600;"
                        maxlength="18">
                    @error('nik') <span class="error">{{ $message }}</span> @enderror
                    <small style="color: var(--gray-500); font-size: 0.75rem;">Format: KodeProv.KodeKab.Tahun.Kec.NoUrut</small>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="nama" style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        Nama Lengkap <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required placeholder="Nama sesuai KTP" style="text-transform: uppercase;">
                    @error('nama') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Section 2: Data Pribadi --}}
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--gray-200); background: var(--gray-50);">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
                <div style="background: var(--primary); color: white; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.875rem;">2</div>
                <h4 style="margin: 0; color: var(--gray-700);">Data Pribadi</h4>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="tempat_lahir" style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        Tempat Lahir <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required placeholder="Kota kelahiran">
                    @error('tempat_lahir') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="tanggal_lahir" style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        Tanggal Lahir <span class="text-danger">*</span>
                    </label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required>
                    @error('tanggal_lahir') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div style="margin-top: 1.25rem;">
                <label for="alamat" style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    Alamat Lengkap <span class="text-danger">*</span>
                </label>
                <textarea id="alamat" name="alamat" rows="4" required placeholder="Contoh: Jl. Melati No. 15, RT 003/RW 005, Kel. Sukamaju, Kec. Cibeunying Kaler, Kota Bandung" style="width: 100%;"></textarea>
                <small style="color: var(--gray-500); font-size: 0.75rem; margin-top: 0.5rem; display: block;">
                    *Isi dengan: Jalan/Perumahan No., RT/RW, Kelurahan/Desa, Kecamatan (Kecamatan pilih di dropdown bawah)
                </small>
                @error('alamat') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Section 3: Wilayah --}}
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--gray-200);">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
                <div style="background: var(--primary); color: white; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.875rem;">3</div>
                <h4 style="margin: 0; color: var(--gray-700);">Wilayah</h4>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="province_id" style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        Provinsi <span class="text-danger">*</span>
                    </label>
                    <select id="province_id" name="province_id" required>
                        <option value="">-- Pilih Provinsi --</option>
                        @foreach($provinces as $province)
                        <option value="{{ $province->id }}">{{ $province->name }}</option>
                        @endforeach
                    </select>
                    @error('province_id') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="regency_id" style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        Kabupaten/Kota <span class="text-danger">*</span>
                    </label>
                    <select id="regency_id" name="regency_id" required disabled>
                        <option value="">-- Pilih Kabupaten/Kota --</option>
                    </select>
                    @error('regency_id') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="district_id" style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        Kecamatan <span class="text-danger">*</span>
                    </label>
                    <select id="district_id" name="district_id" required disabled>
                        <option value="">-- Pilih Kecamatan --</option>
                    </select>
                    @error('district_id') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Section 4: Informasi Tambahan --}}
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--gray-200); background: var(--gray-50);">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
                <div style="background: var(--primary); color: white; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.875rem;">4</div>
                <h4 style="margin: 0; color: var(--gray-700);">Informasi Tambahan</h4>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="jenis_kelamin" style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                        Jenis Kelamin <span class="text-danger">*</span>
                    </label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="">-- Pilih --</option>
                        <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="agama" style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                        Agama <span class="text-danger">*</span>
                    </label>
                    <select id="agama" name="agama" required>
                        <option value="">-- Pilih --</option>
                        @foreach($religions as $religion)
                        <option value="{{ $religion }}" {{ old('agama') == $religion ? 'selected' : '' }}>{{ $religion }}</option>
                        @endforeach
                    </select>
                    @error('agama') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="company_id" style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                        Perusahaan <span class="text-danger">*</span>
                    </label>
                    <select id="company_id" name="company_id" required>
                        <option value="">-- Pilih Perusahaan --</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('company_id') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Section 5: KTA Info --}}
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--gray-200);">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
                <div style="background: var(--primary); color: white; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.875rem;">5</div>
                <h4 style="margin: 0; color: var(--gray-700);">Informasi KTA</h4>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="tanggal_pembuatan" style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        Tanggal Pembuatan <span class="text-danger">*</span>
                    </label>
                    <input type="date" id="tanggal_pembuatan" name="tanggal_pembuatan" value="{{ old('tanggal_pembuatan', now()->format('Y-m-d')) }}" required>
                    @error('tanggal_pembuatan') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="berlaku_hingga" style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Berlaku Hingga <span class="text-danger">*</span>
                    </label>
                    <input type="date" id="berlaku_hingga" name="berlaku_hingga" value="{{ old('berlaku_hingga') }}" required>
                    @error('berlaku_hingga') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Section 6: Foto --}}
        <div style="padding: 1.5rem; background: var(--gray-50);">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
                <div style="background: var(--primary); color: white; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.875rem;">6</div>
                <h4 style="margin: 0; color: var(--gray-700);">Foto Anggota</h4>
            </div>

            <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                <div style="flex: 1;">
                    <label for="foto" style="font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                        Unggah Foto
                    </label>
                    <input type="file" id="foto" name="foto" accept="image/jpeg,image/png" style="padding: 0.5rem;">
                    <small style="color: var(--gray-500); font-size: 0.75rem; display: block; margin-top: 0.5rem;">
                        Format: JPG, PNG. Maks: 2MB. Ukuran disarankan: 300x400 pixel
                    </small>
                    @error('foto') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div id="photo-preview" style="width: 150px; height: 200px; border: 2px dashed var(--gray-300); border-radius: 8px; display: flex; align-items: center; justify-content: center; background: var(--gray-100);">
                    <div style="text-align: center; color: var(--gray-400);">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                        <small style="display: block; margin-top: 0.5rem;">Preview</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div style="padding: 1.5rem; display: flex; gap: 1rem; justify-content: flex-end; border-top: 1px solid var(--gray-200); background: white;">
            <a href="{{ route('members.index') }}" class="btn btn-outline" style="padding: 0.75rem 1.5rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                Batal
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Simpan Anggota
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .form-group {
        position: relative;
    }
    .form-group label {
        display: flex;
        align-items: center;
    }
    .card {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border-radius: 12px;
        overflow: hidden;
    }
    .form-control {
        border-radius: 8px;
        padding: 0.625rem 0.875rem;
    }
    select.form-control {
        padding: 0.625rem 0.875rem;
    }
    textarea.form-control {
        border-radius: 8px;
        padding: 0.75rem 0.875rem;
    }
    textarea#alamat {
        min-height: 120px;
        resize: vertical;
        line-height: 1.6;
    }
    /* Flatpickr input styling */
    .flatpickr-input, input[type="date"] {
        background: white !important;
    }
    /* Make date inputs with flatpickr look like regular inputs */
    input[type="date"].form-control {
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
// Auto-format Nomor Anggota dengan titik: 1.02.01.038.0123
document.getElementById('nik').addEventListener('input', function(e) {
    let value = e.target.value.replace(/[^0-9]/g, '');
    value = value.substring(0, 14);

    let formatted = '';
    if (value.length > 0) formatted = value.substring(0, 1);
    if (value.length > 1) formatted += '.' + value.substring(1, 3);
    if (value.length > 3) formatted += '.' + value.substring(3, 5);
    if (value.length > 5) formatted += '.' + value.substring(5, 8);
    if (value.length > 8) formatted += '.' + value.substring(8, 12);

    e.target.value = formatted;
});

document.getElementById('nik').addEventListener('blur', function(e) {
    const value = e.target.value;
    const pattern = /^[0-9]{1,2}\.[0-9]{2}\.[0-9]{2}\.[0-9]{3}\.[0-9]{4}$/;
    if (value && !pattern.test(value)) {
        e.target.setCustomValidity('Format tidak valid. Gunakan format: 1.02.01.038.0123');
    } else {
        e.target.setCustomValidity('');
    }
});

document.getElementById('province_id').addEventListener('change', function() {
    const provinceId = this.value;
    const regencySelect = document.getElementById('regency_id');
    const districtSelect = document.getElementById('district_id');
    regencySelect.innerHTML = '<option value="">Memuat...</option>';
    districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
    districtSelect.disabled = true;

    if (provinceId) {
        fetch('/api/regencies/' + provinceId)
            .then(r => r.json())
            .then(data => {
                regencySelect.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                data.forEach(r => regencySelect.innerHTML += `<option value="${r.id}">${r.name}</option>`);
                regencySelect.disabled = false;
            });
    } else {
        regencySelect.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
        regencySelect.disabled = true;
    }
});

document.getElementById('regency_id').addEventListener('change', function() {
    const regencyId = this.value;
    const districtSelect = document.getElementById('district_id');
    districtSelect.innerHTML = '<option value="">Memuat...</option>';

    if (regencyId) {
        fetch('/api/districts/' + regencyId)
            .then(r => r.json())
            .then(data => {
                districtSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                data.forEach(d => districtSelect.innerHTML += `<option value="${d.id}">${d.name}</option>`);
                districtSelect.disabled = false;
            });
    } else {
        districtSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        districtSelect.disabled = true;
    }
});

document.getElementById('foto').addEventListener('change', function() {
    const preview = document.getElementById('photo-preview');
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" style="width: 150px; height: 200px; object-fit: cover; border-radius: 6px;">`;
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
@endpush
