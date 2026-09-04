@extends('layouts.app')

@section('title', 'Detail User')
@section('header', 'Detail User')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Data User</h3>
        <div style="display:flex;gap:0.5rem;">
            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                Edit
            </a>
        </div>
    </div>

    <table style="width:100%;">
        <tr>
            <td style="padding:0.75rem;color:#64748b;width:30%;">Nama</td>
            <td style="padding:0.75rem;font-weight:600;">{{ $user->name }}</td>
        </tr>
        <tr>
            <td style="padding:0.75rem;color:#64748b;">Email</td>
            <td style="padding:0.75rem;">{{ $user->email }}</td>
        </tr>
        <tr>
            <td style="padding:0.75rem;color:#64748b;">Role</td>
            <td style="padding:0.75rem;">
                @foreach($user->roles as $role)
                <span class="badge badge-{{ $role->name === 'super-admin' ? 'danger' : 'ready' }}">{{ ucfirst(str_replace('-', ' ', $role->name)) }}</span>
                @endforeach
            </td>
        </tr>
        <tr>
            <td style="padding:0.75rem;color:#64748b;">Tanggal Dibuat</td>
            <td style="padding:0.75rem;">{{ $user->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td style="padding:0.75rem;color:#64748b;">Terakhir Update</td>
            <td style="padding:0.75rem;">{{ $user->updated_at->format('d/m/Y H:i') }}</td>
        </tr>
    </table>
</div>
@endsection
