@php
    /*
    |--------------------------------------------------------------------------
    | KTA CARD TEMPLATE
    |--------------------------------------------------------------------------
    | $member = data anggota
    | $side   = front / back
    |
    | Asset logo di bawah silakan sesuaikan dengan file logo yang memang
    | sudah tersedia di public/ project.
    |--------------------------------------------------------------------------
    */

    $side = $side ?? 'front';

    // Cari tanggal secara aman
    $tanggalLahir = $member->tanggal_lahir
        ? \Carbon\Carbon::parse($member->tanggal_lahir)->format('d/m/Y')
        : '-';

    $berlakuHingga = $member->berlaku_hingga
        ? \Carbon\Carbon::parse($member->berlaku_hingga)->format('d/m/Y')
        : '-';

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    | Ganti nama file jika nama asset logo di project berbeda.
    |--------------------------------------------------------------------------
    */

    $logoPuk = asset('images/logo-puk-spamk.png');
    $logoFspmi = asset('images/logo-fspmi.png');
    $logoSpami = asset('images/logo-spamk.png');
    $logoPp = asset('images/logo-pp-spamk.png');

@endphp


@if($side === 'front')

<div class="kta-card kta-front">

    {{-- TOP GREEN STRIP --}}
    <div class="kta-top-strip">
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

        <div class="kta-logo-left">
            <img src="{{ $logoPuk }}" alt="PUK SPAMK FSPMI">
        </div>

        <div class="kta-title">
            PUK SPAMK FSPMI
        </div>

        <div class="kta-logo-right">
            <img src="{{ $logoFspmi }}" alt="FSPMI">
        </div>

    </div>


    {{-- BODY --}}
    <div class="kta-front-body">

        <div class="kta-fields">

            <div class="kta-field">
                <span class="label">NIK</span>
                <span class="separator">:</span>
                <span class="value">{{ $member->nik ?? '-' }}</span>
            </div>

            <div class="kta-field">
                <span class="label">NAMA</span>
                <span class="separator">:</span>
                <span class="value">{{ strtoupper($member->nama ?? '-') }}</span>
            </div>

            <div class="kta-field">
                <span class="label">Tempat/Tgl Lahir</span>
                <span class="separator">:</span>
                <span class="value">
                    {{ $member->tempat_lahir ?? '-' }}, {{ $tanggalLahir }}
                </span>
            </div>

            <div class="kta-field">
                <span class="label">Alamat</span>
                <span class="separator">:</span>
                <span class="value">
                    {{ $member->alamat ?? '-' }}
                </span>
            </div>


            <div class="kta-field kta-field-spacer">
                <span class="label">Jenis Kelamin</span>
                <span class="separator">:</span>
                <span class="value">
                    {{ $member->jenis_kelamin ?? '-' }}
                </span>
            </div>

            <div class="kta-field">
                <span class="label">Agama</span>
                <span class="separator">:</span>
                <span class="value">
                    {{ $member->agama ?? '-' }}
                </span>
            </div>

            <div class="kta-field">
                <span class="label">Berlaku Hingga</span>
                <span class="separator">:</span>
                <span class="value">
                    {{ $berlakuHingga }}
                </span>
            </div>

        </div>


        {{-- WHITE FSPMI SWOOSH --}}
        <div class="kta-swoosh">
            <div class="swoosh swoosh-1"></div>
            <div class="swoosh swoosh-2"></div>
            <div class="swoosh swoosh-3"></div>

            <div class="swoosh-dot dot-1"></div>
            <div class="swoosh-dot dot-2"></div>
        </div>


        {{-- SIGNATURE AREA --}}
        <div class="kta-signature-area">

            <div class="kta-jakarta">
                Jakarta,
            </div>

            <div class="kta-pimpinan">
                PIMPINAN PUSAT<br>
                SERIKAT PEKERJA AUTOMOTIF MESIN DAN KOMPONEN<br>
                FEDERASI SERIKAT PEKERJA METAL INDONESIA
            </div>


            <div class="kta-signatures">

                {{-- SEKRETARIS --}}
                <div class="signature-box">

                    <div class="signature-title">
                        Sekretaris Umum
                    </div>

                    <div class="signature-image">
                        @if(!empty($ttd_sekretaris_path) && file_exists($ttd_sekretaris_path))
                            <img
                                src="data:image/png;base64,{{ base64_encode(file_get_contents($ttd_sekretaris_path)) }}"
                                alt="Tanda Tangan Sekretaris">
                        @endif
                    </div>

                    <div class="signature-seal">
                        @if(file_exists(public_path('images/logo-pp-spamk.png')))
                            <img src="{{ asset('images/logo-pp-spamk.png') }}">
                        @endif
                    </div>

                    <div class="signature-name">
                        ( {{ $sekretaris?->nama ?? '........................' }} )
                    </div>

                </div>


                {{-- KETUA --}}
                <div class="signature-box">

                    <div class="signature-title">
                        Ketua Umum
                    </div>

                    <div class="signature-image">
                        @if(!empty($ttd_ketua_path) && file_exists($ttd_ketua_path))
                            <img
                                src="data:image/png;base64,{{ base64_encode(file_get_contents($ttd_ketua_path)) }}"
                                alt="Tanda Tangan Ketua">
                        @endif
                    </div>

                    <div class="signature-name">
                        ( {{ $ketua?->nama ?? '........................' }} )
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


@else

{{-- ============================================================
     BACK SIDE
     ============================================================ --}}

<div class="kta-card kta-back">

    {{-- TOP BAND --}}
    <div class="kta-back-top">

        <div class="back-top-logo">
            <img src="{{ $logoSpami }}" alt="SPAMK">
        </div>

        <div class="back-top-logo fspmi">
            <img src="{{ $logoFspmi }}" alt="FSPMI">
        </div>

        <div class="back-url">
            www.spami.or.id
        </div>

    </div>


    {{-- BACK BODY --}}
    <div class="kta-back-body">

        {{-- DOT PATTERN --}}
        <div class="kta-dot-pattern"></div>


        {{-- PHOTO AREA --}}
        <div class="kta-back-photo">

            @if(method_exists($member, 'hasPhoto') && $member->hasPhoto())

                @php
                    $fotoPath = $member->photo_path ?? null;
                @endphp

                @if($fotoPath)
                    <img
                        src="{{ asset('storage/' . ltrim($fotoPath, '/')) }}"
                        alt="Foto Anggota">
                @endif

            @endif

        </div>


        {{-- RIGHT RIBBON --}}
        <div class="kta-ribbon">

            <div class="ribbon ribbon-1"></div>
            <div class="ribbon ribbon-2"></div>
            <div class="ribbon ribbon-3"></div>

        </div>


        {{-- BACK TEXT --}}
        <div class="kta-back-text">

            <div class="back-member-title">
                KARTU TANDA ANGGOTA
            </div>

            <div class="back-description">
                SERIKAT PEKERJA AUTOMOTIF MESIN DAN KOMPONEN<br>
                FEDERASI SERIKAT PEKERJA METAL INDONESIA<br>
                (SPAMK-FSPMI)
            </div>

            <div class="back-logo">
                <img src="{{ $logoPuk }}" alt="SPAMK">
            </div>

        </div>

    </div>

</div>

@endif