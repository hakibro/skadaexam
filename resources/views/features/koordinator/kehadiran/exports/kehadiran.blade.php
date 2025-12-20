<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>ID Yayasan</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Jurusan</th>
            <th>Tingkat</th>
            <th>Ruangan</th>
            <th>Mata Ujian</th>
            <th>Tanggal</th>
            <th>Status Kehadiran</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row->siswa->idyayasan ?? '-' }}</td>
                <td>{{ $row->siswa->nama ?? '-' }}</td>
                <td>{{ $row->siswa->kelas->nama_kelas ?? '-' }}</td>
                <td>{{ $row->siswa->kelas->jurusan ?? '-' }}</td>
                <td>{{ $row->siswa->kelas->tingkat ?? '-' }}</td>
                <td>{{ $row->sesiRuangan->ruangan->nama_ruangan ?? '-' }}</td>
                <td>{{ $row->sesiRuangan->jadwalUjian->tanggal->first() ?? '-' }}</td>
                <td>
                    {{ optional($row->sesiRuangan->jadwalUjian->tanggal)->format('d-m-Y') }}
                </td>
                <td>{{ strtoupper($row->status_kehadiran) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
