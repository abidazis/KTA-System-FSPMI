{{-- ================================================================
     KTA FSPMI - BACKGROUND IMAGE + DATA OVERLAY

     Physical size: PORTRAIT 54mm x 85.6mm
     Background images are portrait (54mm x 85.6mm) - matches card natively

     FRONT (SISI FOTO) = $side === 'front'
       - Background: kta-backgrounds/front_image
       - Contains: foto, nama, nik (overlay on background design)

     BACK (SISI DATA) = $side === 'back'
       - Background: kta-backgrounds/back_image
       - Contains: semua data + tanda tangan (TTL, Alamat, JK, Agama, dll)

     $side = front / back
     $background = KtaBackground instance or null
================================================================ --}}

@php
    $side = $side ?? 'front';
    $isPdf = $isPdf ?? false;

    /*
    |--------------------------------------------------------------------------
    | IMAGE HELPER - base64 for all (works in browser AND DomPDF)
    |--------------------------------------------------------------------------
    */
    $imageSrc = function ($path) {
        if (!$path || !file_exists($path)) {
            return null;
        }
        $mime = mime_content_type($path) ?: 'image/png';
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
    | Photo path bisa dari:
    | 1. $foto_path (absolute path, dari controller)
    | 2. $member->foto_path (relative, perlu ditambahkan storage_path)
    |--------------------------------------------------------------------------
    */
    $fotoSrc = null;

    // Coba dari $foto_path dulu (absolute path dari controller)
    if (!empty($foto_path) && file_exists($foto_path)) {
        $fotoSrc = $imageSrc($foto_path);
    }

    // Fallback: construct dari $member->foto_path
    if (!$fotoSrc && !empty($member->foto_path)) {
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
    | FRONT (sisi foto):
    | - Foto: di kiri card (x=5-28, y=20-60)
    | - Nama: overlay background (x=30, y=52)
    | - NIK: overlay background (x=30, y=60)
    |
    | BACK (sisi data):
    | - TTL: x=27, y=30
    | - Alamat: x=27, y=36
    | - JK: x=27, y=47
    | - Agama: x=43, y=47
    | - Berlaku: x=27, y=53
    | - Tanggal TTD: x=5, y=63
    | - TTD Sekertaris: x=5, y=66
    | - Nama Sekertaris: x=5, y=75
    | - TTD Ketua: x=33, y=66
    | - Nama Ketua: x=33, y=75
    |
    */

    $positions = [
        'front' => [
            // Foto - di sisi kiri card
            'foto'          => ['left' => 5, 'top' => 18, 'width' => 24, 'height' => 32],
            // Nama - overlay di background
            'nama'          => ['left' => 30, 'top' => 50, 'font_size' => 2.5, 'width' => 23],
            // NIK - overlay di background
            'nik'           => ['left' => 30, 'top' => 58, 'font_size' => 2.5, 'width' => 23],
        ],
        'back' => [
            // TTL - Tempat/Tgl Lahir
            'ttl'           => ['left' => 27, 'top' => 30, 'font_size' => 2.2, 'width' => 26],
            // Alamat
            'alamat'        => ['left' => 27, 'top' => 36, 'font_size' => 1.9, 'width' => 26, 'height' => 9],
            // Jenis Kelamin
            'jk'            => ['left' => 27, 'top' => 47, 'font_size' => 2.2],
            // Agama
            'agama'         => ['left' => 43, 'top' => 47, 'font_size' => 2.2, 'width' => 10],
            // Berlaku Hingga
            'berlaku'       => ['left' => 27, 'top' => 53, 'font_size' => 2.2],

            // Tanggal TTD
            'tanggal_ttd'   => ['left' => 5, 'top' => 63, 'font_size' => 1.8, 'width' => 22],
            // TTD Sekretaris
            'ttd_sekretaris'  => ['left' => 5, 'top' => 66, 'width' => 16, 'height' => 8],
            'nama_sekretaris' => ['left' => 5, 'top' => 75, 'font_size' => 1.5, 'width' => 22],
            // TTD Ketua
            'ttd_ketua'     => ['left' => 33, 'top' => 66, 'width' => 16, 'height' => 8],
            'nama_ketua'    => ['left' => 33, 'top' => 75, 'font_size' => 1.5, 'width' => 22],
        ],
    ];

    $fieldPositions = $positions[$side] ?? [];
@endphp


@if($side === 'front')
{{-- ================================================================
     FRONT - SISI FOTO
================================================================ --}}

<div class="kta-card kta-front">

    {{-- BACKGROUND (sisi foto: kuning dengan pola kotak) --}}
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
                        width:{{ $fieldPositions['foto']['width'] ?? 22 }}mm;
                        height:{{ $fieldPositions['foto']['height'] ?? 30 }}mm;">
                <img src="{{ $fotoSrc }}" alt="Foto">
            </div>
        @endif

        {{-- NAMA (overlay di background) --}}
        @if(isset($fieldPositions['nama']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['nama']['left'] }}mm;
                        top:{{ $fieldPositions['nama']['top'] }}mm;
                        font-size:{{ $fieldPositions['nama']['font_size'] }}mm;
                        width:{{ $fieldPositions['nama']['width'] ?? 25 }}mm;">
                {{ strtoupper($member->nama ?? '-') }}
            </div>
        @endif

        {{-- NIK (overlay di background) --}}
        @if(isset($fieldPositions['nik']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['nik']['left'] }}mm;
                        top:{{ $fieldPositions['nik']['top'] }}mm;
                        font-size:{{ $fieldPositions['nik']['font_size'] }}mm;
                        width:{{ $fieldPositions['nik']['width'] ?? 25 }}mm;">
                {{ $member->nik ?? '-' }}
            </div>
        @endif

    </div>

</div>

@else
{{-- ================================================================
     BACK - SISI DATA + TANDA TANGAN
================================================================ --}}

<div class="kta-card kta-back">

    {{-- BACKGROUND (sisi data: pink/oranye) --}}
    @if($bgSrc)
        <img class="kta-bg-image" src="{{ $bgSrc }}" alt="">
    @endif

    {{-- DATA OVERLAY --}}
    <div class="kta-data-layer">

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
                        width:{{ $fieldPositions['alamat']['width'] ?? 26 }}mm;">
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
                        width:{{ $fieldPositions['ttd_sekretaris']['width'] ?? 16 }}mm;
                        height:{{ $fieldPositions['ttd_sekretaris']['height'] ?? 8 }}mm;"
                 src="{{ $ttdSekretaris }}" alt="TTD Sekretaris">
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
                        width:{{ $fieldPositions['ttd_ketua']['width'] ?? 16 }}mm;
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

@endif
