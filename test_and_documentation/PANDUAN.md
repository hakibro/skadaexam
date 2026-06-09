# Skada Exam System

Skada Exam adalah sistem ujian online yang dapat digunakan untuk berbagai jenis ujian, termasuk ujian sekolah, try out, dan lain-lain.

## Fitur

-   Manajemen Bank Soal
-   Import Soal dari DOCX
-   Support untuk soal dengan gambar
-   Manajemen Ujian
-   Manajemen Siswa dan Guru
-   Laporan Hasil Ujian

## Format Impor Soal dari DOCX

Anda dapat mengimpor soal dari file DOCX dengan format berikut:

### Format Dasar

1. **Soal harus dimulai dengan nomor diikuti titik dan spasi**  
   Contoh: `1. Siapakah presiden pertama Indonesia?`

2. **Opsi jawaban harus diformat dengan huruf kapital diikuti titik**  
   Contoh:  
   `A. Soekarno`  
   `B. Soeharto`  
   `C. BJ Habibie`

3. **Untuk kunci jawaban, tambahkan tanda [*] di akhir**  
   Contoh: `A. Soekarno [*]`

4. **Pembahasan soal diawali dengan "Pembahasan:"**  
   Contoh: `Pembahasan: Presiden pertama Indonesia adalah Ir. Soekarno yang menjabat pada tahun 1945-1967.`

### Format untuk Soal dengan Gambar

1. **Gambar dalam soal**  
   Cukup masukkan gambar setelah teks soal, sistem akan otomatis mendeteksi dan menyimpan gambar.

2. **Gambar dalam pilihan**  
   Tulis opsi kosong (contoh: `B.`) dan masukkan gambar setelahnya.

3. **Contoh soal dengan gambar pada opsi jawaban**:

    ```
    1. Perhatikan gambar berikut. Bangunan ini terletak di kota...
    [gambar bangunan]

    A. Jakarta
    B. [gambar Bogor]
    C. Surabaya
    D. Bandung [*]
    E. Yogyakarta

    Pembahasan: Bangunan tersebut adalah Gedung Sate yang merupakan ikon kota Bandung.
    ```

### Tips Import Soal

1. **Pastikan format sesuai**: Ikuti format yang ditentukan dengan tepat agar sistem dapat membaca soal dengan benar.
2. **Ukuran gambar**: Gunakan gambar dengan resolusi yang wajar untuk mempercepat proses upload.
3. **Ukuran file**: Maksimal ukuran file DOCX adalah 10MB.
4. **Format DOCX**: Gunakan format DOCX (bukan DOC lama) untuk kompatibilitas terbaik.
5. **Tanda kunci jawaban**: Jangan lupa menandai kunci jawaban dengan `[*]`.

## Pengembangan

### Prasyarat

-   PHP 8.1 atau lebih tinggi
-   MySQL 5.7 atau lebih tinggi
-   Composer
-   Node.js dan NPM

### Instalasi

1. Clone repository ini
2. Jalankan `composer install`
3. Jalankan `npm install`
4. Salin file `.env.example` menjadi `.env`
5. Sesuaikan pengaturan database di `.env`
6. Jalankan `php artisan key:generate`
7. Jalankan `php artisan migrate`
8. Jalankan `php artisan storage:link`
9. Jalankan `php artisan serve`

## Lisensi

Skada Exam adalah software open-source berlisensi [MIT](LICENSE).
