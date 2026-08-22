@extends('layouts.app')

@section('title', 'Ubah Password')
@section('header', 'Ubah Password')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Ubah Password</h3>
    </div>

    @if(session('status') === 'password-updated')
    <div class="alert alert-success">Password berhasil diubah.</div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="current_password">Password Saat Ini</label>
            <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
            @error('current_password', 'updatePassword') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="password" id="password" name="password" required autocomplete="new-password">
            @error('password', 'updatePassword') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Konfirmasi Password Baru</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary">Ubah Password</button>
    </form>
</div>
@endsection
