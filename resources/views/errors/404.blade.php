@include('errors._page', [
    'status' => 404,
    'title' => 'Halaman Tidak Ditemukan',
    'message' => 'Alamat yang Anda tuju tidak tersedia, sudah dipindahkan, atau tidak pernah dibuat.',
    'hint' => 'Periksa kembali alamat halaman. Anda juga bisa kembali ke dashboard agar tidak tersesat terlalu jauh.',
    'tone' => 'blue',
])
