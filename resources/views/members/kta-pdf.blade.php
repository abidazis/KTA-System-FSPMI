<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">

    <title>KTA {{ $member->nik }}</title>

    <style>
        /*
        |--------------------------------------------------------------------------
        | KTA FSPMI v2 - BACKGROUND IMAGE + DATA OVERLAY
        |--------------------------------------------------------------------------
        | Physical size: PORTRAIT 54mm x 85.6mm (matches background image)
        |--------------------------------------------------------------------------
        */

        * {
            box-sizing: border-box;
        }

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

        /* Card: portrait 54mm x 85.6mm */
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
            font-weight: 700;
            color: #000;
            white-space: nowrap;
            overflow: hidden;
        }

        .kta-field-alamat {
            white-space: normal;
            word-break: break-all;
            overflow-wrap: break-word;
            line-height: 1.3;
        }

        .kta-field-alamat-perusahaan {
            white-space: normal;
            word-break: break-all;
            overflow-wrap: break-word;
            line-height: 1.2;
            padding: 0 1mm;
        }

        .kta-field-small {
            font-weight: 400;
        }

        .kta-field-label {
            font-weight: 700;
            color: #000;
            text-align: right;
        }

        .kta-field-center {
            text-align: center;
            white-space: normal;
        }

        .kta-field-center br {
            display: block;
            content: "";
            margin-top: 1px;
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
    $ttd_ketua_path = $ttd_ketua_path ?? null;
    $ttd_sekretaris_path = $ttd_sekretaris_path ?? null;
@endphp

{{-- DEPAN --}}
<div class="pdf-page">
    <div class="kta-card">
        @php $side = 'front'; @endphp
        @include('print.partials.kta-card-v2')
    </div>
</div>

{{-- BELAKANG --}}
<div class="pdf-page">
    <div class="kta-card">
        @php $side = 'back'; @endphp
        @include('print.partials.kta-card-v2')
    </div>
</div>

</body>
</html>
