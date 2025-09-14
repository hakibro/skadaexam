# Perbaikan Error "siswa Gagal mengumpulkan ujian: Unknown error"

Dalam commit ini, beberapa perbaikan telah diterapkan untuk mengatasi masalah siswa yang tidak dapat mengumpulkan ujian dan masalah dengan data yang tidak lengkap di tabel hasil_ujian.

## Identifikasi Masalah

Beberapa masalah yang teridentifikasi:

1. **Inkonsistensi Model-Tabel**:

    - Model `SoalUjian` menggunakan tabel `soal`, tetapi referensi di foreign key tabel `jawaban_siswas` mengacu ke tabel `soal_ujians`.
    - Nama tabel tidak konsisten, `jawaban_siswa` vs `jawaban_siswas`.

2. **Inkonsistensi Relasi di Method**:

    - Method `calculateScore` mereferensikan `soalUjians()`, tetapi di model `JadwalUjian` yang ada adalah `soals()`.

3. **Error Handling yang Tidak Informatif**:

    - Method `submitExam` di `SiswaDashboardController` hanya mengembalikan "Unknown error" tanpa detail.

4. **Data Tidak Lengkap di Tabel hasil_ujian**:
    - Beberapa kolom penting di tabel `hasil_ujian` tidak terisi saat membuat atau mengumpulkan ujian:
        - `sesi_ruangan_id` = null
        - `durasi_menit` = null
        - `jumlah_soal` = 0
        - `jumlah_dijawab` = 0
        - `is_final` = 0

## Perbaikan yang Dilakukan

1. **Meningkatkan Error Handling**:

    - `SiswaDashboardController@submitExam`: Memperbaiki error handling untuk menampilkan pesan lebih spesifik di mode debug.
    - View exam.blade.php: Memperbaiki tampilan pesan error.

2. **Memperbaiki Model JawabanSiswa**:

    - Memperjelas definisi tabel (`protected $table = 'jawaban_siswa'`).
    - Memperbaiki relasi dengan `SoalUjian` dengan parameter yang lebih spesifik.

3. **Memperbaiki Method calculateScore**:

    - Menggunakan `bankSoal_id` untuk mencari soal yang terkait dengan ujian.
    - Memastikan pengecekan null untuk `$jawaban->jawaban`.

4. **Menyediakan Migrasi Perbaikan Database**:

    - Migrasi `2023_10_30_000001_fix_jawaban_siswa_foreign_key.php` untuk memperbaiki masalah foreign key.
    - Menangani kemungkinan tabel yang memiliki nama berbeda (`jawaban_siswa` vs `jawaban_siswas`).

5. **Panel Admin untuk Menjalankan Perbaikan**:

    - Controller `MaintenanceController` untuk menjalankan migrasi dan memeriksa struktur tabel.
    - View untuk menampilkan status dan kemajuan perbaikan.
    - Rute khusus untuk admin di `/maintenance/fix-jawaban-siswa`.

6. **Perbaikan pada Metode `exam` dan `submitExam`**:

    - Mengisi nilai kolom `sesi_ruangan_id`, `durasi_menit`, `jumlah_soal` saat memulai ujian
    - Memperbarui `jumlah_dijawab` setiap kali siswa menyimpan jawaban
    - Memastikan `is_final = true` saat ujian dikumpulkan
    - Menambahkan penghitungan durasi ujian yang sebenarnya
    - Memperbaiki perhitungan nilai rata-rata dan status lulus berdasarkan KKM 75

7. **Script Perbaikan Data yang Sudah Ada**:
    - Membuat Command `fix:hasil-ujian` untuk memperbaiki data yang sudah ada
    - Membuat file `public/fix-hasil-ujian.php` untuk perbaikan data melalui browser

## Cara Menjalankan Perbaikan

1. Login sebagai admin
2. Akses `/maintenance/fix-jawaban-siswa`
3. Klik tombol "Jalankan Migrasi Perbaikan" untuk memperbaiki struktur tabel
4. Klik link "Jalankan Perbaikan Data Hasil Ujian" untuk memperbaiki data yang sudah ada

Atau melalui command line:

```
php artisan migrate --path=database/migrations/2023_10_30_000001_fix_jawaban_siswa_foreign_key.php
php artisan fix:hasil-ujian
```

## Hasil yang Diharapkan

Setelah perbaikan dijalankan:

1. Siswa seharusnya bisa mengumpulkan ujian tanpa mendapatkan pesan "Unknown error"
2. Semua data di tabel `hasil_ujian` akan memiliki nilai yang benar untuk semua kolom
3. Kolom `jumlah_dijawab` dan `jumlah_tidak_dijawab` akan mencerminkan jumlah soal yang benar-benar dijawab oleh siswa
4. Kolom `nilai` akan berisi nilai rata-rata yang dihitung dengan benar
5. Kolom `lulus` akan menandakan kelulusan siswa berdasarkan KKM 75
6. Laporan dan analisis hasil ujian akan menampilkan data yang akurat
7. Jika masih terjadi error, pesan yang lebih spesifik akan ditampilkan di mode debug untuk membantu troubleshooting lebih lanjut

## Dampak Perubahan

1. **Pembuatan Hasil Ujian**:

    - Sekarang akan selalu mengisi `sesi_ruangan_id`, `durasi_menit`, dan `jumlah_soal`
    - Menghitung `jumlah_tidak_dijawab` = `jumlah_soal` saat awal

2. **Penyimpanan Jawaban**:

    - Sekarang akan memperbarui `jumlah_dijawab` dan `jumlah_tidak_dijawab` setiap kali menyimpan jawaban

3. **Pengumpulan Ujian**:
    - Memperbarui semua field dengan nilai yang benar:
        - `is_final` = true
        - `durasi_menit` = durasi sebenarnya dari waktu_mulai hingga waktu_selesai
        - `jumlah_soal`, `jumlah_dijawab`, `jumlah_benar`, `jumlah_salah`, `jumlah_tidak_dijawab` sesuai dengan data sebenarnya
