@include('errors._page', [
    'status' => 403,
    'title' => 'Akses Ditolak',
    'message' => 'Anda tidak memiliki izin untuk membuka halaman atau menjalankan aksi ini.',
    'hint' => 'Pastikan Anda menggunakan akun dengan peran yang sesuai. Jika seharusnya memiliki akses, hubungi administrator.',
    'tone' => 'amber',
])
