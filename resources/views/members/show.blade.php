@extends('layouts.app')

@section('title', 'Detail Anggota')
@section('header', 'Detail Anggota')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Data Anggota</h3>
        <div style="display:flex;gap:0.5rem;">
            <a href="{{ route('members.kta.preview', $member) }}" class="btn btn-sm btn-success" {{ !$member->hasPhoto() ? 'disabled' : '' }}>Preview KTA</a>
            <a href="{{ route('members.edit', $member) }}" class="btn btn-sm btn-outline">Edit</a>
            <form action="{{ route('members.destroy', $member) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus anggota ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:2rem;">
        <div>
            @if($member->hasPhoto())
            <div style="text-align:center;margin-bottom:1.5rem;">
                <img src="{{ route('storage.local', ['path' => $member->foto_path]) }}" alt="Foto" style="width:150px;height:200px;object-fit:cover;border-radius:0.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
            </div>
            @endif

            <table style="width:100%;">
                <tr>
                    <td style="padding:0.5rem 0;color:#64748b;width:40%;">NIK</td>
                    <td style="padding:0.5rem 0;font-weight:600;">{{ $member->nik }}</td>
                </tr>
                <tr>
                    <td style="padding:0.5rem 0;color:#64748b;">Nama</td>
                    <td style="padding:0.5rem 0;">{{ $member->nama }}</td>
                </tr>
                <tr>
                    <td style="padding:0.5rem 0;color:#64748b;">Status</td>
                    <td style="padding:0.5rem 0;"><span class="badge badge-{{ $member->status }}">{{ ucfirst($member->status) }}</span></td>
                </tr>
            </table>
        </div>

        <div>
            <table style="width:100%;">
                <tr>
                    <td style="padding:0.5rem 0;color:#64748b;">Tempat, Tgl Lahir</td>
                    <td style="padding:0.5rem 0;">{{ $member->tempat_lahir }}, {{ $member->tanggal_lahir->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:0.5rem 0;color:#64748b;">Jenis Kelamin</td>
                    <td style="padding:0.5rem 0;">{{ $member->jenis_kelamin }}</td>
                </tr>
                <tr>
                    <td style="padding:0.5rem 0;color:#64748b;">Agama</td>
                    <td style="padding:0.5rem 0;">{{ $member->agama }}</td>
                </tr>
                <tr>
                    <td style="padding:0.5rem 0;color:#64748b;">Perusahaan</td>
                    <td style="padding:0.5rem 0;">{{ $member->company?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding:0.5rem 0;color:#64748b;">Alamat</td>
                    <td style="padding:0.5rem 0;">{{ $member->alamat }}</td>
                </tr>
                <tr>
                    <td style="padding:0.5rem 0;color:#64748b;">Kecamatan</td>
                    <td style="padding:0.5rem 0;">{{ $member->district?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding:0.5rem 0;color:#64748b;">Kabupaten/Kota</td>
                    <td style="padding:0.5rem 0;">{{ $member->regency?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding:0.5rem 0;color:#64748b;">Provinsi</td>
                    <td style="padding:0.5rem 0;">{{ $member->province?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding:0.5rem 0;color:#64748b;">Tanggal Pembuatan</td>
                    <td style="padding:0.5rem 0;">{{ $member->tanggal_pembuatan->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:0.5rem 0;color:#64748b;">Berlaku Hingga</td>
                    <td style="padding:0.5rem 0;">{{ $member->berlaku_hingga->format('d/m/Y') }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

@if($member->printBatches->count() > 0)
<div class="card">
    <div class="card-header">
        <h3>Riwayat Cetak</h3>
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
                <td>{{ $batch->batch_number }} ({{ ucfirst($batch->type) }})</td>
                <td>{{ $batch->tanggal_cetak->format('d/m/Y') }}</td>
                <td>{{ $batch->printer?->name ?? '-' }}</td>
                <td><a href="{{ route('print.show', $batch) }}" class="btn btn-sm btn-outline">Detail</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
