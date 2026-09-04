@extends('layouts.app')

@section('title', 'Data Pengurus')
@section('header', 'Data Pengurus & Periode Kepengurusan')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Data Pengurus & Periode Kepengurusan</h3>
        <button onclick="document.getElementById('add-period-form').style.display='block'" class="btn btn-primary">+ Tambah Periode</button>
    </div>

    {{-- Add Period Form --}}
    <div id="add-period-form" style="display:none;margin-bottom:2rem;padding:1.5rem;background:var(--gray-50);border-radius:0.5rem;border:1px solid var(--gray-200);">
        <h4 style="margin-bottom:1rem;">Tambah Periode Baru</h4>
        <form action="{{ route('management.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="nama_periode">Nama Periode</label>
                    <input type="text" id="nama_periode" name="nama_periode" placeholder="Contoh: 2026 - 2031" required>
                </div>
                <div class="form-group">
                    <label for="tanggal_mulai">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" required>
                </div>
                <div class="form-group">
                    <label for="tanggal_selesai">Tanggal Selesai</label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" required>
                </div>
            </div>
            <div class="form-group">
                <label for="stempel">Stempel Organisasi (PNG)</label>
                <input type="file" id="stempel" name="stempel" accept="image/png" style="padding:0.5rem;">
                <small style="color:var(--gray-500);">Format: PNG. Maks: 512KB</small>
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;">
                    <input type="checkbox" name="set_active" value="1" style="width:1.125rem;height:1.125rem;">
                    Jadikan periode aktif
                </label>
            </div>
            <div style="display:flex;gap:0.75rem;">
                <button type="submit" class="btn btn-primary"> Simpan</button>
                <button type="button" onclick="document.getElementById('add-period-form').style.display='none'" class="btn btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    Batal
                </button>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Periode</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Pengurus</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($periods as $period)
            <tr>
                <td><strong style="color:var(--primary);">{{ $period->nama_periode }}</strong></td>
                <td>{{ $period->tanggal_mulai->format('d/m/Y') }} - {{ $period->tanggal_selesai->format('d/m/Y') }}</td>
                <td>
                    @if($period->status === 'active')
                    <span class="badge badge-active">✓ Aktif</span>
                    @else
                    <span class="badge badge-inactive">Tidak Aktif</span>
                    @endif
                </td>
                <td>
                    <div style="display:flex;gap:0.25rem;flex-wrap:wrap;">
                        @foreach($period->officials->take(4) as $official)
                        <span class="badge badge-{{ $official->status === 'active' ? 'ready' : 'inactive' }}" style="font-size:0.7rem;">{{ $official->jabatan }}</span>
                        @endforeach
                        @if($period->officials->count() > 4)
                        <span class="badge badge-draft">+{{ $period->officials->count() - 4 }}</span>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="actions">
                        <a href="{{ route('management.show', $period) }}" class="btn btn-sm btn-primary">Detail</a>
                        @if($period->status !== 'active')
                        <form action="{{ route('management.set-active', $period) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">✓ Aktifkan</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="empty-state">
                    <p>Belum ada periode kepengurusan.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1.5rem;">
        {{ $periods->links() }}
    </div>
</div>
@endsection
