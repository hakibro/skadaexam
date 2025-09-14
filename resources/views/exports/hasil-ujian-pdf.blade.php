<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Hasil Ujian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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
        }

        td {
            padding: 6px;
        }

        .header {
            margin-bottom: 20px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 14px;
        }

        .text-green {
            color: #16a34a;
        }

        .text-red {
            color: #dc2626;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">Laporan Hasil Ujian</div>
        <div class="subtitle">Tanggal Cetak: {{ now()->format('d M Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Siswa</th>
                <th>ID Yayasan</th>
                <th>Kelas</th>
                <th>Mata Pelajaran</th>
                <th>Ujian</th>
                <th>Nilai</th>
                <th>Status</th>
                <th>Waktu Selesai</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($hasilUjians as $index => $hasil)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $hasil->siswa->nama ?? 'N/A' }}</td>
                    <td>{{ $hasil->siswa->idyayasan ?? 'N/A' }}</td>
                    <td>{{ $hasil->siswa->kelas->nama_kelas ?? 'N/A' }}</td>
                    <td>{{ $hasil->jadwalUjian->mapel->nama_mapel ?? 'N/A' }}</td>
                    <td>{{ $hasil->jadwalUjian->judul ?? 'N/A' }}</td>
                    <td class="text-center {{ $hasil->lulus ? 'text-green' : 'text-red' }}">
                        {{ $hasil->status === 'selesai' ? number_format($hasil->nilai, 2) : '-' }}
                    </td>
                    <td class="text-center">
                        @if ($hasil->status === 'selesai')
                            @if ($hasil->lulus)
                                <span class="text-green">Lulus</span>
                            @else
                                <span class="text-red">Tidak Lulus</span>
                            @endif
                        @else
                            {{ ucfirst(str_replace('_', ' ', $hasil->status)) }}
                        @endif
                    </td>
                    <td>{{ $hasil->waktu_selesai ? $hasil->waktu_selesai->format('d/m/Y H:i') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak dari sistem SkadaExam pada {{ now()->format('d M Y H:i:s') }}</p>
    </div>
</body>

</html>
