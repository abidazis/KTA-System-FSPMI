@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        </div>
        <div class="stat-value">{{ number_format($stats['total_anggota']) }}</div>
        <div class="stat-label">Total Anggota</div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="stat-value">{{ number_format($stats['kta_aktif']) }}</div>
        <div class="stat-label">KTA Aktif</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="stat-value">{{ number_format($stats['kta_akan_kadaluarsa']) }}</div>
        <div class="stat-label">Akan Kadaluarsa (30hr)</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div class="stat-value">{{ number_format($stats['kta_kadaluarsa']) }}</div>
        <div class="stat-label">KTA Kadaluarsa</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Anggota Terbaru</h3>
        <a href="{{ route('members.index') }}" class="btn btn-sm btn-outline">Lihat Semua</a>
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
                <td><a href="{{ route('members.show', $member) }}">{{ $member->nik }}</a></td>
                <td>{{ $member->nama }}</td>
                <td>{{ $member->district?->name ?? $member->regency?->name ?? '-' }}</td>
                <td><span class="badge badge-{{ $member->status }}">{{ ucfirst($member->status) }}</span></td>
                <td>{{ $member->berlaku_hingga->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="empty-state">Belum ada data anggota</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem;">
    <div class="card">
        <div class="card-header">
            <h3>Anggota per Kecamatan</h3>
        </div>
        @forelse($membersByDistrict as $item)
        <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #f1f5f9;">
            <span>{{ $item->district?->name ?? 'Tidak Diketahui' }}</span>
            <strong>{{ $item->total }}</strong>
        </div>
        @empty
        <p style="padding:1rem;color:#64748b;text-align:center;">Belum ada data</p>
        @endforelse
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Anggota per Jenis Kelamin</h3>
        </div>
        @forelse($membersByGender as $gender => $count)
        <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #f1f5f9;">
            <span>{{ $gender }}</span>
            <strong>{{ $count }}</strong>
        </div>
        @empty
        <p style="padding:1rem;color:#64748b;text-align:center;">Belum ada data</p>
        @endforelse
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Anggota per Status</h3>
        </div>
        @forelse($membersByStatus as $status => $count)
        <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #f1f5f9;">
            <span><span class="badge badge-{{ $status }}">{{ ucfirst($status) }}</span></span>
            <strong>{{ $count }}</strong>
        </div>
        @empty
        <p style="padding:1rem;color:#64748b;text-align:center;">Belum ada data</p>
        @endforelse
    </div>
</div>
@endsection
