@extends('layouts.app')

@section('title', 'Import Data Anggota')
@section('header', 'Import Data Anggota')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Petunjuk Import Data</h3>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;">
        <div>
            <h4 style="margin-bottom:1rem;color:var(--primary);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.5rem;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        Langkah-langkah:
                    </h4>
            <ol style="margin-left:1.5rem;color:var(--gray-700);font-size:1rem;line-height:2;">
                <li style="margin-bottom:0.5rem;">Download template Excel terlebih dahulu</li>
                <li style="margin-bottom:0.5rem;">Isi data anggota sesuai kolom yang tersedia. <strong>Nomor Anggota akan dibuat otomatis oleh sistem.</strong></li>
                <li style="margin-bottom:0.5rem;">Simpan foto anggota dengan nama file sesuai kolom <code>foto</code> di Excel<br>
                    <span style="color:var(--gray-500);font-size:0.9rem;">Contoh: <code style="background:var(--gray-100);padding:0.125rem 0.375rem;border-radius:0.25rem;">ABC-0001.jpg</code></span>
                </li>
                <li style="margin-bottom:0.5rem;">Masukkan file Excel dan folder <code>foto/</code> ke dalam satu file ZIP</li>
                <li>Upload file ZIP ke sistem</li>
            </ol>

            <div style="margin-top:1.5rem;">
                <a href="{{ route('imports.template') }}" class="btn btn-success btn-lg">
                    Download Template
                </a>
            </div>
        </div>

        <div>
            <h4 style="margin-bottom:1rem;color:var(--primary);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.5rem;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                        Struktur File ZIP:
                    </h4>
            <div style="background:var(--gray-900);color:var(--gray-100);padding:1.25rem;border-radius:0.5rem;font-family:'Monaco','Consolas',monospace;font-size:0.9rem;">
                <p>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.5rem;"><path d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    import-kta-2026.zip
                </p>
                <p style="margin-left:1rem;">├── data.xlsx</p>
                <p style="margin-left:1rem;">└── <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg> foto/</p>
                <p style="margin-left:2rem;">├── <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg> ABC-0001.jpg</p>
                <p style="margin-left:2rem;">├── <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg> ABC-0002.jpg</p>
                <p style="margin-left:2rem;">└── ...</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.5rem;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        Upload File ZIP
                    </h3>
    </div>

    <form action="{{ route('imports.validate') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="file">Pilih File ZIP</label>
            <input type="file" id="file" name="file" accept=".zip" required style="padding:0.75rem;">
            <small style="color:var(--gray-500);">Format: ZIP. Maks: 50MB</small>
        </div>
        <div style="display:flex;gap:0.75rem;">
            <button type="submit" class="btn btn-primary btn-lg">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.5rem;"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                        Validasi Data
                    </button>
            <a href="{{ route('members.index') }}" class="btn btn-outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                Batal
            </a>
        </div>
    </form>
</div>

@if(session('import_errors'))
<div class="card" style="border-left:4px solid var(--danger);">
    <h4 style="color:var(--danger);margin-bottom:1rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.5rem;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        Error Import
                    </h4>
    <ul style="color:var(--danger);font-size:1rem;">
        @foreach(session('import_errors') as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@endsection
