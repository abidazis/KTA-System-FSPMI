@extends('layouts.app')

@section('title', 'Edit Anggota')
@section('header', 'Edit Anggota')

@section('content')
<div class="card">
    <div class="card-header">
        <h3> Form Edit Anggota</h3>
        <a href="{{ route('members.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <form action="{{ route('members.update', $member) }}" method="POST" enctype="multipart/form-data" id="member-form">
        @csrf
        @method('PUT')

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;">
            <div class="form-group">
                <label for="nik"> NIK (Nomor Induk Kependudukan) *</label>
                <input type="text" id="nik" name="nik" value="{{ old('nik', $member->nik) }}" required placeholder="16 digit NIK" maxlength="16">
                @error('nik') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="nama"> Nama Lengkap *</label>
                <input type="text" id="nama" name="nama" value="{{ old('nama', $member->nama) }}" required placeholder="Nama sesuai KTP">
                @error('nama') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;">
            <div class="form-group">
                <label for="tempat_lahir"> Tempat Lahir *</label>
                <input type="text" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir', $member->tempat_lahir) }}" required placeholder="Kota kelahiran">
                @error('tempat_lahir') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="tanggal_lahir"> Tanggal Lahir *</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $member->tanggal_lahir->format('Y-m-d')) }}" required>
                @error('tanggal_lahir') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="alamat"> Alamat Lengkap *</label>
            <textarea id="alamat" name="alamat" rows="2" required placeholder="Alamat sesuai KTP">{{ old('alamat', $member->alamat) }}</textarea>
            @error('alamat') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;">
            <div class="form-group">
                <label for="province_id"> Provinsi *</label>
                <select id="province_id" name="province_id" required>
                    <option value="">-- Pilih Provinsi --</option>
                    @foreach($provinces as $province)
                    <option value="{{ $province->id }}" {{ old('province_id', $member->province_id) == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="regency_id"> Kabupaten/Kota</label>
                <select id="regency_id" name="regency_id" disabled>
                    <option value="">-- Pilih Kabupaten/Kota --</option>
                    @if(old('regency_id') || $member->regency_id)
                        @foreach($regencies as $regency)
                        <option value="{{ $regency->id }}" {{ old('regency_id', $member->regency_id) == $regency->id ? 'selected' : '' }}>{{ $regency->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="form-group">
                <label for="district_id">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            Kecamatan
                        </label>
                <select id="district_id" name="district_id" disabled>
                    <option value="">-- Pilih Kecamatan --</option>
                    @if(old('district_id') || $member->district_id)
                        @foreach($districts as $district)
                        <option value="{{ $district->id }}" {{ old('district_id', $member->district_id) == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;">
            <div class="form-group">
                <label for="jenis_kelamin">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.5rem;"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    Jenis Kelamin *
                </label>
                <select id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <option value="Laki-laki" {{ old('jenis_kelamin', $member->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="Perempuan" {{ old('jenis_kelamin', $member->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('jenis_kelamin') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="agama"> Agama *</label>
                <select id="agama" name="agama" required>
                    <option value="">-- Pilih --</option>
                    @foreach($religions as $religion)
                    <option value="{{ $religion }}" {{ old('agama', $member->agama) == $religion ? 'selected' : '' }}>{{ $religion }}</option>
                    @endforeach
                </select>
                @error('agama') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="company_id"> Perusahaan *</label>
                <select id="company_id" name="company_id" required>
                    <option value="">-- Pilih Perusahaan --</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ old('company_id', $member->company_id) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
                @error('company_id') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;">
            <div class="form-group">
                <label for="tanggal_pembuatan"> Tanggal Pembuatan KTA *</label>
                <input type="date" id="tanggal_pembuatan" name="tanggal_pembuatan" value="{{ old('tanggal_pembuatan', $member->tanggal_pembuatan->format('Y-m-d')) }}" required>
                @error('tanggal_pembuatan') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="berlaku_hingga"> Berlaku Hingga *</label>
                <input type="date" id="berlaku_hingga" name="berlaku_hingga" value="{{ old('berlaku_hingga', $member->berlaku_hingga->format('Y-m-d')) }}" required>
                @error('berlaku_hingga') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="foto"> Ganti Foto</label>
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png" style="padding:0.5rem;">
            <small style="color:var(--gray-500);">Format: JPG, PNG. Maks: 2MB. Biarkan kosong jika tidak ingin mengganti.</small>
            <div id="photo-preview" style="margin-top:1rem;">
                @if($member->hasPhoto())
                <img src="{{ $member->getPhotoUrl() }}" style="max-width:200px;border-radius:0.5rem;border:2px solid var(--gray-200);">
                @endif
            </div>
            @error('foto') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div style="display:flex;gap:0.75rem;margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--gray-200);">
            <button type="submit" class="btn btn-primary btn-lg">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Simpan Perubahan
            </button>
            <a href="{{ route('members.index') }}" class="btn btn-outline btn-lg">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                Batal
            </a>
        </div>
    </form>
</div>

<script>
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
            preview.innerHTML = `<img src="${e.target.result}" style="max-width:200px;border-radius:0.5rem;border:2px solid var(--gray-200);">`;
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
@endsection
