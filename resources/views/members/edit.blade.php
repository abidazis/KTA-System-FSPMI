@extends('layouts.app')

@section('title', 'Edit Anggota')
@section('header', 'Edit Anggota')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Form Edit Anggota</h3>
        <a href="{{ route('members.show', $member) }}" class="btn btn-sm btn-outline">Kembali</a>
    </div>

    <form action="{{ route('members.update', $member) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group">
                <label for="nik">NIK *</label>
                <input type="text" id="nik" name="nik" value="{{ old('nik', $member->nik) }}" required>
                @error('nik') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="nama">Nama Lengkap *</label>
                <input type="text" id="nama" name="nama" value="{{ old('nama', $member->nama) }}" required>
                @error('nama') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="tempat_lahir">Tempat Lahir *</label>
                <input type="text" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir', $member->tempat_lahir) }}" required>
            </div>
            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir *</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $member->tanggal_lahir->format('Y-m-d')) }}" required>
            </div>
        </div>

        <div class="form-group">
            <label for="alamat">Alamat *</label>
            <textarea id="alamat" name="alamat" rows="2" required>{{ old('alamat', $member->alamat) }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="province_id">Provinsi</label>
                <select id="province_id" name="province_id">
                    <option value="">-- Pilih --</option>
                    @foreach($provinces as $province)
                    <option value="{{ $province->id }}" {{ $member->province_id == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="regency_id">Kabupaten/Kota</label>
                <select id="regency_id" name="regency_id">
                    <option value="">-- Pilih --</option>
                    @foreach($regencies as $regency)
                    <option value="{{ $regency->id }}" {{ $member->regency_id == $regency->id ? 'selected' : '' }}>{{ $regency->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="district_id">Kecamatan</label>
                <select id="district_id" name="district_id">
                    <option value="">-- Pilih --</option>
                    @foreach($districts as $district)
                    <option value="{{ $district->id }}" {{ $member->district_id == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="jenis_kelamin">Jenis Kelamin *</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="Laki-laki" {{ $member->jenis_kelamin == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="Perempuan" {{ $member->jenis_kelamin == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div class="form-group">
                <label for="agama">Agama *</label>
                <select id="agama" name="agama" required>
                    @foreach($religions as $religion)
                    <option value="{{ $religion }}" {{ $member->agama == $religion ? 'selected' : '' }}>{{ $religion }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="company_id">Perusahaan *</label>
                <select id="company_id" name="company_id" required>
                    <option value="">-- Pilih --</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ $member->company_id == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="tanggal_pembuatan">Tanggal Pembuatan *</label>
                <input type="date" id="tanggal_pembuatan" name="tanggal_pembuatan" value="{{ old('tanggal_pembuatan', $member->tanggal_pembuatan->format('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label for="berlaku_hingga">Berlaku Hingga *</label>
                <input type="date" id="berlaku_hingga" name="berlaku_hingga" value="{{ old('berlaku_hingga', $member->berlaku_hingga->format('Y-m-d')) }}" required>
            </div>
        </div>

        <div class="form-group">
            <label for="foto">Ganti Foto</label>
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png" onchange="previewImage(this)">
            <small style="color:#64748b;">Biarkan kosong jika tidak ingin mengganti foto</small>
            <div id="photo-preview" style="margin-top:1rem;">
                @if($member->hasPhoto())
                <img src="{{ route('storage.local', ['path' => $member->foto_path]) }}" style="max-width:200px;border-radius:0.5rem;">
                @endif
            </div>
        </div>

        <div style="display:flex;gap:1rem;margin-top:1.5rem;">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('members.show', $member) }}" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('photo-preview');
    preview.innerHTML = '';
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="'+e.target.result+'" style="max-width:200px;border-radius:0.5rem;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('province_id').addEventListener('change', function() {
    const regencySelect = document.getElementById('regency_id');
    regencySelect.innerHTML = '<option value="">Memuat...</option>';
    fetch('/api/regencies?province_id='+this.value)
        .then(r => r.json())
        .then(data => {
            regencySelect.innerHTML = '<option value="">-- Pilih --</option>';
            data.forEach(r => regencySelect.innerHTML += '<option value="'+r.id+'">'+r.name+'</option>');
        });
});

document.getElementById('regency_id').addEventListener('change', function() {
    const districtSelect = document.getElementById('district_id');
    districtSelect.innerHTML = '<option value="">Memuat...</option>';
    fetch('/api/districts?regency_id='+this.value)
        .then(r => r.json())
        .then(data => {
            districtSelect.innerHTML = '<option value="">-- Pilih --</option>';
            data.forEach(d => districtSelect.innerHTML += '<option value="'+d.id+'">'+d.name+'</option>');
        });
});
</script>
@endpush
@endsection
