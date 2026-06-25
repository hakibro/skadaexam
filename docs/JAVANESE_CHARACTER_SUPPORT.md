# Dukungan Karakter Bahasa Jawa (Aksara Jawa)

## Masalah

Karakter bahasa Jawa (aksara Jawa) tidak terbaca/ditampilkan dengan benar di sistem ujian. Browser menampilkan kotak kosong (□) atau tanda tanya (?) untuk karakter Unicode aksara Jawa.

## Penyebab

Meskipun konfigurasi encoding database dan HTML sudah benar menggunakan UTF-8/utf8mb4, font standar seperti Arial, Helvetica, dan font sistem lainnya **tidak mendukung** Unicode range aksara Jawa (U+A980–U+A9DF).

## Solusi yang Diterapkan

### 1. Menambahkan Font Noto Sans Javanese

Menambahkan Google Font "Noto Sans Javanese" yang khusus mendukung aksara Jawa ke semua view yang menampilkan soal ujian.

**File yang dimodifikasi:**

#### a. `resources/views/features/siswa/exam.blade.php`
Menambahkan link font di bagian `<head>`:

```html
<!-- Font support untuk bahasa daerah termasuk Jawa -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Javanese:wght@400;500;600;700&display=swap" rel="stylesheet">
```

#### b. `resources/views/layouts/admin.blade.php`
Menambahkan font link yang sama untuk admin dashboard, sehingga preview soal di admin juga dapat menampilkan aksara Jawa.

#### c. `resources/views/features/naskah/hasil/print.blade.php`
Menambahkan font link untuk hasil print ujian agar karakter Jawa tetap terbaca saat dicetak.

### 2. Memperbarui CSS Font-Family

Memperbarui file `resources/views/partials/rich-soal-styles.blade.php` untuk menggunakan font Javanese:

```css
.rich-soal-content {
    overflow-wrap: anywhere;
    font-family: 'Noto Sans Javanese', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.rich-soal-content th,
.rich-soal-content td {
    /* ... other styles ... */
    font-family: 'Noto Sans Javanese', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
```

**Penjelasan font-family stack:**
- `'Noto Sans Javanese'` - Font utama untuk aksara Jawa
- `'Segoe UI', Tahoma, Geneva, Verdana` - Fallback fonts untuk karakter Latin
- `sans-serif` - Generic fallback

## Verifikasi

### Konfigurasi yang Sudah Benar (tidak perlu diubah)

✅ **Database Charset:**
```php
// config/database.php
'charset' => env('DB_CHARSET', 'utf8mb4'),
'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
```

✅ **HTML Meta Charset:**
```html
<meta charset="UTF-8">
```

✅ **Blade Output:**
```blade
{!! $soal !!}  <!-- Unescaped untuk preserve HTML dan Unicode -->
```

## Cara Testing

### 1. Test di Interface Ujian Siswa

1. Login sebagai siswa
2. Mulai ujian yang memiliki soal dengan aksara Jawa
3. Karakter aksara Jawa harus tampil dengan benar, bukan kotak kosong

### 2. Test di Admin Dashboard

1. Login sebagai admin
2. Buka Bank Soal → Preview soal dengan aksara Jawa
3. Karakter harus tampil dengan benar

### 3. Test di Print/PDF

1. Buka hasil ujian siswa
2. Klik "Cetak dengan Jawaban"
3. Pada preview print, karakter aksara Jawa harus tampil dengan benar

### 4. Test dengan Karakter Sample

Contoh karakter aksara Jawa untuk testing:
```
ꦲꦏ꧀ꦱꦫꦗꦮ (Aksara Jawa)
ꦧꦱꦗꦮ (Basa Jawa)
```

## Unicode Range yang Didukung

Font Noto Sans Javanese mendukung:
- **Javanese** (U+A980–U+A9DF)
- **Javanese Supplement** (U+1B000–U+1B0FF)
- Termasuk konsonan, vokal, tanda baca, dan angka Jawa

## Dukungan Bahasa Daerah Lainnya

Jika perlu menambahkan dukungan untuk bahasa daerah lain, gunakan pendekatan yang sama:

### Sunda
```html
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sundanese&display=swap" rel="stylesheet">
```

### Bali
```html
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Balinese&display=swap" rel="stylesheet">
```

### Multiple Scripts
```html
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Javanese:wght@400;500;600;700&family=Noto+Sans+Sundanese&family=Noto+Sans+Balinese&display=swap" rel="stylesheet">
```

Kemudian update CSS:
```css
.rich-soal-content {
    font-family: 'Noto Sans Javanese', 'Noto Sans Sundanese', 'Noto Sans Balinese', 'Segoe UI', sans-serif;
}
```

## Troubleshooting

### Karakter masih tidak muncul

1. **Clear browser cache**: Tekan Ctrl+Shift+R atau Cmd+Shift+R
2. **Periksa koneksi internet**: Font dimuat dari Google CDN
3. **Periksa browser console**: Pastikan tidak ada error loading font
4. **Test di browser lain**: Untuk memastikan bukan masalah browser

### Karakter muncul tapi salah

- Pastikan text encoding saat import soal adalah UTF-8
- Periksa database charset dengan: `SHOW CREATE TABLE soal_ujian;`
- Harus menunjukkan `CHARSET=utf8mb4`

### Performance concern

Font Noto Sans Javanese ukurannya sekitar 50-70KB (compressed), tidak akan signifikan mempengaruhi loading time. Font di-cache oleh browser setelah first load.

## Tanggal Implementasi

**Tanggal:** 25 Juni 2026  
**Versi:** 1.0.0  
**Developer:** System Administrator

## Referensi

- [Google Fonts - Noto Sans Javanese](https://fonts.google.com/noto/specimen/Noto+Sans+Javanese)
- [Unicode Javanese Block](https://www.unicode.org/charts/PDF/UA980.pdf)
- [UTF-8 and Unicode Support](https://dev.mysql.com/doc/refman/8.0/en/charset-unicode.html)
