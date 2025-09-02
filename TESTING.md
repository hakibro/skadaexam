# SKADA Exam - Instruksi Pengujian Impor DOCX

## Persiapan Pengujian

1. Pastikan server Laravel berjalan
2. Siapkan file DOCX test dengan berbagai skenario

## Skenario Pengujian

Gunakan file DOCX dengan kombinasi berikut untuk pengujian impor:

### File Uji 1: Soal Teks Dasar

-   5 soal dengan format teks standar
-   Pilihan jawaban teks biasa
-   Kunci jawaban ditandai dengan [*]
-   Pembahasan teks sederhana

### File Uji 2: Soal dengan Gambar di Pertanyaan

-   3 soal dengan gambar di bagian pertanyaan
-   Pilihan jawaban teks biasa
-   Verifikasi gambar pertanyaan tersimpan di folder yang tepat

### File Uji 3: Soal dengan Gambar di Pilihan

-   3 soal dengan pilihan jawaban berupa gambar
-   Setiap soal minimal memiliki 2 pilihan dengan gambar
-   Verifikasi kunci jawaban yang berupa gambar berfungsi

### File Uji 4: Soal Campuran Kompleks

-   5 soal dengan kombinasi teks dan gambar di pertanyaan dan pilihan
-   Beberapa soal dengan pembahasan yang berisi gambar
-   Verifikasi semua gambar tersimpan dengan benar

## Checklist Pengujian

### Untuk setiap file uji:

1. Upload file ke bank soal
2. Verifikasi jumlah soal yang diimpor sesuai
3. Periksa setiap soal untuk memastikan:
    - Pertanyaan muncul dengan benar (teks dan/atau gambar)
    - Semua pilihan jawaban muncul dengan benar
    - Kunci jawaban ditandai dengan benar
    - Pembahasan muncul dengan benar
4. Periksa direktori penyimpanan gambar:
    - Gambar pertanyaan di `/storage/soal/pertanyaan/`
    - Gambar pilihan di `/storage/soal/pilihan/`
    - Gambar pembahasan di `/storage/soal/pembahasan/`

## Langkah-langkah Detail

1. Login ke sistem
2. Buat bank soal baru atau pilih yang sudah ada
3. Upload file DOCX uji
4. Setelah impor selesai, catat:
    - Jumlah soal berhasil diimpor
    - Jumlah error (jika ada)
    - Waktu yang dibutuhkan untuk impor
5. Periksa setiap soal yang diimpor secara manual
6. Catat semua masalah yang ditemukan

## Pelaporan Hasil

Dokumentasikan temuan dengan format berikut:

```
Nama File Uji: [nama_file]
Jumlah Soal: [jumlah]
Waktu Impor: [waktu]
Soal Berhasil: [jumlah]
Error: [jumlah]

Masalah yang ditemukan:
1. [deskripsi masalah]
2. [deskripsi masalah]

Saran perbaikan:
1. [saran]
2. [saran]
```

## Pemecahan Masalah Umum

### Gambar tidak muncul

-   Periksa apakah path penyimpanan benar
-   Pastikan symbolic link storage sudah dibuat dengan benar
-   Periksa permission direktori storage

### Kunci jawaban tidak terdeteksi

-   Pastikan format [*] digunakan dengan tepat
-   Periksa log untuk melihat apakah ada masalah deteksi

### Error saat impor

-   Periksa log Laravel untuk detail error
-   Verifikasi format DOCX sesuai dengan panduan
