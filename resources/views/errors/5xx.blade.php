@include('errors._page', [
    'status' => method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500,
    'title' => 'Terjadi Gangguan',
    'message' => 'Sistem mengalami gangguan saat memproses permintaan Anda.',
    'hint' => 'Silakan coba lagi beberapa saat lagi. Jika masalah berulang, hubungi administrator dengan informasi halaman yang dibuka.',
    'tone' => 'red',
])
