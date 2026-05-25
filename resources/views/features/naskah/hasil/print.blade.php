<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Hasil Ujian - {{ $hasil->siswa->nama ?? 'Siswa' }}</title>
    <style>
        body {
            color: #111827;
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.45;
            margin: 28px;
        }

        h1,
        h2,
        h3 {
            margin: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 7px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-weight: 700;
        }

        .header {
            align-items: flex-start;
            border-bottom: 2px solid #111827;
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 12px;
        }

        .muted {
            color: #6b7280;
        }

        .grid {
            display: grid;
            gap: 12px;
            grid-template-columns: 1fr 1fr;
            margin-bottom: 18px;
        }

        .box {
            border: 1px solid #d1d5db;
            padding: 12px;
        }

        .score {
            font-size: 34px;
            font-weight: 700;
        }

        .badge {
            border-radius: 999px;
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
        }

        .pass {
            background: #dcfce7;
            color: #166534;
        }

        .fail {
            background: #fee2e2;
            color: #991b1b;
        }

        @media print {
            body {
                margin: 16px;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom: 16px;">
        <button onclick="window.print()">Cetak</button>
    </div>

    <div class="header">
        <div>
            <h1>Hasil Ujian</h1>
            <p class="muted">{{ $hasil->jadwalUjian->judul ?? '-' }}</p>
        </div>
        <div style="text-align: right;">
            <div class="score">{{ number_format((float) $hasil->nilai, 2) }}</div>
            <span class="badge {{ $hasil->lulus ? 'pass' : 'fail' }}">{{ $hasil->lulus ? 'Lulus' : 'Tidak Lulus' }}</span>
        </div>
    </div>

    <div class="grid">
        <div class="box">
            <h3>Data Siswa</h3>
            <table style="margin-top: 10px;">
                <tr>
                    <th>Nama</th>
                    <td>{{ $hasil->siswa->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <th>NIS</th>
                    <td>{{ $hasil->siswa->nis ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Kelas</th>
                    <td>{{ $hasil->siswa->kelas->nama_kelas ?? '-' }}</td>
                </tr>
            </table>
        </div>
        <div class="box">
            <h3>Data Ujian</h3>
            <table style="margin-top: 10px;">
                <tr>
                    <th>Mapel</th>
                    <td>{{ $hasil->jadwalUjian->mapel->nama_mapel ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Bank Soal</th>
                    <td>{{ $hasil->jadwalUjian->bankSoal->judul ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Sesi</th>
                    <td>{{ $hasil->sesiRuangan->nama_sesi ?? '-' }} / {{ $hasil->sesiRuangan->ruangan->nama_ruangan ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Jumlah Soal</th>
                <th>Dijawab</th>
                <th>Benar</th>
                <th>Salah</th>
                <th>Kosong</th>
                <th>Durasi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $hasil->jumlah_soal }}</td>
                <td>{{ $hasil->jumlah_dijawab }}</td>
                <td>{{ $hasil->jumlah_benar }}</td>
                <td>{{ $hasil->jumlah_salah }}</td>
                <td>{{ $hasil->jumlah_tidak_dijawab }}</td>
                <td>{{ $hasil->durasi_menit ? $hasil->durasi_menit . ' menit' : $hasil->getDurationFormatted() }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $hasil->status)) }}</td>
            </tr>
        </tbody>
    </table>

    @if (count($answerRows) > 0)
        <h2 style="margin-top: 22px;">Detail Jawaban</h2>
        <table style="margin-top: 10px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Soal</th>
                    <th>Jawaban</th>
                    <th>Kunci</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($answerRows as $row)
                    <tr>
                        <td>{{ $row['nomor'] }}</td>
                        <td>{!! $row['pertanyaan'] !!}</td>
                        <td>{{ $row['jawaban'] ?: '-' }}</td>
                        <td>{{ $row['kunci'] ?: '-' }}</td>
                        <td>{{ ucfirst($row['status']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
