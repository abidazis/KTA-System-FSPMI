# Acuan Pembaruan Sistem KTA (Fase 2: Layout Print 5 KTA per Halaman)

## Konteks Perubahan
Layout preview dan cetak PDF (A4 Landscape) yang sebelumnya memuat 4 KTA per halaman harus dioptimasi menjadi 5 KTA per halaman. Ukuran dimensi kartu KTA tidak boleh diubah. Solusinya adalah merapatkan *gap* (jarak antar kartu) dan memaksimalkan margin kertas.

## Task List
1. **Update Logika Controller (`PrintController.php`)**
   - Cari metode `preview`, `show`, dan `downloadPdf`.
   - Ubah logika pembagian data (*chunking*) dari yang sebelumnya dibagi 4 (`->chunk(4)`) menjadi dibagi 5 (`->chunk(5)`).

2. **Penyesuaian View Preview (`print/preview.blade.php` & `print/show.blade.php`)**
   - Ubah *class* CSS grid/flex yang membungkus kartu KTA. (Misal: dari `grid-cols-4` menjadi `grid-cols-5`).
   - Kurangi nilai *gap* (jarak antar kolom) menjadi seminimal mungkin (misal `gap-1` atau `gap-2` di Tailwind) agar 5 kartu muat dalam lebar layar/halaman.

3. **Penyesuaian Layout Cetak PDF (`print/batch-pdf.blade.php` / `batch-pdf-5up.blade.php`)**
   - Jika route memanggil `batch-pdf.blade.php`, arahkan agar menggunakan logika 5 kolom, atau integrasikan kode dari `batch-pdf-5up.blade.php` jika file tersebut adalah template khususnya.
   - Sesuaikan aturan `@page` pada CSS PDF (contoh: `@page { size: A4 landscape; margin: 5mm; }`).
   - Pastikan *wrapper* masing-masing kartu memiliki margin/padding yang di- *press* (dikompres), namun dimensi (width/height) dari komponen kartu (`kta-card.blade.php` atau sejenisnya) **TIDAK** diubah.