@extends('layouts.app')

@section('title', 'Data Pengurus')
@section('header', 'Data Pengurus & Periode Kepengurusan')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Daftar Periode Kepengurusan</h3>
        <button onclick="document.getElementById('add-period-form').style.display='block'" class="btn btn-sm btn-primary">+ Tambah Periode</button>
    </div>

    <div id="add-period-form" style="display:none;margin-bottom:2rem;padding:1.5rem;background:#f8fafc;border-radius:0.5rem;">
        <h4 style="margin-bottom:1rem;">Tambah Periode Baru</h4>
        <form action="{{ route('management.store') }}" method="POST">
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
                <label style="display:flex;align-items:center;gap:0.5rem;">
                    <input type="checkbox" name="set_active" value="1"> Jadikan periode aktif
                </label>
            </div>
            <div style="display:flex;gap:1rem;">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" onclick="document.getElementById('add-period-form').style.display='none'" class="btn btn-outline">Batal</button>
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
                <td><strong>{{ $period->nama_periode }}</strong></td>
                <td>{{ $period->tanggal_mulai->format('d/m/Y') }} - {{ $period->tanggal_selesai->format('d/m/Y') }}</td>
                <td>
                    @if($period->status === 'active')
                    <span class="badge badge-active">Aktif</span>
                    @else
                    <span class="badge badge-inactive">Tidak Aktif</span>
                    @endif
                </td>
                <td>
                    @foreach($period->officials->take(3) as $official)
                    <span class="badge badge-{{ $official->status === 'active' ? 'ready' : 'inactive' }}">{{ $official->jabatan }}</span>
                    @endforeach
                    @if($period->officials->count() > 3)
                    <span class="badge badge-draft">+{{ $period->officials->count() - 3 }}</span>
                    @endif
                </td>
                <td>
                    <div class="actions">
                        <a href="{{ route('management.show', $period) }}" class="btn btn-sm btn-outline">Detail</a>
                        @if($period->status !== 'active')
                        <form action="{{ route('management.set-active', $period) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">Aktifkan</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="empty-state">Belum ada periode kepengurusan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{ $periods->links() }}
</div>
@endsection
