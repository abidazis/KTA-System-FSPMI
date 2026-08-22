@extends('layouts.app')

@section('title', 'Detail Periode')
@section('header', 'Periode: ' . $period->nama_periode)

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Detail Periode</h3>
        <div style="display:flex;gap:0.5rem;">
            <a href="{{ route('management.index') }}" class="btn btn-sm btn-outline">Kembali</a>
            <form action="{{ route('management.destroy', $period) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus periode ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
        </div>
    </div>

    <table style="margin-bottom:2rem;">
        <tr>
            <td style="padding:0.5rem;color:#64748b;width:30%;">Nama Periode</td>
            <td style="padding:0.5rem;font-weight:600;">{{ $period->nama_periode }}</td>
        </tr>
        <tr>
            <td style="padding:0.5rem;color:#64748b;">Tanggal</td>
            <td style="padding:0.5rem;">{{ $period->tanggal_mulai->format('d/m/Y') }} - {{ $period->tanggal_selesai->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td style="padding:0.5rem;color:#64748b;">Status</td>
            <td style="padding:0.5rem;">
                @if($period->status === 'active')
                <span class="badge badge-active">Aktif</span>
                @else
                <span class="badge badge-inactive">Tidak Aktif</span>
                @endif
            </td>
        </tr>
    </table>
</div>

<div class="card">
    <div class="card-header">
        <h3>Daftar Pengurus</h3>
        <button onclick="document.getElementById('add-official-form').style.display='block'" class="btn btn-sm btn-primary">+ Tambah Pengurus</button>
    </div>

    <div id="add-official-form" style="display:none;margin-bottom:2rem;padding:1.5rem;background:#f8fafc;border-radius:0.5rem;">
        <h4 style="margin-bottom:1rem;">Tambah Pengurus Baru</h4>
        <form action="{{ route('management.officials.store', $period) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="jabatan">Jabatan</label>
                    <input type="text" id="jabatan" name="jabatan" placeholder="Contoh: Ketua Umum" required>
                </div>
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" placeholder="Nama Lengkap" required>
                </div>
            </div>
            <div class="form-group">
                <label for="signature">Tanda Tangan (PNG)</label>
                <input type="file" id="signature" name="signature" accept="image/png">
                <small>Format: PNG. Maks: 512KB</small>
            </div>
            <div style="display:flex;gap:1rem;">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" onclick="document.getElementById('add-official-form').style.display='none'" class="btn btn-outline">Batal</button>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Jabatan</th>
                <th>Nama</th>
                <th>Tanda Tangan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($period->officials as $official)
            <tr>
                <td><strong>{{ $official->jabatan }}</strong></td>
                <td>{{ $official->nama }}</td>
                <td>
                    @if($official->signature_path)
                    <img src="{{ route('storage.local', ['path' => $official->signature_path]) }}" style="height:30px;">
                    @else
                    <span style="color:#94a3b8;">Belum ada</span>
                    @endif
                </td>
                <td>
                    @if($official->status === 'active')
                    <span class="badge badge-active">Aktif</span>
                    @else
                    <span class="badge badge-inactive">Tidak Aktif</span>
                    @endif
                </td>
                <td>
                    <div class="actions">
                        <form action="{{ route('management.officials.destroy', [$period, $official]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="empty-state">Belum ada pengurus untuk periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
