@include('errors._page', [
    'status' => 500,
    'title' => 'Terjadi Gangguan',
    'message' => 'Sistem mengalami masalah saat memproses permintaan Anda.',
    'hint' => 'Silakan coba lagi beberapa saat lagi. Jika masalah berulang, sampaikan halaman yang dibuka dan waktu kejadian kepada administrator.',
    'tone' => 'red',
])
