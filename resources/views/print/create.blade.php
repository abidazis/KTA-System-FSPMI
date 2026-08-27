@extends('layouts.app')

@section('title', 'Pilih Anggota untuk Cetak')
@section('header', 'Pilih Anggota untuk Cetak')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Pilih Anggota (3 KTA per Halaman A4)</h3>
        <span id="selected-count" style="font-size:0.9rem;color:#64748b;">0 dipilih</span>
    </div>

    <form method="GET" class="filters">
        <div class="form-group">
            <input type="text" name="search" placeholder="Cari NIK atau Nama" value="{{ request('search') }}">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-sm btn-primary">Cari</button>
            <a href="{{ route('print.create') }}" class="btn btn-sm btn-outline">Reset</a>
        </div>
    </form>

    <form action="{{ route('print.preview') }}" method="POST" id="print-form">
        @csrf
        <div style="margin-bottom:1rem;padding:1rem;background:#f1f5f9;border-radius:0.5rem;">
            <p style="font-size:0.9rem;color:#475569;margin-bottom:0.5rem;">
                Pilih anggota yang akan dicetak. Maksimal 100 anggota per batch.
                Setiap halaman A4 berisi 3 KTA, dengan sisi DEPAN di sebelah kiri
                dan sisi BELAKANG di sebelah kanan.
            </p>
            <div style="display:flex;gap:0.5rem;align-items:center;">
                <input type="checkbox" id="select-all">
                <label for="select-all" style="margin-bottom:0;cursor:pointer;">Pilih Semua</label>
                <span id="selected-count-display" style="margin-left:auto;font-weight:600;">0 anggota dipilih</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="40"></th>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Kecamatan</th>
                    <th>Status</th>
                    <th>Berlaku</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                <tr>
                    <td><input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="member-checkbox"></td>
                    <td>{{ $member->nik }}</td>
                    <td>{{ $member->nama }}</td>
                    <td>{{ $member->district?->name ?? '-' }}</td>
                    <td><span class="badge badge-{{ $member->status }}">{{ ucfirst($member->status) }}</span></td>
                    <td>{{ $member->berlaku_hingga->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-state">Tidak ada anggota yang siap cetak.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{ $members->withQueryString()->links() }}

        <div style="margin-top:1.5rem;display:flex;gap:1rem;">
            <button type="submit" class="btn btn-primary" id="preview-btn" disabled>Preview Batch</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
const checkboxes = document.querySelectorAll('.member-checkbox');
const selectAll = document.getElementById('select-all');
const countDisplay = document.getElementById('selected-count');
const countDisplay2 = document.getElementById('selected-count-display');
const previewBtn = document.getElementById('preview-btn');

function updateCount() {
    const checked = document.querySelectorAll('.member-checkbox:checked').length;
    countDisplay.textContent = checked + ' dipilih';
    countDisplay2.textContent = checked + ' anggota dipilih';
    previewBtn.disabled = checked === 0;
}

selectAll.addEventListener('change', function() {
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateCount();
});

checkboxes.forEach(cb => cb.addEventListener('change', updateCount));
</script>
@endpush
@endsection
