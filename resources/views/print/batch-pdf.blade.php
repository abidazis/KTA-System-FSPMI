<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Batch {{ $batch->batch_number }} - {{ strtoupper($side ?? 'front') }}</title>
    <style>
        /*
        |--------------------------------------------------------------------------
        | A4 PORTRAIT — 210 × 297 mm
        |--------------------------------------------------------------------------
        | Layout : 2 kolom × 5 baris = 10 KTA per halaman
        | KTA    : 85,6 × 54 mm (asli ID-1)
        | Setelah ROTATE 90° pada setiap slot:
        |   slot width  = 85.6 mm (horizontal extent of rotated card)
        |   slot height = 54 mm  (vertical extent of rotated card)
        |
        | Padding halaman:
        |   width  = (2 × 85.6) + gap  = 171.2 + 4 = 175.2 mm
        |   margin horizontal ≈ (210 − 175.2) / 2 ≈ 17.4 mm
        |   height = (5 × 54) + 4 gap × 4 + margins = 270 + 16 = 286
        |   margin vertikal ≈ (297 − 286) / 2 ≈ 5.5 mm
        |
        | Untuk DOMPDF kompatibilitas, gunakan TABLE dengan width/height eksplisit mm.
        */

        @page {
            size: 210mm 297mm;
            margin: 0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color: #000;
            background: #fff;
        }

        .page {
            width: 210mm;
            padding: 4mm 17.4mm;
            position: relative;
            page-break-after: always;
            overflow: hidden;
        }

        .page:last-of-type {
            page-break-after: auto;
        }

        /* 5 baris × 2 kolom — total height = 5*54 + 4*2 = 278mm (4 rows with 2mm gap) */
        .grid {
            width: 175.2mm;
            height: 282mm;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
        }

        .grid td {
            width: 85.6mm;
            height: 54mm;
            padding: 0.5mm;
            vertical-align: middle;
            text-align: center;
            background: #fff;
        }

        /* Row gap: 2mm → tinggi cell = 54 + 2 = 56mm, kecuali baris terakhir */
        .grid tr { height: 56mm; }
        .grid tr:last-child { height: 54mm; }

        /* Empty slot placeholder */
        .empty-slot {
            width: 83.6mm;
            height: 52mm;
            border: 0.5pt dashed #cbd5e1;
            display: inline-block;
        }

        /* ============================================================
           KTA CARD (compact — pdf version)
           ============================================================
           Karena desain asli menggunakan px/grid/flex yang tidak stabil
           di DOMPDF, kita render versi compact berbasis tabel dengan
           ukuran mm yang sesuai dengan 85.6×54 mm.
        */

        .kta {
            width: 83.6mm;
            height: 52mm;
            position: relative;
            overflow: hidden;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #3b82f6 100%);
            color: #fff;
            border-radius: 1.5mm;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .kta.back {
            background: linear-gradient(135deg, #f47b91 0%, #f5a077 40%, #f9c65c 72%, #ffe11a 100%);
        }

        /* Front-specific */
        .kta .kta-header {
            background: rgba(255,255,255,0.15);
            padding: 1mm 2mm;
            font-size: 5pt;
            font-weight: 700;
            letter-spacing: 0.05em;
            border-bottom: 0.3pt solid rgba(255,255,255,0.3);
            height: 6mm;
            line-height: 4mm;
        }

        .kta .kta-body {
            padding: 1.5mm 2mm;
            font-size: 5pt;
            line-height: 6pt;
            height: 38mm;
        }

        .kta .kta-body .row {
            margin-bottom: 1.2mm;
            display: block;
        }

        .kta .kta-body .row .label {
            font-size: 4pt;
            text-transform: uppercase;
            opacity: 0.75;
            font-weight: 500;
        }

        .kta .kta-body .row .value {
            font-size: 6pt;
            font-weight: 700;
            word-break: break-word;
        }

        .kta .kta-nik {
            position: absolute;
            right: 2mm;
            top: 7mm;
            color: #fbbf24;
            font-size: 5pt;
            font-weight: 700;
        }

        .kta .kta-footer {
            background: rgba(0,0,0,0.2);
            padding: 1mm 2mm;
            font-size: 3.5pt;
            color: rgba(255,255,255,0.85);
            height: 8mm;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            line-height: 4pt;
        }

        .kta .kta-footer .sig-row {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .kta .kta-footer .sig-row > div {
            display: table-cell;
            vertical-align: bottom;
            padding: 0 1mm;
        }

        .kta .kta-footer .sig-name {
            font-weight: 600;
            font-size: 4pt;
        }

        /* Back-specific */
        .kta.back .kta-photo-box {
            width: 22mm;
            height: 28mm;
            background: rgba(255,255,255,0.25);
            border: 0.3pt solid rgba(255,255,255,0.5);
            margin: 1mm auto;
            text-align: center;
            line-height: 28mm;
            font-size: 4pt;
            color: rgba(0,0,0,0.4);
        }

        .kta.back .kta-back-title {
            font-size: 6pt;
            font-weight: 700;
            text-align: center;
            margin: 1mm 0;
            color: #1e3a8a;
        }

        .kta.back .kta-back-desc {
            font-size: 4pt;
            text-align: center;
            font-weight: 600;
            line-height: 5pt;
            padding: 0 2mm;
        }
    </style>
</head>
<body>
    {{--
        DUPLEX BACK ALIGNMENT
        --------------------------------------------------------------
        Untuk flip-on-long-edge (default duplex printer):
        Halaman BACK dibalik horizontal, sehingga posisi KTA ke-1 di
        halaman front adalah KTA ke-10 di halaman back.

        Untuk flip-on-short-edge:
        Halaman BACK dibalik vertikal, sehingga posisi KTA ke-1 di
        halaman front adalah KTA ke-1 di halaman back (mirrored).

        Kami implementasikan kedua mode di bawah, default long-edge.
    --}}

    @php
        $pages = $members->chunk(10);
        $totalCards = $members->count();
        $isFront = ($side ?? 'front') === 'front';
    @endphp

    @foreach($pages as $pageIndex => $pageMembers)
    <div class="page">

        <table class="grid" cellspacing="0" cellpadding="0">
            @php
                $cells = $pageMembers->values()->all();

                // Jika back dan duplex long-edge, reverse urutan kolom + baris
                // sehingga KTA #1 di front = KTA #10 di back ketika dibalik long-edge
                if (!$isFront && ($duplexMode ?? 'long-edge') === 'long-edge') {
                    $cells = array_reverse($cells);
                }
            @endphp

            @for($r = 0; $r < 5; $r++)
                <tr>
                    @for($c = 0; $c < 2; $c++)
                        @php
                            $idx = ($r * 2) + $c;
                            $kta = $cells[$idx] ?? null;
                        @endphp
                        <td>
                            @if($kta)
                                @php $member = $kta['member']; @endphp
                                @if($isFront)
                                <div class="kta">
                                    <div class="kta-header">KARTU TANDA ANGGOTA &nbsp;·&nbsp; FSPMI</div>
                                    <div class="kta-nik">NIK: {{ $member->nik }}</div>
                                    <div class="kta-body">
                                        <div class="row">
                                            <div class="label">Nama</div>
                                            <div class="value" style="font-size:7pt;">{{ strtoupper($member->nama) }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="label">Tempat/Tgl Lahir</div>
                                            <div class="value">{{ $member->tempat_lahir }}, {{ \Carbon\Carbon::parse($member->tanggal_lahir)->format('d/m/Y') }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="label">Alamat</div>
                                            <div class="value">{{ $member->alamat }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="label">JK / Agama</div>
                                            <div class="value">{{ $member->jenis_kelamin }} / {{ $member->agama }}</div>
                                        </div>
                                    </div>
                                    <div class="kta-footer">
                                        <div class="sig-row">
                                            <div>
                                                <div>Ketua Umum</div>
                                                <div class="sig-name">{{ $kta['ketua']?->nama ?? '........' }}</div>
                                            </div>
                                            <div>
                                                <div>Berlaku: {{ \Carbon\Carbon::parse($member->berlaku_hingga)->format('d/m/Y') }}</div>
                                            </div>
                                            <div>
                                                <div>Sekretaris</div>
                                                <div class="sig-name">{{ $kta['sekretaris']?->nama ?? '........' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="kta back">
                                    <div class="kta-header" style="background:rgba(0,0,0,0.1);">KTA &nbsp;·&nbsp; {{ $member->nik }}</div>
                                    <div class="kta-photo-box">FOTO</div>
                                    <div class="kta-back-title">KARTU TANDA ANGGOTA</div>
                                    <div class="kta-back-desc">
                                        SERIKAT PEKERJA AUTOMOTIF<br>
                                        MESIN DAN KOMPONEN<br>
                                        FEDERASI SERIKAT PEKERJA<br>
                                        METAL INDONESIA
                                    </div>
                                    <div class="kta-footer">
                                        <div class="sig-row">
                                            <div>
                                                <div>Dibuat</div>
                                                <div class="sig-name">{{ \Carbon\Carbon::parse($member->tanggal_pembuatan)->format('d/m/Y') }}</div>
                                            </div>
                                            <div>
                                                <div>Berlaku Hingga</div>
                                                <div class="sig-name">{{ \Carbon\Carbon::parse($member->berlaku_hingga)->format('d/m/Y') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @else
                                <div class="empty-slot"></div>
                            @endif
                        </td>
                    @endfor
                </tr>
            @endfor
        </table>

        {{-- Page number footer --}}
        <div style="position:absolute;bottom:2mm;right:5mm;font-size:6pt;color:#64748b;">
            {{ strtoupper($side ?? 'front') }} · Hal {{ $pageIndex + 1 }} / {{ count($pages) }}
            · Batch {{ $batch->batch_number }}
        </div>

    </div>
    @endforeach
</body>
</html>