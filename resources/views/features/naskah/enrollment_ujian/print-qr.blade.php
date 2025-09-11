<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Login - {{ $enrollmentUjian->siswa->nama }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .qr-container {
            text-align: center;
            margin: 20px 0;
        }

        .qr-container img {
            max-width: 250px;
            height: auto;
        }

        .student-info {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .student-info td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .student-info td:first-child {
            font-weight: bold;
            width: 150px;
        }

        .exam-info {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .exam-info td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .exam-info td:first-child {
            font-weight: bold;
            width: 150px;
        }

        .token {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
            letter-spacing: 5px;
        }

        .instructions {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>QR Code Login Ujian</h1>
            <p>{{ config('app.name') }} - {{ date('d M Y') }}</p>
        </div>

        <div class="student-info">
            <table width="100%">
                <tr>
                    <td>NIS</td>
                    <td>: {{ $enrollmentUjian->siswa->nis }}</td>
                </tr>
                <tr>
                    <td>Nama Siswa</td>
                    <td>: {{ $enrollmentUjian->siswa->nama }}</td>
                </tr>
                <tr>
                    <td>Kelas</td>
                    <td>: {{ $enrollmentUjian->siswa->kelas->nama ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <div class="exam-info">
            <table width="100%">
                <tr>
                    <td>Ujian</td>
                    <td>:
                        {{ $enrollmentUjian->jadwalUjian->judul ?? ($enrollmentUjian->sesiRuangan->jadwalUjians->first()?->judul ?? 'N/A') }}
                    </td>
                </tr>
                <tr>
                    <td>Mata Pelajaran</td>
                    <td>:
                        {{ $enrollmentUjian->jadwalUjian->mapel->nama ?? ($enrollmentUjian->sesiRuangan->jadwalUjians->first()?->mapel->nama ?? 'N/A') }}
                    </td>
                </tr>
                <tr>
                    <td>Sesi</td>
                    <td>: {{ $enrollmentUjian->sesiRuangan->nama_sesi }}</td>
                </tr>
                <tr>
                    <td>Ruangan</td>
                    <td>: {{ $enrollmentUjian->sesiRuangan->ruangan->nama }}</td>
                </tr>
                <tr>
                    <td>Waktu Ujian</td>
                    <td>: {{ $enrollmentUjian->sesiRuangan->waktu_mulai->format('d M Y H:i') }} -
                        {{ $enrollmentUjian->sesiRuangan->waktu_selesai->format('d M Y H:i') }}</td>
                </tr>
            </table>
        </div>

        <div class="qr-container">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode(route('login.direct-token', $enrollmentUjian->token_login ?? '')) }}"
                alt="QR Code for Login">
        </div>

        <div class="token">
            Token: {{ $enrollmentUjian->token_login ?? 'BELUM DIBUAT' }}
        </div>

        <div class="instructions">
            <h3>Cara Login:</h3>
            <ol>
                <li>Scan QR code di atas menggunakan smartphone atau perangkat lain.</li>
                <li>Anda akan diarahkan ke halaman login ujian secara otomatis.</li>
                <li>Jika tidak bisa scan QR, kunjungi {{ route('login.token') }} dan masukkan token di atas.</li>
                <li>Sistem akan memverifikasi identitas Anda dan mengarahkan ke halaman ujian.</li>
                <li>Ikuti petunjuk pengerjaan soal yang muncul di layar.</li>
            </ol>
            <p><strong>Catatan:</strong> Token hanya berlaku untuk satu kali login. Jangan bagikan token ini dengan
                orang lain.</p>
        </div>

        <div class="footer">
            <p>Dicetak pada: {{ now()->format('d M Y H:i:s') }} | {{ config('app.name') }} &copy; {{ date('Y') }}
            </p>
        </div>

        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print();" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Cetak QR
                Code</button>
            <button onclick="window.close();"
                style="padding: 10px 20px; font-size: 16px; cursor: pointer; margin-left: 10px;">Tutup</button>
        </div>
    </div>
</body>

</html>
