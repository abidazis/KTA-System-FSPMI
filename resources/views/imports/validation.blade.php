@extends('layouts.app')

@section('title', 'Validasi Import')
@section('header', 'Hasil Validasi Import')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Hasil Validasi</h3>
    </div>

    <div style="padding:1.5rem;">
        {{-- Summary Stats --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
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
            <div style="padding:1.25rem;border-radius:0.5rem;text-align:center;background:var(--info-light);">
                <div style="font-size:2rem;font-weight:700;color:var(--info);">{{ count($result['preview'] ?? []) }}</div>
                <div style="font-size:0.85rem;color:var(--info);">Foto</div>
            </div>
        </div>

        {{-- ERROR STATE --}}
        @if(!empty($result['errors']) && count($result['errors']) > 0)
        <div style="border:2px solid var(--danger);border-radius:0.75rem;padding:1.5rem;background:var(--danger-light);margin-bottom:1.5rem;">
            <div style="display:flex;align-items:center;margin-bottom:1rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="1.5" style="margin-right:0.75rem;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <h4 style="color:var(--danger);margin:0;font-size:1.2rem;">Import Tidak Dapat Dilanjutkan</h4>
            </div>
            <p style="color:var(--danger);margin-bottom:0.5rem;font-size:1rem;">
                <strong>{{ $result['total'] }}</strong> Total Data |
                <strong>{{ $result['valid'] }}</strong> Valid |
                <strong>{{ count($result['errors']) }}</strong> Error
            </p>
            <p style="color:var(--danger);margin:0;font-size:0.95rem;">
                <strong>Tidak ada data yang disimpan ke database.</strong> Perbaiki error di bawah kemudian upload ulang.
            </p>
        </div>

        {{-- Error Table --}}
        <div style="margin-bottom:1.5rem;">
            <h5 style="color:var(--danger);margin-bottom:0.75rem;display:flex;align-items:center;gap:0.5rem;font-size:1.1rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                Detail Error ({{ count($result['errors']) }})
            </h5>
            <div style="max-height:500px;overflow-y:auto;border:1px solid var(--gray-200);border-radius:0.5rem;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead style="background:var(--danger-light);position:sticky;top:0;">
                        <tr>
                            <th style="padding:0.75rem;text-align:center;width:60px;">Baris</th>
                            <th style="padding:0.75rem;width:150px;">NIK</th>
                            <th style="padding:0.75rem;width:150px;">Nama</th>
                            <th style="padding:0.75rem;width:120px;">Perusahaan</th>
                            <th style="padding:0.75rem;">Error & Solusi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result['errors'] as $error)
                        <tr style="border-bottom:1px solid var(--gray-100);">
                            <td style="padding:0.75rem;text-align:center;font-weight:700;color:var(--danger);">{{ $error['row'] }}</td>
                            <td style="padding:0.75rem;"><code style="background:var(--gray-100);padding:0.25rem 0.5rem;border-radius:0.25rem;font-size:0.85rem;">{{ $error['nik'] }}</code></td>
                            <td style="padding:0.75rem;">{{ $error['nama'] ?? '-' }}</td>
                            <td style="padding:0.75rem;">{{ $error['perusahaan'] ?? '-' }}</td>
                            <td style="padding:0.75rem;">
                                @foreach($error['errors'] as $err)
                                <div style="margin-bottom:0.5rem;padding:0.5rem;background:white;border-radius:0.375rem;border-left:3px solid var(--danger);">
                                    <span style="color:var(--danger);font-weight:600;">❌ </span>
                                    <span style="color:var(--gray-700);font-size:0.9rem;">{{ $err }}</span>
                                </div>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- SUCCESS STATE --}}
        @if(!empty($result['data']) && $result['valid'] > 0 && empty($result['errors']))
        <div style="border:2px solid var(--success);border-radius:0.75rem;padding:1.5rem;background:var(--success-light);margin-bottom:1.5rem;">
            <div style="display:flex;align-items:center;margin-bottom:1rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="1.5" style="margin-right:0.75rem;">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <h4 style="color:var(--success);margin:0;font-size:1.2rem;">Validasi Berhasil</h4>
            </div>
            <p style="color:var(--gray-700);margin-bottom:0.5rem;font-size:1rem;">
                <strong>{{ $result['valid'] }}</strong> Total Data |
                <strong>{{ $result['valid'] }}</strong> Valid |
                <strong>0</strong> Error
            </p>
            <p style="color:var(--gray-600);margin:0;font-size:0.95rem;">
                {{ count($result['preview']) }} Foto Valid |
                0 Foto Duplicate |
                0 NIK Duplicate |
                0 Konflik Perusahaan
            </p>
        </div>

        {{-- Preview: Photo Naming --}}
        @if(!empty($result['preview']))
        <div style="margin-bottom:1.5rem;">
            <h5 style="color:var(--primary);margin-bottom:0.75rem;font-size:1.1rem;">
                📷 Preview Penamaan Foto
            </h5>
            <div style="overflow-x:auto;border:1px solid var(--gray-200);border-radius:0.5rem;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead style="background:var(--gray-50);">
                        <tr>
                            <th style="padding:0.75rem;text-align:center;width:50px;">No</th>
                            <th style="padding:0.75rem;width:180px;">NIK</th>
                            <th style="padding:0.75rem;">Nama</th>
                            <th style="padding:0.75rem;width:120px;">Kode</th>
                            <th style="padding:0.75rem;width:150px;">Foto Baru</th>
                            <th style="padding:0.75rem;width:100px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result['preview'] as $index => $preview)
                        <tr style="border-bottom:1px solid var(--gray-100);">
                            <td style="padding:0.5rem;text-align:center;">{{ $index + 1 }}</td>
                            <td style="padding:0.5rem;"><code style="background:var(--gray-100);padding:0.125rem 0.375rem;border-radius:0.25rem;">{{ $preview['nik'] }}</code></td>
                            <td style="padding:0.5rem;">{{ $preview['nama'] }}</td>
                            <td style="padding:0.5rem;">{{ $preview['company_kode'] }}</td>
                            <td style="padding:0.5rem;"><strong style="color:var(--success);">{{ $preview['photo_name'] }}</strong></td>
                            <td style="padding:0.5rem;">
                                @if($preview['is_new_company'])
                                <span class="badge" style="background:var(--info-light);color:var(--info);">Perusahaan Baru</span>
                                @else
                                <span class="badge" style="background:var(--success-light);color:var(--success);">Ready</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Preview: Company Summary --}}
        @if(!empty($result['company_summary']))
        <div style="margin-bottom:1.5rem;">
            <h5 style="color:var(--primary);margin-bottom:0.75rem;font-size:1.1rem;">
                🏢 Preview Perusahaan
            </h5>
            <div style="overflow-x:auto;border:1px solid var(--gray-200);border-radius:0.5rem;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead style="background:var(--gray-50);">
                        <tr>
                            <th style="padding:0.75rem;width:100px;">Kode</th>
                            <th style="padding:0.75rem;">Nama Perusahaan</th>
                            <th style="padding:0.75rem;width:150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result['company_summary'] as $company)
                        <tr style="border-bottom:1px solid var(--gray-100);">
                            <td style="padding:0.5rem;"><code style="background:var(--gray-100);padding:0.125rem 0.375rem;border-radius:0.25rem;">{{ $company['kode'] }}</code></td>
                            <td style="padding:0.5rem;">{{ $company['nama'] }}</td>
                            <td style="padding:0.5rem;">
                                @if($company['is_new'])
                                <span class="badge" style="background:var(--warning-light);color:var(--warning);">Akan dibuat baru</span>
                                @else
                                <span class="badge" style="background:var(--gray-100);color:var(--gray-600);">Data existing</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Confirm Button --}}
        @if($importToken && !empty($result['data']) && $result['valid'] > 0 && empty($result['errors']))
        <div style="border:2px solid var(--gray-200);border-radius:0.75rem;padding:1.5rem;background:var(--gray-50);margin-bottom:1.5rem;">
            <p style="color:var(--gray-700);margin-bottom:1rem;font-size:1rem;">
                <strong>Konfirmasi Import:</strong> Apakah Anda yakin ingin menyimpan <strong>{{ $result['valid'] }}</strong> data anggota?
            </p>
            <p style="color:var(--gray-500);margin-bottom:1.5rem;font-size:0.9rem;">
                Data yang akan dibuat:
                <br>- <strong>{{ $result['valid'] }}</strong> anggota
                <br>- <strong>{{ count($result['preview']) }}</strong> foto
                @if(!empty($result['company_summary']))
                <br>- <strong>{{ count(array_filter($result['company_summary'], fn($c) => $c['is_new'])) }}</strong> perusahaan baru
                @endif
            </p>

            <form action="{{ route('imports.process') }}" method="POST" id="import-confirm-form">
                @csrf
                <input type="hidden" name="import_token" value="{{ $importToken }}">
                <div style="display:flex;gap:0.75rem;">
                    <button type="submit" class="btn btn-success btn-lg" id="confirm-import-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        Ya, Simpan & Import {{ $result['valid'] }} Data
                    </button>
                    <a href="{{ route('imports.index') }}" class="btn btn-outline">
                        Batal
                    </a>
                </div>
            </form>
        </div>
        @endif
        @endif

        {{-- NO DATA STATE --}}
        @if(empty($result['data']) && empty($result['errors']))
        <div style="border:2px solid var(--warning);border-radius:0.75rem;padding:1.5rem;background:var(--warning-light);">
            <div style="display:flex;align-items:center;margin-bottom:0.5rem;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="1.5" style="margin-right:0.75rem;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <h4 style="color:var(--warning);margin:0;">Tidak Ada Data</h4>
            </div>
            <p style="color:var(--warning);margin:0;">
                File Excel tidak berisi data anggota yang valid.
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
