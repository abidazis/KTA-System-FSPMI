@extends('layouts.app')

@section('title', 'Pilih Anggota untuk Cetak')
@section('header', 'Pilih Anggota untuk Cetak')

@prepend('styles')
<style>
/* Pagination styling */
.pagination {
    display: flex;
    gap: 0.25rem;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
}

.pagination a,
.pagination span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2rem;
    height: 2rem;
    padding: 0 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    color: #475569;
    text-decoration: none;
    transition: all 0.2s;
}

.pagination a:hover {
    background: #f1f5f9;
    border-color: #3b82f6;
    color: #3b82f6;
}

.pagination .active {
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
}

.pagination .disabled {
    opacity: 0.5;
    pointer-events: none;
}

/* SVG icons for pagination */
.pagination svg {
    width: 14px;
    height: 14px;
}
</style>
@endprepend

@section('content')
<div class="card">
    <form action="{{ route('print.preview') }}" method="POST" id="print-form">
        @csrf

        {{-- Header dengan count dan tombol preview --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid #e2e8f0;">
            <div>
                <h3 style="margin:0 0 0.25rem 0;font-size:1rem;font-weight:600;">Pilih Anggota</h3>
                <span id="selected-count-display" style="font-size:0.85rem;color:#64748b;">0 anggota dipilih</span>
            </div>
            <button type="submit" class="btn btn-primary" id="preview-btn" disabled>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Preview Batch
            </button>
        </div>

        {{-- Hidden inputs untuk selected IDs (diisi oleh JS) --}}
        <div id="selected-ids-container"></div>

        {{-- Info selection --}}
        <div style="margin-bottom:1rem;padding:0.75rem;background:#f8fafc;border-radius:0.5rem;display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" id="select-all">
                <label for="select-all" style="margin:0;cursor:pointer;font-size:0.875rem;">Pilih Semua</label>
            </div>
            <span style="font-size:0.8rem;color:#64748b;">• Maksimal 100 anggota per batch</span>
            <span style="font-size:0.8rem;color:#64748b;">• Setiap halaman A4 = 4 KTA (depan + belakang)</span>
        </div>

        {{-- Filter (separate form, not nested) --}}
        <div style="margin-bottom:1rem;">
            <form method="GET" style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                <input type="text" name="search" placeholder="Cari NIK atau Nama" value="{{ request('search') }}" style="flex:1;min-width:180px;padding:0.5rem 0.75rem;border:1px solid #e2e8f0;border-radius:0.375rem;font-size:0.875rem;">
                <select name="status" style="padding:0.5rem 0.75rem;border:1px solid #e2e8f0;border-radius:0.375rem;font-size:0.875rem;min-width:140px;">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready</option>
                    <option value="generated" {{ request('status') == 'generated' ? 'selected' : '' }}>Generated</option>
                    <option value="printed" {{ request('status') == 'printed' ? 'selected' : '' }}>Printed</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    Filter
                </button>
                <a href="{{ route('print.create') }}" class="btn btn-sm btn-outline">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                    Reset
                </a>
                <span style="font-size:0.75rem;color:#94a3b8;margin-left:0.5rem;">{{ $members->count() }} anggota</span>
            </form>
        </div>

        {{-- Tabel dengan max-height untuk scroll --}}
        <div style="max-height:500px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:0.5rem;">
        <table style="width:100%;">
            <thead style="position:sticky;top:0;background:#f8fafc;z-index:1;">
                <tr>
                    <th width="40" style="position:sticky;top:0;background:#f8fafc;"></th>
                    <th style="position:sticky;top:0;background:#f8fafc;">NIK</th>
                    <th style="position:sticky;top:0;background:#f8fafc;">Nama</th>
                    <th style="position:sticky;top:0;background:#f8fafc;">Domisili</th>
                    <th style="position:sticky;top:0;background:#f8fafc;">Status</th>
                    <th style="position:sticky;top:0;background:#f8fafc;">Berlaku</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                <tr style="background:#fff;">
                    <td><input type="checkbox" value="{{ $member->id }}" class="member-checkbox"></td>
                    <td>{{ $member->nik }}</td>
                    <td>{{ $member->nama }}</td>
                    <td>
                        <div>{{ $member->district?->name ?? '-' }}</div>
                        <small style="color:var(--gray-500);">{{ $member->regency?->name ?? '' }}</small>
                    </td>
                    <td><span class="badge badge-{{ $member->status }}">{{ ucfirst($member->status) }}</span></td>
                    <td>{{ $member->berlaku_hingga ? $member->berlaku_hingga->format('d/m/Y') : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-state">Tidak ada anggota ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>{{-- end scrollable div --}}
    </form>
</div>

@push('scripts')
<script>
(function() {
    // Elemen UI
    var selectAll = document.getElementById('select-all');
    var checkboxes = document.querySelectorAll('.member-checkbox');
    var countDisplay = document.getElementById('selected-count-display');
    var previewBtn = document.getElementById('preview-btn');
    var idsContainer = document.getElementById('selected-ids-container');

    // Hitung jumlah dipilih
    function getSelectedCount() {
        var count = 0;
        checkboxes.forEach(function(cb) {
            if (cb.checked) count++;
        });
        return count;
    }

    // Update UI
    function updateUI() {
        var count = getSelectedCount();
        countDisplay.textContent = count + ' anggota dipilih';
        previewBtn.disabled = count === 0;

        // Update select all
        var allChecked = checkboxes.length > 0;
        checkboxes.forEach(function(cb) {
            if (!cb.checked) allChecked = false;
        });
        selectAll.checked = allChecked;

        // Update hidden inputs
        updateHiddenInputs();
    }

    // Update hidden inputs untuk form submission
    function updateHiddenInputs() {
        // Hapus input lama
        idsContainer.innerHTML = '';
        // Tambah input baru untuk setiap checkbox yang dicentang
        checkboxes.forEach(function(cb) {
            if (cb.checked) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'member_ids[]';
                input.value = cb.value;
                idsContainer.appendChild(input);
            }
        });
    }

    // Toggle semua checkbox di halaman ini
    function toggleAll(checked) {
        checkboxes.forEach(function(cb) {
            cb.checked = checked;
        });
        updateUI();
    }

    // Event listeners
    selectAll.addEventListener('change', function() {
        toggleAll(this.checked);
    });

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateUI);
    });

    // Inisialisasi
    updateUI();

    // Debug: log saat submit
    document.getElementById('print-form').addEventListener('submit', function() {
        console.log('[PrintForm] Submitting with ' + getSelectedCount() + ' members');
    });
})();
</script>
@endpush
@endsection
