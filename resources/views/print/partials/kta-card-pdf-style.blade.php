<?php
/*
|--------------------------------------------------------------------------
| KTA CARD PDF ADAPTER
|--------------------------------------------------------------------------
| Tujuan : adapter desain asli kta-card.blade.php + kta-card-style.blade.php
|          agar dapat dirender oleh DOMPDF tanpa mengubah desain visual.
|
| Strategi :
| - Menjaga SEMUA class, struktur, dan styling original.
| - Hanya TAMBAH wrapper .kta-pdf-slot dengan ukuran mm yang konsisten.
| - Mengganti unit CSS yang rapuh (px) dengan mm berdasarkan scale factor.
| - Scale factor: 1px = 0.1295 mm (sehingga 425px = 55.05mm dan 661px = 85.6mm).
|
| Sumber kebenaran : print/partials/kta-card-style.blade.php + kta-card.blade.php
|--------------------------------------------------------------------------
*/
?>
<style>
/* Skala: 1px → 0.1295mm (mengikuti rasio asli 425:661 = 55.05:85.6 mm) */
.kta-pdf-slot {
    width: 55.05mm;
    height: 85.6mm;
    overflow: hidden;
    position: relative;
    /* Padding aman supaya konten tidak terpotong saat dicetak */
    padding: 0;
}

/* Override font size dari px → mm-based.
   Original px × 0.1295 = mm (approx). */
.kta-pdf-slot .kta-card {
    width: 55.05mm;
    height: 85.6mm;
    margin: 0;
    padding: 0;
}

.kta-pdf-slot .kta-top-strip {
    height: 5.05mm; /* 39 × 0.1295 */
}
.kta-pdf-slot .kta-top-strip span {
    border-right-width: 0.13mm;
}
.kta-pdf-slot .kta-front-header {
    height: 10.62mm;
    padding: 0 2.07mm;
}
.kta-pdf-slot .kta-logo-left {
    width: 8.55mm;
    height: 8.55mm;
}
.kta-pdf-slot .kta-logo-left img {
    max-width: 8.42mm;
    max-height: 8.42mm;
}
.kta-pdf-slot .kta-title {
    left: 14.89mm;
    top: 1.30mm;
    font-size: 2.33mm;
}
.kta-pdf-slot .kta-logo-right {
    right: 1.81mm;
    top: 1.04mm;
    width: 11.66mm;
    height: 7.12mm;
}
.kta-pdf-slot .kta-logo-right img {
    max-width: 11.66mm;
    max-height: 7.12mm;
}
.kta-pdf-slot .kta-front-body {
    /* height: calc(100% - 121px) */
    height: 69.93mm; /* 85.6 - (5.05 + 10.62) ≈ 69.93mm */
}
.kta-pdf-slot .kta-fields {
    left: 2.59mm;
    top: 3.37mm;
    width: 47.27mm;
}
.kta-pdf-slot .kta-field {
    /* display: grid tidak reliable di DOMPDF → table */
    display: table;
    width: 100%;
    table-layout: fixed;
    min-height: 4.66mm;
    font-size: 1.81mm;
}
.kta-pdf-slot .kta-field > .label,
.kta-pdf-slot .kta-field > .separator,
.kta-pdf-slot .kta-field > .value {
    display: table-cell;
    vertical-align: top;
    padding-top: 0.1mm;
    padding-bottom: 0.1mm;
}
.kta-pdf-slot .kta-field > .label { width: 17.49mm; }
.kta-pdf-slot .kta-field > .separator { width: 1.94mm; text-align: center; }
.kta-pdf-slot .kta-field > .value { word-break: break-word; }
.kta-pdf-slot .kta-field .label { font-size: 0.65mm; opacity: 0.7; }
.kta-pdf-slot .kta-field .separator { font-size: 1.81mm; }
.kta-pdf-slot .kta-field .value { line-height: 2.33mm; }
.kta-pdf-slot .kta-field-spacer { margin-top: 4.40mm; }

/* SWOOSH — pakai mm */
.kta-pdf-slot .kta-swoosh {
    right: 9.71mm;
    top: 26.55mm;
    width: 29.79mm;
    height: 14.25mm;
    /* Hapus transform rotate agar DOMPDF stabil */
    transform: none;
}
.kta-pdf-slot .swoosh {
    width: 29.79mm;
    height: 3.89mm;
    border-top-width: 1.04mm;
    transform: none;
}
.kta-pdf-slot .swoosh-1 { top: 0.65mm; }
.kta-pdf-slot .swoosh-2 { top: 4.14mm; right: 0.52mm; }
.kta-pdf-slot .swoosh-3 { top: 7.64mm; right: 1.04mm; }
.kta-pdf-slot .swoosh-dot {
    width: 2.33mm;
    height: 2.33mm;
}
.kta-pdf-slot .dot-1 { top: 1.55mm; }
.kta-pdf-slot .dot-2 { top: 5.31mm; }

/* SIGNATURE */
.kta-pdf-slot .kta-signature-area {
    left: 2.33mm;
    right: 2.33mm;
    bottom: 2.59mm;
}
.kta-pdf-slot .kta-jakarta { font-size: 1.68mm; margin-bottom: 0.52mm; }
.kta-pdf-slot .kta-pimpinan { font-size: 1.55mm; line-height: 1.94mm; margin-bottom: 1.55mm; }
.kta-pdf-slot .kta-signatures {
    display: table;
    width: 100%;
    table-layout: fixed;
}
.kta-pdf-slot .signature-box {
    display: table-cell;
    vertical-align: bottom;
    width: 50%;
    text-align: center;
    position: relative;
}
.kta-pdf-slot .signature-title { font-size: 1.55mm; margin-bottom: 0.26mm; }
.kta-pdf-slot .signature-image { height: 6.86mm; }
.kta-pdf-slot .signature-image img { max-width: 12.95mm; max-height: 6.48mm; }
.kta-pdf-slot .signature-seal {
    position: absolute;
    left: 50%;
    bottom: 2.20mm;
    /* Hapus translate-x untuk kompatibilitas DOMPDF */
    margin-left: -2.91mm;
    width: 5.83mm;
    height: 5.83mm;
}
.kta-pdf-slot .signature-seal img { width: 100%; height: 100%; }
.kta-pdf-slot .signature-name { font-size: 1.42mm; }

/* BACK */
.kta-pdf-slot .kta-back-top {
    height: 10.88mm;
    border-top-width: 2.85mm;
}
.kta-pdf-slot .back-top-logo {
    width: 11.66mm;
    height: 5.83mm;
    margin-left: 1.94mm;
}
.kta-pdf-slot .back-top-logo img { max-width: 11.01mm; max-height: 5.83mm; }
.kta-pdf-slot .back-top-logo.fspmi { margin-left: 0.52mm; }
.kta-pdf-slot .back-url {
    right: 1.81mm;
    top: 3.24mm;
    font-size: 2.07mm;
}
.kta-pdf-slot .kta-back-body {
    height: 71.87mm;
}
.kta-pdf-slot .kta-dot-pattern {
    background-size: 4.01mm 4.01mm;
}
.kta-pdf-slot .kta-back-photo {
    left: 4.01mm;
    top: 13.60mm;
    width: 22.02mm;
    height: 25.90mm;
    border-width: 0.13mm;
}
.kta-pdf-slot .kta-back-photo img { width: 100%; height: 100%; }
.kta-pdf-slot .kta-ribbon {
    right: -0.52mm;
    top: 5.44mm;
    width: 12.95mm;
    height: 34.97mm;
}
.kta-pdf-slot .ribbon {
    right: -3.24mm;
    width: 17.49mm;
    height: 8.42mm;
    /* Hapus transform rotate */
    transform: none;
    border-top-width: 1.55mm;
    border-bottom-width: 1.55mm;
}
.kta-pdf-slot .ribbon-1 { top: 0; }
.kta-pdf-slot .ribbon-2 { top: 10.62mm; }
.kta-pdf-slot .ribbon-3 { top: 21.24mm; }
.kta-pdf-slot .kta-back-text {
    left: 2.33mm;
    right: 2.33mm;
    bottom: 3.63mm;
    /* Hapus transform rotate(180deg) agar DOMPDF stabil */
    transform: none;
}
.kta-pdf-slot .back-member-title { font-size: 3.11mm; margin-bottom: 0.52mm; }
.kta-pdf-slot .back-description { font-size: 1.30mm; line-height: 1.68mm; }
.kta-pdf-slot .back-logo {
    right: 0.65mm;
    bottom: -1.94mm;
    width: 8.42mm;
    height: 8.42mm;
}
</style>