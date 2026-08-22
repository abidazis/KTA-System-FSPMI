@extends('layouts.app')

@section('title', 'Edit User')
@section('header', 'Edit User')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Form Edit User</h3>
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline">Kembali</a>
    </div>

    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="password">Password Baru</label>
                <input type="password" id="password" name="password">
                <small>Kosongkan jika tidak ingin mengubah password</small>
            </div>
            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation">
            </div>
        </div>
        <div class="form-group">
            <label for="roles">Role</label>
            <select id="roles" name="roles[]" multiple required>
                @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                </option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;gap:1rem;">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
@endsection
