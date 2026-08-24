@php
    /*
    |--------------------------------------------------------------------------
    | KTA CARD PDF ADAPTER
    |--------------------------------------------------------------------------
    | Wrapper untuk render KTA dalam container PDF dengan ukuran mm.
    | Memanggil kta-card.blade.php asli sebagai partial.
    |
    | Variabel yang dibutuhkan:
    | - $member          : data anggota
    | - $side            : 'front' atau 'back'
    | - $ketua           : object official (opsional)
    | - $sekretaris      : object official (opsional)
    | - $ttd_ketua_path  : path file ttd ketua (opsional)
    | - $ttd_sekretaris_path : path file ttd sekretaris (opsional)
    |--------------------------------------------------------------------------
    */
    $isPdf = true; // flag agar kta-card.blade.php menggunakan absolute file paths untuk logo
@endphp

<div class="kta-pdf-slot">
    @include('print.partials.kta-card')
</div>