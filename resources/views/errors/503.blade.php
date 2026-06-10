@include('errors._page', [
    'status' => 503,
    'title' => 'Layanan Sementara Tidak Tersedia',
    'message' => 'Aplikasi sedang dalam perawatan atau belum bisa menerima request saat ini.',
    'hint' => 'Coba kembali beberapa saat lagi. Proses ujian atau administrasi dapat dilanjutkan setelah layanan aktif kembali.',
    'tone' => 'slate',
])
