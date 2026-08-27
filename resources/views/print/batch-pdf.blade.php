<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">

    <title>
        Batch {{ $batch->batch_number }} - KTA FSPMI
    </title>

    <style>

        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 210mm;
            height: 297mm;

            margin: 0;
            padding: 0;

            background: #fff;

            font-family: Arial, Helvetica, sans-serif;

            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }


        /*
        |--------------------------------------------------------------------------
        | A4 PAGE
        |--------------------------------------------------------------------------
        */

        .page {
            position: relative;

            width: 210mm;
            height: 297mm;

            overflow: hidden;

            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }


        /*
        |--------------------------------------------------------------------------
        | 3 KTA PAIRS PER PAGE
        |--------------------------------------------------------------------------
        |
        | Layout (PORTRAIT A4 - cards in portrait):
        |
        | ┌──────┬──────┐
        | │F m1  │B m1  │   Each cell: 54mm x 85.6mm (portrait, matches card)
        | ├──────┼──────┤
        | │F m2  │B m2  │
        | ├──────┼──────┤
        | │F m3  │B m3  │
        | └──────┴──────┘
        |
        | Page: A4 portrait (210mm x 297mm)
        | Total card width: 54 + 54 = 108mm (with gap)
        | Row height: 85.6mm
        | 3 rows: 3 x 85.6 = 256.8mm + 2 x gap (4mm)
        |--------------------------------------------------------------------------
        */

        .kta-page-grid {

            position: absolute;

            /* Center horizontally: (210mm - 108mm) / 2 - half gap = 49mm */
            left: 49mm;

            /* Center vertically: (297mm - 260mm) / 2 = 18.5mm */
            top: 18.5mm;

            width: 112mm;

            height: 261mm;

            display: table;

            table-layout: fixed;

            border-collapse: separate;
            border-spacing: 4mm 0;
        }


        /*
        |--------------------------------------------------------------------------
        | ROW
        |--------------------------------------------------------------------------
        */

        .kta-pair-row {

            display: table-row;

            width: 112mm;

            height: 85.6mm;
        }


        /*
        |--------------------------------------------------------------------------
        | PAIR CELL
        |--------------------------------------------------------------------------
        */

        .kta-pair-cell {

            display: table-cell;

            width: 54mm;

            height: 85.6mm;

            padding: 0;

            vertical-align: middle;

            text-align: center;
        }


        /*
        |--------------------------------------------------------------------------
        | CARD SLOT
        |--------------------------------------------------------------------------
        |
        | Slot is the actual card container, same as card dimensions.
        | Card is portrait: 54mm x 85.6mm. NO rotation needed.
        |--------------------------------------------------------------------------
        */

        .kta-slot {

            position: relative;

            width: 54mm;

            height: 85.6mm;

            margin: 0;

            padding: 0;

            overflow: hidden;
        }


        /*
        |--------------------------------------------------------------------------
        | FRONT / BACK (no left positioning needed, table-cell handles it)
        |--------------------------------------------------------------------------
        */

        .kta-slot-front {
            /* Default */
        }


        .kta-slot-back {
            /* Default */
        }


        /*
        |--------------------------------------------------------------------------
        | SLOT INNER (just the card itself - same dimensions)
        |--------------------------------------------------------------------------
        */

        .kta-slot-inner {

            position: relative;

            width: 54mm;

            height: 85.6mm;

            overflow: hidden;
        }


        /*
        |--------------------------------------------------------------------------
        | REMOVE TABLE ARTIFACTS
        |--------------------------------------------------------------------------
        */

        table {
            border-collapse: collapse;
            border-spacing: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | PAGE LABEL
        |--------------------------------------------------------------------------
        */

        .page-label {
            position: absolute;

            right: 4mm;
            bottom: 3mm;

            font-size: 2mm;

            color: #999;

            display: none;
        }


        /*
        |--------------------------------------------------------------------------
        | PRINT
        |--------------------------------------------------------------------------
        */

        @media print {

            html,
            body {
                width: 210mm;
                height: 297mm;
            }

            .page {
                width: 210mm;
                height: 297mm;

                page-break-after: always;
            }

            .page:last-child {
                page-break-after: auto;
            }

            .kta-page-grid {
                page-break-inside: avoid;
            }

            .kta-pair-cell {
                page-break-inside: avoid;
            }

            .kta-slot-inner {
                page-break-inside: avoid;
            }

        }

        /*
        |--------------------------------------------------------------------------
        | KTA CARD STYLES (PORTRAIT card, rotated inside landscape slot)
        |--------------------------------------------------------------------------
        */

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

        @media print {

            .kta-card {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            html,
            body {
                width: 210mm;
                height: 297mm;
            }

            .page {
                width: 210mm;
                height: 297mm;

                page-break-after: always;
            }

            .page:last-child {
                page-break-after: auto;
            }

            .kta-page-grid {
                page-break-inside: avoid;
            }

            .kta-pair-cell {
                page-break-inside: avoid;
            }

            .kta-slot-inner {
                page-break-inside: avoid;
            }

        }

    </style>

</head>
<body>

@php
    /*
    |--------------------------------------------------------------------------
    | 3 MEMBER PER PAGE
    |--------------------------------------------------------------------------
    */
    $pages = $members->chunk(3);

    // Pass isPdf flag ke partial
    $isPdf = true;

    // Determine which sides to render based on $side parameter
    $sides = ($side ?? 'front') === 'back' ? ['back'] : ['front'];
@endphp


@foreach($pages as $pageIndex => $pageMembers)

    <div class="page">

        <table class="kta-page-grid">

            <tbody>

            @foreach($pageMembers as $kta)

                @php
                    $member = $kta['member'];
                    $ketua = $kta['ketua'];
                    $sekretaris = $kta['sekretaris'];
                    $ttd_ketua_path = $kta['ttd_ketua_path'] ?? null;
                    $ttd_sekretaris_path = $kta['ttd_sekretaris_path'] ?? null;
                @endphp


                <tr class="kta-pair-row">

                    @if(in_array('front', $sides))
                    {{-- FRONT - KIRI --}}
                    <td class="kta-pair-cell">
                        <div class="kta-slot kta-slot-front">
                            <div class="kta-slot-inner">
                                @php $side = 'front'; @endphp
                                @include('print.partials.kta-card-v2')
                            </div>
                        </div>
                    </td>
                    @endif

                    @if(in_array('back', $sides))
                    {{-- BACK - KANAN --}}
                    <td class="kta-pair-cell">
                        <div class="kta-slot kta-slot-back">
                            <div class="kta-slot-inner">
                                @php $side = 'back'; @endphp
                                @include('print.partials.kta-card-v2')
                            </div>
                        </div>
                    </td>
                    @endif

                </tr>

            @endforeach

            {{--
                Bila halaman terakhir kurang dari 3 KTA,
                tidak perlu membuat slot kosong.
            --}}

            </tbody>

        </table>


        <div class="page-label">
            KTA FSPMI
            &middot;
            {{ $batch->batch_number }}
            &middot;
            Halaman {{ $pageIndex + 1 }} / {{ count($pages) }}
        </div>

    </div>

@endforeach

</body>
</html>
