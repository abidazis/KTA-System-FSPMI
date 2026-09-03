@extends('layouts.app')

@section('title', 'Manajemen User')
@section('header', 'Manajemen User')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Daftar User</h3>
        <a href="{{ route('users.create') }}" class="btn btn-primary"> Tambah User</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Tanggal Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td><strong style="color:var(--primary);">{{ $user->name }}</strong></td>
                <td>{{ $user->email }}</td>
                <td>
                    @foreach($user->roles as $role)
                    <span class="badge badge-{{ $role->name === 'super-admin' ? 'expired' : 'ready' }}">{{ ucfirst(str_replace('-', ' ', $role->name)) }}</span>
                    @endforeach
                </td>
                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                <td>
                    <div class="actions">
                        <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline"></a>
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary"></a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"></button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="empty-state">
                    <p>Belum ada user.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1.5rem;">
        {{ $users->links() }}
    </div>
</div>
@endsection
