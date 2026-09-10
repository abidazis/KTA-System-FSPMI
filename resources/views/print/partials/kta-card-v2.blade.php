{{-- ================================================================
     KTA FSPMI - BACKGROUND IMAGE + DATA OVERLAY

     Physical size: PORTRAIT 54mm x 85.6mm
================================================================ --}}

@php
    $side = $side ?? 'front';
    $isPdf = $isPdf ?? false;

    /* IMAGE HELPER */
    $imageSrc = function ($path) {
        if (!$path || !file_exists($path)) {
            return null;
        }
        $mime = mime_content_type($path) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    };

    /* BACKGROUND IMAGE */
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

    /* MEMBER DATA */
    $tanggalLahir = !empty($member->tanggal_lahir)
        ? \Carbon\Carbon::parse($member->tanggal_lahir)->format('d-m-Y')
        : '-';

    $berlakuHingga = !empty($member->berlaku_hingga)
        ? \Carbon\Carbon::parse($member->berlaku_hingga)->format('d F Y')
        : '-';

    /* SIGNATURE */
    $ttdSekretaris = null;
    if (!empty($ttd_sekretaris_path) && file_exists($ttd_sekretaris_path)) {
        $ttdSekretaris = $imageSrc($ttd_sekretaris_path);
    }

    $ttdKetua = null;
    if (!empty($ttd_ketua_path) && file_exists($ttd_ketua_path)) {
        $ttdKetua = $imageSrc($ttd_ketua_path);
    }

    /* STEMPEL */
    $stempel = null;
    if (!empty($stempel_path) && file_exists($stempel_path)) {
        $stempel = $imageSrc($stempel_path);
    }

    /* MEMBER PHOTO */
    $fotoSrc = null;
    // Debug: log foto_path
    $foto_path_for_debug = $foto_path;
    if (!empty($foto_path) && file_exists($foto_path)) {
        $fotoSrc = $imageSrc($foto_path);
    }
    if (!$fotoSrc && !empty($member->foto_path)) {
        $absoluteFoto = storage_path('app/private/' . $member->foto_path);
        if (file_exists($absoluteFoto)) {
            $fotoSrc = $imageSrc($absoluteFoto);
        }
    }

    /* TEXT WRAP HELPER - pecah string jadi baris dengan <br> untuk DomPDF */
    $wrapText = function ($text, $charsPerLine = 45) {
        if (empty($text)) return '-';
        $wrapped = wordwrap($text, $charsPerLine, "|||BREAK|||", false);
        $lines = explode("|||BREAK|||", $wrapped);
        return implode('<br>', $lines);
    };

    /*
    |--------------------------------------------------------------------------
    | FIELD POSITIONS (mm from top-left of PORTRAIT card: 54mm x 85.6mm)
    | DIKALIBRASI ULANG UNTUK PRESISI KETAT (Interval Rapat)
    |--------------------------------------------------------------------------
    */

    $positions = [
        'front' => [
            // Foto sedikit lebih pas di frame kuning
            'foto'          => ['left' => 26.5, 'top' => 30.5, 'width' => 22.5, 'height' => 30.5],
            // Nama & NIK (Lebar disamakan dengan foto agar center presisi)
            'nama'          => ['left' => 25.5, 'top' => 62, 'font_size' => 1.8, 'width' => 24.5],
            'nik'           => ['left' => 25.5, 'top' => 64, 'font_size' => 1.9, 'width' => 24.5],
        ],
        'back' => [
            // Nama PT dinaikkan karena teks PUK SPAMK sudah nempel di background
            'perusahaan'       => ['left' => 0, 'top' => 8, 'font_size' => 1.8, 'width' => 54],
            // Alamat perusahaan - auto-wrap via PHP, no height restriction
            'alamat_perusahaan'=> ['left' => 4, 'top' => 11, 'font_size' => 1.5, 'width' => 46],

            // Data Anggota - Interval ditekan ke 3.2mm per baris agar rapat
            'nik_label'     => ['left' => 3, 'top' => 20, 'font_size' => 1.75, 'width' => 14],
            'nik'           => ['left' => 17, 'top' => 20, 'font_size' => 1.75, 'width' => 34],

            'nama_label'    => ['left' => 3, 'top' => 23.2, 'font_size' => 1.75, 'width' => 14],
            'nama'          => ['left' => 17, 'top' => 23.2, 'font_size' => 1.75, 'width' => 34],

            'ttl_label'     => ['left' => 3, 'top' => 26.4, 'font_size' => 1.75, 'width' => 14],
            'ttl'           => ['left' => 17, 'top' => 26.4, 'font_size' => 1.75, 'width' => 34],

            'alamat_label'  => ['left' => 3, 'top' => 29.6, 'font_size' => 1.75, 'width' => 14],
            // Alamat auto-wrap, font disamain dengan field lain (1.75mm)
            'alamat'        => ['left' => 17, 'top' => 29.6, 'font_size' => 1.75, 'width' => 34, 'height' => 8],

            // Jarak khusus (gap) antara Alamat ke Jenis Kelamin (Sesuai KTA Asli)
            'jk_label'      => ['left' => 3, 'top' => 36, 'font_size' => 1.75, 'width' => 14],
            'jk'            => ['left' => 17, 'top' => 36, 'font_size' => 1.75, 'width' => 34],

            'agama_label'   => ['left' => 3, 'top' => 39, 'font_size' => 1.75, 'width' => 14],
            'agama'         => ['left' => 17, 'top' => 39, 'font_size' => 1.75, 'width' => 34],

            'berlaku_label' => ['left' => 3, 'top' => 42, 'font_size' => 1.75, 'width' => 14],
            'berlaku'       => ['left' => 17, 'top' => 42, 'font_size' => 1.75, 'width' => 34],

            // Footer (Tanggal, Pimpinan Pusat, TTD) - Posisinya dinaikkan sedikit agar tidak mentok bawah
            'tanggal_ttd'   => ['left' => 0, 'top' => 50, 'font_size' => 1.75, 'width' => 54],
            'pimpinan_pusat' => ['left' => 2, 'top' => 53, 'font_size' => 1.75, 'width' => 50],

            'label_sekretaris'=> ['left' => 2, 'top' => 61, 'font_size' => 1.75, 'width' => 24],
            'ttd_sekretaris'  => ['left' => 6, 'top' => 64, 'width' => 16, 'height' => 8],
            'nama_sekretaris' => ['left' => 2, 'top' => 73, 'font_size' => 1.75, 'width' => 24],

            'label_ketua'     => ['left' => 28, 'top' => 61, 'font_size' => 1.75, 'width' => 24],
            'ttd_ketua'       => ['left' => 32, 'top' => 64, 'width' => 16, 'height' => 8],
            'nama_ketua'      => ['left' => 28, 'top' => 73, 'font_size' => 1.75, 'width' => 24],

            // Stempel organisasi - diletakkan di antara kedua tanda tangan
            'stempel'         => ['left' => 19, 'top' => 62, 'width' => 16, 'height' => 12],
        ],
    ];

    $fieldPositions = $positions[$side] ?? [];
@endphp

@if($side === 'front')
{{-- ================================================================
     FRONT - SISI FOTO
================================================================ --}}
<div class="kta-card kta-front" style="font-family: Arial, Helvetica, sans-serif;">
    @if($bgSrc)
        <img class="kta-bg-image" src="{{ $bgSrc }}" alt="">
    @endif

    <div class="kta-data-layer">
        {{-- FOTO ANGGOTA --}}
        @if(isset($fieldPositions['foto']) && $fotoSrc)
            <div class="kta-photo"
                 style="left:{{ $fieldPositions['foto']['left'] }}mm;
                        top:{{ $fieldPositions['foto']['top'] }}mm;
                        width:{{ $fieldPositions['foto']['width'] }}mm;
                        height:{{ $fieldPositions['foto']['height'] }}mm;
                        position:absolute;">
                <img src="{{ $fotoSrc }}" alt="Foto" style="width:100%; height:100%; object-fit:cover; border-radius:1mm;">
            </div>
        @endif

        {{-- NAMA (Word-wrap aktif untuk nama panjang) --}}
        @if(isset($fieldPositions['nama']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['nama']['left'] }}mm;
                        top:{{ $fieldPositions['nama']['top'] }}mm;
                        font-size:{{ $fieldPositions['nama']['font_size'] }}mm;
                        font-weight:bold;
                        text-align:center;
                        width:{{ $fieldPositions['nama']['width'] }}mm;
                        line-height: 1.0; 
                        word-wrap: break-word;
                        letter-spacing: -0.1mm;">
                {{ strtoupper($member->nama ?? '-') }}
            </div>
        @endif

        {{-- NIK --}}
        @if(isset($fieldPositions['nik']))
            <div class="kta-field"
                 style="left:{{ $fieldPositions['nik']['left'] }}mm;
                        top:{{ $fieldPositions['nik']['top'] }}mm;
                        font-size:{{ $fieldPositions['nik']['font_size'] }}mm;
                        font-weight:bold;
                        text-align:center;
                        width:{{ $fieldPositions['nik']['width'] }}mm;
                        line-height: 1.0;">
                {{ $member->nik ?? '-' }}
            </div>
        @endif
    </div>
</div>

@else
{{-- ================================================================
     BACK - SISI DATA + TANDA TANGAN
================================================================ --}}
<div class="kta-card kta-back" style="font-family: Arial, Helvetica, sans-serif;">
    @if($bgSrc)
        <img class="kta-bg-image" src="{{ $bgSrc }}" alt="">
    @endif

    <div class="kta-data-layer">
        
        {{-- NAMA PERUSAHAAN (PUK SPAMK sudah dihapus, sisa ini saja) --}}
        @if(isset($fieldPositions['perusahaan']))
            <div class="kta-field kta-field-center"
                 style="left:{{ $fieldPositions['perusahaan']['left'] }}mm;
                        top:{{ $fieldPositions['perusahaan']['top'] }}mm;
                        font-size:{{ $fieldPositions['perusahaan']['font_size'] }}mm;
                        width:{{ $fieldPositions['perusahaan']['width'] }}mm;
                        color:#4B0082; font-weight:900;">
                {{ strtoupper($member->company?->name ?? '-') }}
            </div>
        @endif

        {{-- ALAMAT PERUSAHAAN (Auto-wrap via PHP, center text, no height restriction) --}}
        @if(isset($fieldPositions['alamat_perusahaan']))
            <div class="kta-field kta-field-alamat-perusahaan"
                 style="left:{{ $fieldPositions['alamat_perusahaan']['left'] }}mm;
                        top:{{ $fieldPositions['alamat_perusahaan']['top'] }}mm;
                        font-size:{{ $fieldPositions['alamat_perusahaan']['font_size'] }}mm;
                        width:{{ $fieldPositions['alamat_perusahaan']['width'] }}mm;
                        color:#4B0082; font-weight:bold;
                        line-height: 1.2; text-align:center;">
                {!! $wrapText($member->company?->full_address ?? '-', 42) !!}
            </div>
        @endif

        {{-- FIELDS DATA ANGGOTA --}}
        @php
            $fields = [
                ['label' => 'NIK', 'val_key' => 'nik', 'value' => $member->nik ?? '-'],
                ['label' => 'NAMA', 'val_key' => 'nama', 'value' => strtoupper($member->nama ?? '-')],
                ['label' => 'Tempat/Tgl Lahir', 'val_key' => 'ttl', 'value' => strtoupper($member->tempat_lahir ?? '-') . ' ' . $tanggalLahir],
                ['label' => 'Alamat', 'val_key' => 'alamat', 'value' => $member->alamat ?? '-'],
                ['label' => 'Jenis Kelamin', 'val_key' => 'jk', 'value' => $member->jenis_kelamin ?? '-'],
                ['label' => 'Agama', 'val_key' => 'agama', 'value' => $member->agama ?? '-'],
                ['label' => 'Berlaku Hingga', 'val_key' => 'berlaku', 'value' => $berlakuHingga]
            ];
        @endphp

        @foreach($fields as $f)
            @if(isset($fieldPositions[$f['val_key'].'_label']))
                <div class="kta-field"
                     style="left:{{ $fieldPositions[$f['val_key'].'_label']['left'] }}mm;
                            top:{{ $fieldPositions[$f['val_key'].'_label']['top'] }}mm;
                            font-size:{{ $fieldPositions[$f['val_key'].'_label']['font_size'] }}mm;
                            font-weight:bold; color:#000;">
                    {{ $f['label'] }}
                </div>
            @endif
            @if(isset($fieldPositions[$f['val_key']]))
                @php
                    $extraClass = $f['val_key'] === 'alamat' ? ' kta-field-alamat' : '';
                    // Wrap alamat dengan PHP untuk DomPDF compatibility
                    $displayValue = $f['val_key'] === 'alamat'
                        ? $wrapText($f['value'], 30)
                        : $f['value'];
                @endphp
                <div class="kta-field{{ $extraClass }}"
                     style="left:{{ $fieldPositions[$f['val_key']]['left'] }}mm;
                            top:{{ $fieldPositions[$f['val_key']]['top'] }}mm;
                            font-size:{{ $fieldPositions[$f['val_key']]['font_size'] }}mm;
                            font-weight:bold; color:#000;
                            width:{{ $fieldPositions[$f['val_key']]['width'] }}mm;
                            @if(isset($fieldPositions[$f['val_key']]['height']) && $f['val_key'] !== 'alamat') height:{{ $fieldPositions[$f['val_key']]['height'] }}mm; overflow:hidden; line-height: 1.2; @endif
                            @if($f['val_key'] === 'alamat') line-height: 1.2; @endif">
                    : {!! $displayValue !!}
                </div>
            @endif
        @endforeach

        {{-- Tanggal TTD --}}
        @if(isset($fieldPositions['tanggal_ttd']))
            <div class="kta-field kta-field-center"
                 style="left:{{ $fieldPositions['tanggal_ttd']['left'] }}mm;
                        top:{{ $fieldPositions['tanggal_ttd']['top'] }}mm;
                        font-size:{{ $fieldPositions['tanggal_ttd']['font_size'] }}mm;
                        font-weight:bold; color:#000;
                        width:{{ $fieldPositions['tanggal_ttd']['width'] }}mm;">
                Jakarta, {{ \Carbon\Carbon::now()->format('d F Y') }}
            </div>
        @endif

        {{-- PIMPINAN PUSAT --}}
        @if(isset($fieldPositions['pimpinan_pusat']))
            <div class="kta-field kta-field-center"
                 style="left:{{ $fieldPositions['pimpinan_pusat']['left'] }}mm;
                        top:{{ $fieldPositions['pimpinan_pusat']['top'] }}mm;
                        font-size:{{ $fieldPositions['pimpinan_pusat']['font_size'] }}mm;
                        font-weight:bold; color:#000; line-height:1.1;
                        width:{{ $fieldPositions['pimpinan_pusat']['width'] }}mm;">
                PIMPINAN PUSAT<br>
                SERIKAT PEKERJA AUTOMOTIF MESIN DAN KOMPONEN<br>
                FEDERASI SERIKAT PEKERJA METAL INDONESIA
            </div>
        @endif

        {{-- TTD SEKRETARIS UMUM --}}
        @if(isset($fieldPositions['label_sekretaris']))
            <div class="kta-field kta-field-center" style="left:{{ $fieldPositions['label_sekretaris']['left'] }}mm; top:{{ $fieldPositions['label_sekretaris']['top'] }}mm; font-size:{{ $fieldPositions['label_sekretaris']['font_size'] }}mm; font-weight:bold; width:{{ $fieldPositions['label_sekretaris']['width'] }}mm;">
                Sekretaris Umum
            </div>
        @endif
        @if(isset($fieldPositions['ttd_sekretaris']) && $ttdSekretaris)
            <img class="kta-signature"
                 style="left:{{ $fieldPositions['ttd_sekretaris']['left'] }}mm;
                        top:{{ $fieldPositions['ttd_sekretaris']['top'] }}mm;
                        width:{{ $fieldPositions['ttd_sekretaris']['width'] }}mm;
                        height:{{ $fieldPositions['ttd_sekretaris']['height'] }}mm; position:absolute;"
                 src="{{ $ttdSekretaris }}" alt="TTD Sekretaris">
        @endif
        @if(isset($fieldPositions['nama_sekretaris']))
            <div class="kta-field kta-field-center" style="left:{{ $fieldPositions['nama_sekretaris']['left'] }}mm; top:{{ $fieldPositions['nama_sekretaris']['top'] }}mm; font-size:{{ $fieldPositions['nama_sekretaris']['font_size'] }}mm; font-weight:bold; width:{{ $fieldPositions['nama_sekretaris']['width'] }}mm;">
                ( {{ $sekretaris?->nama ?? 'Slamet Fitriono' }} )
            </div>
        @endif

        {{-- TTD KETUA UMUM --}}
        @if(isset($fieldPositions['label_ketua']))
            <div class="kta-field kta-field-center" style="left:{{ $fieldPositions['label_ketua']['left'] }}mm; top:{{ $fieldPositions['label_ketua']['top'] }}mm; font-size:{{ $fieldPositions['label_ketua']['font_size'] }}mm; font-weight:bold; width:{{ $fieldPositions['label_ketua']['width'] }}mm;">
                Ketua Umum
            </div>
        @endif
        @if(isset($fieldPositions['ttd_ketua']) && $ttdKetua)
            <img class="kta-signature"
                 style="left:{{ $fieldPositions['ttd_ketua']['left'] }}mm;
                        top:{{ $fieldPositions['ttd_ketua']['top'] }}mm;
                        width:{{ $fieldPositions['ttd_ketua']['width'] }}mm;
                        height:{{ $fieldPositions['ttd_ketua']['height'] }}mm; position:absolute;"
                 src="{{ $ttdKetua }}" alt="TTD Ketua">
        @endif
        @if(isset($fieldPositions['nama_ketua']))
            <div class="kta-field kta-field-center" style="left:{{ $fieldPositions['nama_ketua']['left'] }}mm; top:{{ $fieldPositions['nama_ketua']['top'] }}mm; font-size:{{ $fieldPositions['nama_ketua']['font_size'] }}mm; font-weight:bold; width:{{ $fieldPositions['nama_ketua']['width'] }}mm;">
                ( {{ $ketua?->nama ?? 'Heriyanto' }} )
            </div>
        @endif

        {{-- STEMPEL ORGANISASI --}}
        @if(isset($fieldPositions['stempel']) && $stempel)
            <img class="kta-seal"
                 style="left:{{ $fieldPositions['stempel']['left'] }}mm;
                        top:{{ $fieldPositions['stempel']['top'] }}mm;
                        width:{{ $fieldPositions['stempel']['width'] }}mm;
                        height:{{ $fieldPositions['stempel']['height'] }}mm; position:absolute;"
                 src="{{ $stempel }}" alt="Stempel">
        @endif

    </div>
</div>
@endif