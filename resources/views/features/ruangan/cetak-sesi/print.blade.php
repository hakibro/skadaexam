<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Sesi Ruangan</title>
    <style>
        /* ===================== PRINT PAGE SETUP ===================== */
        @page {
            size: A4 portrait;
            margin: 0;
        }
        @page cover {
            size: A4 landscape;
            margin: 0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
            background: #f1f5f9;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ===================== TOOLBAR (screen only) ===================== */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #0f172a;
            color: #e2e8f0;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            border-bottom: 2px solid #3b82f6;
        }
        .toolbar-title { font-size: 14px; font-weight: 700; color: #fff; flex: 1; min-width: 180px; }
        .toolbar-meta { font-size: 12px; color: #94a3b8; }
        .btn-print {
            background: #3b82f6; color: #fff; border: none;
            padding: 7px 18px; border-radius: 6px; font-weight: 700;
            font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px;
        }
        .btn-print:hover { background: #2563eb; }
        .btn-back {
            color: #94a3b8; font-size: 12px; text-decoration: none;
            border: 1px solid #334155; padding: 6px 12px; border-radius: 6px;
        }
        .btn-back:hover { color: #fff; border-color: #94a3b8; }

        /* ===================== PAGE WRAPPERS ===================== */
        .page {
            background: #fff;
            margin: 16px auto;
            box-shadow: 0 4px 24px rgba(0,0,0,.12);
            position: relative;
            overflow: hidden;
            page-break-after: always;
            break-after: page;
        }
        .page:last-child { page-break-after: auto; break-after: auto; }

        /* A4 landscape: 297mm × 210mm */
        .page-cover {
            width: 297mm;
            height: 210mm;
            page: cover;
        }

        /* A4 portrait: 210mm × 297mm */
        .page-sesi {
            width: 210mm;
            height: 297mm;
            padding: 12mm 12mm 10mm;
            display: flex;
            flex-direction: column;
        }

        /* ===================== COVER PAGE ===================== */
        .cover-bg {
            position: absolute; inset: 0;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0369a1 100%);
        }
        .cover-accent-top {
            position: absolute; top: 0; left: 0; right: 0; height: 6px;
            background: linear-gradient(90deg, #3b82f6, #06b6d4, #8b5cf6);
        }
        .cover-accent-bottom {
            position: absolute; bottom: 0; left: 0; right: 0; height: 6px;
            background: linear-gradient(90deg, #8b5cf6, #06b6d4, #3b82f6);
        }
        .cover-body {
            position: relative; z-index: 1;
            height: 100%;
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            text-align: center;
            padding: 16mm 20mm;
        }
        .cover-school {
            font-size: 11pt; font-weight: 500; color: #94a3b8;
            letter-spacing: .08em; text-transform: uppercase;
            margin-bottom: 6mm;
        }
        .cover-room-name {
            font-size: 68pt; font-weight: 900; color: #fff;
            line-height: 1; letter-spacing: -.02em;
            text-shadow: 0 4px 24px rgba(0,0,0,.4);
            word-break: break-word;
        }
        .cover-room-name.long { font-size: 48pt; }
        .cover-room-name.very-long { font-size: 36pt; }
        .cover-divider {
            width: 60mm; height: 3px; border-radius: 2px;
            background: linear-gradient(90deg, #3b82f6, #06b6d4);
            margin: 6mm auto;
        }
        .cover-meta {
            display: flex; align-items: center; gap: 8mm;
            margin-top: 4mm;
        }
        .cover-meta-item {
            display: flex; flex-direction: column; align-items: center;
        }
        .cover-meta-label {
            font-size: 7.5pt; color: #64748b; text-transform: uppercase;
            letter-spacing: .08em; margin-bottom: 1mm;
        }
        .cover-meta-value {
            font-size: 10pt; font-weight: 700; color: #cbd5e1;
        }
        .cover-footer {
            position: absolute; bottom: 12mm; left: 0; right: 0;
            text-align: center;
            font-size: 7.5pt; color: #475569; letter-spacing: .05em;
        }

        /* ===================== SESI PAGE ===================== */
        /* Header */
        .sesi-header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 4mm;
            margin-bottom: 5mm;
        }
        .sesi-header-top {
            display: flex; justify-content: space-between; align-items: flex-start;
        }
        .sesi-title { font-size: 20pt; font-weight: 800; color: #0f172a; line-height: 1.1; }
        .sesi-time {
            font-size: 11pt; font-weight: 600; color: #3b82f6;
            margin-top: 2mm;
            display: flex; align-items: center; gap: 2mm;
        }
        .sesi-time-icon { font-size: 9pt; opacity: .7; }
        .sesi-info-right {
            text-align: right; flex-shrink: 0; max-width: 65mm;
        }
        .sesi-room { font-size: 9.5pt; font-weight: 700; color: #374151; }
        .sesi-meta { font-size: 8pt; color: #6b7280; margin-top: 1mm; line-height: 1.4; }
        .sesi-badges {
            display: flex; gap: 2mm; flex-wrap: wrap; justify-content: flex-end; margin-top: 2mm;
        }
        .badge {
            font-size: 7pt; font-weight: 600; padding: 1px 5px; border-radius: 99px;
            border: 1px solid currentColor;
        }
        .badge-blue  { color: #2563eb; border-color: #bfdbfe; background: #eff6ff; }
        .badge-green { color: #16a34a; border-color: #bbf7d0; background: #f0fdf4; }

        /* Student grid 2 kolom */
        .student-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 5mm;
            flex: 1;
        }
        .student-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        .student-table thead th {
            background: #0f172a;
            color: #e2e8f0;
            text-align: left;
            padding: 2.5mm 3mm;
            font-size: 8pt;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .student-table thead th:first-child { border-radius: 3px 0 0 3px; }
        .student-table thead th:last-child  { border-radius: 0 3px 3px 0; }
        .student-table tbody tr:nth-child(even) td { background: #f8fafc; }
        .student-table tbody td {
            padding: 2.2mm 3mm;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #1e293b;
        }
        .td-id { font-family: monospace; font-size: 8.5pt; color: #475569; white-space: nowrap; }
        .td-name { font-weight: 600; }
        .td-kelas { color: #6b7280; white-space: nowrap; }
        .student-empty {
            font-size: 9pt; color: #94a3b8; text-align: center; padding: 6mm 0;
            grid-column: span 2;
        }

        /* Footer info bar */
        .sesi-footer {
            margin-top: auto;
            padding-top: 3mm;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .footer-count { font-size: 8pt; color: #64748b; }
        .footer-count strong { color: #0f172a; }
        .footer-print-date { font-size: 7.5pt; color: #94a3b8; }

        /* ===================== PRINT OVERRIDES ===================== */
        @media print {
            .toolbar { display: none !important; }
            body { background: none; }
            .page { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>

{{-- =================== TOOLBAR =================== --}}
@php
    $totalRuangan = $grouped->count();
    $totalSesi    = $grouped->sum(fn($sesis) => $sesis->count());
    $totalSiswa   = $grouped->sum(fn($sesis) =>
        $sesis->sum(fn($sesi) => $sesi->sesiRuanganSiswa->count())
    );
@endphp
<div class="toolbar no-print">
    <div class="toolbar-title">
        <svg style="display:inline;vertical-align:-2px;margin-right:5px" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Cetak Sesi Ruangan
    </div>
    <span class="toolbar-meta">{{ $totalRuangan }} ruangan · {{ $totalSesi }} sesi · {{ $totalSiswa }} siswa</span>
    @if ($tahunAjaran)
        <span class="toolbar-meta">{{ $tahunAjaran->nama }}</span>
    @endif
    @if ($paketUjian)
        <span class="toolbar-meta">{{ $paketUjian->nama }}</span>
    @endif
    <a class="btn-back" href="{{ url()->previous() }}">&#8592; Kembali</a>
    <button class="btn-print" onclick="window.print()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Cetak / Print
    </button>
</div>

@if ($grouped->isEmpty())
    <div style="text-align:center;padding:60px 20px;color:#94a3b8;">
        <p style="font-size:16pt;font-weight:700;margin-bottom:8px">Tidak ada data</p>
        <p style="font-size:10pt">Tidak ditemukan sesi sumber untuk filter yang dipilih.</p>
    </div>
@else

@foreach ($grouped as $ruanganId => $sesiDiRuangan)
@php $ruangan = $sesiDiRuangan->first()->ruangan; @endphp

{{-- =================== COVER PAGE =================== --}}
<div class="page page-cover">
    <div class="cover-bg"></div>
    <div class="cover-accent-top"></div>
    <div class="cover-accent-bottom"></div>
    <div class="cover-body">
        <div class="cover-school">
            {{ $settings['nama_sekolah'] ?? 'Sekolah' }}
        </div>

        @php
            $namaLen = strlen($ruangan->nama_ruangan ?? '');
            $sizeClass = $namaLen > 30 ? 'very-long' : ($namaLen > 18 ? 'long' : '');
        @endphp
        <div class="cover-room-name {{ $sizeClass }}">
            {{ $ruangan->nama_ruangan ?? 'Ruangan' }}
        </div>

        <div class="cover-divider"></div>

        <div class="cover-meta">
            @if ($ruangan->kode_ruangan)
            <div class="cover-meta-item">
                <span class="cover-meta-label">Kode</span>
                <span class="cover-meta-value">{{ $ruangan->kode_ruangan }}</span>
            </div>
            @endif
            @if ($ruangan->lokasi)
            <div class="cover-meta-item">
                <span class="cover-meta-label">Lokasi</span>
                <span class="cover-meta-value">{{ $ruangan->lokasi }}</span>
            </div>
            @endif
            @if ($ruangan->kapasitas)
            <div class="cover-meta-item">
                <span class="cover-meta-label">Kapasitas</span>
                <span class="cover-meta-value">{{ $ruangan->kapasitas }} Siswa</span>
            </div>
            @endif
            <div class="cover-meta-item">
                <span class="cover-meta-label">Sesi</span>
                <span class="cover-meta-value">{{ $sesiDiRuangan->count() }} Sesi</span>
            </div>
        </div>
    </div>
    <div class="cover-footer">
        @if ($tahunAjaran) {{ $tahunAjaran->nama }} @endif
        @if ($paketUjian) &nbsp;·&nbsp; {{ $paketUjian->nama }} @endif
        &nbsp;·&nbsp; Dicetak {{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY') }}
    </div>
</div>

{{-- =================== SESI PAGES =================== --}}
@foreach ($sesiDiRuangan as $sesi)
@php
    $siswas = $sesi->sesiRuanganSiswa
        ->sortBy(fn($s) => $s->siswa?->nama ?? '')
        ->values();
    $half    = (int) ceil($siswas->count() / 2);
    $kolKiri = $siswas->take($half);
    $kolKanan = $siswas->skip($half);
@endphp
<div class="page page-sesi">

    {{-- Header --}}
    <div class="sesi-header">
        <div class="sesi-header-top">
            <div>
                <div class="sesi-title">{{ $sesi->nama_sesi }}</div>
                <div class="sesi-time">
                    <span class="sesi-time-icon">⏰</span>
                    {{ \Illuminate\Support\Str::substr($sesi->waktu_mulai, 0, 5) }}
                    &ndash;
                    {{ \Illuminate\Support\Str::substr($sesi->waktu_selesai, 0, 5) }}
                </div>
            </div>
            <div class="sesi-info-right">
                <div class="sesi-room">{{ $ruangan->nama_ruangan }}</div>
                <div class="sesi-meta">
                    @if ($tahunAjaran) {{ $tahunAjaran->nama }} @endif
                    @if ($paketUjian)<br>{{ $paketUjian->nama }}@endif
                </div>
                <div class="sesi-badges">
                    <span class="badge badge-blue">Sesi Sumber</span>
                    @if ($sesi->status)
                        <span class="badge badge-green">{{ ucfirst(str_replace('_', ' ', $sesi->status)) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Student list --}}
    @if ($siswas->isEmpty())
        <div class="student-empty">Belum ada siswa di sesi ini</div>
    @else
    <div class="student-grid">
        <table class="student-table">
            <thead>
                <tr>
                    <th>ID Yayasan</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kolKiri as $rec)
                @php $kelas = $rec->siswa?->kelasForTahunAjaran($sesi->tahun_ajaran_id); @endphp
                <tr>
                    <td class="td-id">{{ $rec->siswa?->idyayasan ?? '—' }}</td>
                    <td class="td-name">{{ $rec->siswa?->nama ?? '—' }}</td>
                    <td class="td-kelas">{{ $kelas?->nama_kelas ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if ($kolKanan->isNotEmpty())
        <table class="student-table">
            <thead>
                <tr>
                    <th>ID Yayasan</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kolKanan as $rec)
                @php $kelas = $rec->siswa?->kelasForTahunAjaran($sesi->tahun_ajaran_id); @endphp
                <tr>
                    <td class="td-id">{{ $rec->siswa?->idyayasan ?? '—' }}</td>
                    <td class="td-name">{{ $rec->siswa?->nama ?? '—' }}</td>
                    <td class="td-kelas">{{ $kelas?->nama_kelas ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    {{-- Footer --}}
    <div class="sesi-footer">
        <span class="footer-count">
            Total <strong>{{ $siswas->count() }}</strong> siswa
            · Kapasitas ruangan {{ $ruangan->kapasitas ?? '—' }}
        </span>
        <span class="footer-print-date">
            Dicetak {{ \Carbon\Carbon::now()->isoFormat('D MMM YYYY, HH:mm') }}
        </span>
    </div>
</div>
@endforeach

@endforeach
@endif

</body>
</html>
