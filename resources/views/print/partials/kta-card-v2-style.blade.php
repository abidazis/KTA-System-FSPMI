/*
|--------------------------------------------------------------------------
| KTA FSPMI v2 - BACKGROUND IMAGE + DATA OVERLAY
|--------------------------------------------------------------------------
| Physical size: PORTRAIT 54mm x 85.6mm (matches background image)
| Card is rotated -90deg (CCW) inside landscape print slot (85.6mm x 54mm)
|--------------------------------------------------------------------------
*/

* {
    box-sizing: border-box;
}

.kta-card {
    position: relative;

    /* Portrait: 54mm wide x 85.6mm tall */
    width: 54mm;
    height: 85.6mm;

    margin: 0;
    padding: 0;
    overflow: hidden;

    font-family: Arial, Helvetica, sans-serif;

    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}


/*
|--------------------------------------------------------------------------
| BACKGROUND IMAGE
|--------------------------------------------------------------------------
| Background image is portrait (54mm x 85.6mm) - matches card natively.
| No rotation needed; coordinates stay in portrait space.
|--------------------------------------------------------------------------
*/

.kta-bg-image {
    position: absolute;

    left: 0;
    top: 0;

    width: 54mm;
    height: 85.6mm;

    z-index: 0;
}


/*
|--------------------------------------------------------------------------
| DATA OVERLAY LAYER
|--------------------------------------------------------------------------
*/

.kta-data-layer {
    position: absolute;

    left: 0;
    top: 0;

    width: 54mm;
    height: 85.6mm;

    z-index: 1;
}


/*
|--------------------------------------------------------------------------
| FIELD - ABSOLUTE POSITIONING (in portrait card space)
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| AUTO-FIT FIELD (NAMA, NIK on front side)
|--------------------------------------------------------------------------
| Teks yang panjang akan di-scale secara horizontal menggunakan
| CSS transform (via JS) agar muat di container. Pendek tetap pada
| ukuran semula. Untuk PDF (tanpa JS), font-size sudah dikecilkan
| di template agar teks 16 digit NIK tidak terpotong.
| Fallback text-overflow: ellipsis untuk browser tanpa JS support.
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


/*
|--------------------------------------------------------------------------
| SIGNATURE IMAGE
|--------------------------------------------------------------------------
*/

.kta-signature {
    position: absolute;

    object-fit: contain;

    opacity: .95;
}


/*
|--------------------------------------------------------------------------
| SEAL / STAMP
|--------------------------------------------------------------------------
*/

.kta-seal {
    position: absolute;

    object-fit: contain;

    opacity: .95;
}


/*
|--------------------------------------------------------------------------
| PHOTO (BACK SIDE)
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| PRINT
|--------------------------------------------------------------------------
*/

@media print {

    .kta-card {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

}


/*
|--------------------------------------------------------------------------
| AUTO-FIT JS (browser preview only)
|--------------------------------------------------------------------------
| Skala horizontal text (transform: scaleX) untuk NAMA & NIK yang
| panjang sehingga muat di container. Pendek tetap ukuran normal.
| Untuk PDF (DomPDF, tanpa JS), font-size 2.4mm sudah aman untuk
| NIK 16 digit standar Indonesia.
|--------------------------------------------------------------------------
*/

(function () {
    if (typeof document === 'undefined') return;

    function fitField(el) {
        if (!el || !el.scrollWidth || !el.clientWidth) return;
        var overflow = el.scrollWidth - el.clientWidth;
        if (overflow > 0.5) {
            // teks melebihi container → scale down horizontal
            var ratio = el.clientWidth / el.scrollWidth;
            // safety clamp supaya font tidak kekecilan
            if (ratio < 0.6) ratio = 0.6;
            el.style.transform = 'scaleX(' + ratio.toFixed(3) + ')';
            el.setAttribute('data-autofit-scaled', 'true');
        }
    }

    function fitAll() {
        var nodes = document.querySelectorAll('[data-autofit="true"]');
        for (var i = 0; i < nodes.length; i++) {
            fitField(nodes[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fitAll);
    } else {
        fitAll();
    }
    // Jalankan ulang setelah font web load (jaga-jaga)
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(fitAll);
    }
    // Jalankan ulang saat window load (gambar selesai)
    window.addEventListener('load', fitAll);
})();