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
                @error('tempat_lahir') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir *</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $member->tanggal_lahir->format('Y-m-d')) }}" required>
                @error('tanggal_lahir') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="alamat">Alamat *</label>
            <textarea id="alamat" name="alamat" rows="2" required>{{ old('alamat', $member->alamat) }}</textarea>
            @error('alamat') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="province_id">Provinsi</label>
                <select id="province_id" name="province_id">
                    <option value="">-- Pilih --</option>
                    @foreach($provinces as $province)
                    <option value="{{ $province->id }}" {{ old('province_id', $member->province_id) == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="regency_id">Kabupaten/Kota</label>
                <select id="regency_id" name="regency_id">
                    <option value="">-- Pilih --</option>
                    @foreach($regencies as $regency)
                    <option value="{{ $regency->id }}" {{ old('regency_id', $member->regency_id) == $regency->id ? 'selected' : '' }}>{{ $regency->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="district_id">Kecamatan</label>
                <select id="district_id" name="district_id">
                    <option value="">-- Pilih --</option>
                    @foreach($districts as $district)
                    <option value="{{ $district->id }}" {{ old('district_id', $member->district_id) == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="jenis_kelamin">Jenis Kelamin *</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <option value="Laki-laki" {{ old('jenis_kelamin', $member->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="Perempuan" {{ old('jenis_kelamin', $member->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('jenis_kelamin') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="agama">Agama *</label>
                <select id="agama" name="agama" required>
                    <option value="">-- Pilih --</option>
                    @foreach($religions as $religion)
                    <option value="{{ $religion }}" {{ old('agama', $member->agama) == $religion ? 'selected' : '' }}>{{ $religion }}</option>
                    @endforeach
                </select>
                @error('agama') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="company_id">Perusahaan *</label>
                <select id="company_id" name="company_id" required>
                    <option value="">-- Pilih --</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ old('company_id', $member->company_id) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
                @error('company_id') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="tanggal_pembuatan">Tanggal Pembuatan *</label>
                <input type="date" id="tanggal_pembuatan" name="tanggal_pembuatan" value="{{ old('tanggal_pembuatan', $member->tanggal_pembuatan->format('Y-m-d')) }}" required>
                @error('tanggal_pembuatan') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="berlaku_hingga">Berlaku Hingga *</label>
                <input type="date" id="berlaku_hingga" name="berlaku_hingga" value="{{ old('berlaku_hingga', $member->berlaku_hingga->format('Y-m-d')) }}" required>
                @error('berlaku_hingga') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="foto">Ganti Foto</label>
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png" onchange="previewImage(this)">
            <small style="color:#64748b;">Biarkan kosong jika tidak ingin mengganti foto</small>
            @error('foto') <span class="error">{{ $message }}</span> @enderror
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

<script type="text/javascript">
(function() {
    var memberProvinceId = "{{ $member->province_id ?? '' }}";
    var memberRegencyId = "{{ $member->regency_id ?? '' }}";
    var memberDistrictId = "{{ $member->district_id ?? '' }}";

    var wilayahCache = { regencies: {}, districts: {} };

    function loadRegencies(provinceId, selectedId) {
        var regencySelect = document.getElementById('regency_id');
        var districtSelect = document.getElementById('district_id');

        if (!provinceId) {
            regencySelect.innerHTML = '<option value="">-- Pilih --</option>';
            districtSelect.innerHTML = '<option value="">-- Pilih --</option>';
            return;
        }

        if (!wilayahCache.regencies[provinceId]) {
            wilayahCache.regencies[provinceId] = [];
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '/api/regencies?province_id=' + provinceId, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    wilayahCache.regencies[provinceId] = JSON.parse(xhr.responseText);
                    populateRegencies(provinceId, selectedId);
                }
            };
            xhr.send();
        } else {
            populateRegencies(provinceId, selectedId);
        }

        function populateRegencies(pid, sid) {
            var regencies = wilayahCache.regencies[pid];
            regencySelect.innerHTML = '<option value="">-- Pilih --</option>';
            for (var i = 0; i < regencies.length; i++) {
                var sel = (sid && sid == regencies[i].id) ? 'selected' : '';
                regencySelect.innerHTML += '<option value="' + regencies[i].id + '" ' + sel + '>' + regencies[i].name + '</option>';
            }
            districtSelect.innerHTML = '<option value="">-- Pilih --</option>';
        }
    }

    function loadDistricts(regencyId, selectedId) {
        var districtSelect = document.getElementById('district_id');

        if (!regencyId) {
            districtSelect.innerHTML = '<option value="">-- Pilih --</option>';
            return;
        }

        if (!wilayahCache.districts[regencyId]) {
            wilayahCache.districts[regencyId] = [];
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '/api/districts?regency_id=' + regencyId, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    wilayahCache.districts[regencyId] = JSON.parse(xhr.responseText);
                    populateDistricts(regencyId, selectedId);
                }
            };
            xhr.send();
        } else {
            populateDistricts(regencyId, selectedId);
        }

        function populateDistricts(rid, sid) {
            var districts = wilayahCache.districts[rid];
            districtSelect.innerHTML = '<option value="">-- Pilih --</option>';
            for (var i = 0; i < districts.length; i++) {
                var sel = (sid && sid == districts[i].id) ? 'selected' : '';
                districtSelect.innerHTML += '<option value="' + districts[i].id + '" ' + sel + '>' + districts[i].name + '</option>';
            }
        }
    }

    function previewImage(input) {
        var preview = document.getElementById('photo-preview');
        preview.innerHTML = '';
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" style="max-width:200px;border-radius:0.5rem;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize with member data
        if (memberProvinceId) {
            loadRegencies(memberProvinceId, memberRegencyId);
        }
        if (memberRegencyId) {
            // Delay slightly to ensure regencies are loaded first
            setTimeout(function() { loadDistricts(memberRegencyId, memberDistrictId); }, 100);
        }

        // Photo preview
        var fotoInput = document.getElementById('foto');
        if (fotoInput) {
            fotoInput.addEventListener('change', function() { previewImage(this); });
        }

        // Province change
        document.getElementById('province_id').addEventListener('change', function() {
            loadRegencies(this.value, null);
        });

        // Regency change
        document.getElementById('regency_id').addEventListener('change', function() {
            loadDistricts(this.value, null);
        });
    });
})();
</script>
@endsection
