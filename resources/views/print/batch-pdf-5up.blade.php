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

        /*
        |--------------------------------------------------------------------------
        | KTA CARD - SCALED TO FIT 5 PER A4 LANDSCAPE
        |--------------------------------------------------------------------------
        | Original: 54mm x 85.6mm (portrait)
        | Scaled to: 35.2mm x 55.8mm (65.2% scale factor)
        | Layout: 1 column x 5 rows, centered on A4 landscape (297mm x 210mm)
        |--------------------------------------------------------------------------|
        */
        .kta-card {
            position: relative;
            width: 35.2mm;
            height: 55.8mm;
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
            width: 35.2mm;
            height: 55.8mm;
            z-index: 0;
        }

        .kta-data-layer {
            position: absolute;
            left: 0;
            top: 0;
            width: 35.2mm;
            height: 55.8mm;
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

        .kta-field-center br {
            display: block;
            content: "";
            margin-top: 1px;
        }

        .kta-field-label {
            font-weight: 700;
            color: #000;
            text-align: right;
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
        | BACK PAGE - HORIZONTAL MIRROR
        |--------------------------------------------------------------------------
        | transform: scaleX(-1) flips horizontally around the vertical center axis.
        | This makes the back card a mirror image of the front for duplex printing.
        | The transform origin is center center so the flip is around the card center.
        | DOMPDF supports CSS transforms for basic operations.
        |--------------------------------------------------------------------------
        */
        .back-card-wrapper {
            transform: scaleX(-1);
            -webkit-transform: scaleX(-1);
            transform-origin: center center;
            -webkit-transform-origin: center center;
        }

        /*
        |--------------------------------------------------------------------------
        | AUTO-FIT FIELD (fallback for browser without JS)
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

        @media print {
            .kta-card {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .back-card-wrapper {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

@php
    $isPdf = true;

    /*
    |--------------------------------------------------------------------------
    | LAYOUT GEOMETRY: 5 KTA per A4 Landscape (297mm x 210mm)
    |--------------------------------------------------------------------------
    |
    | A4 Landscape: 297mm (width) x 210mm (height)
    |
    | Card (scaled to fit 5 in column): 35.2mm x 55.8mm
    |   Scale factor: 65.2% from original 54mm x 85.6mm
    |   Aspect ratio: 35.2/55.8 = 0.631 vs original 54/85.6 = 0.631 ✓
    |
    | Layout: 1 column, 5 rows vertically centered
    |   - Top margin: 5mm
    |   - Bottom margin: 5mm
    |   - 5 cards x 55.8mm = 279mm
    |   - 4 gaps x 2mm = 8mm
    |   - Total: 287mm = 210 - 5 - 5 (margins) ✓
    |
    | Card positions (top): 5, 62.8, 120.6, 178.4, 236.2mm
    | Card left: 130.9mm (horizontally centered on 297mm page)
    | Back mirror: transform: scaleX(-1) on .back-card-wrapper
    |
    | PAGINATION:
    |   - Page 1: Front 1-5
    |   - Page 2: Back 1-5 (mirrored)
    |   - Page 3: Front 6-10
    |   - Page 4: Back 6-10 (mirrored)
    |   - etc.
    |
    */

    // Card dimensions (scaled 65.2% from original 54mm x 85.6mm)
    $cardWidth = 35.2;  // mm
    $cardHeight = 55.8; // mm
    $topMargin = 5;     // mm
    $gap = 2;           // mm between cards

    // Calculate card top positions
    $cardTops = [];
    for ($i = 0; $i < 5; $i++) {
        $cardTops[$i] = $topMargin + $i * ($cardHeight + $gap);
    }

    // Center the column horizontally on A4 landscape (297mm wide)
    $pageWidth = 297;
    $cardLeft = ($pageWidth - $cardWidth) / 2;

    // Scale factor for the card content (field positions)
    $scaleFactor = 0.652; // 35.2/54 = 0.652

    // Members per batch = 5
    $batchSize = 5;
@endphp

{{-- ================================================================
     PAGINATION: 5 KTA per page, all FRONT or all BACK per page
     Pages: 1(front 1-5), 2(back 1-5), 3(front 6-10), 4(back 6-10), ...
================================================================ --}}
@foreach($members->chunk($batchSize) as $batchIndex => $batchMembers)
@php
    $batchMembersArray = $batchMembers->values()->all();
    $memberCount = count($batchMembersArray);

    // For duplex, the back page needs cards in the SAME ORDER as front
    // (no reversal needed since each physical page = one side only)
    // The mirror transform handles the horizontal flip for physical alignment
@endphp

{{-- ================================================================
     PAGE {{ $batchIndex * 2 + 1 }}: FRONT side (all 5 cards)
================================================================ --}}
<div class="page">
    @foreach([0, 1, 2, 3, 4] as $i)
        @if(isset($batchMembersArray[$i]))
            @php
                $kta = $batchMembersArray[$i];
                $member = $kta['member'];
                $ketua = $kta['ketua'];
                $sekretaris = $kta['sekretaris'];
                $ttd_ketua_path = $kta['ttd_ketua_path'] ?? null;
                $ttd_sekretaris_path = $kta['ttd_sekretaris_path'] ?? null;
                $foto_path = $kta['foto_path'] ?? null;
                $stempel_path = $kta['stempel_path'] ?? null;
                $side = 'front';
                $cardScale = $scaleFactor;
            @endphp
            <div style="position:absolute; left:{{ $cardLeft }}mm; top:{{ $cardTops[$i] }}mm; width:{{ $cardWidth }}mm; height:{{ $cardHeight }}mm; overflow:hidden;">
                @include('print.partials.kta-card-v2-scaled')
            </div>
        @endif
    @endforeach
</div>

{{-- ================================================================
     PAGE {{ $batchIndex * 2 + 2 }}: BACK side (all 5 cards, mirrored)
================================================================ --}}
<div class="page">
    @foreach([0, 1, 2, 3, 4] as $i)
        @if(isset($batchMembersArray[$i]))
            @php
                $kta = $batchMembersArray[$i];
                $member = $kta['member'];
                $ketua = $kta['ketua'];
                $sekretaris = $kta['sekretaris'];
                $ttd_ketua_path = $kta['ttd_ketua_path'] ?? null;
                $ttd_sekretaris_path = $kta['ttd_sekretaris_path'] ?? null;
                $foto_path = $kta['foto_path'] ?? null;
                $stempel_path = $kta['stempel_path'] ?? null;
                $side = 'back';
                $cardScale = $scaleFactor;
            @endphp
            <div class="back-card-wrapper" style="position:absolute; left:{{ $cardLeft }}mm; top:{{ $cardTops[$i] }}mm; width:{{ $cardWidth }}mm; height:{{ $cardHeight }}mm; overflow:hidden;">
                @include('print.partials.kta-card-v2-scaled')
            </div>
        @endif
    @endforeach
</div>

@endforeach

</body>
</html>
