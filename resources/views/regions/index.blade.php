@extends('layouts.app')

@section('title', 'Data Wilayah')
@section('header', 'Data Wilayah')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Daftar Wilayah Indonesia</h3>
        <div style="display:flex;gap:0.75rem;">
            <form action="{{ route('regions.import') }}" method="POST" enctype="multipart/form-data" style="display:inline;">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls" style="display:none;" id="region-file" onchange="this.form.submit()">
                <label for="region-file" class="btn btn-outline" style="cursor:pointer;"> Import Excel</label>
            </form>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Provinsi</th>
                <th>Kabupaten/Kota</th>
                <th>Kecamatan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($provinces as $province)
            <tr>
                <td><strong style="color:var(--primary);">{{ $province->name }}</strong></td>
                <td><span class="badge badge-ready">{{ $province->regencies->count() }}</span></td>
                <td><span class="badge badge-generated">{{ $province->regencies->sum(fn($r) => $r->districts->count()) }}</span></td>
                <td>
                    <a href="{{ route('regions.show', $province) }}" class="btn btn-sm btn-primary"> Detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="empty-state">
                    <p>Belum ada data wilayah.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1.5rem;">
        {{ $provinces->links() }}
    </div>
</div>
@endsection
