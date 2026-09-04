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

        {{-- Hidden input untuk selected IDs (diisi oleh JS) --}}
        <input type="hidden" name="member_ids" id="member-ids-input" value="">

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

        {{-- Info selection --}}
        <div style="margin-bottom:1rem;padding:0.75rem;background:#f8fafc;border-radius:0.5rem;display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" id="select-all">
                <label for="select-all" style="margin:0;cursor:pointer;font-size:0.875rem;">Pilih Semua</label>
            </div>
            <span style="font-size:0.8rem;color:#64748b;">• Maksimal 100 anggota per batch</span>
            <span style="font-size:0.8rem;color:#64748b;">• Setiap halaman A4 = 3 KTA (depan + belakang)</span>
        </div>

        {{-- Filter (separate form, not nested) --}}
        <div style="margin-bottom:1rem;">
            <form method="GET" style="display:flex;gap:0.5rem;align-items:center;">
                <input type="text" name="search" placeholder="Cari NIK atau Nama" value="{{ request('search') }}" style="flex:1;max-width:300px;">
                <button type="submit" class="btn btn-sm btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    Cari
                </button>
                <a href="{{ route('print.create') }}" class="btn btn-sm btn-outline">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                    Reset
                </a>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="40"></th>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Domisili</th>
                    <th>Status</th>
                    <th>Berlaku</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                <tr>
                    <td><input type="checkbox" value="{{ $member->id }}" class="member-checkbox" data-id="{{ $member->id }}"></td>
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
                    <td colspan="6" class="empty-state">Tidak ada anggota yang siap cetak.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination centered --}}
        <div style="display:flex;justify-content:center;margin-top:1rem;">
            {{ $members->withQueryString()->links() }}
        </div>
    </form>
</div>

@push('scripts')
<script>
// Storage key untuk localStorage
const STORAGE_KEY = 'kta_print_selected_ids';

// Ambil semua ID yang sudah dipilih dari localStorage
function getSelectedIds() {
    const stored = localStorage.getItem(STORAGE_KEY);
    return stored ? JSON.parse(stored) : [];
}

// Simpan ID yang dipilih ke localStorage
function saveSelectedIds(ids) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
}

// Update UI dan hidden input
function updateUI() {
    const selectedIds = getSelectedIds();
    const checkboxes = document.querySelectorAll('.member-checkbox');
    const selectAll = document.getElementById('select-all');
    const countDisplay = document.getElementById('selected-count-display');
    const previewBtn = document.getElementById('preview-btn');
    const hiddenInput = document.getElementById('member-ids-input');

    // Update checkbox state
    checkboxes.forEach(cb => {
        cb.checked = selectedIds.includes(cb.dataset.id);
    });

    // Update "select all" checkbox
    const allCurrentPage = Array.from(checkboxes).map(cb => cb.dataset.id);
    const checkedOnPage = allCurrentPage.filter(id => selectedIds.includes(id));
    selectAll.checked = allCurrentPage.length > 0 && checkedOnPage.length === allCurrentPage.length;

    // Update count
    countDisplay.textContent = selectedIds.length + ' anggota dipilih';

    // Update hidden input
    hiddenInput.value = selectedIds.join(',');

    // Update button state
    previewBtn.disabled = selectedIds.length === 0;
}

// Toggle single checkbox
function toggleMember(id) {
    const selectedIds = getSelectedIds();
    const index = selectedIds.indexOf(id);

    if (index === -1) {
        selectedIds.push(id);
    } else {
        selectedIds.splice(index, 1);
    }

    saveSelectedIds(selectedIds);
    updateUI();
}

// Select all on current page
function selectAllOnPage(checked) {
    const checkboxes = document.querySelectorAll('.member-checkbox');
    const selectedIds = getSelectedIds();

    checkboxes.forEach(cb => {
        const id = cb.dataset.id;
        const index = selectedIds.indexOf(id);

        if (checked && index === -1) {
            selectedIds.push(id);
        } else if (!checked && index !== -1) {
            selectedIds.splice(index, 1);
        }
    });

    saveSelectedIds(selectedIds);
    updateUI();
}

// Init
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.member-checkbox');
    const selectAll = document.getElementById('select-all');

    // Restore checkboxes state
    updateUI();

    // Checkbox click handler
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            toggleMember(this.dataset.id);
        });
    });

    // Select all handler
    selectAll.addEventListener('change', function() {
        selectAllOnPage(this.checked);
    });

    // Form submit - convert IDs to array format
    document.getElementById('print-form').addEventListener('submit', function(e) {
        const selectedIds = getSelectedIds();
        const hiddenInput = document.getElementById('member-ids-input');

        // Create hidden inputs for each selected ID
        hiddenInput.disabled = true; // disable the comma-separated one

        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'member_ids[]';
            input.value = id;
            this.appendChild(input);
        });
    });
});
</script>
@endpush
@endsection
