@extends('layouts.app')

@section('title', 'Detail Periode')
@section('header', 'Periode: ' . $period->nama_periode)

@section('content')
<script>
function showEditForm(id) {
    document.getElementById('edit-form-' + id).style.display = 'table-row';
}
function hideEditForm(id) {
    document.getElementById('edit-form-' + id).style.display = 'none';
}
</script>
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

{{-- STEMPEL ORGANISASI --}}
<div class="card">
    <div class="card-header">
        <h3>Stempel Organisasi</h3>
    </div>
    <div style="padding:1.5rem;">
        @if($period->stempel_path)
        <div style="margin-bottom:1rem;">
            <img src="{{ route('storage.local', ['path' => $period->stempel_path]) }}"
                 style="height:100px;border:1px solid #e2e8f0;border-radius:0.5rem;padding:0.5rem;background:#f8fafc;">
        </div>
        <form action="{{ route('management.stempel', $period) }}" method="POST" enctype="multipart/form-data" style="display:flex;align-items:center;gap:1rem;">
            @csrf
            @method('PUT')
            <input type="file" name="stempel" accept="image/png" style="flex:1;">
            <button type="submit" class="btn btn-sm btn-primary">Update Stempel</button>
        </form>
        <small style="color:#64748b;display:block;margin-top:0.5rem;">Format: PNG. Maks: 512KB. Upload ulang untuk mengganti.</small>
        @else
        <p style="color:#64748b;margin-bottom:1rem;">Belum ada stempel organisasi.</p>
        <form action="{{ route('management.stempel', $period) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div style="display:flex;gap:1rem;align-items:end;">
                <div class="form-group" style="flex:1;">
                    <label for="stempel">Upload Stempel (PNG)</label>
                    <input type="file" id="stempel" name="stempel" accept="image/png" required>
                    <small>Format: PNG. Maks: 512KB</small>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Stempel</button>
            </div>
        </form>
        @endif
    </div>
</div>

{{-- TANDA TANGAN KETUA UMUM & SEKRETARIS UMUM --}}
<div class="card">
    <div class="card-header">
        <h3>Tanda Tangan resmi</h3>
        <small style="color:#64748b;font-weight:normal;">Digunakan untuk KTA anggota</small>
    </div>
    <div style="padding:1.5rem;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;">
            {{-- KETUA UMUM --}}
            <div style="border:1px solid #e2e8f0;border-radius:0.5rem;padding:1rem;">
                <h4 style="margin-bottom:1rem;">Ketua Umum</h4>
                @php
                    $ketua = $period->officials->first(function($o) {
                        return stripos($o->jabatan, 'ketua') !== false && stripos($o->jabatan, 'umum') !== false;
                    });
                @endphp
                @if($ketua)
                    @if($ketua->signature_path)
                    <div style="margin-bottom:1rem;">
                        <img src="{{ route('storage.local', ['path' => $ketua->signature_path]) }}"
                             style="height:60px;border:1px solid #e2e8f0;border-radius:0.25rem;background:#f8fafc;padding:0.25rem;">
                    </div>
                    @else
                    <p style="color:#f59e0b;margin-bottom:1rem;">⚠️ Belum ada tanda tangan</p>
                    @endif
                    <p style="color:#64748b;margin-bottom:0.5rem;">{{ $ketua->nama }}</p>
                    <form action="{{ route('management.officials.signature', [$period, $ketua]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div style="display:flex;gap:0.5rem;align-items:center;">
                            <input type="file" name="signature" accept="image/png" style="flex:1;">
                            <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                        </div>
                        <small style="color:#64748b;">Format: PNG. Maks: 512KB</small>
                    </form>
                @else
                    <p style="color:#ef4444;margin-bottom:1rem;">⚠️ Belum ada data Ketua Umum.</p>
                    <p style="color:#64748b;">Tambahkan di daftar pengurus di bawah.</p>
                @endif
            </div>

            {{-- SEKRETARIS UMUM --}}
            <div style="border:1px solid #e2e8f0;border-radius:0.5rem;padding:1rem;">
                <h4 style="margin-bottom:1rem;">Sekretaris Umum</h4>
                @php
                    $sekretaris = $period->officials->first(function($o) {
                        return stripos($o->jabatan, 'sekretaris') !== false && stripos($o->jabatan, 'umum') !== false;
                    });
                @endphp
                @if($sekretaris)
                    @if($sekretaris->signature_path)
                    <div style="margin-bottom:1rem;">
                        <img src="{{ route('storage.local', ['path' => $sekretaris->signature_path]) }}"
                             style="height:60px;border:1px solid #e2e8f0;border-radius:0.25rem;background:#f8fafc;padding:0.25rem;">
                    </div>
                    @else
                    <p style="color:#f59e0b;margin-bottom:1rem;">⚠️ Belum ada tanda tangan</p>
                    @endif
                    <p style="color:#64748b;margin-bottom:0.5rem;">{{ $sekretaris->nama }}</p>
                    <form action="{{ route('management.officials.signature', [$period, $sekretaris]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div style="display:flex;gap:0.5rem;align-items:center;">
                            <input type="file" name="signature" accept="image/png" style="flex:1;">
                            <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                        </div>
                        <small style="color:#64748b;">Format: PNG. Maks: 512KB</small>
                    </form>
                @else
                    <p style="color:#ef4444;margin-bottom:1rem;">⚠️ Belum ada data Sekretaris Umum.</p>
                    <p style="color:#64748b;">Tambahkan di daftar pengurus di bawah.</p>
                @endif
            </div>
        </div>
    </div>
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
                        <button type="button" onclick="showEditForm({{ $official->id }})" class="btn btn-sm btn-outline">Edit</button>
                        <form action="{{ route('management.officials.destroy', [$period, $official]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            {{-- FORM EDIT PENGURUS --}}
            <tr id="edit-form-{{ $official->id }}" style="display:none; background:#f0f9ff;">
                <td colspan="5" style="padding:1rem;">
                    <form action="{{ route('management.officials.update', [$period, $official]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-row">
                            <div class="form-group">
                                <label>Jabatan</label>
                                <input type="text" name="jabatan" value="{{ $official->jabatan }}" required>
                            </div>
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama" value="{{ $official->nama }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Tanda Tangan (PNG)</label>
                            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:0.5rem;">
                                @if($official->signature_path)
                                <img src="{{ route('storage.local', ['path' => $official->signature_path]) }}" style="height:40px;border:1px solid #e2e8f0;">
                                @endif
                                <input type="file" name="signature" accept="image/png">
                            </div>
                            <small style="color:#64748b;">Format: PNG. Maks: 512KB. Kosongkan jika tidak diubah.</small>
                        </div>
                        <div style="display:flex;gap:1rem;">
                            <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                            <button type="button" onclick="hideEditForm({{ $official->id }})" class="btn btn-sm btn-outline">Batal</button>
                        </div>
                    </form>
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
