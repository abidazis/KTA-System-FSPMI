@extends('layouts.app')

@section('title', 'Validasi Import')
@section('header', 'Hasil Validasi Import')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Hasil Validasi</h3>
        <span class="badge" style="background:var(--primary);color:var(--white);padding:0.5rem 1rem;font-size:0.9rem;">
            {{ $result['valid'] ?? 0 }} Valid / {{ $result['total'] ?? 0 }} Total
        </span>
    </div>

    <div style="padding:1.5rem;">
        {{-- DEBUG: Show what was extracted from ZIP --}}
        @if(!empty($zipNames))
        <div class="alert" style="background:var(--info-light);color:var(--info);border-left:4px solid var(--info);padding:1rem;border-radius:0.5rem;margin-bottom:1.25rem;font-size:0.85rem;">
            <strong>DEBUG - ZIP Entries:</strong> {{ json_encode($zipNames) }}<br>
            @if(!empty($result['data']))
                <strong>DEBUG - NIK(s) detected:</strong>
                @foreach($result['data'] as $row)
                    NIK={{ $row['nik'] ?? '?' }} (type={{ gettype($row['nik'] ?? null) }})
                @endforeach
            @endif
        </div>
        @elseif(empty($result['errors'][0]['errors'][0]) == false)
        <div class="alert" style="background:var(--info-light);color:var(--info);border-left:4px solid var(--info);padding:1rem;border-radius:0.5rem;margin-bottom:1.25rem;font-size:0.85rem;">
            <strong>DEBUG - ZIP Entries:</strong> (none - file may not have been extracted)<br>
        </div>
        @endif

        {{-- Summary Stats --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;">
            <div style="padding:1.25rem;border-radius:0.5rem;text-align:center;background:var(--gray-100);">
                <div style="font-size:2rem;font-weight:700;color:var(--primary);">{{ $result['total'] ?? 0 }}</div>
                <div style="font-size:0.85rem;color:var(--gray-500);">Total Data</div>
            </div>
            <div style="padding:1.25rem;border-radius:0.5rem;text-align:center;background:var(--success-light);">
                <div style="font-size:2rem;font-weight:700;color:var(--success);">{{ $result['valid'] ?? 0 }}</div>
                <div style="font-size:0.85rem;color:var(--success);">Valid</div>
            </div>
            <div style="padding:1.25rem;border-radius:0.5rem;text-align:center;background:var(--danger-light);">
                <div style="font-size:2rem;font-weight:700;color:var(--danger);">{{ count($result['errors'] ?? []) }}</div>
                <div style="font-size:0.85rem;color:var(--danger);">Error</div>
            </div>
        </div>

        {{-- Error Table --}}
        @if(!empty($result['errors']))
        <div style="margin-bottom:1.5rem;">
            <h5 style="color:var(--danger);margin-bottom:0.75rem;display:flex;align-items:center;gap:0.5rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                Error yang Ditemukan ({{ count($result['errors']) }})
            </h5>
            <div style="max-height:400px;overflow-y:auto;border:1px solid var(--gray-200);border-radius:0.5rem;">
                <table>
                    <thead style="background:var(--danger-light);position:sticky;top:0;">
                        <tr>
                            <th style="width:60px;">Baris</th>
                            <th style="width:180px;">NIK</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result['errors'] as $error)
                        <tr>
                            <td style="text-align:center;font-weight:700;">{{ $error['row'] }}</td>
                            <td><code style="background:var(--gray-100);padding:0.125rem 0.375rem;border-radius:0.25rem;">{{ $error['nik'] }}</code></td>
                            <td>
                                @foreach($error['errors'] as $err)
                                <span class="badge" style="background:var(--danger);color:white;padding:0.25rem 0.5rem;font-size:0.75rem;margin-right:0.25rem;margin-bottom:0.25rem;">{{ $err }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Valid: Show Summary + Import Button --}}
        @if(!empty($result['data']) && $result['valid'] > 0)
        <div style="border:2px solid var(--success);border-radius:0.75rem;padding:1.5rem;background:var(--success-light);margin-bottom:1.5rem;">
            <div style="display:flex;align-items:center;margin-bottom:1rem;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="1.5" style="margin-right:0.75rem;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <h4 style="color:var(--success);margin:0;">Validasi Berhasil</h4>
            </div>
            <p style="color:var(--gray-700);margin-bottom:1rem;">
                <strong>{{ $result['valid'] }}</strong> data anggota siap disimpan.
                Semua data telah divalidasi termasuk foto.
            </p>

            @if($importToken)
            <form action="{{ route('imports.process') }}" method="POST">
                @csrf
                <input type="hidden" name="import_token" value="{{ $importToken }}">
                <button type="submit" class="btn btn-success btn-lg">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Simpan & Import {{ $result['valid'] }} Data
                </button>
            </form>
            @endif
        </div>
        @endif

        {{-- Has Errors: No Import Button --}}
        @if(empty($result['data']) || $result['valid'] == 0)
        <div style="border:2px solid var(--danger);border-radius:0.75rem;padding:1.5rem;background:var(--danger-light);">
            <div style="display:flex;align-items:center;margin-bottom:0.5rem;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="1.5" style="margin-right:0.75rem;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                <h4 style="color:var(--danger);margin:0;">Import Tidak Dapat Dilanjutkan</h4>
            </div>
            <p style="color:var(--danger);margin:0;">
                Perbaiki error di atas, kemudian upload ulang file ZIP.
            </p>
        </div>
        @endif

        <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--gray-200);">
            <a href="{{ route('imports.index') }}" class="btn btn-outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Kembali ke Import
            </a>
        </div>
    </div>
</div>
@endsection
