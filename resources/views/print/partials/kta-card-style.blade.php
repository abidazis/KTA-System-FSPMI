<style>

* {
    box-sizing: border-box;
}

.kta-card {
    position: relative;

    /*
    |--------------------------------------------------------------------------
    | Rasio mengikuti template asli
    |--------------------------------------------------------------------------
    */
    width: 425px;
    height: 661px;

    overflow: hidden;

    font-family: Arial, Helvetica, sans-serif;

    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;

    page-break-inside: avoid;
}


/* =========================================================
   FRONT
   ========================================================= */

.kta-front {

    background:
        linear-gradient(
            to bottom,
            #ef9b8c 0%,
            #f1a18d 18%,
            #f5b07c 38%,
            #f8c55c 65%,
            #ffe10b 100%
        );

}


/* TOP GREEN STRIP */

.kta-top-strip {

    height: 39px;

    background: #b8dc16;

    display: flex;

    border-bottom: 1px solid rgba(255,255,255,.4);
}

.kta-top-strip span {

    flex: 1;

    border-right: 1px solid rgba(255,255,255,.35);
}


/* HEADER */

.kta-front-header {

    height: 82px;

    display: flex;

    align-items: center;

    position: relative;

    padding: 0 16px;

}


.kta-logo-left {

    width: 66px;
    height: 66px;

    display: flex;
    align-items: center;
    justify-content: center;
}

.kta-logo-left img {

    max-width: 65px;
    max-height: 65px;

    object-fit: contain;
}


.kta-title {

    position: absolute;

    left: 115px;
    top: 10px;

    font-size: 18px;

    font-weight: 700;

    color: #000;

    white-space: nowrap;
}


.kta-logo-right {

    position: absolute;

    right: 14px;
    top: 8px;

    width: 90px;
    height: 55px;

    display: flex;
    align-items: center;
    justify-content: center;
}

.kta-logo-right img {

    max-width: 90px;
    max-height: 55px;

    object-fit: contain;
}


/* FRONT BODY */

.kta-front-body {

    position: relative;

    height: calc(100% - 121px);
}


/* DATA */

.kta-fields {

    position: absolute;

    left: 20px;
    top: 26px;

    width: 365px;

    z-index: 5;
}


.kta-field {

    display: grid;

    grid-template-columns: 135px 15px 1fr;

    min-height: 36px;

    align-items: start;

    font-size: 14px;

    color: #000;
}


.kta-field .label {

    font-weight: 500;

    white-space: nowrap;
}


.kta-field .separator {

    text-align: center;

    font-weight: 700;
}


.kta-field .value {

    font-weight: 500;

    line-height: 18px;

    word-break: break-word;
}


.kta-field-spacer {

    margin-top: 34px;
}


/* =========================================================
   WHITE SWOOSH
   ========================================================= */

.kta-swoosh {

    position: absolute;

    right: 75px;
    top: 205px;

    width: 230px;
    height: 110px;

    transform: rotate(5deg);

    z-index: 2;

    opacity: .95;
}


.swoosh {

    position: absolute;

    right: 0;

    width: 230px;

    height: 30px;

    border-top: 8px solid #fff;

    border-radius: 50%;

    transform: rotate(5deg);
}


.swoosh-1 {
    top: 5px;
}

.swoosh-2 {
    top: 32px;
    right: 4px;
}

.swoosh-3 {
    top: 59px;
    right: 8px;
}


.swoosh-dot {

    position: absolute;

    width: 18px;
    height: 18px;

    background: #fff;

    border-radius: 50%;

    left: 0;
}


.dot-1 {
    top: 12px;
}

.dot-2 {
    top: 41px;
}


/* =========================================================
   SIGNATURE
   ========================================================= */

.kta-signature-area {

    position: absolute;

    left: 18px;
    right: 18px;
    bottom: 20px;

    text-align: center;

    z-index: 10;
}


.kta-jakarta {

    font-size: 13px;

    margin-bottom: 4px;
}


.kta-pimpinan {

    font-size: 12px;

    font-weight: 700;

    line-height: 15px;

    margin-bottom: 12px;
}


.kta-signatures {

    display: flex;

    justify-content: space-between;

    align-items: flex-end;
}


.signature-box {

    width: 48%;

    position: relative;

    text-align: center;
}


.signature-title {

    font-size: 12px;

    font-weight: 600;

    margin-bottom: 2px;
}


.signature-image {

    height: 53px;

    display: flex;

    align-items: center;

    justify-content: center;
}


.signature-image img {

    max-width: 100px;

    max-height: 50px;

    object-fit: contain;
}


.signature-seal {

    position: absolute;

    left: 50%;

    bottom: 17px;

    transform: translateX(-50%);

    width: 45px;
    height: 45px;
}


.signature-seal img {

    width: 100%;
    height: 100%;

    object-fit: contain;

    opacity: .9;
}


.signature-name {

    font-size: 11px;

    font-weight: 600;
}


/* =========================================================
   BACK
   ========================================================= */

.kta-back {

    background:

        linear-gradient(
            to bottom,
            #f47b91 0%,
            #f5a077 40%,
            #f9c65c 72%,
            #ffe11a 100%
        );

}


/* BACK TOP */

.kta-back-top {

    height: 84px;

    position: relative;

    background: #b9dd17;

    border-top: 22px solid #ed0873;

    border-bottom: 1px solid rgba(255,255,255,.5);

    display: flex;

    align-items: center;
}


.back-top-logo {

    width: 90px;
    height: 45px;

    margin-left: 15px;

    display: flex;

    align-items: center;
    justify-content: center;
}


.back-top-logo img {

    max-width: 85px;
    max-height: 45px;

    object-fit: contain;
}


.back-top-logo.fspmi {

    margin-left: 4px;
}


.back-url {

    position: absolute;

    right: 14px;

    top: 25px;

    font-size: 16px;

    font-weight: 500;

    color: #000;

}


/* BACK BODY */

.kta-back-body {

    position: relative;

    height: calc(100% - 84px);

    overflow: hidden;
}


/* DOT PATTERN */

.kta-dot-pattern {

    position: absolute;

    inset: 0;

    background-image:

        radial-gradient(
            circle,
            rgba(255,255,255,.65) 0 5px,
            transparent 6px
        );

    background-size: 31px 31px;

    opacity: .65;
}


/* PHOTO */

.kta-back-photo {

    position: absolute;

    left: 31px;

    top: 105px;

    width: 170px;

    height: 200px;

    border: 1px solid rgba(80,80,80,.45);

    background: rgba(255,255,255,.05);

    overflow: hidden;

    z-index: 3;
}


.kta-back-photo img {

    width: 100%;
    height: 100%;

    object-fit: cover;
}


/* RIBBON */

.kta-ribbon {

    position: absolute;

    right: -4px;

    top: 42px;

    width: 100px;

    height: 270px;

    overflow: hidden;

    z-index: 4;
}


.ribbon {

    position: absolute;

    right: -25px;

    width: 135px;

    height: 65px;

    background: #b7df19;

    transform: rotate(36deg);

    border-top: 12px solid #f7a2ae;

    border-bottom: 12px solid #f7a2ae;
}


.ribbon-1 {
    top: 0;
}

.ribbon-2 {
    top: 82px;
}

.ribbon-3 {
    top: 164px;
}


/* BACK TEXT */

.kta-back-text {

    position: absolute;

    left: 18px;

    right: 18px;

    bottom: 28px;

    z-index: 5;

    transform: rotate(180deg);

    text-align: center;
}


.back-member-title {

    font-size: 24px;

    font-weight: 700;

    margin-bottom: 4px;
}


.back-description {

    font-size: 10px;

    font-weight: 700;

    line-height: 13px;
}


.back-logo {

    position: absolute;

    right: 5px;

    bottom: -15px;

    width: 65px;

    height: 65px;
}


.back-logo img {

    width: 100%;
    height: 100%;

    object-fit: contain;
}


/* =========================================================
   PRINT
   ========================================================= */

@media print {

    @page {

        size: A4 portrait;

        margin: 0;
    }


    body {

        margin: 0 !important;

        padding: 0 !important;

        background: #fff !important;
    }


    .kta-card {

        box-shadow: none !important;

        border-radius: 0 !important;
    }


    .no-print {

        display: none !important;
    }

}

</style>