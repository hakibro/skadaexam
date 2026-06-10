@include('errors._page', [
    'status' => 419,
    'title' => 'Sesi Kedaluwarsa',
    'message' => 'Halaman ini sudah terlalu lama terbuka sehingga token keamanan tidak lagi valid.',
    'hint' => 'Muat ulang halaman, lalu ulangi aksi terakhir. Jika sedang mengisi data, buka lagi halaman dari dashboard agar token sesi diperbarui.',
    'tone' => 'amber',
])
