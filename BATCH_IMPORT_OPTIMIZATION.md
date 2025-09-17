# OPTIMASI PROSES IMPORT SISWA - BATCH PROCESSING

## Perubahan yang Dilakukan

### 1. Batch Processing untuk Siswa

-   **Ukuran Batch**: 100 siswa per batch
-   **Manfaat**: Mengurangi memory usage dan meningkatkan performa
-   **Implementasi**:
    -   Proses data dalam chunk 100 record
    -   Bulk insert untuk siswa baru
    -   Batch update untuk siswa existing

### 2. Optimasi Database Query

-   **Before**: N+1 query problem (1 query per siswa)
-   **After**: Batch query dengan `whereIn()`
-   **Manfaat**: Drastis mengurangi jumlah database queries

### 3. Bulk Operations

-   **Bulk Insert**: Menggunakan `Siswa::insert()` untuk create multiple records sekaligus
-   **Batch Update**: Update multiple records dengan prepared statements
-   **Fallback**: Individual operations jika bulk operation gagal

### 4. Progress Tracking

-   Progress berdasarkan batch, bukan per record
-   Update session lebih efisien
-   Log per batch completion

### 5. Memory Management

-   Process data dalam chunks untuk menghindari memory exhaustion
-   Unset variables setelah batch completion
-   Garbage collection hints

## Performa Improvement

### Before (Individual Processing):

```php
foreach ($apiData as $studentData) {
    $existingSiswa = Siswa::where('idyayasan', $studentData['idyayasan'])->first();
    // Process individually
}
// 1000 students = 1000 database queries minimum
```

### After (Batch Processing):

```php
for ($batchIndex = 0; $batchIndex < $totalBatches; $batchIndex++) {
    $batchIdyayasans = collect($batch)->pluck('idyayasan');
    $existingStudents = Siswa::whereIn('idyayasan', $batchIdyayasans)->get();
    // Bulk operations
}
// 1000 students = ~10 database queries (10 batches)
```

## Expected Performance Gains

-   **Database Queries**: Reduction by ~90%
-   **Memory Usage**: Controlled and predictable
-   **Processing Time**: 50-70% faster for large datasets
-   **Server Load**: Significantly reduced

## Implementation Details

### Batch Size Configuration

-   **Siswa**: 100 records per batch (optimal for most scenarios)
-   **Kelas**: 50 records per batch (usually smaller datasets)
-   **Configurable**: Can be adjusted based on server capacity

### Error Handling

-   Individual record errors don't stop entire batch
-   Fallback to individual processing if bulk operations fail
-   Detailed error logging with batch context

### Session Management

-   Progress updates per batch instead of per record
-   Reduced session save frequency
-   Better user experience with meaningful progress messages

## Usage

Import process will automatically use batch processing:

-   No configuration changes needed
-   Backward compatible
-   Same API endpoints and parameters

## Monitoring

-   Enhanced logging with batch information
-   Performance metrics in logs
-   Memory usage tracking
