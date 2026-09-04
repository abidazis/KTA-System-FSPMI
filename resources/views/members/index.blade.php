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
    <form method="GET" class="filters" style="gap:0.5rem;padding:0.75rem;">
        <input type="text" name="search" placeholder="Cari..." value="{{ request('search') }}" style="flex:1;min-width:120px;padding:0.5rem 0.75rem;font-size:0.9rem;">
        <select name="province_id" id="filter-province" style="padding:0.5rem 0.75rem;font-size:0.9rem;min-width:140px;">
            <option value="">Semua Provinsi</option>
            @foreach($provinces as $province)
            <option value="{{ $province->id }}" {{ request('province_id') == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
            @endforeach
        </select>
        <select name="regency_id" id="filter-regency" disabled style="padding:0.5rem 0.75rem;font-size:0.9rem;min-width:140px;">
            <option value="">Kab/Kota</option>
        </select>
        <select name="district_id" id="filter-district" disabled style="padding:0.5rem 0.75rem;font-size:0.9rem;min-width:140px;">
            <option value="">Kecamatan</option>
        </select>
        <select name="jenis_kelamin" style="padding:0.5rem 0.75rem;font-size:0.9rem;min-width:100px;">
            <option value="">Gender</option>
            <option value="Laki-laki" {{ request('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
            <option value="Perempuan" {{ request('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
        </select>
        <select name="status" style="padding:0.5rem 0.75rem;font-size:0.9rem;min-width:100px;">
            <option value="">Status</option>
            @foreach($statuses as $status)
            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary" style="padding:0.5rem 1rem;font-size:0.9rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </button>
        <a href="{{ route('members.index') }}" class="btn btn-outline" style="padding:0.5rem 0.75rem;font-size:0.9rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
        </a>
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
                    <th>Domisili</th>
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
                    <td>
                        <div>{{ $member->district?->name ?? '-' }}</div>
                        <small style="color:var(--gray-500);">{{ $member->regency?->name ?? '' }}</small>
                    </td>
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
                            <a href="{{ route('members.show', $member) }}" class="btn btn-sm btn-outline" title="Detail">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </a>
                            <a href="{{ route('members.edit', $member) }}" class="btn btn-sm btn-primary" title="Edit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                            </a>
                            <form method="POST" action="{{ route('members.destroy', $member) }}" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus anggota ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="empty-state">
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
// ============================================
// FILTER DROPDOWN - Province/Regency/District Cascade
// ============================================

const filterProvince = document.getElementById('filter-province');
const filterRegency = document.getElementById('filter-regency');
const filterDistrict = document.getElementById('filter-district');

// Load regencies when province changes
filterProvince.addEventListener('change', function() {
    const provinceId = this.value;
    filterRegency.innerHTML = '<option value="">Memuat...</option>';
    filterDistrict.innerHTML = '<option value="">Semua Kecamatan</option>';
    filterDistrict.disabled = true;

    if (provinceId) {
        fetch('/api/regencies/' + provinceId)
            .then(r => r.json())
            .then(data => {
                filterRegency.innerHTML = '<option value="">Semua Kab/Kota</option>';
                data.forEach(r => {
                    filterRegency.innerHTML += `<option value="${r.id}">${r.name}</option>`;
                });
                filterRegency.disabled = false;
                // Preserve selection if exists
                @if(request('regency_id'))
                    filterRegency.value = '{{ request('regency_id') }}';
                @endif
            })
            .catch(err => {
                filterRegency.innerHTML = '<option value="">Error memuat</option>';
            });
    } else {
        filterRegency.innerHTML = '<option value="">Semua Kab/Kota</option>';
        filterRegency.disabled = true;
    }
});

// Load districts when regency changes
filterRegency.addEventListener('change', function() {
    const regencyId = this.value;
    filterDistrict.innerHTML = '<option value="">Memuat...</option>';

    if (regencyId) {
        fetch('/api/districts/' + regencyId)
            .then(r => r.json())
            .then(data => {
                filterDistrict.innerHTML = '<option value="">Semua Kecamatan</option>';
                data.forEach(d => {
                    filterDistrict.innerHTML += `<option value="${d.id}">${d.name}</option>`;
                });
                filterDistrict.disabled = false;
                // Preserve selection if exists
                @if(request('district_id'))
                    filterDistrict.value = '{{ request('district_id') }}';
                @endif
            })
            .catch(err => {
                filterDistrict.innerHTML = '<option value="">Error memuat</option>';
            });
    } else {
        filterDistrict.innerHTML = '<option value="">Semua Kecamatan</option>';
        filterDistrict.disabled = true;
    }
});

// Initialize on page load - check if province already selected
document.addEventListener('DOMContentLoaded', function() {
    @if(request('province_id'))
        // Province is selected, load its regencies
        filterProvince.dispatchEvent(new Event('change'));
    @endif

    @if(request('regency_id') && request('province_id'))
        // After regencies loaded, also load districts
        setTimeout(function() {
            @if(request('district_id'))
                filterRegency.dispatchEvent(new Event('change'));
            @endif
        }, 500);
    @endif
});

// ============================================
// Bulk Selection
// ============================================
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
