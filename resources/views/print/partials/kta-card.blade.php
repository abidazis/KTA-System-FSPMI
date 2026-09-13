{{-- ================================================================
     KTA FSPMI - ORIGINAL DESIGN
     Physical size:
     Width  : 54 mm
     Height : 85.6 mm

     $side = front / back
================================================================ --}}

@php
    $side = $side ?? 'front';

    $tanggalLahir = !empty($member->tanggal_lahir)
        ? \Carbon\Carbon::parse($member->tanggal_lahir)->format('d/m/Y')
        : '-';

    $berlakuHingga = !empty($member->berlaku_hingga)
        ? \Carbon\Carbon::parse($member->berlaku_hingga)->format('d/m/Y')
        : '-';

    $isPdf = isset($isPdf) && $isPdf === true;

    if ($isPdf) {
        $logoPuk    = public_path('images/puk-spamk.png');
        $logoFspmi  = public_path('images/fspmi.png');
        $logoPp     = public_path('images/pp-spamk.png');
        $logoKspi   = public_path('images/kspi.png');
        $logoSparta = public_path('images/sparta.png');
    } else {
        $logoPuk    = asset('images/puk-spamk.png');
        $logoFspmi  = asset('images/fspmi.png');
        $logoPp     = asset('images/pp-spamk.png');
        $logoKspi   = asset('images/kspi.png');
        $logoSparta = asset('images/sparta.png');
    }

    /*
    |--------------------------------------------------------------------------
    | IMAGE HELPER
    |--------------------------------------------------------------------------
    */

    $imageSrc = function ($path) use ($isPdf) {
        if (!$path) {
            return null;
        }

        if ($isPdf) {
            if (!file_exists($path)) {
                return null;
            }

            $mime = mime_content_type($path) ?: 'image/png';

            return 'data:' . $mime . ';base64,' .
                base64_encode(file_get_contents($path));
        }

        return $path;
    };

    $logoPukSrc    = $imageSrc($logoPuk);
    $logoFspmiSrc  = $imageSrc($logoFspmi);
    $logoPpSrc     = $imageSrc($logoPp);
    $logoKspiSrc   = $imageSrc($logoKspi);
    $logoSpartaSrc = $imageSrc($logoSparta);

    /*
    |--------------------------------------------------------------------------
    | SIGNATURE
    |--------------------------------------------------------------------------
    */

    $ttdSekretaris = null;

    if (
        !empty($ttd_sekretaris_path) &&
        file_exists($ttd_sekretaris_path)
    ) {
        $ttdSekretaris = $imageSrc($ttd_sekretaris_path);
    }

    $ttdKetua = null;

    if (
        !empty($ttd_ketua_path) &&
        file_exists($ttd_ketua_path)
    ) {
        $ttdKetua = $imageSrc($ttd_ketua_path);
    }

    /*
    |--------------------------------------------------------------------------
    | MEMBER PHOTO
    |--------------------------------------------------------------------------
    */

    $fotoSrc = null;

    if (!empty($member->foto_path)) {
        $absoluteFoto = storage_path('app/' . $member->foto_path);

        if (file_exists($absoluteFoto)) {
            $fotoSrc = $imageSrc($absoluteFoto);
        }
    }
@endphp


@if($side === 'front')

{{-- ================================================================
     FRONT
================================================================ --}}

<div class="kta-card kta-front">

    {{-- TOP GREEN STRIP --}}
    <div class="kta-front-strip">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>

    {{-- HEADER --}}
    <div class="kta-front-header">

        <div class="kta-front-logo-left">
            @if($logoPukSrc)
                <img src="{{ $logoPukSrc }}" alt="PUK SPAMK">
            @endif
        </div>

        <div class="kta-front-title">
            PUK SPAMK FSPMI
        </div>

        <div class="kta-front-logo-right">
            @if($logoFspmiSrc)
                <img src="{{ $logoFspmiSrc }}" alt="FSPMI">
            @endif
        </div>

    </div>

    {{-- BODY --}}
    <div class="kta-front-body">

        {{-- LEFT COLOR BLOCK --}}
        <div class="kta-front-left-block"></div>

        {{-- MEMBER DATA --}}
        <div class="kta-front-fields">

            <div class="kta-row">
                <div class="kta-label">NO. ANGGOTA</div>
                <div class="kta-colon">:</div>
                <div class="kta-value">
                    {{ $member->nik ?? '-' }}
                </div>
            </div>

            <div class="kta-row">
                <div class="kta-label">NAMA</div>
                <div class="kta-colon">:</div>
                <div class="kta-value">
                    {{ strtoupper($member->nama ?? '-') }}
                </div>
            </div>

            <div class="kta-row kta-row-tanggal">
                <div class="kta-label">Tempat/Tgl Lahir</div>
                <div class="kta-colon">:</div>
                <div class="kta-value">
                    {{ $member->tempat_lahir ?? '-' }}, {{ $tanggalLahir }}
                </div>
            </div>

            <div class="kta-row kta-row-alamat">
                <div class="kta-label">Alamat</div>
                <div class="kta-colon">:</div>
                <div class="kta-value">
                    {{ $member->alamat ?? '-' }}
                </div>
            </div>

            <div class="kta-row kta-row-gender">
                <div class="kta-label">Jenis Kelamin</div>
                <div class="kta-colon">:</div>
                <div class="kta-value">
                    {{ $member->jenis_kelamin ?? '-' }}
                </div>
            </div>

            <div class="kta-row">
                <div class="kta-label">Agama</div>
                <div class="kta-colon">:</div>
                <div class="kta-value">
                    {{ $member->agama ?? '-' }}
                </div>
            </div>

            <div class="kta-row">
                <div class="kta-label">Berlaku Hingga</div>
                <div class="kta-colon">:</div>
                <div class="kta-value">
                    {{ $berlakuHingga }}
                </div>
            </div>

        </div>

        {{-- WHITE SWOOSH --}}
        <div class="kta-swoosh">

            <div class="kta-swoosh-line swoosh-1"></div>
            <div class="kta-swoosh-line swoosh-2"></div>
            <div class="kta-swoosh-line swoosh-3"></div>

            <span class="kta-swoosh-dot dot-1"></span>
            <span class="kta-swoosh-dot dot-2"></span>
            <span class="kta-swoosh-dot dot-3"></span>

        </div>

        {{-- SIGNATURE AREA --}}
        <div class="kta-front-signature">

            <div class="kta-jakarta">
                Jakarta,
            </div>

            <div class="kta-pimpinan">
                PIMPINAN PUSAT<br>
                SERIKAT PEKERJA AUTOMOTIF MESIN DAN KOMPONEN<br>
                FEDERASI SERIKAT PEKERJA METAL INDONESIA
            </div>

            <div class="kta-signature-columns">

                {{-- SEKRETARIS --}}
                <div class="kta-signature-column">

                    <div class="kta-signature-title">
                        Sekretaris Umum
                    </div>

                    <div class="kta-signature-space">

                        @if($ttdSekretaris)
                            <img
                                src="{{ $ttdSekretaris }}"
                                alt="Tanda Tangan Sekretaris"
                            >
                        @endif

                        @if($logoPpSrc)
                            <img
                                class="kta-pp-seal"
                                src="{{ $logoPpSrc }}"
                                alt="PP SPAMK"
                            >
                        @endif

                    </div>

                    <div class="kta-signature-name">
                        ( {{ $sekretaris?->nama ?? '........................' }} )
                    </div>

                </div>

                {{-- KETUA --}}
                <div class="kta-signature-column">

                    <div class="kta-signature-title">
                        Ketua Umum
                    </div>

                    <div class="kta-signature-space">

                        @if($ttdKetua)
                            <img
                                src="{{ $ttdKetua }}"
                                alt="Tanda Tangan Ketua"
                            >
                        @endif

                    </div>

                    <div class="kta-signature-name">
                        ( {{ $ketua?->nama ?? '........................' }} )
                    </div>

                </div>

            </div>

        </div>

    </div>
</div>


@else

{{-- ================================================================
     BACK
================================================================ --}}

<div class="kta-card kta-back">

    {{-- TOP BAND --}}
    <div class="kta-back-header">

        <div class="kta-back-pink-top"></div>

        <div class="kta-back-header-content">

            <div class="kta-back-logo kta-back-logo-1">
                @if($logoKspiSrc)
                    <img src="{{ $logoKspiSrc }}" alt="KSPI">
                @endif
            </div>

            <div class="kta-back-logo kta-back-logo-2">
                @if($logoFspmiSrc)
                    <img src="{{ $logoFspmiSrc }}" alt="FSPMI">
                @endif
            </div>

            <div class="kta-back-url">
                www.fspmi.or.id
            </div>

        </div>

    </div>

    {{-- BACK BODY --}}
    <div class="kta-back-body">

        {{-- DOT BACKGROUND --}}
        <div class="kta-back-dots"></div>

        {{-- PHOTO --}}
        <div class="kta-back-photo">
            @if($fotoSrc)
                <img src="{{ $fotoSrc }}" alt="Foto Anggota">
            @endif
        </div>

        {{-- GREEN DIAGONAL RIBBON --}}
        <div class="kta-back-ribbons">

            <div class="kta-back-ribbon ribbon-1"></div>
            <div class="kta-back-ribbon ribbon-2"></div>
            <div class="kta-back-ribbon ribbon-3"></div>

        </div>

        {{-- BACK TEXT --}}
        <div class="kta-back-text">

            <div class="kta-back-title">
                KARTU TANDA ANGGOTA
            </div>

            <div class="kta-back-description">
                SERIKAT PEKERJA AUTOMOTIF MESIN DAN KOMPONEN<br>
                FEDERASI SERIKAT PEKERJA METAL INDONESIA<br>
                (SPAMK-FSPMI)
            </div>

        </div>

        {{-- SPARTA --}}
        <div class="kta-back-sparta">
            @if($logoSpartaSrc)
                <img
                    src="{{ $logoSpartaSrc }}"
                    alt="SPAMK-FSPMI"
                >
            @endif
        </div>

    </div>

</div>

@endif