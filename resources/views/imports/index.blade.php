@extends('layouts.app')

@section('title', 'Import Data Anggota')
@section('header', 'Import Data Anggota')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Import Data Anggota</h3>
    </div>

    <div style="margin-bottom:2rem;">
        <h4 style="margin-bottom:1rem;">Petunjuk Import</h4>
        <ol style="margin-left:1.5rem;color:#475569;font-size:0.9rem;line-height:1.8;">
            <li>Download template Excel terlebih dahulu</li>
            <li>Isi data anggota sesuai kolom yang tersedia</li>
            <li>Simpan foto anggota dengan nama file sesuai NIK (contoh: <code>3275010101900001.jpg</code>)</li>
            <li>Masukkan file Excel dan folder <code>foto/</code> ke dalam satu file ZIP</li>
            <li>Upload file ZIP ke sistem</li>
        </ol>

        <div style="background:#f1f5f9;padding:1rem;border-radius:0.5rem;margin-top:1rem;font-family:monospace;font-size:0.85rem;">
            <p>Struktur ZIP:</p>
            <pre style="margin-top:0.5rem;">
import-kta-2026.zip
├── data.xlsx          ← File Excel data anggota
└── foto/             ← Folder foto
    ├── 3275010101900001.jpg
    ├── 3275010202910002.jpg
    └── ...</pre>
        </div>
    </div>

    <div style="display:flex;gap:1rem;margin-bottom:2rem;">
        <a href="{{ route('imports.template') }}" class="btn btn-primary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Download Template Excel
        </a>
    </div>

    <form action="{{ route('imports.validate') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="file">Upload File ZIP</label>
            <input type="file" id="file" name="file" accept=".zip" required>
            <small style="color:#64748b;">Format: ZIP. Maks: 50MB</small>
        </div>
        <button type="submit" class="btn btn-primary">Validasi Data</button>
    </form>
</div>

@if(session('import_errors'))
<div class="card" style="border-left:4px solid #dc2626;">
    <h4 style="color:#dc2626;margin-bottom:1rem;">Error Import</h4>
    <ul style="color:#991b1b;">
        @foreach(session('import_errors') as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@endsection
