<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">

    <title>KTA {{ $member->nik }}</title>

    <style>

        @page {
            size: A4 portrait;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .pdf-page {
            width: 210mm;
            min-height: 297mm;
            position: relative;
            page-break-after: always;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pdf-page:last-child {
            page-break-after: auto;
        }

        /* Card: landscape 85.6mm x 54mm */
        .kta-card-wrapper {
            width: 85.6mm;
            height: 54mm;
            position: relative;
            overflow: hidden;
        }

    </style>

    @include('print.partials.kta-card-v2-style')

</head>
<body>

@php
    $isPdf = true;
    $ttd_ketua_path = $ttd_ketua_path ?? null;
    $ttd_sekretaris_path = $ttd_sekretaris_path ?? null;
@endphp

{{-- DEPAN --}}
<div class="pdf-page">
    <div class="kta-card-wrapper">
        @php $side = 'front'; @endphp
        @include('print.partials.kta-card-v2')
    </div>
</div>

{{-- BELAKANG --}}
<div class="pdf-page">
    <div class="kta-card-wrapper">
        @php $side = 'back'; @endphp
        @include('print.partials.kta-card-v2')
    </div>
</div>

</body>
</html>
