# Fitur Mode Kiosk - Dokumentasi

## Deskripsi
Fitur mode kiosk memungkinkan admin untuk mengatur password exit dan masa berlaku password untuk mengunci aplikasi ujian pada perangkat siswa.

## Fitur yang Diimplementasikan

### 1. Menu Admin
- Menu baru "Mode Kiosk" telah ditambahkan di sidebar admin
- Lokasi: Di bawah "Setting Sekolah" di panel admin
- Icon: Desktop (fa-desktop)

### 2. Halaman Pengaturan Kiosk
**URL:** `/admin/kiosk-settings`

**Fitur:**
- Form input password exit (minimal 4 karakter, maksimal 50 karakter)
- Form input masa berlaku password (datetime picker)
- Menampilkan status password saat ini (aktif/kadaluarsa)
- Tombol "Test API" untuk menguji endpoint API
- Tombol "Simpan Pengaturan" untuk menyimpan konfigurasi

### 3. API Endpoints

#### a. GET /api/kiosk/settings
Mengambil pengaturan kiosk saat ini.

**Response:**
```json
{
  "success": true,
  "data": {
    "exit_password": "password123",
    "password_expires_at": "2026-12-31 23:59:59",
    "is_expired": false
  }
}
```

#### b. POST /api/kiosk/settings
Menyimpan pengaturan kiosk baru.

**Request Body:**
```json
{
  "exit_password": "password123",
  "password_expires_at": "2026-12-31 23:59:59"
}
```

**Validasi:**
- `exit_password`: required, string, minimal 4 karakter, maksimal 50 karakter
- `password_expires_at`: required, date, harus tanggal di masa depan

**Response Success:**
```json
{
  "success": true,
  "message": "Pengaturan mode kiosk berhasil disimpan",
  "data": {
    "exit_password": "password123",
    "password_expires_at": "2026-12-31 23:59:59"
  }
}
```

**Response Error:**
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "exit_password": ["Password minimal 4 karakter"],
    "password_expires_at": ["Tanggal harus di masa depan"]
  }
}
```

#### c. POST /api/kiosk/verify
Memverifikasi password exit yang dimasukkan pengguna.

**Request Body:**
```json
{
  "password": "password123"
}
```

**Response Password Benar:**
```json
{
  "success": true,
  "message": "Password benar"
}
```

**Response Password Salah:**
```json
{
  "success": false,
  "message": "Password salah"
}
```

**Response Password Kadaluarsa:**
```json
{
  "success": false,
  "message": "Password telah kadaluarsa"
}
```

## Struktur Database

### Tabel: kiosk_settings
```sql
CREATE TABLE kiosk_settings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Keys yang digunakan:**
- `exit_password`: Password untuk keluar dari mode kiosk
- `password_expires_at`: Tanggal dan waktu kadaluarsa password

## File-file yang Dibuat/Dimodifikasi

### File Baru:
1. **Migration:**
   - `database/migrations/2026_06_24_210826_create_kiosk_settings_table.php`

2. **Model:**
   - `app/Models/KioskSetting.php`

3. **Controllers:**
   - `app/Http/Controllers/Admin/KioskSettingController.php`
   - `app/Http/Controllers/Api/KioskSettingController.php`

4. **Views:**
   - `resources/views/admin/kiosk-settings/edit.blade.php`

### File yang Dimodifikasi:
1. **Routes:**
   - `routes/admin.php` - Menambahkan route admin kiosk settings
   - `routes/api.php` - Menambahkan route API kiosk

2. **Layout:**
   - `resources/views/layouts/admin.blade.php` - Menambahkan menu Mode Kiosk di sidebar

## Cara Penggunaan

### Untuk Admin:

1. **Mengatur Password Kiosk:**
   - Login sebagai admin
   - Buka menu "Mode Kiosk" di sidebar
   - Masukkan password exit yang diinginkan
   - Pilih tanggal dan waktu kadaluarsa
   - Klik "Simpan Pengaturan"

2. **Test API:**
   - Setelah mengisi form, klik tombol "Test API"
   - Modal akan menampilkan hasil request ke API
   - Gunakan untuk memverifikasi API berfungsi dengan baik

### Untuk Aplikasi Klien (Siswa):

1. **Mengambil Password Kiosk:**
   ```javascript
   fetch('/api/kiosk/settings')
     .then(response => response.json())
     .then(data => {
       if (data.success && !data.data.is_expired) {
         // Gunakan password untuk mode kiosk
         const exitPassword = data.data.exit_password;
       }
     });
   ```

2. **Memverifikasi Password Exit:**
   ```javascript
   fetch('/api/kiosk/verify', {
     method: 'POST',
     headers: {
       'Content-Type': 'application/json',
       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
     },
     body: JSON.stringify({
       password: userInputPassword
     })
   })
   .then(response => response.json())
   .then(data => {
     if (data.success) {
       // Password benar, izinkan keluar dari kiosk
     } else {
       // Password salah atau kadaluarsa
       alert(data.message);
     }
   });
   ```

## Fitur Keamanan

1. **Validasi Input:**
   - Password minimal 4 karakter untuk mencegah password terlalu lemah
   - Password maksimal 50 karakter
   - Tanggal kadaluarsa harus di masa depan

2. **Caching:**
   - Pengaturan di-cache untuk performa lebih baik
   - Cache otomatis dibersihkan saat pengaturan diupdate

3. **Verifikasi Kadaluarsa:**
   - API otomatis mengecek apakah password sudah kadaluarsa
   - Password kadaluarsa tidak dapat digunakan

## Catatan Pengembangan

- Model `KioskSetting` menggunakan pattern key-value seperti `SchoolSetting`
- Implementasi menggunakan Laravel cache untuk optimasi
- CSRF protection aktif pada semua endpoint
- Response API mengikuti format standar (success, message, data)

## Testing

### Manual Testing:
1. Akses `/admin/kiosk-settings`
2. Isi form dan klik "Simpan Pengaturan"
3. Klik tombol "Test API" untuk verifikasi
4. Gunakan Postman/curl untuk test endpoint API

### Contoh cURL:
```bash
# Get settings
curl -X GET http://localhost/api/kiosk/settings

# Update settings
curl -X POST http://localhost/api/kiosk/settings \
  -H "Content-Type: application/json" \
  -d '{"exit_password":"test1234","password_expires_at":"2026-12-31 23:59:59"}'

# Verify password
curl -X POST http://localhost/api/kiosk/verify \
  -H "Content-Type: application/json" \
  -d '{"password":"test1234"}'
```

## Troubleshooting

### Route tidak ditemukan:
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Perubahan tidak muncul:
```bash
php artisan cache:forget kiosk_settings
```

### Migrasi gagal:
```bash
php artisan migrate:rollback --step=1
php artisan migrate
```

---

**Tanggal Dibuat:** 24 Juni 2026  
**Versi:** 1.0.0  
**Status:** ✅ Selesai dan Siap Digunakan
