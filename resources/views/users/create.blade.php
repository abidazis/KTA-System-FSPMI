@extends('layouts.app')

@section('title', 'Tambah User')
@section('header', 'Tambah User Baru')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Form User</h3>
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline">Kembali</a>
    </div>

    <form action="{{ route('users.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>
        </div>
        <div class="form-group">
            <label for="roles">Role</label>
            <select id="roles" name="roles[]" multiple required>
                @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ in_array($role->name, old('roles', [])) ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                </option>
                @endforeach
            </select>
            <small>Tekan Ctrl/Cmd untuk memilih multiple</small>
            @error('roles') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div style="display:flex;gap:1rem;">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
@endsection
