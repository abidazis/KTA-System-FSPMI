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
    word-break: break-word;
    line-height: 1.3;
}

.kta-field-small {
    font-weight: 400;
}

.kta-field-label {
    font-weight: 700;
    color: #000;
}

.kta-field-center {
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