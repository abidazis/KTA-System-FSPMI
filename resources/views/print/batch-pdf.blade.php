<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Batch {{ $batch->batch_number }} - {{ strtoupper($side) }}</title>
    <style>
        @page {
            size: 210mm 297mm;
            margin: 0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page {
            width: 210mm;
            padding: 0;
            position: relative;
            page-break-after: always;
        }

        .page:last-of-type { page-break-after: auto; }

        /* Grid TABEL 2x5 = 10 KTA per halaman.
           Lebar grid = 2 × 85.6 + 1 × 4 = 175.2 mm
           Margin horizontal = (210 - 175.2) / 2 = 17.4 mm
           Tinggi grid = 5 × 54 + 4 × 2 = 278 mm
           Margin vertikal = (297 - 278) / 2 = 9.5 mm
        */
        .grid {
            width: 175.2mm;
            height: 278mm;
            margin: 9.5mm 17.4mm;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
        }

        .grid td {
            width: 85.6mm;
            height: 54mm;
            padding: 0;
            vertical-align: middle;
            text-align: center;
            background: #fff;
        }

        .grid tr { height: 56mm; }      /* 54 + 2 row gap */
        .grid tr:last-child { height: 54mm; }

        /* Slot wrapper: rotate 90° sehingga portrait design (55x85.6) jadi landscape (85.6x55) */
        .kta-slot {
            width: 85.6mm;
            height: 54mm;
            position: relative;
            overflow: hidden;
        }

        /* Container rotated 90° ke kanan dengan center origin */
        .kta-slot-inner {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 54mm;
            height: 85.6mm;
            margin-left: -27mm;
            margin-top: -42.8mm;
            transform: rotate(90deg);
            transform-origin: center center;
        }

        .empty-slot {
            width: 85.6mm;
            height: 54mm;
            display: inline-block;
            border: 0.3pt dashed #cbd5e1;
        }

        .page-label {
            position: absolute;
            top: 2mm;
            right: 3mm;
            font-size: 2.2mm;
            color: #94a3b8;
            letter-spacing: 0.05em;
        }
    </style>

    @include('print.partials.kta-card-style')
    @include('print.partials.kta-card-pdf-style')
</head>
<body>
    @php
        $pages = $members->chunk(10);
    @endphp

    @foreach($pages as $pageIndex => $pageMembers)
        @php
            $cells = $pageMembers->values()->all();

            // Duplex ordering untuk back side
            if ($side === 'back') {
                $cells = \App\Support\PrintOrder::reorderForDuplex(
                    $cells,
                    $duplexMode ?? 'long-edge'
                );
            }
        @endphp

        <div class="page">
            <table class="grid" cellspacing="0" cellpadding="0">
                @for($r = 0; $r < 5; $r++)
                    <tr>
                        @for($c = 0; $c < 2; $c++)
                            @php
                                $idx = ($r * 2) + $c;
                                $kta = $cells[$idx] ?? null;
                            @endphp
                            <td>
                                @if($kta)
                                    <div class="kta-slot">
                                        <div class="kta-slot-inner">
                                            @php
                                                $member = $kta['member'];
                                            @endphp
                                            @include('print.partials.kta-card-pdf')
                                        </div>
                                    </div>
                                @else
                                    <div class="empty-slot"></div>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endfor
            </table>

            <div class="page-label">
                {{ strtoupper($side) }} &middot; {{ $batch->batch_number }} &middot; Hal {{ $pageIndex + 1 }} / {{ count($pages) }}
            </div>
        </div>
    @endforeach
</body>
</html>