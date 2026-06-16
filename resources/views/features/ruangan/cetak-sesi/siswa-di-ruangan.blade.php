<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Cetak Siswa di Ruangan</title>
    <style>
        @page {
            size: auto;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #172033;
            background: #eef2f7;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            color: #dbe7ff;
            background: #111827;
            border-bottom: 2px solid #2563eb;
        }

        .toolbar-title {
            flex: 1;
            min-width: 180px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
        }

        .toolbar-meta {
            font-size: 12px;
            color: #9fb0cc;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid #334155;
            color: #dbe7ff;
            background: transparent;
            cursor: pointer;
        }

        .btn-primary {
            border-color: #2563eb;
            background: #2563eb;
            color: #fff;
        }

        .page {
            margin: 16px auto;
            padding: 11mm 10mm 10mm;
            background: #fff;
            box-shadow: 0 8px 26px rgba(15, 23, 42, .14);
            page-break-after: always;
            break-after: page;
        }

        .page:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        .top-bar {
            display: flex;
            align-items: center;
            gap: 4px;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
            border-bottom: 1px solid #e2e8f0;
            font-size: 7.5pt;
            color: #64748b;
        }

        .school {
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #475569;
        }

        .top-bar-sep {
            color: #cbd5e1;
        }

        .top-bar-info {
            color: #64748b;
        }

        .room-section {
            margin-bottom: 4mm;
            padding: 3mm 4mm;
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            background: #f8fafc;
            display: flex;
            justify-content: space-between;
        }

        .room-badge {
            display: inline-block;
            padding: .6mm 1.5mm;
            border-radius: 3px;
            background: #2563eb;
            color: #fff;
            font-size: 7pt;
            font-weight: 800;
            text-transform: uppercase;
        }

        .room-main-title {
            margin-top: 1.5mm;
            font-size: 16pt;
            line-height: 1.1;
            font-weight: 900;
            color: #0f172a;
        }

        .room-stats {
            margin-top: 1.5mm;
            font-size: 7.5pt;
            color: #64748b;
        }

        .stat-sep {
            margin: 0 2mm;
            color: #cbd5e1;
        }

        .session-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 3px;
            margin-bottom: 5mm;
            padding: 2mm;
            border-radius: 4px;
            background: #f1f5f9;
        }

        .session-block {
            display: flex;
            align-items: center;
            gap: 2mm;
            padding: 1mm 2mm;
            border-radius: 3px;
            background: #fff;
            border: 1px solid #e2e8f0;
        }

        .session-block-name {
            font-size: 7.2pt;
            font-weight: 800;
            color: #1e40af;
        }

        .session-block-time {
            font-size: 7pt;
            font-weight: 700;
            color: #64748b;
        }

        .student-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            border-top: 1.5px dashed #94a3b8;
            border-left: 1.5px dashed #94a3b8;
        }

        .student-group {
            padding: 3mm;
            border-right: 1.5px dashed #94a3b8;
            border-bottom: 1.5px dashed #94a3b8;
            background: #fff;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .group-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 7mm;
            height: 7mm;
            margin-bottom: 2mm;
            border-radius: 999px;
            background: #111827;
            color: #fff;
            font-size: 7.5pt;
            font-weight: 800;
        }

        .session-student {
            padding: 2mm 0 2.4mm;
            border-top: 1px dashed #cbd5e1;
        }

        .session-student:first-of-type {
            border-top: 0;
            padding-top: 0;
        }

        .session-label {
            display: flex;
            justify-content: space-between;
            gap: 2mm;
            margin-bottom: 1.2mm;
            color: #2563eb;
            font-size: 7pt;
            font-weight: 800;
            text-transform: uppercase;
        }

        .session-time {
            color: #64748b;
            font-weight: 700;
            white-space: nowrap;
        }

        .student-name {
            font-size: 9.4pt;
            line-height: 1.2;
            font-weight: 900;
            color: #0f172a;
            overflow-wrap: anywhere;
        }

        .student-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2mm;
            margin-top: 1.6mm;
        }

        .info-pill {
            min-width: 0;
            padding: 1.1mm 1.5mm;
            border-radius: 3px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
        }

        .info-label {
            display: block;
            font-size: 5.8pt;
            line-height: 1;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 800;
        }

        .info-value {
            display: block;
            margin-top: .8mm;
            font-size: 7.3pt;
            line-height: 1.1;
            color: #111827;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .empty {
            padding: 20mm 10mm;
            text-align: center;
            color: #94a3b8;
            font-size: 12pt;
            font-weight: 700;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none !important;
            }

            .page {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    @php
        $totalRuangan = $rooms->count();
        $totalKelompok = $rooms->sum(fn($room) => $room['studentGroups']->count());
        $totalSiswa = $rooms->sum(fn($room) => $room['totalSiswa']);
    @endphp

    <div class="toolbar">
        <div class="toolbar-title">Cetak Siswa di Ruangan</div>
        <span class="toolbar-meta">{{ $totalRuangan }} ruangan</span>
        <span class="toolbar-meta">{{ $totalKelompok }} kelompok</span>
        <span class="toolbar-meta">{{ $totalSiswa }} siswa</span>
        <a href="{{ url()->previous() }}" class="btn">Kembali</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Cetak / Print</button>
    </div>

    @if ($rooms->isEmpty())
        <div class="page">
            <div class="empty">
                Tidak ada siswa di ruangan untuk filter yang dipilih.
            </div>
        </div>
    @else
        @foreach ($rooms as $room)
            @php
                $ruangan = $room['ruangan'];
            @endphp

            <div class="page">
                <div class="top-bar">
                    <span class="school">{{ $settings['nama_sekolah'] ?? 'Sekolah' }}</span>
                    @if ($tahunAjaran || $paketUjian)
                        <span class="top-bar-sep">·</span>
                        <span class="top-bar-info">
                            @if ($tahunAjaran)
                                {{ $tahunAjaran->nama }}
                            @endif
                            @if ($paketUjian)
                                {{ $tahunAjaran ? ' - ' : '' }}{{ $paketUjian->nama }}
                            @endif
                        </span>
                    @endif
                </div>

                <div class="room-section">
                    <div class="flex">
                        <div class="room-badge">{{ $ruangan?->kode_ruangan ?? 'Tanpa Kode' }}</div>
                        <div class="room-main-title">{{ $ruangan?->nama_ruangan ?? 'Tanpa Ruangan' }}</div>
                        <div class="room-stats">
                            <span>{{ $room['sessions']->count() }} sesi</span>
                            <span class="stat-sep">|</span>
                            <span>{{ $room['totalSiswa'] }} siswa</span>
                            @if ($ruangan?->kapasitas)
                                <span class="stat-sep">|</span>
                                <span>Kapasitas {{ $ruangan->kapasitas }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="session-strip">
                        @foreach ($room['sessions'] as $session)
                            @php($sesi = $session['sesi'])
                            <div class="session-block">
                                <span class="session-block-name">{{ $sesi->nama_sesi }}</span>
                                @if ($sesi->waktu_mulai || $sesi->waktu_selesai)
                                    <span
                                        class="session-block-time">{{ substr((string) $sesi->waktu_mulai, 0, 5) }}-{{ substr((string) $sesi->waktu_selesai, 0, 5) }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>



                <div class="student-grid">
                    @foreach ($room['studentGroups'] as $group)
                        @php($globalNumber = $loop->iteration)
                        <div class="student-group">
                            <div class="group-number">{{ $globalNumber }}</div>

                            @foreach ($group as $student)
                                <div class="session-student">
                                    <div class="session-label">
                                        <span>{{ $student['nama_sesi'] }}</span>
                                        @if ($student['waktu'])
                                            <span class="session-time">{{ $student['waktu'] }}</span>
                                        @endif
                                    </div>
                                    <div class="student-name">{{ $student['nama'] }}</div>
                                    <div class="student-info">
                                        <div class="info-pill">
                                            <span class="info-label">Kelas</span>
                                            <span class="info-value">{{ $student['kelas'] }}</span>
                                        </div>
                                        <div class="info-pill">
                                            <span class="info-label">ID Yayasan</span>
                                            <span class="info-value">{{ $student['idyayasan'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</body>

</html>
