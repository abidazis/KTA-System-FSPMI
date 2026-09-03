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

{{-- Info Card --}}
<div class="card">
    <div class="card-header">
        <h3>Informasi Periode</h3>
        <div style="display:flex;gap:0.75rem;">
            <a href="{{ route('management.index') }}" class="btn btn-outline">Kembali</a>
            <form action="{{ route('management.destroy', $period) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus periode ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.25rem;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        Hapus
                    </button>
            </form>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;padding:0.5rem;">
        <div style="padding:1rem;background:var(--gray-50);border-radius:0.5rem;border-left:4px solid var(--primary);">
            <div style="font-size:0.85rem;color:var(--gray-500);margin-bottom:0.25rem;">Nama Periode</div>
            <div style="font-size:1.1rem;font-weight:600;color:var(--gray-800);">{{ $period->nama_periode }}</div>
        </div>
        <div style="padding:1rem;background:var(--gray-50);border-radius:0.5rem;border-left:4px solid var(--info);">
            <div style="font-size:0.85rem;color:var(--gray-500);margin-bottom:0.25rem;">Tanggal</div>
            <div style="font-size:1.1rem;font-weight:600;color:var(--gray-800);">{{ $period->tanggal_mulai->format('d/m/Y') }} - {{ $period->tanggal_selesai->format('d/m/Y') }}</div>
        </div>
        <div style="padding:1rem;background:var(--gray-50);border-radius:0.5rem;border-left:4px solid var(--success);">
            <div style="font-size:0.85rem;color:var(--gray-500);margin-bottom:0.25rem;">Status</div>
            <div style="font-size:1.1rem;font-weight:600;">
                @if($period->status === 'active')
                <span class="badge badge-active">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:0.25rem;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Aktif
                        </span>
                @else
                <span class="badge badge-inactive">Tidak Aktif</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Stempel & Tanda Tangan Side by Side --}}
<div style="display:grid;grid-template-columns:350px 1fr;gap:1.5rem;">
    {{-- STEMPEL --}}
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.5rem;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                Stempel Organisasi
            </h3>
        </div>
        <div style="padding:1rem;">
            @if($period->stempel_path)
            <div style="text-align:center;margin-bottom:1.5rem;">
                <img src="{{ route('storage.local', ['path' => $period->stempel_path]) }}"
                     style="max-height:120px;border:2px solid var(--gray-200);border-radius:0.5rem;padding:0.5rem;background:var(--gray-50);">
            </div>
            <form action="{{ route('management.stempel', $period) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div style="display:flex;gap:0.75rem;align-items:center;">
                    <input type="file" name="stempel" accept="image/png" style="flex:1;padding:0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.25rem;"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                        Update
                    </button>
                </div>
                <small style="color:var(--gray-500);display:block;margin-top:0.5rem;">Format: PNG. Maks: 512KB</small>
            </form>
            @else
            <div style="text-align:center;padding:2rem;color:var(--gray-500);">
                <p style="margin-bottom:1rem;">Belum ada stempel organisasi.</p>
                <form action="{{ route('management.stempel', $period) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="file" name="stempel" accept="image/png" required style="margin-bottom:1rem;">
                    <br>
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.25rem;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        Upload Stempel
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    {{-- TANDA TANGAN --}}
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.5rem;"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                Tanda Tangan Resmi
            </h3>
            <small style="color:var(--gray-500);">Digunakan untuk KTA anggota</small>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;padding:1rem;">
            {{-- KETUA UMUM --}}
            <div style="border:2px solid var(--gray-200);border-radius:0.5rem;padding:1.25rem;text-align:center;">
                <h4 style="margin-bottom:1rem;color:var(--primary);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.5rem;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        Ketua Umum
                    </h4>
                @php
                    $ketua = $period->officials->first(function($o) {
                        return stripos($o->jabatan, 'ketua') !== false && stripos($o->jabatan, 'umum') !== false;
                    });
                @endphp
                @if($ketua)
                    @if($ketua->signature_path)
                    <div style="margin-bottom:1rem;">
                        <img src="{{ route('storage.local', ['path' => $ketua->signature_path]) }}"
                             style="height:70px;border:1px solid var(--gray-200);border-radius:0.25rem;background:var(--gray-50);padding:0.25rem;">
                    </div>
                    @else
                    <div style="padding:1rem;background:var(--warning-light);color:var(--warning);border-radius:0.5rem;margin-bottom:1rem;">
                         <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.25rem;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                         Belum ada tanda tangan
                    </div>
                    @endif
                    <p style="font-weight:600;color:var(--gray-700);margin-bottom:0.75rem;">{{ $ketua->nama }}</p>
                    <form action="{{ route('management.officials.signature', [$period, $ketua]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="file" name="signature" accept="image/png" style="margin-bottom:0.75rem;">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.25rem;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            Upload
                        </button>
                    </form>
                @else
                    <div style="padding:1rem;background:var(--danger-light);color:var(--danger);border-radius:0.5rem;margin-bottom:1rem;">
                         <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.25rem;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                         Belum ada data
                    </div>
                    <p style="color:var(--gray-500);font-size:0.9rem;">Tambahkan di daftar pengurus.</p>
                @endif
            </div>

            {{-- SEKRETARIS UMUM --}}
            <div style="border:2px solid var(--gray-200);border-radius:0.5rem;padding:1.25rem;text-align:center;">
                <h4 style="margin-bottom:1rem;color:var(--primary);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.5rem;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        Sekretaris Umum
                    </h4>
                @php
                    $sekretaris = $period->officials->first(function($o) {
                        return stripos($o->jabatan, 'sekretaris') !== false && stripos($o->jabatan, 'umum') !== false;
                    });
                @endphp
                @if($sekretaris)
                    @if($sekretaris->signature_path)
                    <div style="margin-bottom:1rem;">
                        <img src="{{ route('storage.local', ['path' => $sekretaris->signature_path]) }}"
                             style="height:70px;border:1px solid var(--gray-200);border-radius:0.25rem;background:var(--gray-50);padding:0.25rem;">
                    </div>
                    @else
                    <div style="padding:1rem;background:var(--warning-light);color:var(--warning);border-radius:0.5rem;margin-bottom:1rem;">
                         <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.25rem;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                         Belum ada tanda tangan
                    </div>
                    @endif
                    <p style="font-weight:600;color:var(--gray-700);margin-bottom:0.75rem;">{{ $sekretaris->nama }}</p>
                    <form action="{{ route('management.officials.signature', [$period, $sekretaris]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="file" name="signature" accept="image/png" style="margin-bottom:0.75rem;">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.25rem;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            Upload
                        </button>
                    </form>
                @else
                    <div style="padding:1rem;background:var(--danger-light);color:var(--danger);border-radius:0.5rem;margin-bottom:1rem;">
                         <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.25rem;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                         Belum ada data Sekretaris Umum
                    </div>
                    <p style="color:var(--gray-500);font-size:0.9rem;">Tambahkan di daftar pengurus.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Daftar Pengurus --}}
<div class="card">
    <div class="card-header">
        <h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.5rem;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            Daftar Pengurus
        </h3>
        <button onclick="document.getElementById('add-official-form').style.display='block'" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.25rem;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah
                </button>
    </div>

    <div id="add-official-form" style="display:none;margin-bottom:1.5rem;padding:1.5rem;background:var(--gray-50);border-radius:0.5rem;border:1px solid var(--gray-200);">
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
                <small style="color:var(--gray-500);">Format: PNG. Maks: 512KB</small>
            </div>
            <div style="display:flex;gap:0.75rem;">
                <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.25rem;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        Simpan
                    </button>
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
                <td><strong style="color:var(--primary);">{{ $official->jabatan }}</strong></td>
                <td>{{ $official->nama }}</td>
                <td>
                    @if($official->signature_path)
                    <img src="{{ route('storage.local', ['path' => $official->signature_path]) }}" style="height:35px;border:1px solid var(--gray-200);border-radius:0.25rem;">
                    @else
                    <span style="color:var(--gray-400);">-</span>
                    @endif
                </td>
                <td>
                    @if($official->status === 'active')
                    <span class="badge badge-active">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:0.25rem;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Aktif
                        </span>
                    @else
                    <span class="badge badge-inactive">Tidak Aktif</span>
                    @endif
                </td>
                <td>
                    <div class="actions">
                        <button type="button" onclick="showEditForm({{ $official->id }})" class="btn btn-sm btn-outline">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.25rem;"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                            Edit
                        </button>
                        <form action="{{ route('management.officials.destroy', [$period, $official]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.25rem;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                        </form>
                    </div>
                </td>
            </tr>
            {{-- FORM EDIT --}}
            <tr id="edit-form-{{ $official->id }}" style="display:none;background:var(--info-light);">
                <td colspan="5" style="padding:1.5rem;">
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
                                <img src="{{ route('storage.local', ['path' => $official->signature_path]) }}" style="height:45px;border:1px solid var(--gray-200);border-radius:0.25rem;">
                                @endif
                                <input type="file" name="signature" accept="image/png">
                            </div>
                            <small style="color:var(--gray-500);">Format: PNG. Kosongkan jika tidak diubah.</small>
                        </div>
                        <div style="display:flex;gap:0.75rem;">
                            <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.25rem;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        Simpan
                    </button>
                            <button type="button" onclick="hideEditForm({{ $official->id }})" class="btn btn-outline">Batal</button>
                        </div>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="empty-state">
                    <p>Belum ada pengurus untuk periode ini.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
