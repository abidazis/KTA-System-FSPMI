@extends('layouts.app')

@section('title', 'Detail Batch Cetak')
@section('header', 'Batch ' . $batch->batch_number)

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Detail Batch</h3>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">

            <a href="{{ route('print.pdf', ['batch' => $batch]) }}" class="btn btn-sm btn-success">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download PDF KTA
            </a>

            <form action="{{ route('print.destroy', $batch) }}" method="POST" style="display:inline;" id="delete-batch-form">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-sm btn-danger" onclick="FSPMIModal.confirmDelete('Yakin ingin menghapus batch ini?').then(function(ok){ if(ok){document.getElementById('delete-batch-form').submit();} });">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    Hapus
                </button>
            </form>

        </div>
    </div>

    <div style="background:#dbeafe;padding:1rem;border-radius:0.5rem;margin-bottom:1.5rem;">

        <strong>Petunjuk Cetak:</strong>

        <ol style="margin:0.5rem 0 0 1.5rem;">

            <li>
                PDF menggunakan format <strong>A4 Landscape</strong> dengan <strong>4 KTA per halaman</strong> (2 atas + 2 bawah).
            </li>

            <li>
                Baris atas = <strong>DEPAN (foto)</strong>, Baris bawah = <strong>BELAKANG (data)</strong>.
            </li>

            <li>
                Cetak dengan ukuran <strong>Actual Size / 100%</strong>.
            </li>

            <li>
                Cetak baris atas dulu, lalu balik kertas untuk baris bawah.
            </li>

        </ol>

    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem;">
        <div style="background:#f1f5f9;padding:1rem;border-radius:0.5rem;">
            <div style="font-size:0.8rem;color:#64748b;">Batch Number</div>
            <div style="font-size:1.25rem;font-weight:700;">{{ $batch->batch_number }}</div>
        </div>
        <div style="background:#f1f5f9;padding:1rem;border-radius:0.5rem;">
            <div style="font-size:0.8rem;color:#64748b;">Tanggal Cetak</div>
            <div style="font-size:1.25rem;font-weight:700;">{{ $batch->tanggal_cetak->format('d/m/Y') }}</div>
        </div>
        <div style="background:#f1f5f9;padding:1rem;border-radius:0.5rem;">
            <div style="font-size:0.8rem;color:#64748b;">Jumlah KTA</div>
            <div style="font-size:1.25rem;font-weight:700;">{{ $batch->jumlah }}</div>
        </div>
        <div style="background:#f1f5f9;padding:1rem;border-radius:0.5rem;">
            <div style="font-size:0.8rem;color:#64748b;">Sisi</div>
            <div style="font-size:1.25rem;font-weight:700;"><span class="badge badge-{{ $batch->type == 'front' ? 'ready' : 'generated' }}">{{ $batch->type == 'front' ? 'DEPAN' : 'BELAKANG' }}</span></div>
        </div>
    </div>

    <h4 style="margin-bottom:1rem;">Daftar Anggota</h4>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Anggota</th>
                <th>Nama</th>
                <th>Domisili</th>
            </tr>
        </thead>
        <tbody>
            @foreach($members as $kta)
            <tr>
                <td>{{ $kta['member']->pivot->position ?? $loop->iteration }}</td>
                <td><a href="{{ route('members.show', $kta['member']) }}">{{ $kta['member']->nik }}</a></td>
                <td>{{ $kta['member']->nama }}</td>
                <td>
                    <div>{{ $kta['member']->district?->name ?? '-' }}</div>
                    <small style="color:var(--gray-500);">{{ $kta['member']->regency?->name ?? '' }}</small>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
