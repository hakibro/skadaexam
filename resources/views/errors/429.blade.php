@include('errors._page', [
    'status' => 429,
    'title' => 'Terlalu Banyak Percobaan',
    'message' => 'Sistem menerima terlalu banyak request dalam waktu singkat.',
    'hint' => 'Tunggu sebentar sebelum mencoba lagi. Ini membantu menjaga layanan tetap stabil untuk semua pengguna.',
    'tone' => 'amber',
])
