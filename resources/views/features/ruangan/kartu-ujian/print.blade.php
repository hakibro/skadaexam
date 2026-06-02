<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Cetak Kartu Ujian</title>
    <style>
        @page {
            size: auto;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
            background-color: #ffffff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .toolbar {
            padding: 12px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .toolbar button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 6px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
        }

        .toolbar button:hover {
            background: #1d4ed8;
        }

        /* Layout Grid Kartu */
        .sheet {
            display: grid;
            grid-template-columns: repeat(2, 85.6mm);
            gap: 4mm;
            padding: 4mm;
            align-content: start;
        }

        /* Desain Dasar Kartu (CR80 Standard) */
        .card {
            width: 85.6mm;
            height: 53.98mm;
            border: 0.2mm solid #cbd5e1;
            border-radius: 6px;
            padding: 3.5mm 4mm;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #ffffff;
            page-break-inside: avoid;
            break-inside: avoid;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Aksen Modern: Garis warna di sisi kiri kartu */
        .card::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 1.2mm;
            background: linear-gradient(180deg, #2563eb 0%, #3b82f6 100%);
        }

        /* Bagian Depan (Front Card) */
        .header {
            display: flex;
            gap: 3mm;
            align-items: center;
            border-bottom: 0.5px solid #e2e8f0;
            padding-bottom: 1.5mm;
        }

        .logo {
            width: 9mm;
            height: 9mm;
            object-fit: contain;
            filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.1));
        }

        .header-text {
            flex: 1;
            min-width: 0;
        }

        .school {
            font-size: 7.5pt;
            font-weight: 800;
            line-height: 1.1;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .addr {
            font-size: 5.2pt;
            line-height: 1.2;
            color: #64748b;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sub-addr {
            font-size: 5pt;
            font-weight: 600;
            color: #0284c7;
            margin-top: 0.2mm;
        }

        .title {
            font-size: 7.5pt;
            font-weight: 800;
            text-align: center;
            color: #1e293b;
            background: #f1f5f9;
            padding: 0.8mm 0;
            border-radius: 4px;
            letter-spacing: 0.5px;
            margin: 1.5mm 0;
        }

        /* Grid Informasi Siswa yang Lebih Rapi */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            row-gap: 1.2mm;
            column-gap: 3mm;
        }

        .meta-full {
            grid-column: span 2;
            text-align: center;
        }

        .field-label {
            font-size: 4.8pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.2px;
            margin-bottom: 0.2mm;
        }

        .field-value {
            font-size: 6.5pt;
            font-weight: 600;
            color: #0f172a;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .field-value.name {
            font-size: 8pt;
            font-weight: 700;
            color: #2563eb;
        }

        /* Bagian Belakang (Back Card) */
        .back-container {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .schedule-title {
            font-size: 8pt;
            font-weight: 800;
            text-align: center;
            color: #1e3a8a;
            letter-spacing: 0.6px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 1.5mm;
            margin-bottom: 1.5mm;
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            column-gap: 3mm;
            row-gap: 1.5mm;
            flex: 1;
            align-content: start;
        }

        .schedule-group {
            background: #f8fafc;
            border: 0.2mm solid #f1f5f9;
            border-radius: 4px;
            padding: 1mm 1.5mm;
            break-inside: avoid;
        }

        .day {
            font-size: 5.2pt;
            font-weight: 700;
            color: #2563eb;
            text-transform: uppercase;
            border-bottom: 0.3px solid #e2e8f0;
            padding-bottom: 0.4mm;
            margin-bottom: 0.6mm;
        }

        .exam {
            font-size: 5.2pt;
            font-weight: 600;
            line-height: 1.2;
            color: #334155;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .no-schedule {
            grid-column: span 2;
            font-size: 6pt;
            color: #64748b;
            text-align: center;
            margin-top: 4mm;
            font-style: italic;
        }

        /* Tweaks khusus saat cetak */
        @media print {
            .toolbar {
                display: none;
            }

            body {
                background: none;
            }

            .sheet {
                padding: 0;
                gap: 0;
            }

            .card {
                border: 0.2mm solid #94a3b8;
                border-radius: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <button onclick="window.print()">Cetak Kartu</button>
    </div>
    <main class="sheet">
        @foreach ($cards as $card)
            @php
                $siswa = $card['siswa'];
                $kelas = $card['kelas'];
                $source = $card['source_session'];
                $sessionTime =
                    $source?->waktu_mulai && $source?->waktu_selesai
                        ? \Carbon\Carbon::parse($source->waktu_mulai)->format('H:i') .
                            ' - ' .
                            \Carbon\Carbon::parse($source->waktu_selesai)->format('H:i')
                        : '-';
                $logoPath = !empty($settings['logo_path']) ? public_path('storage/' . $settings['logo_path']) : null;
                $logoUrl = !empty($settings['logo_path']) ? asset('storage/' . $settings['logo_path']) : null;
            @endphp

            <section class="card">
                @if ($mode === 'back')
                    <div class="back-container">
                        <div class="schedule-title">JADWAL UJIAN</div>
                        <div class="schedule-grid">
                            @forelse ($card['jadwals'] as $date => $items)
                                <div class="schedule-group">
                                    <div class="day">
                                        {{ \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('D, d M Y') }}
                                    </div>
                                    @foreach ($items as $jadwal)
                                        <div class="exam">• {{ $jadwal->mapel?->nama_mapel ?? $jadwal->judul }}</div>
                                    @endforeach
                                </div>
                            @empty
                                <div class="no-schedule">Belum ada jadwal ujian untuk kelas ini.</div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div>
                        <div class="header">
                            @if ($logoPath && file_exists($logoPath))
                                <img src="{{ $logoUrl }}" class="logo">
                            @endif
                            <div class="header-text">
                                <div class="school">{{ $settings['nama_sekolah'] ?? 'Sekolah' }}</div>
                                <div class="addr">{{ $settings['alamat'] ?? '' }}</div>
                                <div class="sub-addr">{{ $tahunAjaran?->nama ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="title">Kartu Peserta {{ $paketUjian?->nama ?? '-' }}</div>

                        <div class="meta-grid">
                            <div class="meta-full">
                                <div class="field-label">Nama Lengkap Peserta</div>
                                <div class="field-value name">{{ $siswa?->nama }}</div>
                            </div>
                            <div>
                                <div class="field-label">Kelas</div>
                                <div class="field-value">{{ $kelas?->nama_kelas ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="field-label">ID Yayasan / NISN</div>
                                <div class="field-value">{{ $siswa?->idyayasan ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="field-label">Ruangan Ujian</div>
                                <div class="field-value" style="color: #0284c7;">{{ $source?->nama_ruangan ?? '-' }}
                                </div>
                            </div>
                            <div>
                                <div class="field-label">Sesi / Jam</div>
                                <div class="field-value">{{ $source?->nama_sesi ?? '-' }} ({{ $sessionTime }})</div>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        @endforeach
    </main>
</body>

</html>
