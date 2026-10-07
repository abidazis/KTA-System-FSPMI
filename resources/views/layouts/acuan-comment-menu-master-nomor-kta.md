# Acuan Pembaruan Sistem KTA (Fase 1: Simplifikasi Nomor Anggota)

## Konteks Perubahan
User memutuskan untuk tidak menggunakan generator formula otomatis untuk Nomor Anggota. Manajemen Nomor Anggota akan dilakukan secara manual (input langsung) pada modul Data Anggota.

## Task List
1. **Nonaktifkan Menu Master KTA**
   - Hide/comment out menu navigasi untuk "Kelola Nomor Anggota" dan "Formula Nomor".
   - (Opsional) Comment out route yang mengarah ke modul tersebut agar tidak bisa diakses via URL.

2. **Modifikasi Modul Data Anggota (UI/UX)**
   - **Form Create:** Tambahkan field input text untuk `nomor_anggota`. Pastikan statusnya *required*.
   - **Form Edit:** Tambahkan/aktifkan field input text untuk `nomor_anggota` agar bisa diubah. 
   - Tampilkan kolom `nomor_anggota` di tabel Data Index (jika belum ada).

3. **Penyesuaian Backend & Validasi**
   - Update Controller `Store` dan `Update` pada Data Anggota untuk menerima request `nomor_anggota`.
   - Tambahkan rule validasi: `required`, `string`, dan `unique` (pastikan nomor anggota tidak boleh duplikat di database).
   - Pastikan rule `unique` pada saat *Update* mengecualikan ID anggota yang sedang diedit.