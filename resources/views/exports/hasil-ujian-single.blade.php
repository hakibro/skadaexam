<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan Hasil Ujian Siswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 14px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            padding: 8px;
            text-align: left;
            width: 30%;
            font-weight: bold;
        }

        td {
            padding: 8px;
        }

        .section-title {
            background-color: #eee;
            padding: 8px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-green {
            color: #16a34a;
        }

        .text-red {
            color: #dc2626;
        }

        .score-box {
            border: 2px solid #ddd;
            padding: 10px;
            text-align: center;
            width: 120px;
            margin: 0 auto;
        }

        .score-value {
            font-size: 24px;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">LAPORAN HASIL UJIAN SISWA</div>
        <div class="subtitle">{{ $hasil->jadwalUjian->judul ?? 'Ujian' }}</div>
    </div>

    <div class="section-title">INFORMASI SISWA</div>
    <table>
        <tr>
            <th>Nama Siswa</th>
            <td>{{ $hasil->siswa->nama ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>ID Yayasan</th>
            <td>{{ $hasil->siswa->idyayasan ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Kelas</th>
            <td>{{ $hasil->siswa->kelas->nama_kelas ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">INFORMASI UJIAN</div>
    <table>
        <tr>
            <th>Mata Pelajaran</th>
            <td>{{ $hasil->jadwalUjian->mapel->nama_mapel ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Nama Ujian</th>
            <td>{{ $hasil->jadwalUjian->judul ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Tanggal Ujian</th>
            <td>{{ $hasil->jadwalUjian->tanggal ? $hasil->jadwalUjian->tanggal->format('d M Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Sesi/Ruangan</th>
            <td>{{ $hasil->sesiRuangan->nama_sesi ?? 'N/A' }} /
                {{ $hasil->sesiRuangan->ruangan->nama_ruangan ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Waktu Mulai</th>
            <td>{{ $hasil->waktu_mulai ? $hasil->waktu_mulai->format('d M Y, H:i') : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Waktu Selesai</th>
            <td>{{ $hasil->waktu_selesai ? $hasil->waktu_selesai->format('d M Y, H:i') : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Durasi</th>
            <td>{{ $hasil->durasi_menit ?? '0' }} menit</td>
        </tr>
    </table>

    <div class="section-title">HASIL UJIAN</div>

    <div class="score-box {{ $hasil->lulus ? 'text-green' : 'text-red' }}">
        <div class="score-value">{{ number_format($hasil->nilai, 2) }}</div>
        <div>{{ $hasil->lulus ? 'LULUS' : 'TIDAK LULUS' }}</div>
    </div>

    <table style="margin-top:20px;">
        <tr>
            <th>Jumlah Soal</th>
            <td>{{ $hasil->jumlah_soal }}</td>
        </tr>
        <tr>
            <th>Jawaban Benar</th>
            <td>{{ $hasil->jumlah_benar }}</td>
        </tr>
        <tr>
            <th>Jawaban Salah</th>
            <td>{{ $hasil->jumlah_salah }}</td>
        </tr>
        <tr>
            <th>Tidak Dijawab</th>
            <td>{{ $hasil->jumlah_tidak_dijawab }}</td>
        </tr>
        <tr>
            <th>Persentase Benar</th>
            <td>{{ number_format(($hasil->jumlah_benar / $hasil->jumlah_soal) * 100, 2) }}%</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ ucfirst(str_replace('_', ' ', $hasil->status)) }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Laporan ini dicetak dari sistem SkadaExam pada {{ now()->format('d M Y H:i:s') }}.</p>
        <p>Dokumen ini bersifat resmi dan diterbitkan oleh sistem ujian sekolah.</p>
    </div>
</body>

</html>
