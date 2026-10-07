@extends('layouts.app')

@section('title', 'Import Data Anggota')
@section('header', 'Import Data Anggota')

@section('content')
{{-- Section 1: Instructions Card --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="background: var(--primary); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            </div>
            <div>
                <h3 style="margin: 0;">Petunjuk Import Data Anggota</h3>
                <small style="color: var(--gray-500);">Ikuti langkah-langkah berikut untuk import data</small>
            </div>
        </div>
    </div>

    <div style="padding: 1.5rem;">
        {{-- Steps Grid --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            @php $steps = [
                ['num' => 1, 'title' => 'Download Template', 'desc' => 'Download template Excel terlebih dahulu', 'icon' => 'download'],
                ['num' => 2, 'title' => 'Isi Data', 'desc' => 'Lengkapi data anggota. Isi nomor anggota jika sudah punya, atau kosongkan untuk auto-generate.', 'icon' => 'edit'],
                ['num' => 3, 'title' => 'Siapkan Foto', 'desc' => 'Simpan foto dengan nama sesuai kolom foto di Excel', 'icon' => 'camera'],
                ['num' => 4, 'title' => 'Kompres ZIP', 'desc' => 'Masukkan Excel dan folder foto/ ke dalam file ZIP', 'icon' => 'folder'],
                ['num' => 5, 'title' => 'Upload', 'desc' => 'Upload file ZIP ke sistem', 'icon' => 'upload'],
            ]; @endphp

            @foreach($steps as $step)
            <div style="display: flex; gap: 1rem; padding: 1rem; background: var(--gray-50); border-radius: 8px; border: 1px solid var(--gray-200);">
                <div style="background: var(--primary); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1rem; flex-shrink: 0;">
                    {{ $step['num'] }}
                </div>
                <div>
                    <h4 style="margin: 0 0 0.25rem 0; color: var(--gray-800); font-size: 0.95rem;">{{ $step['title'] }}</h4>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.85rem; line-height: 1.4;">{{ $step['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Info Box: Format Nomor Anggota --}}
        <div style="background: linear-gradient(135deg, var(--info-light) 0%, #e0f2fe 100%); border: 1px solid #0ea5e9; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
            <div style="display: flex; gap: 1rem; align-items: flex-start;">
                <div style="background: var(--info); color: white; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                </div>
                <div style="flex: 1;">
                    <h4 style="margin: 0 0 0.5rem 0; color: var(--info); font-size: 1rem;">Format Nomor Anggota</h4>
                    <div style="background: white; border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem;">
                        <code style="font-family: 'Courier New', monospace; font-size: 1.1rem; color: var(--primary); font-weight: bold;">X.XX.XX.XXX.XXXX</code>
                    </div>
                    <p style="margin: 0; font-size: 0.9rem; color: var(--gray-700);">
                        <strong>Contoh:</strong> <code style="background: var(--gray-100); padding: 0.125rem 0.375rem; border-radius: 4px;">1.02.01.038.0123</code>
                    </p>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: var(--gray-600);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        Kosongkan kolom jika ingin <strong>auto-generate</strong> oleh sistem
                    </p>
                </div>
            </div>
        </div>

        {{-- Struktur File ZIP --}}
        <div style="background: var(--gray-900); border-radius: 12px; padding: 1.5rem; color: var(--gray-100);">
            <h4 style="margin: 0 0 1rem 0; color: white; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                Struktur File ZIP
            </h4>
            <div style="font-family: 'Courier New', monospace; font-size: 0.95rem; line-height: 1.8;">
                <div style="color: #60a5fa;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;"><path d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                    import-kta-2026.zip
                </div>
                <div style="margin-left: 1.5rem; color: #a3e635;">
                    ├── data.xlsx
                </div>
                <div style="margin-left: 1.5rem; color: #fbbf24;">
                    └── foto/
                </div>
                <div style="margin-left: 3rem; color: #94a3b8;">
                    ├── ABC-0001.jpg
                </div>
                <div style="margin-left: 3rem; color: #94a3b8;">
                    ├── ABC-0002.jpg
                </div>
                <div style="margin-left: 3rem; color: #94a3b8;">
                    └── ...
                </div>
            </div>
        </div>

        {{-- Download Button --}}
        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="{{ route('imports.template') }}" class="btn btn-success btn-lg" style="padding: 0.875rem 2rem; font-size: 1rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Download Template Excel
            </a>
        </div>
    </div>
</div>

{{-- Section 2: Upload Card --}}
<div class="card">
    <div class="card-header">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="background: var(--success); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            </div>
            <div>
                <h3 style="margin: 0;">Upload File ZIP</h3>
                <small style="color: var(--gray-500);">Pilih file ZIP yang sudah disiapkan</small>
            </div>
        </div>
    </div>

    <form action="{{ route('imports.validate') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div style="padding: 1.5rem;">
            <div style="border: 2px dashed var(--gray-300); border-radius: 12px; padding: 2rem; text-align: center; background: var(--gray-50);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--gray-400)" stroke-width="1.5" style="margin-bottom: 1rem;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <p style="margin: 0 0 1rem 0; color: var(--gray-600); font-size: 1rem;">
                    Seret file ZIP ke sini, atau
                </p>
                <label for="file" class="btn btn-outline" style="cursor: pointer;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Pilih File
                </label>
                <input type="file" id="file" name="file" accept=".zip" required style="display: none;" onchange="handleFileSelect(this)">
                <p id="file-name" style="margin: 1rem 0 0 0; color: var(--gray-500); font-size: 0.875rem;"></p>
                <p style="margin: 0.5rem 0 0 0; color: var(--gray-400); font-size: 0.8rem;">
                    Format: ZIP. Maks: 50MB
                </p>
            </div>

            <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" id="upload-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                    Validasi Data
                </button>
                <a href="{{ route('members.index') }}" class="btn btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>

@if(session('import_errors'))
<div class="card" style="border-left: 4px solid var(--danger); margin-top: 1.5rem;">
    <div style="padding: 1.5rem;">
        <h4 style="color: var(--danger); margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            Error Import
        </h4>
        <ul style="color: var(--danger); margin: 0; padding-left: 1.5rem;">
            @foreach(session('import_errors') as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

@push('scripts')
<script>
function handleFileSelect(input) {
    const fileName = document.getElementById('file-name');
    const uploadBtn = document.getElementById('upload-btn');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileSize = (file.size / 1024 / 1024).toFixed(2);

        fileName.innerHTML = `
            <strong>${file.name}</strong>
            <span style="color: var(--gray-500);"> (${fileSize} MB)</span>
        `;

        uploadBtn.disabled = false;
        uploadBtn.style.opacity = '1';
        uploadBtn.style.cursor = 'pointer';
    } else {
        fileName.innerHTML = '';
        uploadBtn.disabled = true;
        uploadBtn.style.opacity = '0.5';
        uploadBtn.style.cursor = 'not-allowed';
    }
}
</script>
@endpush
@endsection
