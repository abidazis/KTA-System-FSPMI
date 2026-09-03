@extends('layouts.app')

@section('title', 'Data Anggota')
@section('header', 'Data Anggota')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Daftar Anggota</h3>
        <div style="display:flex;gap:0.75rem;">
            <a href="{{ route('imports.index') }}" class="btn btn-outline">Import</a>
            <a href="{{ route('members.create') }}" class="btn btn-primary">+ Tambah</a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="filters">
        <div class="form-group">
            <input type="text" name="search" placeholder="Cari NIK atau Nama..." value="{{ request('search') }}">
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
                <option value="">Semua Kab/Kota</option>
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
        <div class="form-group" style="flex:0;">
            <button type="submit" class="btn btn-primary"> Filter</button>
            <a href="{{ route('members.index') }}" class="btn btn-outline">Reset</a>
        </div>
    </form>

    {{-- Bulk Actions --}}
    <form id="bulk-form" method="POST" action="{{ route('members.bulk-action') }}">
        @csrf
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;padding:1rem;background:var(--gray-50);border-radius:0.5rem;border:1px solid var(--gray-200);">
            <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;">
                <input type="checkbox" id="select-all" style="width:1.25rem;height:1.25rem;">
                <span style="font-weight:500;">Pilih Semua</span>
            </label>
            <div id="bulk-actions" style="display:none;">
                <span id="selected-count" style="font-weight:500;color:var(--primary);margin-right:1rem;">0 dipilih</span>
                <select name="action" id="bulk-action-select" style="padding:0.5rem 0.75rem;border:2px solid var(--gray-200);border-radius:0.375rem;font-size:0.95rem;">
                    <option value="">-- Aksi --</option>
                    <option value="update_status">Ubah Status</option>
                    <option value="delete">Hapus</option>
                </select>
                <select name="new_status" id="new-status-select" style="padding:0.5rem 0.75rem;border:2px solid var(--gray-200);border-radius:0.375rem;font-size:0.95rem;display:none;">
                    <option value="">-- Status --</option>
                    <option value="draft">Draft</option>
                    <option value="ready">Ready</option>
                    <option value="generated">Generated</option>
                    <option value="printed">Printed</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button type="submit" class="btn btn-danger" id="bulk-submit-btn">Proses</button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="50"></th>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Kabupaten/Kota</th>
                    <th>Kecamatan</th>
                    <th>Gender</th>
                    <th>Status</th>
                    <th>Berlaku</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                <tr>
                    <td>
                        <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="member-checkbox" style="width:1.125rem;height:1.125rem;">
                    </td>
                    <td><strong><a href="{{ route('members.show', $member) }}" style="color:var(--primary);text-decoration:none;">{{ $member->nik }}</a></strong></td>
                    <td>{{ $member->nama }}</td>
                    <td>{{ $member->regency?->name ?? '-' }}</td>
                    <td>{{ $member->district?->name ?? '-' }}</td>
                    <td>{{ $member->jenis_kelamin }}</td>
                    <td>
                        <select name="status" class="status-select" data-member-id="{{ $member->id }}" style="padding:0.375rem 0.5rem;border-radius:0.25rem;border:2px solid var(--gray-200);font-size:0.85rem;">
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
                            <a href="{{ route('members.show', $member) }}" class="btn btn-sm btn-outline" title="Detail"></a>
                            <a href="{{ route('members.edit', $member) }}" class="btn btn-sm btn-primary" title="Edit"></a>
                            <form method="POST" action="{{ route('members.destroy', $member) }}" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus anggota ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="empty-state">
                        <p>Belum ada data anggota.</p>
                        <a href="{{ route('members.create') }}" class="btn btn-primary">+ Tambah Anggota Baru</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:1.5rem;">
            {{ $members->withQueryString()->links() }}
        </div>
    </form>
</div>

<script>
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.member-checkbox').forEach(cb => cb.checked = this.checked);
    updateBulkUI();
});
document.querySelectorAll('.member-checkbox').forEach(cb => cb.addEventListener('change', updateBulkUI));
function updateBulkUI() {
    const checked = document.querySelectorAll('.member-checkbox:checked').length;
    document.getElementById('bulk-actions').style.display = checked > 0 ? 'flex' : 'none';
    document.getElementById('selected-count').textContent = checked + ' dipilih';
}
document.getElementById('bulk-action-select').addEventListener('change', function() {
    document.getElementById('new-status-select').style.display = this.value === 'update_status' ? 'inline-block' : 'none';
});
</script>
@endsection
