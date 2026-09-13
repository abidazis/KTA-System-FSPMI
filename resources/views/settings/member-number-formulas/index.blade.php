@extends('layouts.app')

@section('title', 'Kelola Formula Nomor Anggota')

@section('header', 'Kelola Formula Nomor Anggota')

@section('content')
{{-- Info Banner --}}
<div class="card" style="background: linear-gradient(135deg, var(--info-light) 0%, #e0f2fe 100%); border-color: var(--info);">
    <div style="display: flex; align-items: flex-start; gap: 1rem;">
        <div style="width: 44px; height: 44px; background: var(--info); color: white; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <h3 style="font-size: 1rem; font-weight: 600; color: var(--info); margin-bottom: 0.25rem;">
                Tentang Formula Nomor Anggota
            </h3>
            <p style="font-size: 0.9rem; color: var(--gray-600); margin: 0; line-height: 1.5;">
                Formula menentukan format nomor anggota yang di-generate otomatis oleh sistem.
                Hanya <strong>satu formula</strong> yang dapat aktif pada satu waktu.
                Ubah pengaturan di sini untuk mengubah format nomor anggota baru.
            </p>
        </div>
    </div>
</div>

{{-- Warning Banner if no active formula --}}
@if(!$activeFormula)
<div class="card" style="background: linear-gradient(135deg, var(--warning-light) 0%, #fef3c7 100%); border-color: var(--warning);">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <div style="width: 48px; height: 48px; background: var(--warning); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div style="flex: 1;">
            <h3 style="font-size: 1rem; font-weight: 600; color: var(--warning); margin-bottom: 0.25rem;">
                Tidak Ada Formula Aktif
            </h3>
            <p style="font-size: 0.9rem; color: var(--gray-600); margin: 0;">
                Sistem tidak dapat membuat nomor anggota karena belum ada formula yang aktif.
                Silakan aktifkan salah satu formula atau buat yang baru.
            </p>
        </div>
        <a href="{{ route('member-number-formulas.create') }}" class="btn btn-warning">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Sekarang
        </a>
    </div>
</div>
@endif

{{-- Formula List --}}
<div class="card">
    <div class="card-header">
        <h3>Daftar Formula</h3>
        <a href="{{ route('member-number-formulas.create') }}" class="btn btn-primary btn-sm">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Formula
        </a>
    </div>

    @if($formulas->isEmpty())
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p>Belum ada formula nomor anggota.</p>
            <a href="{{ route('member-number-formulas.create') }}" class="btn btn-primary">
                Tambah Formula Pertama
            </a>
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Formula</th>
                    <th>Format</th>
                    <th>Perusahaan</th>
                    <th>Digit</th>
                    <th>Dibuat</th>
                    <th style="width: 160px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($formulas as $formula)
                <tr>
                    <td>
                        @if($formula->is_active)
                            <span class="badge badge-active">Aktif</span>
                        @else
                            <span class="badge badge-inactive">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--gray-800);">{{ $formula->name }}</div>
                        <div style="font-size: 0.8rem; color: var(--gray-500);">oleh {{ $formula->creator?->name ?? 'System' }}</div>
                    </td>
                    <td>
                        <code style="background: var(--gray-100); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-family: 'Courier New', monospace; font-size: 0.85rem; display: inline-block;">
                            {{ $formula->prefix }}{{ $formula->separator }}
                            @if($formula->include_company_code)
                                <span style="color: var(--info);">{{ $formula->prefix }}{{ $formula->separator }}XXX</span>
                            @endif
                            <span style="color: var(--success);">{{ str_repeat('0', $formula->sequence_digits) }}</span>
                        </code>
                    </td>
                    <td>
                        @if($formula->include_company_code)
                            <span style="color: var(--success);">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 0.25rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Ya
                            </span>
                        @else
                            <span style="color: var(--gray-400);">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 0.25rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Tidak
                            </span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-draft">{{ $formula->sequence_digits }} digit</span>
                    </td>
                    <td style="color: var(--gray-500); font-size: 0.9rem;">
                        {{ $formula->created_at->format('d/m/Y') }}
                    </td>
                    <td>
                        <div class="actions">
                            {{-- Toggle Active Button --}}
                            <button type="button"
                                    onclick="toggleActive({{ $formula->id }}, {{ $formula->is_active ? 'true' : 'false' }})"
                                    class="btn btn-sm {{ $formula->is_active ? 'btn-warning' : 'btn-success' }}"
                                    title="{{ $formula->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                    style="padding: 0.375rem 0.5rem;">
                                @if($formula->is_active)
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L10 6.477V16h2a1 1 0 110 2H8a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z"/>
                                    </svg>
                                @else
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </button>

                            {{-- Edit Button --}}
                            <a href="{{ route('member-number-formulas.edit', $formula) }}"
                               class="btn btn-outline btn-sm"
                               title="Edit"
                               style="padding: 0.375rem 0.5rem;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>

                            {{-- Delete Button (only for inactive formulas) --}}
                            @if(!$formula->is_active)
                                <form action="{{ route('member-number-formulas.destroy', $formula) }}"
                                      method="POST"
                                      style="display: inline;"
                                      data-fspmi-confirm="Hapus formula '{{ $formula->name }}'? Tindakan ini tidak dapat dibatalkan."
                                      data-fspmi-confirm-title="Hapus Formula"
                                      data-fspmi-confirm-btn="Ya, Hapus"
                                      data-fspmi-confirm-type="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-danger btn-sm"
                                            title="Hapus"
                                            style="padding: 0.375rem 0.5rem;">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 3rem;">
                        <div class="empty-state">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p>Belum ada formula nomor anggota.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</div>

{{-- Active Formula Examples --}}
@if($activeFormula)
<div class="card" style="background: linear-gradient(135deg, var(--success-light) 0%, #d1fae5 100%); border-color: var(--success);">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <div style="width: 48px; height: 48px; background: var(--success); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div style="flex: 1;">
            <h3 style="font-size: 1rem; font-weight: 600; color: var(--success); margin-bottom: 0.25rem;">
                Contoh Format Nomor Anggota (Aktif)
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 0.75rem;">
                <div>
                    <span style="font-size: 0.8rem; color: var(--gray-500);">PT ABC:</span>
                    <code style="display: block; background: white; padding: 0.5rem; border-radius: 0.375rem; font-family: 'Courier New', monospace; font-size: 1rem; font-weight: 600; color: var(--primary); margin-top: 0.25rem;">{{ $activeFormula->buildSampleNumber('ABC') }}</code>
                </div>
                <div>
                    <span style="font-size: 0.8rem; color: var(--gray-500);">PT XYZ:</span>
                    <code style="display: block; background: white; padding: 0.5rem; border-radius: 0.375rem; font-family: 'Courier New', monospace; font-size: 1rem; font-weight: 600; color: var(--primary); margin-top: 0.25rem;">{{ $activeFormula->buildSampleNumber('XYZ') }}</code>
                </div>
                <div>
                    <span style="font-size: 0.8rem; color: var(--gray-500);">Tanpa Perusahaan:</span>
                    <code style="display: block; background: white; padding: 0.5rem; border-radius: 0.375rem; font-family: 'Courier New', monospace; font-size: 1rem; font-weight: 600; color: var(--primary); margin-top: 0.25rem;">{{ $activeFormula->buildSampleNumber() }}</code>
                </div>
            </div>
        </div>
        <a href="{{ route('member-number-formulas.edit', $activeFormula) }}" class="btn btn-success btn-sm">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit
        </a>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function toggleActive(formulaId, currentStatus) {
    var title = currentStatus ? 'Nonaktifkan Formula' : 'Aktifkan Formula';
    var message = currentStatus
        ? 'Nonaktifkan formula ini? Nomor anggota baru akan menggunakan formula lain.'
        : 'Aktifkan formula ini? Formula lain akan otomatis dinonaktifkan.';

    FSPMIModal.confirm(message, title, currentStatus ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan', 'Batal', 'warning')
        .then(function(ok) {
            if (!ok) return;

            fetch('/settings/member-number-formulas/' + formulaId + '/toggle-active', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    FSPMIModal.error(data.message || 'Terjadi kesalahan saat mengubah status formula.');
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                FSPMIModal.error('Terjadi kesalahan saat mengubah status formula.');
            });
        });
}
</script>
@endpush
