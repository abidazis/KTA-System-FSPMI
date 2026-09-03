@extends('layouts.app')

@section('title', 'Data Anggota')
@section('header', 'Data Anggota')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Daftar Anggota</h3>
        <div style="display:flex;gap:0.5rem;">
            <a href="{{ route('imports.index') }}" class="btn btn-sm btn-outline">Import</a>
            <a href="{{ route('members.create') }}" class="btn btn-sm btn-primary">+ Tambah Anggota</a>
        </div>
    </div>

    <form method="GET" class="filters">
        <div class="form-group">
            <input type="text" name="search" placeholder="Cari NIK atau Nama" value="{{ request('search') }}">
        </div>
        <div class="form-group">
            <select name="province_id" id="filter-province">
                <option value="">Semua Provinsi</option>
                @foreach($provinces as $province)
                <option value="{{ $province->id }}" {{ request('province_id') == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <select name="regency_id" id="filter-regency">
                <option value="">Semua Kabupaten/Kota</option>
            </select>
        </div>
        <div class="form-group">
            <select name="district_id" id="filter-district">
                <option value="">Semua Kecamatan</option>
            </select>
        </div>
        <div class="form-group">
            <select name="jenis_kelamin">
                <option value="">Semua Gender</option>
                <option value="Laki-laki" {{ request('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                <option value="Perempuan" {{ request('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>
        <div class="form-group">
            <select name="status">
                <option value="">Semua Status</option>
                @foreach($statuses as $status)
                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <select name="agama">
                <option value="">Semua Agama</option>
                @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $agama)
                <option value="{{ $agama }}" {{ request('agama') == $agama ? 'selected' : '' }}>{{ $agama }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <select name="masa_berlaku">
                <option value="">Semua Masa Berlaku</option>
                <option value="active" {{ request('masa_berlaku') == 'active' ? 'selected' : '' }}>Aktif (>30 hari)</option>
                <option value="expiring" {{ request('masa_berlaku') == 'expiring' ? 'selected' : '' }}>Segera Expired (<30 hari)</option>
                <option value="expired" {{ request('masa_berlaku') == 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <a href="{{ route('members.index') }}" class="btn btn-sm btn-outline">Reset</a>
        </div>
    </form>

    <form id="bulk-form" method="POST" action="{{ route('members.bulk-action') }}">
        @csrf
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <label style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" id="select-all"> Pilih Semua
            </label>
            <div id="bulk-actions" style="display:none;">
                <span id="selected-count">0 dipilih</span>
                <select name="action" id="bulk-action-select" style="margin-left:0.5rem;padding:0.4rem;">
                    <option value="">-- Aksi --</option>
                    <option value="update_status">Ubah Status</option>
                    <option value="delete">Hapus</option>
                </select>
                <select name="new_status" id="new-status-select" style="margin-left:0.5rem;padding:0.4rem;display:none;">
                    <option value="">-- Status --</option>
                    <option value="draft">Draft</option>
                    <option value="ready">Ready</option>
                    <option value="generated">Generated</option>
                    <option value="printed">Printed</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button type="submit" class="btn btn-sm btn-danger" id="bulk-submit-btn">Proses</button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="40"></th>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Kabupaten/Kota</th>
                    <th>Kecamatan</th>
                    <th>Jenis Kelamin</th>
                    <th>Status</th>
                    <th>Berlaku</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                <tr>
                    <td>
                        <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="member-checkbox">
                    </td>
                    <td><a href="{{ route('members.show', $member) }}">{{ $member->nik }}</a></td>
                    <td>{{ $member->nama }}</td>
                    <td>{{ $member->regency?->name ?? '-' }}</td>
                    <td>{{ $member->district?->name ?? '-' }}</td>
                    <td>{{ $member->jenis_kelamin }}</td>
                    <td>
                        <select name="status" class="status-select" data-member-id="{{ $member->id }}" style="padding:0.3rem;border-radius:0.25rem;border:1px solid #d1d5db;font-size:0.8rem;">
                            <option value="draft" {{ $member->status == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="ready" {{ $member->status == 'ready' ? 'selected' : '' }}>Ready</option>
                            <option value="generated" {{ $member->status == 'generated' ? 'selected' : '' }}>Generated</option>
                            <option value="printed" {{ $member->status == 'printed' ? 'selected' : '' }}>Printed</option>
                            <option value="active" {{ $member->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $member->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </td>
                    <td>{{ $member->berlaku_hingga->format('d/m/Y') }}</td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('members.show', $member) }}" class="btn btn-sm btn-outline" title="Detail">Detail</a>
                            <a href="{{ route('members.edit', $member) }}" class="btn btn-sm btn-primary" title="Edit">Edit</a>
                            <form method="POST" action="{{ route('members.destroy', $member) }}" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus anggota ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="empty-state">
                        <p>Belum ada data anggota.</p>
                        <a href="{{ route('members.create') }}" class="btn btn-primary" style="margin-top:1rem;">Tambah Anggota</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{ $members->withQueryString()->links() }}
    </form>
</div>
@endsection
