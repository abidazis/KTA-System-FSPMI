<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">

    <title>KTA {{ $member->nik }}</title>

    @include('print.partials.kta-card-style')

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

            align-items: flex-start;

            justify-content: center;

            padding-top: 15mm;
        }


        .pdf-page:last-child {

            page-break-after: auto;
        }


        .pdf-card-wrapper {

            width: 425px;

            height: 661px;
        }


        .kta-card {

            box-shadow: none !important;

            border-radius: 0 !important;
        }

    </style>

</head>

<body>


{{-- ============================================================
     DEPAN
     ============================================================ --}}

<div class="pdf-page">

    <div class="pdf-card-wrapper">

        @php($side = 'front')

        @include('print.partials.kta-card')

    </div>

</div>


{{-- ============================================================
     BELAKANG
     ============================================================ --}}

<div class="pdf-page">

    <div class="pdf-card-wrapper">

        @php($side = 'back')

        @include('print.partials.kta-card')

    </div>

</div>


</body>
</html>