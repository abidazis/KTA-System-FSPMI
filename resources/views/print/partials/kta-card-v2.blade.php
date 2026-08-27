{{-- ================================================================
     KTA FSPMI - BACKGROUND IMAGE + DATA OVERLAY
     Physical size (PORTRAIT = print-ready orientation):
     Width  : 54 mm
     Height : 85.6 mm
     Background images are portrait (54mm x 85.6mm) - matches card natively

     $side = front / back
     $background = KtaBackground instance or null
================================================================ --}}

@php
    $side = $side ?? 'front';
    $isPdf = $isPdf ?? false;

    /*
    |--------------------------------------------------------------------------
    | IMAGE HELPER
    |--------------------------------------------------------------------------
    */
    $imageSrc = function ($path) use ($isPdf) {
        if (!$path || !file_exists($path)) {
            return null;
        }
        $mime = mime_content_type($path) ?: 'image/png';
        // Always use base64 for reliability (works in both browser and DomPDF)
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    };

    /*
    |--------------------------------------------------------------------------
    | BACKGROUND IMAGE
    |--------------------------------------------------------------------------
    */
    $bgImagePath = null;
    $bgSrc = null;
    if ($background) {
        if ($side === 'front') {
            $bgImagePath = $background->getFrontImagePath();
        } else {
            $bgImagePath = $background->getBackImagePath();
        }
        if ($bgImagePath) {
            $bgSrc = $imageSrc($bgImagePath);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MEMBER DATA
    |--------------------------------------------------------------------------
    */
    $tanggalLahir = !empty($member->tanggal_lahir)
        ? \Carbon\Carbon::parse($member->tanggal_lahir)->format('d/m/Y')
        : '-';

    $berlakuHingga = !empty($member->berlaku_hingga)
        ? \Carbon\Carbon::parse($member->berlaku_hingga)->format('d/m/Y')
        : '-';

    /*
    |--------------------------------------------------------------------------
    | SIGNATURE
    |--------------------------------------------------------------------------
    */
    $ttdSekretaris = null;
    if (!empty($ttd_sekretaris_path) && file_exists($ttd_sekretaris_path)) {
        $ttdSekretaris = $imageSrc($ttd_sekretaris_path);
    }

    $ttdKetua = null;
    if (!empty($ttd_ketua_path) && file_exists($ttd_ketua_path)) {
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

    /*
    |--------------------------------------------------------------------------
    | FIELD POSITIONS (mm from top-left of PORTRAIT card: 54mm x 85.6mm)
    | Unit: mm (milimeter) - sesuai ukuran fisik KTA
    |--------------------------------------------------------------------------
    |
    | Background image is portrait (54mm x 85.6mm).
    | Card is portrait (54mm x 85.6mm).
    | Direct mapping: (x_p, y_p) → (x_p, y_p)
    |
    | Layout based on background label positions:
    | Labels are in left half of card (x ~ 22-26mm).
    | Values appear to the right of each label.
    |
    | Estimated label Y positions (mm from top):
    | NIK: y=21
    | NAMA: y=27
    | TTL: y=33
    | Alamat: y=39 (multi-line)
    | JK: y=49
    | Agama: y=49 (right of JK)
    | Berlaku: y=55
    |
    | Signature area: bottom of card (y=70-85)
    |
    */

    $positions = [
        'front' => [
            // NIK - value to right of label
            'nik'           => ['left' => 26, 'top' => 21, 'font_size' => 2.5, 'width' => 27],
            // NAMA - value to right of label
            'nama'          => ['left' => 26, 'top' => 27, 'font_size' => 2.5, 'width' => 27],
            // TTL - value to right of label
            'ttl'           => ['left' => 26, 'top' => 33, 'font_size' => 2.3, 'width' => 27],
            // Alamat - value to right of label, may wrap
            'alamat'        => ['left' => 26, 'top' => 39, 'font_size' => 2.0, 'width' => 27, 'height' => 9],
            // Jenis Kelamin - value to right of label
            'jk'            => ['left' => 26, 'top' => 49, 'font_size' => 2.3],
            // Agama - right side
            'agama'         => ['left' => 41, 'top' => 49, 'font_size' => 2.3, 'width' => 12],
            // Berlaku - value to right of label
            'berlaku'       => ['left' => 26, 'top' => 55, 'font_size' => 2.3],

            // Tanggal - top of signature block
            'tanggal_ttd'   => ['left' => 5, 'top' => 65, 'font_size' => 1.9, 'width' => 22],
            // TTD Sekretaris - left signature box
            'ttd_sekretaris'  => ['left' => 5, 'top' => 67, 'width' => 16, 'height' => 8],
            'nama_sekretaris' => ['left' => 5, 'top' => 76, 'font_size' => 1.5, 'width' => 22],
            // TTD Ketua - right signature box
            'ttd_ketua'     => ['left' => 31, 'top' => 67, 'width' => 16, 'height' => 8],
            'nama_ketua'    => ['left' => 31, 'top' => 76, 'font_size' => 1.5, 'width' => 22],
        ],
        'back' => [
            // Photo placeholder area on back card
            'foto'          => ['left' => 8, 'top' => 25, 'width' => 22, 'height' => 28],
        ],
    ];

    $fieldPositions = $positions[$side] ?? [];
@endphp


@if($side === 'front')
{{-- ================================================================
     FRONT
================================================================ --}}

<div class="kta-card kta-front">

    {{-- BACKGROUND --}}
    @if($bgSrc)
        <img class="kta-bg-image" src="{{ $bgSrc }}" alt="">
    @endif

    {{-- DATA OVERLAY --}}
    <div class="kta-data-layer">

        {{-- NAMA --}}
        @if(isset($fieldPositions['nama']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['nama']['left'] }}mm;
                        top:{{ $fieldPositions['nama']['top'] }}mm;
                        font-size:{{ $fieldPositions['nama']['font_size'] }}mm;">
                {{ strtoupper($member->nama ?? '-') }}
            </div>
        @endif

        {{-- NIK --}}
        @if(isset($fieldPositions['nik']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['nik']['left'] }}mm;
                        top:{{ $fieldPositions['nik']['top'] }}mm;
                        font-size:{{ $fieldPositions['nik']['font_size'] }}mm;">
                {{ $member->nik ?? '-' }}
            </div>
        @endif

        {{-- TEMPAT/TGL LAHIR --}}
        @if(isset($fieldPositions['ttl']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['ttl']['left'] }}mm;
                        top:{{ $fieldPositions['ttl']['top'] }}mm;
                        font-size:{{ $fieldPositions['ttl']['font_size'] }}mm;">
                {{ $member->tempat_lahir ?? '-' }}, {{ $tanggalLahir }}
            </div>
        @endif

        {{-- ALAMAT --}}
        @if(isset($fieldPositions['alamat']))
            <div class="kta-field kta-field-alamat"
                 style="left:{{ $fieldPositions['alamat']['left'] }}mm;
                        top:{{ $fieldPositions['alamat']['top'] }}mm;
                        font-size:{{ $fieldPositions['alamat']['font_size'] }}mm;
                        width:{{ $fieldPositions['alamat']['width'] ?? 40 }}mm;">
                {{ $member->alamat ?? '-' }}
            </div>
        @endif

        {{-- JENIS KELAMIN --}}
        @if(isset($fieldPositions['jk']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['jk']['left'] }}mm;
                        top:{{ $fieldPositions['jk']['top'] }}mm;
                        font-size:{{ $fieldPositions['jk']['font_size'] }}mm;">
                {{ $member->jenis_kelamin ?? '-' }}
            </div>
        @endif

        {{-- AGAMA --}}
        @if(isset($fieldPositions['agama']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['agama']['left'] }}mm;
                        top:{{ $fieldPositions['agama']['top'] }}mm;
                        font-size:{{ $fieldPositions['agama']['font_size'] }}mm;">
                {{ $member->agama ?? '-' }}
            </div>
        @endif

        {{-- BERLAKU HINGGA --}}
        @if(isset($fieldPositions['berlaku']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['berlaku']['left'] }}mm;
                        top:{{ $fieldPositions['berlaku']['top'] }}mm;
                        font-size:{{ $fieldPositions['berlaku']['font_size'] }}mm;">
                {{ $berlakuHingga }}
            </div>
        @endif

        {{-- LOGO PUK --}}
        @if(isset($fieldPositions['logo_puk']))
            @php
                $logoPukPath = public_path('images/puk-spamk.png');
                if ($isPdf) {
                    $logoPukSrc = $imageSrc($logoPukPath);
                } else {
                    $logoPukSrc = file_exists($logoPukPath) ? asset('images/puk-spamk.png') : null;
                }
            @endphp
            @if($logoPukSrc)
                <img class="kta-seal"
                     style="left:{{ $fieldPositions['logo_puk']['left'] }}mm;
                            top:{{ $fieldPositions['logo_puk']['top'] }}mm;
                            width:{{ $fieldPositions['logo_puk']['width'] ?? 15 }}mm;
                            height:{{ $fieldPositions['logo_puk']['height'] ?? 10 }}mm;"
                     src="{{ $logoPukSrc }}" alt="Logo PUK">
            @endif
        @endif

        {{-- LOGO CAP --}}
        @if(isset($fieldPositions['logo_cap']))
            @php
                $logoCapPath = public_path('images/pp-spamk.png');
                if ($isPdf) {
                    $logoCapSrc = $imageSrc($logoCapPath);
                } else {
                    $logoCapSrc = file_exists($logoCapPath) ? asset('images/pp-spamk.png') : null;
                }
            @endphp
            @if($logoCapSrc)
                <img class="kta-seal"
                     style="left:{{ $fieldPositions['logo_cap']['left'] }}mm;
                            top:{{ $fieldPositions['logo_cap']['top'] }}mm;
                            width:{{ $fieldPositions['logo_cap']['width'] ?? 10 }}mm;
                            height:{{ $fieldPositions['logo_cap']['height'] ?? 10 }}mm;"
                     src="{{ $logoCapSrc }}" alt="Logo">
            @endif
        @endif

        {{-- TANDA TANGAN --}}
        {{-- Tanggal --}}
        @if(isset($fieldPositions['tanggal_ttd']))
            <div class="kta-field kta-field-small"
                 style="left:{{ $fieldPositions['tanggal_ttd']['left'] }}mm;
                        top:{{ $fieldPositions['tanggal_ttd']['top'] }}mm;
                        font-size:{{ $fieldPositions['tanggal_ttd']['font_size'] }}mm;">
                Jakarta,
            </div>
        @endif

        {{-- TTD Sekretaris --}}
        @if(isset($fieldPositions['ttd_sekretaris']) && $ttdSekretaris)
            <img class="kta-signature"
                 style="left:{{ $fieldPositions['ttd_sekretaris']['left'] }}mm;
                        top:{{ $fieldPositions['ttd_sekretaris']['top'] }}mm;
                        width:{{ $fieldPositions['ttd_sekretaris']['width'] ?? 13 }}mm;
                        height:{{ $fieldPositions['ttd_sekretaris']['height'] ?? 8 }}mm;"
                 src="{{ $ttdSekretaris }}" alt="TTD Sekretaris">
        @endif

        {{-- Stempel PP SPAMK --}}
        @if(isset($fieldPositions['ttd_seal']))
            @php
                $logoPpPath = public_path('images/pp-spamk.png');
                if ($isPdf) {
                    $logoPpSrc = $imageSrc($logoPpPath);
                } else {
                    $logoPpSrc = file_exists($logoPpPath) ? asset('images/pp-spamk.png') : null;
                }
            @endphp
            @if($logoPpSrc)
                <img class="kta-seal"
                     style="left:{{ $fieldPositions['ttd_seal']['left'] }}mm;
                            top:{{ $fieldPositions['ttd_seal']['top'] }}mm;
                            width:{{ $fieldPositions['ttd_seal']['width'] ?? 6 }}mm;
                            height:{{ $fieldPositions['ttd_seal']['height'] ?? 6 }}mm;"
                     src="{{ $logoPpSrc }}" alt="PP SPAMK">
            @endif
        @endif

        {{-- Nama Sekretaris --}}
        @if(isset($fieldPositions['nama_sekretaris']))
            <div class="kta-field kta-field-small kta-field-center"
                 style="left:{{ $fieldPositions['nama_sekretaris']['left'] }}mm;
                        top:{{ $fieldPositions['nama_sekretaris']['top'] }}mm;
                        font-size:{{ $fieldPositions['nama_sekretaris']['font_size'] }}mm;">
                ( {{ $sekretaris?->nama ?? '........................' }} )
            </div>
        @endif

        {{-- TTD Ketua --}}
        @if(isset($fieldPositions['ttd_ketua']) && $ttdKetua)
            <img class="kta-signature"
                 style="left:{{ $fieldPositions['ttd_ketua']['left'] }}mm;
                        top:{{ $fieldPositions['ttd_ketua']['top'] }}mm;
                        width:{{ $fieldPositions['ttd_ketua']['width'] ?? 13 }}mm;
                        height:{{ $fieldPositions['ttd_ketua']['height'] ?? 8 }}mm;"
                 src="{{ $ttdKetua }}" alt="TTD Ketua">
        @endif

        {{-- Nama Ketua --}}
        @if(isset($fieldPositions['nama_ketua']))
            <div class="kta-field kta-field-small kta-field-center"
                 style="left:{{ $fieldPositions['nama_ketua']['left'] }}mm;
                        top:{{ $fieldPositions['nama_ketua']['top'] }}mm;
                        font-size:{{ $fieldPositions['nama_ketua']['font_size'] }}mm;">
                ( {{ $ketua?->nama ?? '........................' }} )
            </div>
        @endif

    </div>

</div>

@else
{{-- ================================================================
     BACK
================================================================ --}}

<div class="kta-card kta-back">

    {{-- BACKGROUND --}}
    @if($bgSrc)
        <img class="kta-bg-image" src="{{ $bgSrc }}" alt="">
    @endif

    {{-- DATA OVERLAY --}}
    <div class="kta-data-layer">

        {{-- FOTO ANGGOTA --}}
        @if(isset($fieldPositions['foto']) && $fotoSrc)
            <div class="kta-photo"
                 style="left:{{ $fieldPositions['foto']['left'] }}mm;
                        top:{{ $fieldPositions['foto']['top'] }}mm;
                        width:{{ $fieldPositions['foto']['width'] ?? 20 }}mm;
                        height:{{ $fieldPositions['foto']['height'] ?? 28 }}mm;">
                <img src="{{ $fotoSrc }}" alt="Foto">
            </div>
        @endif

    </div>

</div>

@endif
