@include('errors._page', [
    'status' => $exception->getStatusCode(),
    'title' => 'Permintaan Tidak Dapat Diproses',
    'message' => 'Halaman atau aksi yang diminta tidak dapat diproses oleh sistem.',
    'hint' => 'Periksa kembali alamat, metode akses, atau hak akun yang digunakan. Anda bisa kembali ke dashboard untuk melanjutkan.',
    'tone' => 'amber',
])
