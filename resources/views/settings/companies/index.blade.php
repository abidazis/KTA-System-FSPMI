@extends('layouts.app')

@section('title', 'Master Perusahaan')
@section('header', 'Master Perusahaan')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Daftar Perusahaan</h3>
        <a href="{{ route('settings.companies.create') }}" class="btn btn-primary"> Tambah Perusahaan</a>
    </div>

    <table>
        <thead>
            <tr>
                <th width="50">No</th>
                <th>Nama Perusahaan</th>
                <th>Alamat</th>
                <th>Kota</th>
                <th>Telepon</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($companies as $company)
            <tr>
                <td>{{ $loop->iteration + ($companies->currentPage() - 1) * $companies->perPage() }}</td>
                <td><strong style="color:var(--primary);">{{ $company->name }}</strong></td>
                <td>{{ $company->address ?? '-' }}</td>
                <td>{{ $company->city ?? '-' }}</td>
                <td>{{ $company->phone ?? '-' }}</td>
                <td>
                    @if($company->is_active)
                        <span class="badge badge-active">✓ Aktif</span>
                    @else
                        <span class="badge badge-inactive">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <div class="actions">
                        <a href="{{ route('settings.companies.edit', $company) }}" class="btn btn-sm btn-primary"> Edit</a>
                        <form action="{{ route('settings.companies.destroy', $company) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus perusahaan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty-state">
                    <p>Belum ada data perusahaan.</p>
                    <a href="{{ route('settings.companies.create') }}" class="btn btn-primary"> Tambah Perusahaan</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1.5rem;">
        {{ $companies->links() }}
    </div>
</div>
@endsection
