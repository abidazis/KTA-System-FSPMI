<style>
    /*
    |--------------------------------------------------------------------------
    | KTA PDF CARD WRAPPER
    |--------------------------------------------------------------------------
    */

    .kta-pdf-card {
        position: relative;

        width: 54mm;
        height: 85.6mm;

        margin: 0;
        padding: 0;

        overflow: hidden;

        page-break-inside: avoid;

        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .kta-pdf-card .kta-card {
        position: relative;

        width: 54mm;
        height: 85.6mm;

        margin: 0;
        padding: 0;

        overflow: hidden;
    }

    @media print {

        .kta-pdf-card,
        .kta-pdf-card .kta-card {
            page-break-inside: avoid;

            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

    }
</style>