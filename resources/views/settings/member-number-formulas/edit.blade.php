@extends('layouts.app')

@section('title', 'Edit Formula Nomor Anggota')

@push('styles')
<style>
    .preview-card {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: 2px dashed #94a3b8;
        border-radius: 0.75rem;
        padding: 1.5rem;
    }
    .preview-number {
        font-family: 'Courier New', Courier, monospace;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
        letter-spacing: 1px;
    }
    .preview-label {
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .live-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem 0.625rem;
        background: #10b981;
        color: white;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    .live-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        background: white;
        border-radius: 50%;
        animation: pulse 1.5s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h3>Edit Formula Nomor Anggota</h3>
            @if($formula->is_active)
                <span class="badge badge-active" style="margin-top: 0.5rem;">Aktif</span>
            @endif
        </div>
        <a href="{{ route('member-number-formulas.index') }}" class="btn btn-outline btn-sm">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    <form action="{{ route('member-number-formulas.update', $formula) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-row">
            {{-- Nama Formula --}}
            <div class="form-group">
                <label for="name">Nama Formula <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $formula->name) }}"
                       class="@error('name') border-red-500 @enderror"
                       placeholder="Contoh: Formula Standar FSPMI" required>
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Prefix --}}
            <div class="form-group">
                <label for="prefix">Prefix / Awalan</label>
                <input type="text" name="prefix" id="prefix" value="{{ old('prefix', $formula->prefix) }}"
                       class="@error('prefix') border-red-500 @enderror"
                       placeholder="Contoh: FSPMI">
                @error('prefix')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="form-row">
            {{-- Separator --}}
            <div class="form-group">
                <label for="separator">Separator / Pemisah</label>
                <select name="separator" id="separator">
                    <option value="-" {{ old('separator', $formula->separator) == '-' ? 'selected' : '' }}>Tanda hubung (-)</option>
                    <option value="/" {{ old('separator', $formula->separator) == '/' ? 'selected' : '' }}>Slash (/)</option>
                    <option value="_" {{ old('separator', $formula->separator) == '_' ? 'selected' : '' }}>Underscore (_)</option>
                    <option value="." {{ old('separator', $formula->separator) == '.' ? 'selected' : '' }}>Titik (.)</option>
                    <option value="" {{ old('separator', $formula->separator) == '' ? 'selected' : '' }}>Tanpa pemisah</option>
                </select>
            </div>

            {{-- Sequence Digits --}}
            <div class="form-group">
                <label for="sequence_digits">Jumlah Digit Sequence <span class="text-red-500">*</span></label>
                <select name="sequence_digits" id="sequence_digits">
                    <option value="3" {{ old('sequence_digits', $formula->sequence_digits) == '3' ? 'selected' : '' }}>3 digit (001)</option>
                    <option value="4" {{ old('sequence_digits', $formula->sequence_digits) == '4' ? 'selected' : '' }}>4 digit (0001)</option>
                    <option value="5" {{ old('sequence_digits', $formula->sequence_digits) == '5' ? 'selected' : '' }}>5 digit (00001)</option>
                    <option value="6" {{ old('sequence_digits', $formula->sequence_digits) == '6' ? 'selected' : '' }}>6 digit (000001)</option>
                </select>
            </div>
        </div>

        {{-- Include Company Code --}}
        <div class="form-group">
            <div class="checkbox-wrapper">
                <input type="checkbox" name="include_company_code" id="include_company_code" value="1"
                       {{ old('include_company_code', $formula->include_company_code) ? 'checked' : '' }}>
                <label for="include_company_code">
                    Gunakan Kode Perusahaan
                </label>
            </div>
            <p style="font-size: 0.85rem; color: var(--gray-500); margin-top: 0.5rem; margin-left: 1.75rem;">
                Tambahkan kode perusahaan (misal: ABC) di antara prefix dan sequence
            </p>
        </div>

        {{-- DYNAMIC PREVIEW --}}
        <div class="preview-card mb-6">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--gray-700);">
                    Preview Format
                </h3>
                <span class="live-badge">Live Update</span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <div>
                    <div class="preview-label">Format Lengkap</div>
                    <div id="preview-full" class="preview-number">{{ $formula->prefix }}{{ $formula->separator }}ABC{{ $formula->separator }}{{ str_repeat('0', $formula->sequence_digits) }}</div>
                </div>
                <div>
                    <div class="preview-label">Tanpa Kode Perusahaan</div>
                    <div id="preview-no-company" class="preview-number">{{ $formula->prefix }}{{ $formula->separator }}{{ str_repeat('0', $formula->sequence_digits) }}</div>
                </div>
            </div>

            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                <div class="preview-label" style="margin-bottom: 0.5rem;">Contoh Sequence</div>
                <div id="preview-sequence" style="font-family: 'Courier New', monospace; font-size: 0.9rem; color: var(--gray-600); display: flex; gap: 1rem; flex-wrap: wrap;">
                    @php
                        $sep = $formula->separator;
                        $zeros = str_repeat('0', $formula->sequence_digits);
                    @endphp
                    @if($formula->include_company_code)
                        <span>{{ $formula->prefix }}{{ $sep }}ABC{{ $sep }}0001</span>
                        <span>{{ $formula->prefix }}{{ $sep }}ABC{{ $sep }}0002</span>
                        <span>{{ $formula->prefix }}{{ $sep }}XYZ{{ $sep }}0001</span>
                    @else
                        <span>{{ $formula->prefix }}{{ $sep }}0001</span>
                        <span>{{ $formula->prefix }}{{ $sep }}0002</span>
                        <span>{{ $formula->prefix }}{{ $sep }}0003</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Active Status --}}
        <div class="form-group">
            <div class="checkbox-wrapper">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       {{ old('is_active', $formula->is_active) ? 'checked' : '' }}>
                <label for="is_active">
                    Aktifkan Formula
                </label>
            </div>
            <p style="font-size: 0.85rem; color: var(--gray-500); margin-top: 0.5rem; margin-left: 1.75rem;">
                Hanya satu formula yang dapat aktif pada satu waktu
            </p>
        </div>

        <div class="actions" style="margin-top: 2rem;">
            <a href="{{ route('member-number-formulas.index') }}" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function() {
    function updatePreview() {
        var prefix = document.getElementById('prefix').value || 'FSPMI';
        var separator = document.getElementById('separator').value || '-';
        var includeCompany = document.getElementById('include_company_code').checked;
        var digits = parseInt(document.getElementById('sequence_digits').value) || 4;

        var zeros = '';
        for (var i = 0; i < digits; i++) zeros += '0';

        var withCompany = prefix + separator + 'ABC' + separator + zeros;
        var withoutCompany = prefix + separator + zeros;

        document.getElementById('preview-full').textContent = withCompany;
        document.getElementById('preview-no-company').textContent = withoutCompany;

        var sequenceHtml = '';
        for (var n = 1; n <= 3; n++) {
            var num = n.toString().padStart(digits, '0');
            if (includeCompany) {
                sequenceHtml += '<span>' + prefix + separator + 'ABC' + separator + num + '</span>';
                if (n === 2) sequenceHtml += '<span>' + prefix + separator + 'XYZ' + separator + '0001' + '</span>';
            } else {
                sequenceHtml += '<span>' + prefix + separator + num + '</span>';
            }
        }
        document.getElementById('preview-sequence').innerHTML = sequenceHtml;
    }

    // Listen to all inputs
    ['prefix', 'separator', 'include_company_code', 'sequence_digits'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        }
    });

    // Initial update
    updatePreview();
})();
</script>
@endpush
