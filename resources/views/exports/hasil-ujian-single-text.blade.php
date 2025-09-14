LAPORAN HASIL UJIAN SISWA
{{ $hasil->jadwalUjian->judul ?? 'Ujian' }}

INFORMASI SISWA
Nama Siswa: {{ $hasil->siswa->nama ?? 'N/A' }}
ID Yayasan: {{ $hasil->siswa->idyayasan ?? 'N/A' }}
Kelas: {{ $hasil->siswa->kelas->nama_kelas ?? 'N/A' }}

INFORMASI UJIAN
Mata Pelajaran: {{ $hasil->jadwalUjian->mapel->nama_mapel ?? 'N/A' }}
Nama Ujian: {{ $hasil->jadwalUjian->judul ?? 'N/A' }}
Tanggal Ujian: {{ $hasil->jadwalUjian->tanggal ? $hasil->jadwalUjian->tanggal->format('d M Y') : 'N/A' }}
Sesi/Ruangan: {{ $hasil->sesiRuangan->nama_sesi ?? 'N/A' }} / {{ $hasil->sesiRuangan->ruangan->nama_ruangan ?? 'N/A' }}
Waktu Mulai: {{ $hasil->waktu_mulai ? $hasil->waktu_mulai->format('d M Y, H:i') : 'N/A' }}
Waktu Selesai: {{ $hasil->waktu_selesai ? $hasil->waktu_selesai->format('d M Y, H:i') : 'N/A' }}
Durasi: {{ $hasil->durasi_menit ?? '0' }} menit

HASIL UJIAN
Nilai: {{ number_format($hasil->nilai, 2) }}
Status: {{ $hasil->lulus ? 'LULUS' : 'TIDAK LULUS' }}

Jumlah Soal: {{ $hasil->jumlah_soal }}
Jawaban Benar: {{ $hasil->jumlah_benar }}
Jawaban Salah: {{ $hasil->jumlah_salah }}
Tidak Dijawab: {{ $hasil->jumlah_tidak_dijawab }}
Persentase Benar: {{ number_format(($hasil->jumlah_benar / $hasil->jumlah_soal) * 100, 2) }}%
Status: {{ ucfirst(str_replace('_', ' ', $hasil->status)) }}

Laporan ini dicetak dari sistem SkadaExam pada {{ now()->format('d M Y H:i:s') }}.
Dokumen ini bersifat resmi dan diterbitkan oleh sistem ujian sekolah.
