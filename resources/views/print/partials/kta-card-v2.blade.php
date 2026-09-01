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
    | - Foto: di sisi kiri card (x=5-29, y=15-47)
    | - Nama: di sisi kanan foto, di bawah (x=30, y=52)
    | - NIK: di kanan foto, di bawah Nama (x=30, y=58)
    |
    | BACK (sisi data) - Label di kiri, Value di kanan:
    | - NIK: label x=5, value x=22
    | - NAMA: label x=5, value x=22
    | - TEMPAT/TGL LAHIR: label x=5, value x=22
    | - ALAMAT: label x=5, value x=22
    | - JENIS KELAMIN: label x=5, value x=28 (baris sama dg Agama)
    | - AGAMA: label x=27, value x=35 (baris sama dg JK)
    | - BERLAKU HINGGA: label x=5, value x=22
    | - Tanggal TTD: x=5, y=60
    | - TTD Sekretaris: x=5, y=63, Nama Sekertaris: x=5, y=73
    | - TTD Ketua: x=29, y=63, Nama Ketua: x=29, y=73
    |
    */

    $positions = [
        'front' => [
            // Foto - di sisi kiri card
            'foto'          => ['left' => 5, 'top' => 10, 'width' => 23, 'height' => 40],
            // Nama - di sisi kanan foto, di bawah (lebih besar & bold)
            'nama'          => ['left' => 30, 'top' => 56, 'font_size' => 2.8, 'width' => 23],
            // NIK - di kanan foto, di bawah Nama (lebih rapat)
            'nik'           => ['left' => 30, 'top' => 61, 'font_size' => 2.8, 'width' => 23],
        ],
        'back' => [
            // NIK - label di kiri, value di kanan
            'nik_label'     => ['left' => 5, 'top' => 10, 'font_size' => 2.0, 'width' => 16],
            'nik'           => ['left' => 22, 'top' => 10, 'font_size' => 2.0, 'width' => 30],
            // NAMA
            'nama_label'    => ['left' => 5, 'top' => 15, 'font_size' => 2.0, 'width' => 16],
            'nama'          => ['left' => 22, 'top' => 15, 'font_size' => 2.0, 'width' => 30],
            // TEMPAT/TANGGAL LAHIR
            'ttl_label'     => ['left' => 5, 'top' => 20, 'font_size' => 2.0, 'width' => 16],
            'ttl'           => ['left' => 22, 'top' => 20, 'font_size' => 2.0, 'width' => 30],
            // ALAMAT
            'alamat_label' => ['left' => 5, 'top' => 25, 'font_size' => 2.0, 'width' => 16],
            'alamat'        => ['left' => 22, 'top' => 25, 'font_size' => 1.8, 'width' => 30, 'height' => 9],
            // JENIS KELAMIN
            'jk_label'     => ['left' => 5, 'top' => 33, 'font_size' => 2.0, 'width' => 16],
            'jk'           => ['left' => 22, 'top' => 33, 'font_size' => 2.0, 'width' => 30],
            // AGAMA - baris sendiri
            'agama_label'  => ['left' => 5, 'top' => 38, 'font_size' => 2.0, 'width' => 16],
            'agama'        => ['left' => 22, 'top' => 38, 'font_size' => 2.0, 'width' => 30],
            // BERLAKU HINGGA
            'berlaku_label' => ['left' => 5, 'top' => 43, 'font_size' => 2.0, 'width' => 16],
            'berlaku'       => ['left' => 22, 'top' => 43, 'font_size' => 2.0, 'width' => 30],

            // Tanggal TTD
            'tanggal_ttd'   => ['left' => 5, 'top' => 51, 'font_size' => 1.8, 'width' => 22],

            // PIMPINAN PUSAT
            'pimpinan_pusat' => ['left' => 5, 'top' => 55, 'font_size' => 1.3, 'width' => 45],

            // TTD Sekretaris
            'ttd_sekretaris'  => ['left' => 5, 'top' => 62, 'width' => 16, 'height' => 8],
            'nama_sekretaris' => ['left' => 5, 'top' => 72, 'font_size' => 1.4, 'width' => 16],
            // TTD Ketua
            'ttd_ketua'     => ['left' => 30, 'top' => 62, 'width' => 16, 'height' => 8],
            'nama_ketua'    => ['left' => 30, 'top' => 72, 'font_size' => 1.4, 'width' => 16],
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
                        font-weight:700;
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
                        font-weight:700;
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

        {{-- NIK --}}
        @if(isset($fieldPositions['nik_label']))
            <div class="kta-field kta-field-label"
                 style="left:{{ $fieldPositions['nik_label']['left'] }}mm;
                        top:{{ $fieldPositions['nik_label']['top'] }}mm;
                        font-size:{{ $fieldPositions['nik_label']['font_size'] }}mm;
                        font-weight:700;">
                NIK
            </div>
        @endif
        @if(isset($fieldPositions['nik']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['nik']['left'] }}mm;
                        top:{{ $fieldPositions['nik']['top'] }}mm;
                        font-size:{{ $fieldPositions['nik']['font_size'] }}mm;
                        font-weight:700;">
                {{ $member->nik ?? '-' }}
            </div>
        @endif

        {{-- NAMA --}}
        @if(isset($fieldPositions['nama_label']))
            <div class="kta-field kta-field-label"
                 style="left:{{ $fieldPositions['nama_label']['left'] }}mm;
                        top:{{ $fieldPositions['nama_label']['top'] }}mm;
                        font-size:{{ $fieldPositions['nama_label']['font_size'] }}mm;
                        font-weight:700;">
                NAMA
            </div>
        @endif
        @if(isset($fieldPositions['nama']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['nama']['left'] }}mm;
                        top:{{ $fieldPositions['nama']['top'] }}mm;
                        font-size:{{ $fieldPositions['nama']['font_size'] }}mm;
                        font-weight:700;">
                {{ strtoupper($member->nama ?? '-') }}
            </div>
        @endif

        {{-- TEMPAT/TGL LAHIR --}}
        @if(isset($fieldPositions['ttl_label']))
            <div class="kta-field kta-field-label"
                 style="left:{{ $fieldPositions['ttl_label']['left'] }}mm;
                        top:{{ $fieldPositions['ttl_label']['top'] }}mm;
                        font-size:{{ $fieldPositions['ttl_label']['font_size'] }}mm;
                        font-weight:700;">
                Tempat/Tgl Lahir
            </div>
        @endif
        @if(isset($fieldPositions['ttl']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['ttl']['left'] }}mm;
                        top:{{ $fieldPositions['ttl']['top'] }}mm;
                        font-size:{{ $fieldPositions['ttl']['font_size'] }}mm;
                        font-weight:700;">
                {{ $member->tempat_lahir ?? '-' }}, {{ $tanggalLahir }}
            </div>
        @endif

        {{-- ALAMAT --}}
        @if(isset($fieldPositions['alamat_label']))
            <div class="kta-field kta-field-label"
                 style="left:{{ $fieldPositions['alamat_label']['left'] }}mm;
                        top:{{ $fieldPositions['alamat_label']['top'] }}mm;
                        font-size:{{ $fieldPositions['alamat_label']['font_size'] }}mm;
                        font-weight:700;">
                Alamat
            </div>
        @endif
        @if(isset($fieldPositions['alamat']))
            <div class="kta-field kta-field-alamat"
                 style="left:{{ $fieldPositions['alamat']['left'] }}mm;
                        top:{{ $fieldPositions['alamat']['top'] }}mm;
                        font-size:{{ $fieldPositions['alamat']['font_size'] }}mm;
                        font-weight:700;
                        width:{{ $fieldPositions['alamat']['width'] ?? 30 }}mm;">
                {{ $member->alamat ?? '-' }}
            </div>
        @endif

        {{-- JENIS KELAMIN --}}
        @if(isset($fieldPositions['jk_label']))
            <div class="kta-field kta-field-label"
                 style="left:{{ $fieldPositions['jk_label']['left'] }}mm;
                        top:{{ $fieldPositions['jk_label']['top'] }}mm;
                        font-size:{{ $fieldPositions['jk_label']['font_size'] }}mm;
                        font-weight:700;">
                Jenis Kelamin
            </div>
        @endif
        @if(isset($fieldPositions['jk']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['jk']['left'] }}mm;
                        top:{{ $fieldPositions['jk']['top'] }}mm;
                        font-size:{{ $fieldPositions['jk']['font_size'] }}mm;
                        font-weight:700;">
                {{ $member->jenis_kelamin ?? '-' }}
            </div>
        @endif

        {{-- AGAMA --}}
        @if(isset($fieldPositions['agama_label']))
            <div class="kta-field kta-field-label"
                 style="left:{{ $fieldPositions['agama_label']['left'] }}mm;
                        top:{{ $fieldPositions['agama_label']['top'] }}mm;
                        font-size:{{ $fieldPositions['agama_label']['font_size'] }}mm;
                        font-weight:700;">
                Agama
            </div>
        @endif
        @if(isset($fieldPositions['agama']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['agama']['left'] }}mm;
                        top:{{ $fieldPositions['agama']['top'] }}mm;
                        font-size:{{ $fieldPositions['agama']['font_size'] }}mm;
                        font-weight:700;">
                {{ $member->agama ?? '-' }}
            </div>
        @endif

        {{-- BERLAKU HINGGA --}}
        @if(isset($fieldPositions['berlaku_label']))
            <div class="kta-field kta-field-label"
                 style="left:{{ $fieldPositions['berlaku_label']['left'] }}mm;
                        top:{{ $fieldPositions['berlaku_label']['top'] }}mm;
                        font-size:{{ $fieldPositions['berlaku_label']['font_size'] }}mm;
                        font-weight:700;">
                Berlaku Hingga
            </div>
        @endif
        @if(isset($fieldPositions['berlaku']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['berlaku']['left'] }}mm;
                        top:{{ $fieldPositions['berlaku']['top'] }}mm;
                        font-size:{{ $fieldPositions['berlaku']['font_size'] }}mm;
                        font-weight:700;">
                {{ $berlakuHingga }}
            </div>
        @endif

        {{-- PIMPINAN PUSAT --}}
        @if(isset($fieldPositions['pimpinan_pusat']))
            <div class="kta-field kta-field-center"
                 style="left:{{ $fieldPositions['pimpinan_pusat']['left'] }}mm;
                        top:{{ $fieldPositions['pimpinan_pusat']['top'] }}mm;
                        font-size:{{ $fieldPositions['pimpinan_pusat']['font_size'] }}mm;
                        font-weight:700;
                        width:{{ $fieldPositions['pimpinan_pusat']['width'] }}mm;">
                PIMPINAN PUSAT<br>
                SERIKAT PEKERJA AUTOMOTIF MESIN DAN KOMPONEN<br>
                FEDERASI SERIKAT PEKERJA METAL INDONESIA
            </div>
        @endif

        {{-- TANDA TANGAN --}}
        {{-- Tanggal --}}
        @if(isset($fieldPositions['tanggal_ttd']))
            <div class="kta-field kta-field-small"
                 style="left:{{ $fieldPositions['tanggal_ttd']['left'] }}mm;
                        top:{{ $fieldPositions['tanggal_ttd']['top'] }}mm;
                        font-size:{{ $fieldPositions['tanggal_ttd']['font_size'] }}mm;
                        font-weight:700;">
                Jakarta,
            </div>
        @endif

        {{-- TTD Sekretaris --}}
        @if(isset($fieldPositions['ttd_sekretaris']) && $ttdSekretaris)
            <img class="kta-signature"
                 style="left:{{ $fieldPositions['ttd_sekretaris']['left'] }}mm;
                        top:{{ $fieldPositions['ttd_sekretaris']['top'] }}mm;
                        width:{{ $fieldPositions['ttd_sekretaris']['width'] ?? 16 }}mm;
                        height:{{ $fieldPositions['ttd_sekretaris']['height'] ?? 7 }}mm;"
                 src="{{ $ttdSekretaris }}" alt="TTD Sekretaris">
        @endif

        {{-- Nama Sekretaris + Label --}}
        @if(isset($fieldPositions['nama_sekretaris']))
            <div class="kta-field kta-field-small kta-field-center"
                 style="left:{{ $fieldPositions['nama_sekretaris']['left'] }}mm;
                        top:{{ $fieldPositions['nama_sekretaris']['top'] }}mm;
                        font-size:{{ $fieldPositions['nama_sekretaris']['font_size'] }}mm;
                        font-weight:700;
                        width:{{ $fieldPositions['nama_sekretaris']['width'] }}mm;">
                SEKRETARIS UMUM<br>
                ( {{ $sekretaris?->nama ?? '........................' }} )
            </div>
        @endif

        {{-- TTD Ketua --}}
        @if(isset($fieldPositions['ttd_ketua']) && $ttdKetua)
            <img class="kta-signature"
                 style="left:{{ $fieldPositions['ttd_ketua']['left'] }}mm;
                        top:{{ $fieldPositions['ttd_ketua']['top'] }}mm;
                        width:{{ $fieldPositions['ttd_ketua']['width'] ?? 16 }}mm;
                        height:{{ $fieldPositions['ttd_ketua']['height'] ?? 7 }}mm;"
                 src="{{ $ttdKetua }}" alt="TTD Ketua">
        @endif

        {{-- Nama Ketua + Label --}}
        @if(isset($fieldPositions['nama_ketua']))
            <div class="kta-field kta-field-small kta-field-center"
                 style="left:{{ $fieldPositions['nama_ketua']['left'] }}mm;
                        top:{{ $fieldPositions['nama_ketua']['top'] }}mm;
                        font-size:{{ $fieldPositions['nama_ketua']['font_size'] }}mm;
                        font-weight:700;
                        width:{{ $fieldPositions['nama_ketua']['width'] }}mm;">
                KETUA UMUM<br>
                ( {{ $ketua?->nama ?? '........................' }} )
            </div>
        @endif

    </div>

</div>

@endif
