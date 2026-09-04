@extends('layouts.app')

@section('title', 'Master Perusahaan')
@section('header', 'Master Perusahaan')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Daftar Perusahaan</h3>
        <a href="{{ route('settings.companies.create') }}" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Tambah Perusahaan
        </a>
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
                        <a href="{{ route('settings.companies.edit', $company) }}" class="btn btn-sm btn-primary">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                            Edit
                        </a>
                        <form action="{{ route('settings.companies.destroy', $company) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus perusahaan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty-state">
                    <p>Belum ada data perusahaan.</p>
                    <a href="{{ route('settings.companies.create') }}" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Tambah Perusahaan
                    </a>
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
