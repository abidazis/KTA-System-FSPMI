@extends('layouts.app')

@prepend('styles')
<style>
/*
|--------------------------------------------------------------------------
| PREVIEW PAGE - 4 KTA PER PAGE (A4 LANDSCAPE)
| Layout: A4 landscape
| Row 1: 4 KTA DEPAN berjejer horizontal
| Row 2: 4 KTA BELAKANG berjejer horizontal
|--------------------------------------------------------------------------
*/

.preview-container {
    background: #fff;
    padding: 1rem;
    margin-bottom: 2rem;
}

.info-banner {
    background: #dbeafe;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
    font-size: 0.875rem;
}

.page-wrapper {
    display: flex;
    justify-content: center;
    margin-bottom: 2rem;
}

.preview-page {
    width: 29.7cm;
    height: 21cm;
    position: relative;
    background: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    border-radius: 4px;
    overflow: visible;
}

.kta-card {
    position: relative;
    width: 5.4cm;
    height: 8.56cm;
    overflow: visible;
    font-family: Arial, Helvetica, sans-serif;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.kta-bg-image {
    position: absolute;
    left: 0;
    top: 0;
    width: 5.4cm;
    height: 8.56cm;
    z-index: 0;
}

.kta-data-layer {
    position: absolute;
    left: 0;
    top: 0;
    width: 5.4cm;
    height: 8.56cm;
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
}

.kta-photo img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/*
|--------------------------------------------------------------------------
| AUTO-FIT untuk NAMA & NIK yang panjang (front side)
|--------------------------------------------------------------------------
*/
.kta-field-autofit {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: clip;
    transform-origin: left center;
    line-height: 1;
}

.kta-field-autofit-center {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: clip;
    transform-origin: center center;
    line-height: 1;
    text-align: center;
}

.page-label {
    position: absolute;
    bottom: 0.3cm;
    right: 0.5cm;
    font-size: 0.22cm;
    color: #94a3b8;
}
</style>
@endprepend

@prepend('scripts')
<script>
(function () {
    function fitField(el) {
        if (!el || !el.scrollWidth || !el.clientWidth) return;
        var overflow = el.scrollWidth - el.clientWidth;
        if (overflow > 0.5) {
            var ratio = el.clientWidth / el.scrollWidth;
            if (ratio < 0.6) ratio = 0.6;
            el.style.transform = 'scaleX(' + ratio.toFixed(3) + ')';
        }
    }
    function fitAll() {
        var nodes = document.querySelectorAll('[data-autofit="true"]');
        for (var i = 0; i < nodes.length; i++) fitField(nodes[i]);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fitAll);
    } else {
        fitAll();
    }
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(fitAll);
    window.addEventListener('load', fitAll);
})();
</script>
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
        <strong>Format Cetak:</strong>
        <ul style="margin:0.5rem 0 0 1.5rem;">
            <li>A4 Landscape — <strong>4 KTA per halaman</strong> (4 depan + 4 belakang)</li>
            <li>Baris atas: <strong>DEPAN</strong> · Baris bawah: <strong>BELAKANG</strong></li>
            <li>Cetak dengan ukuran <strong>Actual Size / 100%</strong></li>
        </ul>
    </div>

    @php
        $isPdf = false;
    @endphp

    {{-- 4 KTA per page: row 1 front + row 2 back --}}
    @foreach($members->chunk(4) as $pageIndex => $pageMembers)
        <div class="page-wrapper">
            <div class="preview-page">

                {{-- ROW 1: 4 KTA DEPAN berjejer horizontal --}}
                @foreach([0, 1, 2, 3] as $i)
                    @if(isset($pageMembers[$i]))
                        @php
                            $kta = $pageMembers[$i];
                            $member = $kta['member'];
                            $ketua = $kta['ketua'];
                            $sekretaris = $kta['sekretaris'];
                            $ttd_ketua_path = $kta['ttd_ketua_path'] ?? null;
                            $ttd_sekretaris_path = $kta['ttd_sekretaris_path'] ?? null;
                            $foto_path = $kta['foto_path'] ?? null;
                            $stempel_path = $kta['stempel_path'] ?? null;
                            $side = 'front';
                        @endphp
                        <div style="position:absolute; left:{{ 1.5 + $i * 6.9 }}cm; top:1.5cm;">
                            @include('print.partials.kta-card-v2')
                        </div>
                    @endif
                @endforeach

                {{-- ROW 2: 4 KTA BELAKANG berjejer horizontal --}}
                @foreach([0, 1, 2, 3] as $i)
                    @if(isset($pageMembers[$i]))
                        @php
                            $kta = $pageMembers[$i];
                            $member = $kta['member'];
                            $ketua = $kta['ketua'];
                            $sekretaris = $kta['sekretaris'];
                            $ttd_ketua_path = $kta['ttd_ketua_path'] ?? null;
                            $ttd_sekretaris_path = $kta['ttd_sekretaris_path'] ?? null;
                            $foto_path = $kta['foto_path'] ?? null;
                            $stempel_path = $kta['stempel_path'] ?? null;
                            $side = 'back';
                        @endphp
                        <div style="position:absolute; left:{{ 1.5 + $i * 6.9 }}cm; top:11cm;">
                            @include('print.partials.kta-card-v2')
                        </div>
                    @endif
                @endforeach

                <div class="page-label">Halaman {{ $pageIndex + 1 }}</div>
            </div>
        </div>
    @endforeach

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
