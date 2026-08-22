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
                <select name="action" style="margin-left:0.5rem;padding:0.4rem;">
                    <option value="">-- Aksi --</option>
                    <option value="delete">Hapus</option>
                </select>
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus anggota terpilih?')">Proses</button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="40"></th>
                    <th>NIK</th>
                    <th>Nama</th>
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
                    <td>{{ $member->district?->name ?? '-' }}</td>
                    <td>{{ $member->jenis_kelamin }}</td>
                    <td><span class="badge badge-{{ $member->status }}">{{ ucfirst($member->status) }}</span></td>
                    <td>{{ $member->berlaku_hingga->format('d/m/Y') }}</td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('members.show', $member) }}" class="btn btn-sm btn-outline">Detail</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="empty-state">
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

@push('scripts')
<script>
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.member-checkbox').forEach(cb => cb.checked = this.checked);
    updateBulkActions();
});

document.querySelectorAll('.member-checkbox').forEach(cb => cb.addEventListener('change', updateBulkActions));

function updateBulkActions() {
    const checked = document.querySelectorAll('.member-checkbox:checked');
    const bulkActions = document.getElementById('bulk-actions');
    const count = document.getElementById('selected-count');
    if (checked.length > 0) {
        bulkActions.style.display = 'inline-flex';
        count.textContent = checked.length + ' dipilih';
    } else {
        bulkActions.style.display = 'none';
    }
}
</script>
@endpush
@endsection
