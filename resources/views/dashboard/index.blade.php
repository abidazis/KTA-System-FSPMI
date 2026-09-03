@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
{{-- Stats Cards --}}
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-value">{{ number_format($stats['total_anggota']) }}</div>
        <div class="stat-label">Total Anggota</div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-value">{{ number_format($stats['kta_aktif']) }}</div>
        <div class="stat-label">KTA Aktif</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-value">{{ number_format($stats['kta_akan_kadaluarsa']) }}</div>
        <div class="stat-label">Akan Kadaluarsa (30 hari)</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div class="stat-value">{{ number_format($stats['kta_kadaluarsa']) }}</div>
        <div class="stat-label">KTA Kadaluarsa</div>
    </div>
</div>

{{-- Recent Members --}}
<div class="card">
    <div class="card-header">
        <h3>Daftar Anggota Terbaru</h3>
        <a href="{{ route('members.index') }}" class="btn btn-outline">Lihat Semua</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>NIK</th>
                <th>Nama</th>
                <th>Wilayah</th>
                <th>Status</th>
                <th>Berlaku Hingga</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentMembers as $member)
            <tr>
                <td><strong><a href="{{ route('members.show', $member) }}" style="color:var(--primary);text-decoration:none;">{{ $member->nik }}</a></strong></td>
                <td>{{ $member->nama }}</td>
                <td>{{ $member->district?->name ?? $member->regency?->name ?? '-' }}</td>
                <td><span class="badge badge-{{ $member->status }}">{{ ucfirst($member->status) }}</span></td>
                <td>{{ $member->berlaku_hingga->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="empty-state">
                    <p>Belum ada data anggota</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Stats Grid --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;">
    <div class="card">
        <div class="card-header">
            <h3>Anggota per Kecamatan</h3>
        </div>
        @forelse($membersByDistrict->take(8) as $item)
        <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--gray-100);">
            <span style="color:var(--gray-700);">{{ $item->district?->name ?? 'Tidak Diketahui' }}</span>
            <strong style="color:var(--primary);">{{ $item->total }} org</strong>
        </div>
        @empty
        <p style="padding:1.5rem;text-align:center;color:var(--gray-500);">Belum ada data</p>
        @endforelse
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Jenis Kelamin</h3>
        </div>
        @forelse($membersByGender as $gender => $count)
        <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--gray-100);">
            <span style="color:var(--gray-700);">{{ $gender }}</span>
            <strong style="color:var(--primary);">{{ $count }} org</strong>
        </div>
        @empty
        <p style="padding:1.5rem;text-align:center;color:var(--gray-500);">Belum ada data</p>
        @endforelse
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Status KTA</h3>
        </div>
        @forelse($membersByStatus as $status => $count)
        <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--gray-100);">
            <span><span class="badge badge-{{ $status }}">{{ ucfirst($status) }}</span></span>
            <strong style="color:var(--primary);">{{ $count }} org</strong>
        </div>
        @empty
        <p style="padding:1.5rem;text-align:center;color:var(--gray-500);">Belum ada data</p>
        @endforelse
    </div>
</div>
@endsection
