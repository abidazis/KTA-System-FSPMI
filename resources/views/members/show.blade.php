@extends('layouts.app')

@section('title', 'Detail Anggota')
@section('header', 'Detail Anggota')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Data Anggota</h3>
        <div style="display:flex;gap:0.75rem;">
            <a href="{{ route('members.kta.preview', $member) }}" class="btn btn-success" {{ !$member->hasPhoto() ? 'disabled' : '' }}>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                Preview KTA
            </a>
            <a href="{{ route('members.edit', $member) }}" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                Edit
            </a>
            <form action="{{ route('members.destroy', $member) }}" method="POST" style="display:inline;" id="delete-form-{{ $member->id }}">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-danger" onclick="FSPMIModal.confirmDelete('Yakin ingin menghapus anggota ini?').then(function(ok){ if(ok){document.getElementById('delete-form-{{ $member->id }}').submit();} });">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:280px 1fr;gap:2rem;">
        {{-- Photo & Basic Info --}}
        <div style="text-align:center;">
            @if($member->hasPhoto())
            <div style="margin-bottom:1.5rem;">
                <img src="{{ route('storage.local', ['path' => $member->foto_path]) }}" alt="Foto"
                     style="width:180px;height:240px;object-fit:cover;border-radius:0.75rem;box-shadow:0 4px 12px rgba(0,0,0,0.15);border:3px solid var(--white);">
            </div>
            @else
            <div style="width:180px;height:240px;margin:0 auto 1.5rem;background:var(--gray-100);border-radius:0.75rem;display:flex;align-items:center;justify-content:center;color:var(--gray-400);font-size:3rem;">
                            </div>
            @endif

            <div style="background:var(--gray-50);border-radius:0.5rem;padding:1rem;text-align:left;">
                <div style="margin-bottom:0.75rem;">
                    <div style="font-size:0.8rem;color:var(--gray-500);">NIK</div>
                    <div style="font-size:1.1rem;font-weight:600;color:var(--primary);">{{ $member->nik }}</div>
                </div>
                <div style="margin-bottom:0.75rem;">
                    <div style="font-size:0.8rem;color:var(--gray-500);">Nama</div>
                    <div style="font-size:1rem;font-weight:600;">{{ $member->nama }}</div>
                </div>
                <div>
                    <div style="font-size:0.8rem;color:var(--gray-500);">Status</div>
                    <span class="badge badge-{{ $member->status }}">{{ ucfirst($member->status) }}</span>
                </div>
            </div>
        </div>

        {{-- Detail Info --}}
        <div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.5rem;">
                <div class="card" style="margin-bottom:0;box-shadow:none;border:1px solid var(--gray-200);">
                    <h4 style="font-size:0.95rem;font-weight:600;margin-bottom:1rem;color:var(--gray-700);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        Data Pribadi
                    </h4>
                    <table style="width:100%;">
                        <tr>
                            <td style="padding:0.5rem 0;color:var(--gray-500);font-size:0.9rem;">Tempat, Tgl Lahir</td>
                            <td style="padding:0.5rem 0;font-size:0.95rem;">{{ $member->tempat_lahir }}, {{ $member->tanggal_lahir->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td style="padding:0.5rem 0;color:var(--gray-500);font-size:0.9rem;">Jenis Kelamin</td>
                            <td style="padding:0.5rem 0;font-size:0.95rem;">{{ $member->jenis_kelamin }}</td>
                        </tr>
                        <tr>
                            <td style="padding:0.5rem 0;color:var(--gray-500);font-size:0.9rem;">Agama</td>
                            <td style="padding:0.5rem 0;font-size:0.95rem;">{{ $member->agama }}</td>
                        </tr>
                        <tr>
                            <td style="padding:0.5rem 0;color:var(--gray-500);font-size:0.9rem;">Alamat</td>
                            <td style="padding:0.5rem 0;font-size:0.95rem;">{{ $member->alamat }}</td>
                        </tr>
                    </table>
                </div>

                <div class="card" style="margin-bottom:0;box-shadow:none;border:1px solid var(--gray-200);">
                    <h4 style="font-size:0.95rem;font-weight:600;margin-bottom:1rem;color:var(--gray-700);">Data Perusahaan & Wilayah</h4>
                    <table style="width:100%;">
                        <tr>
                            <td style="padding:0.5rem 0;color:var(--gray-500);font-size:0.9rem;">Perusahaan</td>
                            <td style="padding:0.5rem 0;font-size:0.95rem;">{{ $member->company?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:0.5rem 0;color:var(--gray-500);font-size:0.9rem;">Kecamatan</td>
                            <td style="padding:0.5rem 0;font-size:0.95rem;">{{ $member->district?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:0.5rem 0;color:var(--gray-500);font-size:0.9rem;">Kab/Kota</td>
                            <td style="padding:0.5rem 0;font-size:0.95rem;">{{ $member->regency?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:0.5rem 0;color:var(--gray-500);font-size:0.9rem;">Provinsi</td>
                            <td style="padding:0.5rem 0;font-size:0.95rem;">{{ $member->province?->name ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

                <div class="card" style="margin-bottom:0;box-shadow:none;border:1px solid var(--gray-200);">
                    <h4 style="font-size:0.95rem;font-weight:600;margin-bottom:1rem;color:var(--gray-700);">Masa Berlaku</h4>
                    <table style="width:100%;">
                        <tr>
                            <td style="padding:0.5rem 0;color:var(--gray-500);font-size:0.9rem;">Tanggal Pembuatan</td>
                            <td style="padding:0.5rem 0;font-size:0.95rem;">{{ $member->tanggal_pembuatan->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td style="padding:0.5rem 0;color:var(--gray-500);font-size:0.9rem;">Berlaku Hingga</td>
                            <td style="padding:0.5rem 0;font-size:0.95rem;font-weight:600;">{{ $member->berlaku_hingga->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td style="padding:0.5rem 0;color:var(--gray-500);font-size:0.9rem;">Sisa Hari</td>
                            <td style="padding:0.5rem 0;font-size:0.95rem;">
                                @php
                                    $daysLeft = now()->diffInDays($member->berlaku_hingga, false);
                                @endphp
                                @if($daysLeft < 0)
                                    <span style="color:var(--danger);">Sudah Expired</span>
                                @elseif($daysLeft <= 30)
                                    <span style="color:var(--warning);">{{ $daysLeft }} hari</span>
                                @else
                                    <span style="color:var(--success);">{{ $daysLeft }} hari</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Print History --}}
@if($member->printBatches->count() > 0)
<div class="card">
    <div class="card-header">
        <h3>Riwayat Cetak KTA</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Batch</th>
                <th>Tanggal</th>
                <th>Operator</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($member->printBatches as $batch)
            <tr>
                <td><strong style="color:var(--primary);">{{ $batch->batch_number }}</strong> ({{ ucfirst($batch->type) }})</td>
                <td>{{ $batch->tanggal_cetak->format('d/m/Y') }}</td>
                <td>{{ $batch->printer?->name ?? '-' }}</td>
                <td><a href="{{ route('print.show', $batch) }}" class="btn btn-sm btn-outline">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    Detail
                </a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div style="margin-top:1.5rem;">
    <a href="{{ route('members.index') }}" class="btn btn-outline">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Kembali ke Daftar
    </a>
</div>
@endsection
