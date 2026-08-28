<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Batch {{ $batch->batch_number }} - KTA FSPMI</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 297mm;
            height: 210mm;
            margin: 0;
            padding: 0;
            background: #fff;
            font-family: Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page {
            position: relative;
            width: 297mm;
            height: 210mm;
            overflow: hidden;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

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
        }

        .kta-photo img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @media print {
            .kta-card {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

@php
    $isPdf = true;
@endphp

{{-- 4 KTA per page: row 1 front + row 2 back --}}
@foreach($members->chunk(4) as $pageIndex => $pageMembers)
<div class="page">

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
                $side = 'front';
            @endphp
            <div style="position:absolute; left:{{ 15 + $i * 69 }}mm; top:15mm;">
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
                $side = 'back';
            @endphp
            <div style="position:absolute; left:{{ 15 + $i * 69 }}mm; top:110mm;">
                @include('print.partials.kta-card-v2')
            </div>
        @endif
    @endforeach

</div>
@endforeach

</body>
</html>
