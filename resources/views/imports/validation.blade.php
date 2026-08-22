@extends('layouts.app')

@section('title', 'Validasi Import')
@section('header', 'Hasil Validasi Import')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Hasil Validasi</h3>
        <span class="badge px-3 py-2" style="background:#1e40af;color:#fff;font-size:0.9rem;">
            {{ $result['valid'] ?? 0 }} Valid / {{ $result['total'] ?? 0 }} Total
        </span>
    </div>

    <div class="card-body">

        {{-- DEBUG: Show what was extracted from ZIP --}}
        @if(!empty($zipNames))
        <div class="alert alert-secondary mb-3" style="font-size:0.8rem;">
            <strong>DEBUG - ZIP Entries:</strong> {{ json_encode($zipNames) }}<br>
            @if(!empty($result['data']))
                <strong>DEBUG - NIK(s) detected:</strong>
                @foreach($result['data'] as $row)
                    NIK={{ $row['nik'] ?? '?' }} (type={{ gettype($row['nik'] ?? null) }})
                @endforeach
            @endif
        </div>
        @elseif(empty($result['errors'][0]['errors'][0]) == false)
        <div class="alert alert-secondary mb-3" style="font-size:0.8rem;">
            <strong>DEBUG - ZIP Entries:</strong> (none - file may not have been extracted)<br>
        </div>
        @endif
        {{-- Summary Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="p-3 rounded text-center" style="background:#f1f5f9;">
                    <div style="font-size:2rem;font-weight:700;color:#1e40af;">{{ $result['total'] ?? 0 }}</div>
                    <div style="font-size:0.8rem;color:#64748b;">Total Data</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded text-center" style="background:#d1fae5;">
                    <div style="font-size:2rem;font-weight:700;color:#059669;">{{ $result['valid'] ?? 0 }}</div>
                    <div style="font-size:0.8rem;color:#065f46;">Valid</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded text-center" style="background:#fee2e2;">
                    <div style="font-size:2rem;font-weight:700;color:#dc2626;">{{ count($result['errors'] ?? []) }}</div>
                    <div style="font-size:0.8rem;color:#991b1b;">Error</div>
                </div>
            </div>
        </div>

        {{-- Error Table --}}
        @if(!empty($result['errors']))
        <div class="mb-4">
            <h5 class="mb-3" style="color:#dc2626;">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Error yang Ditemukan ({{ count($result['errors']) }})
            </h5>
            <div style="max-height:400px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:0.5rem;">
                <table class="table table-sm table-striped mb-0">
                    <thead style="background:#fef2f2;position:sticky;top:0;">
                        <tr>
                            <th style="width:60px;">Baris</th>
                            <th style="width:180px;">NIK</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result['errors'] as $error)
                        <tr>
                            <td class="text-center fw-bold">{{ $error['row'] }}</td>
                            <td><code>{{ $error['nik'] }}</code></td>
                            <td>
                                @foreach($error['errors'] as $err)
                                <span class="badge me-1 mb-1" style="background:#dc2626;color:#fff;font-size:0.75rem;">{{ $err }}</span>
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
        <div style="border:2px solid #059669;border-radius:0.75rem;padding:1.5rem;background:#f0fdf4;margin-bottom:1.5rem;">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-check-circle-fill me-2" style="font-size:1.5rem;color:#059669;"></i>
                <h4 class="mb-0" style="color:#059669;">Validasi Berhasil</h4>
            </div>
            <p class="mb-3" style="color:#166534;">
                <strong>{{ $result['valid'] }}</strong> data anggota siap disimpan.
                Semua data telah divalidasi termasuk foto.
            </p>

            @if($importToken)
            <form action="{{ route('imports.process') }}" method="POST">
                @csrf
                <input type="hidden" name="import_token" value="{{ $importToken }}">
                <button type="submit" class="btn btn-success btn-lg px-5">
                    <i class="bi bi-download me-2"></i>
                    Simpan &amp; Import {{ $result['valid'] }} Data
                </button>
            </form>
            @endif
        </div>
        @endif

        {{-- Has Errors: No Import Button --}}
        @if(empty($result['data']) || $result['valid'] == 0)
        <div style="border:2px solid #dc2626;border-radius:0.75rem;padding:1.5rem;background:#fef2f2;">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-x-circle-fill me-2" style="font-size:1.5rem;color:#dc2626;"></i>
                <h4 class="mb-0" style="color:#dc2626;">Import Tidak Dapat Dilanjutkan</h4>
            </div>
            <p class="mb-0" style="color:#991b1b;">
                Perbaiki error di atas, kemudian upload ulang file ZIP.
            </p>
        </div>
        @endif

        <div class="mt-4 pt-3 border-top">
            <a href="{{ route('imports.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Import
            </a>
        </div>
    </div>
</div>
@endsection
