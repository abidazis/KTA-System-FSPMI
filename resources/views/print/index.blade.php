@extends('layouts.app')

@section('title', 'Cetak KTA')
@section('header', 'Cetak KTA')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Daftar Batch Cetak</h3>
        <a href="{{ route('print.create') }}" class="btn btn-sm btn-primary">+ Buat Batch Baru</a>
    </div>

    <form method="GET" class="filters">
        <div class="form-group">
            <input type="text" name="search" placeholder="Cari Batch atau Anggota" value="{{ request('search') }}">
        </div>
        <div class="form-group">
            <select name="type">
                <option value="">Semua Sisi</option>
                <option value="front" {{ request('type') == 'front' ? 'selected' : '' }}>Depan</option>
                <option value="back" {{ request('type') == 'back' ? 'selected' : '' }}>Belakang</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Batch</th>
                <th>Tanggal</th>
                <th>Periode</th>
                <th>Jumlah</th>
                <th>Sisi</th>
                <th>Operator</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batches as $batch)
            <tr>
                <td><strong>{{ $batch->batch_number }}</strong></td>
                <td>{{ $batch->tanggal_cetak->format('d/m/Y') }}</td>
                <td>{{ $batch->period?->nama_periode ?? '-' }}</td>
                <td>{{ $batch->jumlah }} KTA</td>
                <td><span class="badge badge-{{ $batch->type == 'front' ? 'ready' : 'generated' }}">{{ $batch->type == 'front' ? 'Depan' : 'Belakang' }}</span></td>
                <td>{{ $batch->printer?->name ?? '-' }}</td>
                <td>
                    <div class="actions">
                        <a href="{{ route('print.show', $batch) }}" class="btn btn-sm btn-outline">Detail</a>
                        <a href="{{ route('print.pdf', $batch) }}" class="btn btn-sm btn-success">PDF</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty-state">
                    <p>Belum ada batch cetak.</p>
                    <a href="{{ route('print.create') }}" class="btn btn-primary" style="margin-top:1rem;">Buat Batch Baru</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{ $batches->withQueryString()->links() }}
</div>
@endsection
