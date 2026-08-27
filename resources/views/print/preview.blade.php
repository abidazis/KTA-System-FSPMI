@extends('layouts.app')

@prepend('styles')
<style>
/*
|--------------------------------------------------------------------------
| PREVIEW PAGE - 3 KTA PER PAGE
| Layout: A4 portrait, 2 cols x 3 rows
|--------------------------------------------------------------------------
*/
.preview-wrap{background:#fff;padding:1.5rem;margin-bottom:2rem;}
.preview-label{font-size:0.8rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;}
.preview-label::before{content:'';display:inline-block;width:12px;height:12px;border-radius:2px;}
.preview-label.front-label::before{background:#3b82f6;}
.preview-label.back-label::before{background:#ef4444;}
.preview-page{width:21cm;min-height:29.7cm;margin:0 auto 2rem auto;position:relative;background:#fff;box-shadow:0 4px 16px rgba(0,0,0,.12);padding:1.5cm;border-radius:4px;}

/* Grid: 2 cols (front|back) x 3 rows = 6 cards per page */
/* Card: portrait 54mm x 85.6mm. Each cell needs 54mm x 85.6mm */
.preview-grid{width:11.2cm;height:auto;display:table;border-collapse:separate;border-spacing:0.4cm 0;table-layout:fixed;}
.preview-grid-row{display:table-row;width:11.2cm;height:8.56cm;}
.preview-grid-cell{display:table-cell;width:5.4cm;height:8.56cm;padding:0;vertical-align:middle;text-align:center;}

/* Card wrapper: portrait slot 54mm x 85.6mm */
.kta-card-wrapper{position:relative;width:5.4cm;height:8.56cm;overflow:hidden;margin:0 auto;}

/* Inner: same as wrapper (no rotation) */
.kta-card-wrapper .kta-slot-inner{
    position:relative;
    width:54mm;
    height:85.6mm;
}

/* ============================================================
   KTA CARD STYLES (must match batch-pdf)
   ============================================================ */

* { box-sizing: border-box; }

.kta-card {
    position: relative;
    width: 54mm;
    height: 85.6mm;
    margin: 0;
    padding: 0;
    overflow: hidden;
    font-family: Arial, Helvetica, sans-serif;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.kta-bg-image {
    position: absolute;
    left: 0;
    top: 0;
    width: 54mm;
    height: 85.6mm;
    z-index: 0;
}

.kta-data-layer {
    position: absolute;
    left: 0;
    top: 0;
    width: 54mm;
    height: 85.6mm;
    z-index: 1;
}

.kta-field {
    position: absolute;
    font-weight: 500;
    color: #000;
    white-space: nowrap;
    overflow: hidden;
}

.kta-field-alamat {
    white-space: normal;
    word-break: break-word;
    line-height: 1.3;
}

.kta-field-small {
    font-weight: 400;
}

.kta-field-center {
    text-align: center;
}

.kta-signature {
    position: absolute;
    object-fit: contain;
    opacity: .95;
}

.kta-seal {
    position: absolute;
    object-fit: contain;
    opacity: .95;
}

.kta-photo {
    position: absolute;
    overflow: hidden;
    border: 0.25mm solid rgba(75,75,75,.55);
}

.kta-photo img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.page-separator{border-top:2px dashed #e2e8f0;margin:2rem 0;}
.info-banner{background:#fef3c7;padding:1rem;border-radius:0.5rem;margin-bottom:1.5rem;font-size:0.875rem;}
.info-banner ul{margin:0.5rem 0 0 1.5rem;}
</style>
@endprepend

@section('title', 'Preview Batch Cetak')
@section('header', 'Preview Batch Cetak')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Preview Batch ({{ $count }} KTA)</h3>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            <a href="{{ route('print.index') }}" class="btn btn-sm btn-outline">Kembali</a>
        </div>
    </div>

    <div class="info-banner">
        <strong>Petunjuk:</strong>
        <ul>
            <li>Halaman <span style="background:#3b82f6;color:#fff;padding:0 4px;font-weight:700;">DEPAN</span> = sisi pertama (FRONT)</li>
            <li>Halaman <span style="background:#ef4444;color:#fff;padding:0 4px;font-weight:700;">BELAKANG</span> = sisi sebaliknya (BACK)</li>
            <li>Setiap halaman berisi 3 KTA (FRONT di kiri, BACK di kanan, 3 baris)</li>
            <li>Cetak DEPAN dulu, lalu BALIK kertas untuk BELAKANG (mode duplex)</li>
        </ul>
    </div>

    @php
        $pages = $members->chunk(3);
    @endphp

    {{-- =========================================================
         HALAMAN DEPAN
    ========================================================= --}}
    <div class="preview-wrap">
        <div class="preview-label front-label">DEPAN &mdash; {{ $pages->count() }} halaman</div>

        @foreach($pages as $pageIndex => $pageMembers)
            <div class="preview-page">
                <table class="preview-grid" cellpadding="0" cellspacing="0">
                    <tbody>
                        @foreach($pageMembers as $kta)
                            <tr class="preview-grid-row">
                                @php
                                    $member = $kta['member'];
                                    $ketua = $kta['ketua'];
                                    $sekretaris = $kta['sekretaris'];
                                    $ttd_ketua_path = $kta['ttd_ketua_path'] ?? null;
                                    $ttd_sekretaris_path = $kta['ttd_sekretaris_path'] ?? null;
                                    $side = 'front';
                                    $isPdf = false;
                                @endphp
                                {{-- FRONT --}}
                                <td class="preview-grid-cell">
                                    <div class="kta-card-wrapper">
                                        <div class="kta-slot-inner">
                                            @include('print.partials.kta-card-v2')
                                        </div>
                                    </div>
                                </td>
                                {{-- BACK --}}
                                <td class="preview-grid-cell">
                                    <div class="kta-card-wrapper">
                                        <div class="kta-slot-inner">
                                            @php $side = 'back'; @endphp
                                            @include('print.partials.kta-card-v2')
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="page-label" style="position:absolute;bottom:0.2cm;right:0.3cm;font-size:0.22cm;color:#94a3b8;">
                    Hal {{ $pageIndex + 1 }} / {{ $pages->count() }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="page-separator"></div>

    {{-- =========================================================
         HALAMAN BELAKANG
         (reverse order: long-edge duplex flip)
    ========================================================= --}}
    <div class="preview-wrap">
        <div class="preview-label back-label">BELAKANG &mdash; {{ $pages->count() }} halaman</div>

        @foreach($pages as $pageIndex => $pageMembers)
            <div class="preview-page">
                <table class="preview-grid" cellpadding="0" cellspacing="0">
                    <tbody>
                        @foreach($pageMembers->reverse() as $kta)
                            <tr class="preview-grid-row">
                                @php
                                    $member = $kta['member'];
                                    $ketua = $kta['ketua'];
                                    $sekretaris = $kta['sekretaris'];
                                    $ttd_ketua_path = $kta['ttd_ketua_path'] ?? null;
                                    $ttd_sekretaris_path = $kta['ttd_sekretaris_path'] ?? null;
                                    $side = 'front';
                                    $isPdf = false;
                                @endphp
                                {{-- FRONT --}}
                                <td class="preview-grid-cell">
                                    <div class="kta-card-wrapper">
                                        <div class="kta-slot-inner">
                                            @include('print.partials.kta-card-v2')
                                        </div>
                                    </div>
                                </td>
                                {{-- BACK --}}
                                <td class="preview-grid-cell">
                                    <div class="kta-card-wrapper">
                                        <div class="kta-slot-inner">
                                            @php $side = 'back'; @endphp
                                            @include('print.partials.kta-card-v2')
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="page-label" style="position:absolute;bottom:0.2cm;right:0.3cm;font-size:0.22cm;color:#94a3b8;">
                    Hal {{ $pageIndex + 1 }} / {{ $pages->count() }}
                </div>
            </div>
        @endforeach
    </div>

    <form action="{{ route('print.store') }}" method="POST">
        @csrf
        @foreach(request('member_ids', []) as $id)
        <input type="hidden" name="member_ids[]" value="{{ $id }}">
        @endforeach
        <div style="display:flex;gap:1rem;margin-top:1rem;">
            <a href="{{ route('print.create') }}" class="btn btn-outline">Pilih Ulang</a>
            <button type="submit" class="btn btn-primary">Buat Batch Cetak</button>
        </div>
    </form>
</div>
@endsection
