/*
|--------------------------------------------------------------------------
| KTA FSPMI - ORIGINAL DESIGN
|--------------------------------------------------------------------------
| Physical size:
| Width  : 54 mm
| Height : 85.6 mm
|--------------------------------------------------------------------------
*/

* {
    box-sizing: border-box;
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


/*
|--------------------------------------------------------------------------
| FRONT
|--------------------------------------------------------------------------
*/

.kta-front {
    background:
        linear-gradient(
            to bottom,
            #ed8d91 0%,
            #f39a91 15%,
            #f7b37f 34%,
            #f9cb63 55%,
            #f9dc28 76%,
            #f7e20b 100%
        );
}


/*
|--------------------------------------------------------------------------
| FRONT TOP GREEN STRIP
|--------------------------------------------------------------------------
*/

.kta-front-strip {
    position: absolute;
    left: 0;
    top: 0;

    width: 100%;
    height: 5mm;

    background: #b9df18;

    display: table;

    border-bottom: 0.2mm solid rgba(255,255,255,.75);
}

.kta-front-strip span {
    display: table-cell;

    width: 14.285%;

    border-right: 0.15mm solid rgba(255,255,255,.65);
}

.kta-front-strip span:last-child {
    border-right: none;
}


/*
|--------------------------------------------------------------------------
| FRONT HEADER
|--------------------------------------------------------------------------
*/

.kta-front-header {
    position: absolute;

    left: 0;
    top: 5mm;

    width: 100%;
    height: 11mm;

    z-index: 20;
}


/* LEFT PUK LOGO */

.kta-front-logo-left {
    position: absolute;

    left: 4mm;
    top: 0.8mm;

    width: 9mm;
    height: 9mm;

    display: flex;
    align-items: center;
    justify-content: center;
}

.kta-front-logo-left img {
    display: block;

    width: 9mm;
    height: 9mm;

    object-fit: contain;
}


/* TITLE */

.kta-front-title {
    position: absolute;

    left: 13mm;
    top: 1.1mm;

    width: 29mm;

    text-align: center;

    font-size: 2.45mm;
    line-height: 3mm;

    font-weight: 700;

    color: #000;

    white-space: nowrap;
}


/* RIGHT FSPMI */

.kta-front-logo-right {
    position: absolute;

    right: 2.8mm;
    top: 0.8mm;

    width: 12mm;
    height: 8mm;

    display: flex;
    align-items: center;
    justify-content: center;
}

.kta-front-logo-right img {
    display: block;

    width: 12mm;
    height: 8mm;

    object-fit: contain;
}


/*
|--------------------------------------------------------------------------
| FRONT BODY
|--------------------------------------------------------------------------
*/

.kta-front-body {
    position: absolute;

    left: 0;
    top: 16mm;

    width: 100%;
    height: calc(100% - 16mm);

    overflow: hidden;
}


/*
|--------------------------------------------------------------------------
| LEFT COLOR BLOCK
|--------------------------------------------------------------------------
*/

.kta-front-left-block {
    position: absolute;

    left: 0;
    top: 5mm;

    width: 25.5mm;
    height: 34mm;

    background:
        linear-gradient(
            to bottom,
            rgba(222,100,82,.22),
            rgba(239,151,72,.17),
            rgba(255,204,58,.05)
        );

    z-index: 1;
}


/*
|--------------------------------------------------------------------------
| MEMBER FIELDS
|--------------------------------------------------------------------------
*/

.kta-front-fields {
    position: absolute;

    left: 4mm;
    top: 0mm;

    width: 45mm;

    z-index: 10;
}

.kta-row {
    display: table;

    width: 100%;

    table-layout: fixed;

    min-height: 5.8mm;

    font-size: 2.15mm;
    line-height: 2.8mm;

    color: #000;
}

.kta-row > div {
    display: table-cell;

    vertical-align: top;
}

.kta-label {
    width: 18mm;

    font-weight: 600;

    white-space: nowrap;
}

.kta-colon {
    width: 2.8mm;

    text-align: center;

    font-weight: 700;
}

.kta-value {
    width: auto;

    font-weight: 500;

    word-break: break-word;
}

.kta-row-tanggal {
    margin-top: 0.5mm;
}

.kta-row-alamat {
    min-height: 8mm;
}

.kta-row-gender {
    margin-top: 8mm;
}


/*
|--------------------------------------------------------------------------
| WHITE SWOOSH
|--------------------------------------------------------------------------
*/

.kta-swoosh {
    position: absolute;

    left: 10mm;
    top: 38mm;

    width: 40mm;
    height: 21mm;

    z-index: 8;
}

.kta-swoosh-line {
    position: absolute;

    left: 0;

    width: 36mm;
    height: 9mm;

    border-top: 1.05mm solid rgba(255,255,255,.94);

    border-radius: 50%;
}

.swoosh-1 {
    top: 0;
    left: 0;

    transform: rotate(3deg);
}

.swoosh-2 {
    top: 4.7mm;
    left: 0.7mm;

    transform: rotate(5deg);
}

.swoosh-3 {
    top: 9.4mm;
    left: 1.4mm;

    transform: rotate(7deg);
}

.kta-swoosh-dot {
    position: absolute;

    width: 2.5mm;
    height: 2.5mm;

    background: #fff;

    border-radius: 50%;
}

.dot-1 {
    left: -0.2mm;
    top: -0.8mm;
}

.dot-2 {
    left: 0.5mm;
    top: 3.9mm;
}

.dot-3 {
    left: 1.2mm;
    top: 8.6mm;
}


/*
|--------------------------------------------------------------------------
| FRONT SIGNATURE
|--------------------------------------------------------------------------
*/

.kta-front-signature {
    position: absolute;

    left: 3mm;
    right: 3mm;
    bottom: 2.4mm;

    text-align: center;

    z-index: 20;
}

.kta-jakarta {
    font-size: 1.95mm;
    line-height: 2.3mm;

    margin-bottom: 0.7mm;
}

.kta-pimpinan {
    font-size: 1.65mm;
    line-height: 2mm;

    font-weight: 700;

    margin-bottom: 1mm;
}

.kta-signature-columns {
    display: table;

    width: 100%;

    table-layout: fixed;
}

.kta-signature-column {
    display: table-cell;

    width: 50%;

    position: relative;

    vertical-align: bottom;

    text-align: center;
}

.kta-signature-title {
    font-size: 1.7mm;
    line-height: 2mm;

    font-weight: 600;
}

.kta-signature-space {
    position: relative;

    height: 8mm;

    display: flex;

    align-items: center;
    justify-content: center;
}

.kta-signature-space > img:not(.kta-pp-seal) {
    max-width: 13mm;
    max-height: 6mm;

    object-fit: contain;
}

.kta-pp-seal {
    position: absolute;

    left: 50%;
    top: 1.8mm;

    transform: translateX(-50%);

    width: 6mm;
    height: 6mm;

    object-fit: contain;

    opacity: .95;
}

.kta-signature-name {
    font-size: 1.55mm;
    line-height: 2mm;

    font-weight: 600;

    white-space: nowrap;
}


/*
|--------------------------------------------------------------------------
| BACK
|--------------------------------------------------------------------------
*/

.kta-back {
    background:
        linear-gradient(
            to bottom,
            #f08494 0%,
            #f28b91 17%,
            #f4a080 34%,
            #f6bc69 53%,
            #f8d63a 76%,
            #f7df12 100%
        );
}


/*
|--------------------------------------------------------------------------
| BACK HEADER
|--------------------------------------------------------------------------
*/

.kta-back-header {
    position: absolute;

    left: 0;
    top: 0;

    width: 100%;
    height: 11mm;

    background: #b9df18;

    z-index: 20;
}

.kta-back-pink-top {
    position: absolute;

    left: 0;
    top: 0;

    width: 100%;
    height: 4.7mm;

    background: #e90073;
}

.kta-back-header-content {
    position: absolute;

    left: 0;
    top: 4.7mm;

    width: 100%;
    height: 6.3mm;

    border-bottom: 0.2mm solid rgba(255,255,255,.8);
}


/*
|--------------------------------------------------------------------------
| BACK HEADER LOGOS
|--------------------------------------------------------------------------
*/

.kta-back-logo {
    position: absolute;

    top: 0.5mm;

    width: 11mm;
    height: 5mm;

    display: flex;

    align-items: center;
    justify-content: center;

    transform: rotate(180deg);
}

.kta-back-logo img {
    width: 11mm;
    height: 5mm;

    object-fit: contain;
}

.kta-back-logo-1 {
    left: 2.5mm;
}

.kta-back-logo-2 {
    left: 14mm;
}


/*
|--------------------------------------------------------------------------
| BACK URL
|--------------------------------------------------------------------------
*/

.kta-back-url {
    position: absolute;

    right: 2.5mm;
    top: 0.7mm;

    font-size: 2.2mm;
    line-height: 2.6mm;

    font-weight: 500;

    color: #000;

    transform: rotate(180deg);

    white-space: nowrap;
}


/*
|--------------------------------------------------------------------------
| BACK BODY
|--------------------------------------------------------------------------
*/

.kta-back-body {
    position: absolute;

    left: 0;
    top: 11mm;

    width: 100%;
    height: calc(100% - 11mm);

    overflow: hidden;
}


/*
|--------------------------------------------------------------------------
| DOT PATTERN
|--------------------------------------------------------------------------
*/

.kta-back-dots {
    position: absolute;

    inset: 0;

    opacity: .55;

    background-image:
        radial-gradient(
            circle,
            rgba(255,244,205,.95) 0,
            rgba(255,244,205,.95) 1.3mm,
            transparent 1.4mm
        );

    background-size: 7mm 7mm;

    z-index: 1;
}


/*
|--------------------------------------------------------------------------
| PHOTO
|--------------------------------------------------------------------------
*/

.kta-back-photo {
    position: absolute;

    left: 4mm;
    top: 8mm;

    width: 20mm;
    height: 27mm;

    border: 0.25mm solid rgba(75,75,75,.55);

    background: rgba(255,255,255,.08);

    overflow: hidden;

    z-index: 5;
}

.kta-back-photo img {
    display: block;

    width: 100%;
    height: 100%;

    object-fit: cover;
}


/*
|--------------------------------------------------------------------------
| DIAGONAL RIBBONS
|--------------------------------------------------------------------------
*/

.kta-back-ribbons {
    position: absolute;

    right: -1mm;
    top: 7mm;

    width: 17mm;
    height: 38mm;

    overflow: hidden;

    z-index: 6;
}

.kta-back-ribbon {
    position: absolute;

    right: -5mm;

    width: 23mm;
    height: 10mm;

    background: #b8df18;

    border-top: 2.1mm solid #f39aaa;
    border-bottom: 2.1mm solid #f39aaa;

    transform: rotate(36deg);
}

.ribbon-1 {
    top: 0;
}

.ribbon-2 {
    top: 12mm;
}

.ribbon-3 {
    top: 24mm;
}


/*
|--------------------------------------------------------------------------
| BACK TEXT
|--------------------------------------------------------------------------
|
| Dibuat terbalik 180 derajat seperti desain asli.
|--------------------------------------------------------------------------
*/

.kta-back-text {
    position: absolute;

    left: 5mm;
    bottom: 6mm;

    width: 42mm;

    text-align: left;

    transform: rotate(180deg);

    transform-origin: center center;

    z-index: 15;
}

.kta-back-title {
    font-size: 4mm;
    line-height: 4.4mm;

    font-weight: 700;

    margin-bottom: 0.7mm;
}

.kta-back-description {
    font-size: 1.55mm;
    line-height: 2mm;

    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| SPARTA LOGO
|--------------------------------------------------------------------------
*/

.kta-back-sparta {
    position: absolute;

    right: 3mm;
    bottom: 3mm;

    width: 8mm;
    height: 8mm;

    z-index: 20;

    transform: rotate(180deg);
}

.kta-back-sparta img {
    display: block;

    width: 8mm;
    height: 8mm;

    object-fit: contain;
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