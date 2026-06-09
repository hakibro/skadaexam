# Implementasi Sistem Penjadwalan Fleksibel Berbasis Sesi Ruangan

## Overview

Sistem ini telah diperbarui untuk mendukung konsep penjadwalan ujian yang lebih fleksibel, di mana sesi ruangan dengan tanggal yang sama dengan jadwal ujian akan otomatis terkait, dan waktu ujian akan mengikuti waktu dari masing-masing sesi ruangan.

## Fitur Utama

### 1. **Mode Penjadwalan (Scheduling Mode)**

-   **Flexible Mode**: Waktu ujian mengikuti sesi ruangan yang terkait
-   **Fixed Mode**: Waktu ujian tetap sesuai tanggal dan durasi yang ditentukan

### 2. **Auto Assignment Sesi Ruangan**

-   Sistem otomatis mengaitkan sesi ruangan berdasarkan tanggal yang sama
-   Dapat diaktifkan/dinonaktifkan per jadwal ujian
-   Validasi kapasitas ruangan dan durasi ujian

### 3. **Multiple Time Slots**

-   Satu jadwal ujian dapat memiliki beberapa slot waktu
-   Setiap slot waktu mengikuti jadwal sesi ruangan
-   Total kapasitas terdistribusi di semua sesi

## Implementasi Database

### Migration: `modify_jadwal_ujian_for_sesi_based_scheduling`

```sql
ALTER TABLE jadwal_ujian ADD COLUMN:
- auto_assign_sesi BOOLEAN DEFAULT true
- scheduling_mode ENUM('fixed', 'flexible') DEFAULT 'flexible'
- timezone VARCHAR(191) DEFAULT 'Asia/Jakarta'
```

## Service Layer

### `SesiAssignmentService`

#### Method Utama:

1. **`autoAssignSesiByDate(JadwalUjian $jadwal)`**

    - Auto assign sesi berdasarkan tanggal yang sama
    - Return: jumlah sesi yang berhasil ditambahkan

2. **`getConsolidatedSchedule(JadwalUjian $jadwal)`**

    - Mengembalikan informasi lengkap jadwal dan sesi terkait
    - Termasuk waktu paling awal, paling akhir, dan total kapasitas

3. **`cleanupAssignments(JadwalUjian $jadwal)`**

    - Membersihkan assignment yang tidak valid/expired

4. **`autoAssignForAllEligibleJadwal()`**
    - Auto assign untuk semua jadwal yang memenuhi kriteria

#### Kriteria Sesi yang Suitable:

-   Status tidak dibatalkan
-   Durasi sesuai (toleransi ±15 menit)
-   Kapasitas ruangan minimal 10
-   Tanggal yang sama dengan jadwal ujian

## Model Updates

### JadwalUjian Model

#### New Attributes:

-   `auto_assign_sesi`: Boolean untuk mengaktifkan auto assignment
-   `scheduling_mode`: Mode penjadwalan ('fixed' atau 'flexible')
-   `timezone`: Zona waktu untuk jadwal

#### New Methods:

-   `isFlexibleScheduling()`: Check apakah menggunakan flexible scheduling
-   `getTotalCapacity()`: Total kapasitas dari semua sesi terkait
-   `getTimeSlots()`: Array semua slot waktu tersedia
-   `getScheduleSummary()`: Ringkasan jadwal untuk display

#### Updated Accessors:

-   `getWaktuMulaiAttribute()`: Untuk flexible mode, ambil waktu paling awal dari sesi
-   `getWaktuSelesaiAttribute()`: Untuk flexible mode, ambil waktu paling akhir dari sesi

## Controller Updates

### JadwalUjianController

#### New Methods:

1. **`reassignSesi(JadwalUjian $jadwal)`**

    - Re-assign sesi ruangan untuk jadwal tertentu
    - Cleanup + auto assign ulang

2. **`toggleAutoAssign(JadwalUjian $jadwal)`**

    - Toggle auto assignment on/off
    - Jika diaktifkan, langsung jalankan assignment

3. **`switchSchedulingMode(JadwalUjian $jadwal)`**
    - Switch antara fixed dan flexible mode
    - Handle logic perpindahan mode

#### Updated Methods:

-   **`store()`**: Support scheduling_mode dan auto_assign_sesi
-   **`show()`**: Include schedule information dari SesiAssignmentService

## Routes

### New Routes:

```php
Route::post('jadwal/{jadwal}/reassign-sesi', 'reassignSesi');
Route::put('jadwal/{jadwal}/toggle-auto-assign', 'toggleAutoAssign');
Route::put('jadwal/{jadwal}/switch-scheduling-mode', 'switchSchedulingMode');
```

## Artisan Command

### `sesi:auto-assign`

```bash
# Auto assign untuk semua jadwal eligible
php artisan sesi:auto-assign

# Auto assign untuk jadwal tertentu
php artisan sesi:auto-assign --jadwal-id=123

# Dry run untuk melihat preview
php artisan sesi:auto-assign --dry-run
```

## Interface Updates

### Create Form (`jadwal/create.blade.php`)

#### New Fields:

1. **Scheduling Mode Selector**

    - Dropdown: Flexible / Fixed
    - Dynamic description berdasarkan mode

2. **Auto Assign Checkbox**
    - Hanya muncul untuk flexible mode
    - Default: enabled

#### JavaScript Enhancements:

-   Dynamic form behavior berdasarkan scheduling mode
-   Hide/show auto assign option

### Show View (`jadwal/show.blade.php`)

#### New Information Sections:

1. **Schedule Summary**

    - Mode penjadwalan aktif
    - Total sesi terkait
    - Rentang waktu keseluruhan

2. **Time Slots List**

    - Detail setiap sesi ruangan
    - Waktu, ruangan, kapasitas per slot

3. **Assignment Controls**
    - Toggle auto assignment
    - Manual reassign button
    - Switch scheduling mode

## Workflow Example

### Skenario: Membuat Jadwal UTS Matematika

1. **User membuat jadwal baru**:

    - Judul: "UTS Matematika Kelas X"
    - Tanggal: 2025-09-15
    - Mode: Flexible (default)
    - Auto assign: Enabled (default)

2. **System auto assignment**:

    - Cari sesi ruangan dengan tanggal 2025-09-15
    - Filter berdasarkan kapasitas dan durasi
    - Auto link sesi yang sesuai

3. **Hasil**:
    - Jadwal terkait dengan 3 sesi ruangan:
        - Ruang A1: 08:00-10:00 (30 siswa)
        - Ruang A2: 08:00-10:00 (30 siswa)
        - Ruang B1: 10:15-12:15 (25 siswa)
    - Total kapasitas: 85 siswa
    - Waktu ujian: 08:00-12:15

## Benefits

### 1. **Fleksibilitas Waktu**

-   Satu jadwal ujian bisa punya multiple time slots
-   Waktu mengikuti ketersediaan ruangan
-   Optimal utilization ruangan

### 2. **Automation**

-   Auto assignment berdasarkan tanggal
-   Cleanup otomatis untuk assignment expired
-   Command untuk batch processing

### 3. **Scalability**

-   Support untuk exam scheduling yang kompleks
-   Easy maintenance dengan service layer
-   Consistent data dengan validation

### 4. **User Experience**

-   Interface yang intuitive
-   Real-time feedback untuk assignment
-   Clear visualization schedule information

## Migration Guide

### Untuk Data Existing:

1. Semua jadwal existing otomatis set ke:

    - `scheduling_mode = 'flexible'`
    - `auto_assign_sesi = true`
    - `timezone = 'Asia/Jakarta'`

2. Jalankan auto assignment:
    ```bash
    php artisan sesi:auto-assign
    ```

### Testing:

1. Test dengan jadwal baru (flexible mode)
2. Verify auto assignment working
3. Test manual reassignment
4. Test mode switching

## Future Enhancements

### Potential Features:

1. **Smart Assignment Algorithm**

    - AI-based room allocation
    - Load balancing optimization
    - Conflict resolution

2. **Advanced Scheduling**

    - Multi-day exam sessions
    - Break time management
    - Resource dependency tracking

3. **Analytics & Reporting**
    - Capacity utilization reports
    - Assignment success metrics
    - Performance optimization insights

---

**Status**: ✅ Fully Implemented and Tested  
**Version**: 2025-09-05  
**Migration**: Applied  
**Command**: Available  
**Interface**: Updated
