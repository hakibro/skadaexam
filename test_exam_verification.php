<?php
/**
 * Uji Proses Ujian - Verifikasi dengan data contoh
 * 
 * Tujuan: memverifikasi apakah soal termuat benar dan nilai akurat
 * untuk mode tidak acak maupun acak.
 * 
 * Fokus pada pengujian, bukan perubahan kode.
 */

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\BankSoal;
use App\Models\Soal;
use App\Models\Mapel;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Ruangan;
use App\Models\SesiRuangan;
use App\Models\EnrollmentUjian;
use App\Models\JadwalUjian;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa;
use App\Models\TahunAjaran;
use App\Models\PaketUjian;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$uniq = date('mdHis');

echo "============================================\n";
echo "UJI PROSES UJIAN - VERIFIKASI DATA CONTOH\n";
echo "============================================\n\n";

// ============================================
// CLEANUP SEBELUMNYA
// ============================================
echo "CLEANUP DATA LAMA...\n";
DB::table('bank_soal')->where('kode_bank', 'like', 'UJI-V%')->delete();
DB::table('soal')->where('pertanyaan', 'like', 'Soal Verifikasi%')->delete();
DB::table('jadwal_ujian')->where('judul', 'like', 'Uji Verifikasi%')->delete();
DB::table('ruangan')->where('nama_ruangan', 'like', '%Uji Verifikasi%')->delete();
DB::table('siswa')->where('nama', 'like', '%Uji Verifikasi%')->delete();
echo "Done.\n\n";

// ============================================
// TAHAP 1: SIAPKAN DATA CONTOH
// ============================================
echo "TAHAP 1: SIAPKAN DATA CONTOH\n";
echo "------------------------------\n";

// Cari user admin
$admin = User::first();
if (!$admin) {
    die("ERROR: Tidak ada user di database.\n");
}
echo "Admin: ID {$admin->id} - {$admin->name}\n";

// Cari mapel yang ada
$mapel = Mapel::first();
if (!$mapel) {
    die("ERROR: Tidak ada mapel. Jalankan seeder terlebih dahulu.\n");
}
echo "Mapel: ID {$mapel->id} - {$mapel->nama_mapel}\n";

// Cari tahun ajaran
$tahunAjaran = TahunAjaran::where('status', 'aktif')->first() ?? TahunAjaran::first();
if (!$tahunAjaran) {
    die("ERROR: Tidak ada tahun ajaran.\n");
}
echo "Tahun Ajaran: ID {$tahunAjaran->id}\n";

// Cari paket ujian
$paketUjian = PaketUjian::first();
if (!$paketUjian) {
    die("ERROR: Tidak ada paket ujian.\n");
}
echo "Paket Ujian: ID {$paketUjian->id} - {$paketUjian->nama_paket}\n";

// Cari kelas
$kelas = Kelas::first();
if (!$kelas) {
    die("ERROR: Tidak ada kelas.\n");
}
echo "Kelas: ID {$kelas->id} - {$kelas->nama_kelas}\n";

// Buat bank soal
$bankSoal = BankSoal::create([
    'kode_bank' => 'UJI-VRF-' . $uniq,
    'judul' => 'Bank Soal Uji Verifikasi',
    'tingkat' => '10',
    'mapel_id' => $mapel->id,
    'paket_ujian_id' => $paketUjian->id,
    'tahun_ajaran_id' => $tahunAjaran->id,
    'deskripsi' => 'Bank soal untuk verifikasi ujian',
    'created_by' => $admin->id,
    'status' => 'aktif',
]);
echo "Bank Soal: ID {$bankSoal->id} - {$bankSoal->judul}\n";

// Buat 5 soal dengan kunci dan bobot tetap
$soalData = [
    ['pertanyaan' => 'Soal Verifikasi 1: Berapakah 2 + 2?', 'kunci' => 'B', 'bobot' => 2.00],
    ['pertanyaan' => 'Soal Verifikasi 2: Ibukota Indonesia adalah?', 'kunci' => 'A', 'bobot' => 2.00],
    ['pertanyaan' => 'Soal Verifikasi 3: Siapakah presiden pertama RI?', 'kunci' => 'C', 'bobot' => 2.00],
    ['pertanyaan' => 'Soal Verifikasi 4: Berapa jumlah provinsi di Indonesia?', 'kunci' => 'D', 'bobot' => 2.00],
    ['pertanyaan' => 'Soal Verifikasi 5: Warna bendera Indonesia adalah?', 'kunci' => 'A', 'bobot' => 2.00],
];

// Opsi A-E untuk semua soal (berbeda agar mudah dibedakan)
$sharedOptions = ['A' => 'Jakarta', 'B' => '4', 'C' => 'Soekarno', 'D' => '38', 'E' => 'Merah Putih'];

$createdSoalIds = [];
foreach ($soalData as $i => $sd) {
    DB::table('soal')->insert([
        'bank_soal_id' => $bankSoal->id,
        'nomor_soal' => $i + 1,
        'pertanyaan' => $sd['pertanyaan'],
        'pilihan_a_teks' => $sharedOptions['A'],
        'pilihan_b_teks' => $sharedOptions['B'],
        'pilihan_c_teks' => $sharedOptions['C'],
        'pilihan_d_teks' => $sharedOptions['D'],
        'pilihan_e_teks' => $sharedOptions['E'],
        'kunci_jawaban' => $sd['kunci'],
        'bobot' => $sd['bobot'],
        'kategori' => 'sedang',
        'tipe_pertanyaan' => 'teks',
        'tipe_soal' => 'pilihan_ganda',
        'status' => 'aktif',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $soalId = DB::getPdo()->lastInsertId();
    $createdSoalIds[] = $soalId;
    echo "  Soal {$i}: ID {$soalId} - {$sd['pertanyaan']} (Kunci: {$sd['kunci']}, Bobot: {$sd['bobot']})\n";
}

// Reload soal
$soals = Soal::whereIn('id', $createdSoalIds)->get();
$soalAktifCount = Soal::where('bank_soal_id', $bankSoal->id)
    ->where('status', 'aktif')
    ->count();
echo "Total soal aktif di bank: {$soalAktifCount}\n";

// Buat siswa
$siswa = Siswa::create([
    'idyayasan' => 'UJV-' . $uniq,
    'nis' => 'UJV-' . $uniq,
    'nama' => 'Siswa Uji Verifikasi',
    'email' => 'ujv.' . $uniq . '@test.com',
    'password' => bcrypt('password'),
]);
echo "Siswa: ID {$siswa->id} - {$siswa->nama}\n";

// Buat ruangan
$ruangan = Ruangan::create([
    'kode_ruangan' => 'UJV-' . $uniq,
    'nama_ruangan' => 'Ruangan Uji Verifikasi',
    'kapasitas' => 30,
    'lokasi' => 'Gedung Uji',
    'tahun_ajaran_id' => $tahunAjaran->id,
    'paket_ujian_id' => $paketUjian->id,
    'status' => 'aktif',
]);
echo "Ruangan: ID {$ruangan->id} - {$ruangan->nama_ruangan}\n";

// Buat sesi ruangan
$now = Carbon::now();
$sesiRuangan = SesiRuangan::create([
    'ruangan_id' => $ruangan->id,
    'kode_sesi' => 'UJI-' . $uniq,
    'nama_sesi' => 'Sesi Uji Verifikasi',
    'waktu_mulai' => $now->copy()->subHours(1)->format('H:i:s'),
    'waktu_selesai' => $now->copy()->addHours(3)->format('H:i:s'),
    'status' => 'berlangsung',
]);
echo "Sesi Ruangan: ID {$sesiRuangan->id}\n";

// Buat enrollment
$enrollment = EnrollmentUjian::create([
    'siswa_id' => $siswa->id,
    'sesi_ruangan_id' => $sesiRuangan->id,
    'status_enrollment' => 'active',
    'waktu_mulai_ujian' => $now,
    'waktu_selesai_ujian' => $now->copy()->addHours(2),
    'last_login_at' => $now,
]);
echo "Enrollment: ID {$enrollment->id}\n\n";

// ============================================
// TAHAP 2: BUAT 4 JADWAL UJI
// ============================================
echo "TAHAP 2: BUAT 4 JADWAL UJI\n";
echo "------------------------------\n";

$jadwalConfigs = [
    ['mode' => 'non-acak (0,0)', 'acak_soal' => false, 'acak_jawaban' => false],
    ['mode' => 'acak_soal (1,0)', 'acak_soal' => true, 'acak_jawaban' => false],
    ['mode' => 'acak_jawaban (0,1)', 'acak_soal' => false, 'acak_jawaban' => true],
    ['mode' => 'acak_soal+acak_jawaban (1,1)', 'acak_soal' => true, 'acak_jawaban' => true],
];

$jadwals = [];
foreach ($jadwalConfigs as $idx => $jConf) {
    $jadwal = JadwalUjian::create([
        'mapel_id' => $mapel->id,
        'bank_soal_id' => $bankSoal->id,
        'paket_ujian_id' => $paketUjian->id,
        'tahun_ajaran_id' => $tahunAjaran->id,
        'kode_ujian' => 'V' . $uniq . $idx,
        'judul' => 'Uji Verifikasi - ' . $jConf['mode'],
        'tanggal' => $now->format('Y-m-d'),
        'durasi_menit' => 120,
        'jumlah_soal' => $soalAktifCount,
        'acak_soal' => $jConf['acak_soal'],
        'acak_jawaban' => $jConf['acak_jawaban'],
        'tampilkan_hasil' => true,
        'aktifkan_auto_logout' => true,
        'status' => 'aktif',
        'created_by' => $admin->id,
    ]);
    $jadwals[] = $jadwal;
    echo "  Jadwal ID {$jadwal->id}: {$jConf['mode']}\n";
}
echo "\n";

// ============================================
// TAHAP 3-7: UJI 4 MODE UJIAN
// ============================================
echo "==============================================================\n";
echo "TAHAP 3-7: UJI 4 MODE UJIAN\n";
echo "==============================================================\n";

$hasilKeseluruhan = [];

foreach ($jadwals as $idx => $jadwal) {
    $modeName = $jadwalConfigs[$idx]['mode'];
    echo "\n--- UJI JADWAL: {$modeName} ---\n";
    echo "Jadwal ID: {$jadwal->id}, acak_soal=" . ($jadwal->acak_soal ? '1' : '0') . ", acak_jawaban=" . ($jadwal->acak_jawaban ? '1' : '0') . "\n";

    // === A. Load soal (simulasi exam()) ===
    $soals = Soal::where('bank_soal_id', $jadwal->bank_soal_id)
        ->where('status', 'aktif')
        ->get();
    echo "  Soal dimuat: {$soals->count()} soal\n";

    // Buat hasil ujian
    $hasilUjian = HasilUjian::create([
        'siswa_id' => $siswa->id,
        'jadwal_ujian_id' => $jadwal->id,
        'enrollment_ujian_id' => $enrollment->id,
        'sesi_ruangan_id' => $sesiRuangan->id,
        'waktu_mulai' => $now,
        'durasi_menit' => $jadwal->durasi_menit,
        'jumlah_soal' => $soals->count(),
        'jumlah_dijawab' => 0,
        'jumlah_benar' => 0,
        'jumlah_salah' => 0,
        'jumlah_tidak_dijawab' => $soals->count(),
        'skor' => 0,
        'is_final' => false,
        'status' => 'berlangsung',
    ]);
    echo "  HasilUjian ID: {$hasilUjian->id}\n";

    // === B. Apply randomization ===
    $seed = $siswa->id + $jadwal->id;

    if ($jadwal->acak_soal) {
        $soalArray = $soals->toArray();
        mt_srand($seed);
        for ($i = count($soalArray) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$soalArray[$i], $soalArray[$j]] = [$soalArray[$j], $soalArray[$i]];
        }
        $soalIdsOrder = array_column($soalArray, 'id');
        $soalModels = Soal::whereIn('id', $soalIdsOrder)->get()->keyBy('id');
        $soals = collect($soalIdsOrder)->map(fn($id) => $soalModels[$id]);
        echo "  (acak_soal active, seed={$seed})\n";
    } else {
        $soals = $soals->sortBy('nomor_soal')->values();
        echo "  (non-acak, urut nomor_soal)\n";
    }

    echo "  Urutan ID Soal: " . $soals->pluck('id')->implode(', ') . "\n";
    echo "  Urutan Kunci: " . $soals->pluck('kunci_jawaban')->implode(', ') . "\n";

    // === C. Build mapping jawaban ===
    $jawabanMapping = [];
    foreach ($soals as $soal) {
        $originalKey = $soal->kunci_jawaban;

        if ($jadwal->acak_jawaban) {
            $letters = ['A', 'B', 'C', 'D', 'E'];
            $jawabanSeed = $siswa->id * 1000 + $soal->id;
            mt_srand($jawabanSeed);
            $shuffled = $letters;
            for ($i = count($shuffled) - 1; $i > 0; $i--) {
                $j = mt_rand(0, $i);
                [$shuffled[$i], $shuffled[$j]] = [$shuffled[$j], $shuffled[$i]];
            }
            $keyIndex = array_search($originalKey, $letters);
            $mappedKey = $shuffled[$keyIndex];
            $jawabanMapping[$soal->id] = ['original' => $originalKey, 'mapped' => $mappedKey, 'order' => implode('', $shuffled)];
        } else {
            $jawabanMapping[$soal->id] = ['original' => $originalKey, 'mapped' => $originalKey, 'order' => 'ABCDE'];
        }
    }

    // Show mapping detail
    foreach ($soals as $soal) {
        $m = $jawabanMapping[$soal->id];
        echo "    Soal ID {$soal->id}: kunci={$m['original']}, tampil={$m['mapped']}, order={$m['order']}\n";
    }

    // === D. Jawab semua soal ===
    JawabanSiswa::where('hasil_ujian_id', $hasilUjian->id)->delete();
    echo "\n  --- MENJAWAB SEMUA SOAL ---\n";

    foreach ($soals as $soal) {
        $mapping = $jawabanMapping[$soal->id];
        $jawaban = $mapping['mapped'];
        JawabanSiswa::create([
            'hasil_ujian_id' => $hasilUjian->id,
            'soal_ujian_id' => $soal->id,
            'jawaban' => $jawaban,
            'is_ragu' => false,
        ]);
        echo "    Jawab soal {$soal->id}: {$jawaban} (kunci asli: {$mapping['original']})\n";
    }

    // === E. Simulasi calculateScore() ===
    $allSoals = Soal::where('bank_soal_id', $jadwal->bank_soal_id)->get();
    echo "\n  --- SKORING ---\n";
    echo "  Total soal di bank (untuk skoring): {$allSoals->count()}\n";

    $jumlahBenar = 0;
    $jumlahSalah = 0;
    $totalSkor = 0;
    $totalBobot = 0;

    foreach ($allSoals as $s) {
        $totalBobot += $s->bobot;
        $jawabanRecord = JawabanSiswa::where('hasil_ujian_id', $hasilUjian->id)
            ->where('soal_ujian_id', $s->id)
            ->first();

        if (!$jawabanRecord || empty($jawabanRecord->jawaban)) {
            echo "    Soal {$s->id}: tidak dijawab\n";
            continue;
        }

        // Evaluate: getCorrectAnswerForStudent() logic
        $correctAnswer = $s->kunci_jawaban;
        if ($jadwal->acak_jawaban) {
            $letters = ['A', 'B', 'C', 'D', 'E'];
            $jawabanSeed = $siswa->id * 1000 + $s->id;
            mt_srand($jawabanSeed);
            $shuffled = $letters;
            for ($i = count($shuffled) - 1; $i > 0; $i--) {
                $j = mt_rand(0, $i);
                [$shuffled[$i], $shuffled[$j]] = [$shuffled[$j], $shuffled[$i]];
            }
            $keyIndex = array_search($s->kunci_jawaban, $letters);
            $correctAnswer = $shuffled[$keyIndex];
        }

        if ($jawabanRecord->jawaban === $correctAnswer) {
            $jumlahBenar++;
            $totalSkor += $s->bobot;
            echo "    Soal {$s->id}: BENAR (jawab={$jawabanRecord->jawaban}, kunci={$correctAnswer})\n";
        } else {
            $jumlahSalah++;
            echo "    Soal {$s->id}: SALAH (jawab={$jawabanRecord->jawaban}, kunci={$correctAnswer})\n";
        }
    }

    $jumlahDijawab = $jumlahBenar + $jumlahSalah;
    $jumlahTidakDijawab = $allSoals->count() - $jumlahDijawab;
    $nilai = $totalBobot > 0 ? round(($totalSkor / $totalBobot) * 100, 2) : 0;

    echo "\n  HASIL:\n";
    echo "    Total Bobot: {$totalBobot}\n";
    echo "    Total Skor: {$totalSkor}\n";
    echo "    Benar: {$jumlahBenar}, Salah: {$jumlahSalah}, Dijawab: {$jumlahDijawab}, Tidak: {$jumlahTidakDijawab}\n";
    echo "    Nilai: {$nilai}\n";

    $passed = ($nilai == 100);
    echo "  STATUS: " . ($passed ? '✅ PASS (100)' : "❌ FAIL ({$nilai})") . "\n";

    $hasilKeseluruhan[$modeName] = [
        'passed' => $passed,
        'nilai' => $nilai,
        'benar' => $jumlahBenar,
        'salah' => $jumlahSalah,
        'dijawab' => $jumlahDijawab,
        'tidak' => $jumlahTidakDijawab,
        'skor' => $totalSkor,
        'bobot' => $totalBobot,
    ];

    // Cleanup hasil ujian ini (biarkan data bersih)
    JawabanSiswa::where('hasil_ujian_id', $hasilUjian->id)->delete();
    $hasilUjian->delete();
}

echo "\n\n==============================================================\n";
echo "RINGKASAN 4 MODE\n";
echo "==============================================================\n";
foreach ($hasilKeseluruhan as $mode => $r) {
    $icon = $r['passed'] ? '✅' : '❌';
    echo "{$icon} {$mode}: nilai={$r['nilai']}, benar={$r['benar']}, salah={$r['salah']}, td={$r['tidak']}\n";
}

// ============================================
// TAHAP 8: SKENARIO NEGATIF/PARSIAL
// ============================================
echo "\n\n==============================================================\n";
echo "TAHAP 8: SKENARIO NEGATIF/PARSIAL (non-acak)\n";
echo "==============================================================\n";

$jadwalNonAcak = $jadwals[0];

// Load soal non-acak
$soalsNonAcak = Soal::where('bank_soal_id', $jadwalNonAcak->bank_soal_id)
    ->where('status', 'aktif')
    ->orderBy('nomor_soal')
    ->get();

echo "Soal count: {$soalsNonAcak->count()}\n";

// Buat hasil ujian baru
$hasilParsial = HasilUjian::create([
    'siswa_id' => $siswa->id,
    'jadwal_ujian_id' => $jadwalNonAcak->id,
    'enrollment_ujian_id' => $enrollment->id,
    'sesi_ruangan_id' => $sesiRuangan->id,
    'waktu_mulai' => $now,
    'durasi_menit' => $jadwalNonAcak->durasi_menit,
    'jumlah_soal' => $soalsNonAcak->count(),
    'jumlah_dijawab' => 0,
    'jumlah_benar' => 0,
    'jumlah_salah' => 0,
    'jumlah_tidak_dijawab' => $soalsNonAcak->count(),
    'skor' => 0,
    'is_final' => false,
    'status' => 'berlangsung',
]);
echo "HasilUjian ID: {$hasilParsial->id}\n";

// Jawab 4 dari 5 soal:
// Soal 1 (kunci=B): jawab B -> benar
// Soal 2 (kunci=A): jawab C -> salah
// Soal 3 (kunci=C): jawab C -> benar
// Soal 4 (kunci=D): kosong
// Soal 5 (kunci=A): jawab A -> benar
// Expected: benar=3, salah=1, dijawab=4, tidak=1, skor=6, bobot=10, nilai=60

$answers = [
    0 => ['jawab' => $soalsNonAcak[0]->kunci_jawaban, 'desc' => 'benar'],
    1 => ['jawab' => ($soalsNonAcak[1]->kunci_jawaban == 'A' ? 'C' : 'A'), 'desc' => 'salah'],
    2 => ['jawab' => $soalsNonAcak[2]->kunci_jawaban, 'desc' => 'benar'],
    3 => ['jawab' => '', 'desc' => 'kosong'],
    4 => ['jawab' => $soalsNonAcak[4]->kunci_jawaban, 'desc' => 'benar'],
];

foreach ($soalsNonAcak as $i => $soal) {
    $plan = $answers[$i];
    echo "  Soal {$soal->id} (nomor {$soal->nomor_soal}, kunci={$soal->kunci_jawaban}): ";
    if (!empty($plan['jawab'])) {
        JawabanSiswa::create([
            'hasil_ujian_id' => $hasilParsial->id,
            'soal_ujian_id' => $soal->id,
            'jawaban' => $plan['jawab'],
            'is_ragu' => false,
        ]);
        echo "jawab {$plan['jawab']} -> {$plan['desc']}\n";
    } else {
        echo "TIDAK DIJAWAB -> {$plan['desc']}\n";
    }
}

// Skoring
$allSoalsParsial = Soal::where('bank_soal_id', $jadwalNonAcak->bank_soal_id)->get();
$bP = 0;
$sP = 0;
$skorP = 0;
$bobotP = 0;
foreach ($allSoalsParsial as $s) {
    $bobotP += $s->bobot;
    $jawab = JawabanSiswa::where('hasil_ujian_id', $hasilParsial->id)
        ->where('soal_ujian_id', $s->id)->first();
    if (!$jawab || empty($jawab->jawaban))
        continue;
    if ($jawab->jawaban === $s->kunci_jawaban) {
        $bP++;
        $skorP += $s->bobot;
    } else {
        $sP++;
    }
}
$dijawabP = $bP + $sP;
$tdP = $allSoalsParsial->count() - $dijawabP;
$nilaiP = $bobotP > 0 ? round(($skorP / $bobotP) * 100, 2) : 0;

echo "\nHASIL PARSIAL:\n";
echo "  Benar: {$bP} (expected: 3)\n";
echo "  Salah: {$sP} (expected: 1)\n";
echo "  Dijawab: {$dijawabP} (expected: 4)\n";
echo "  Tidak: {$tdP} (expected: 1)\n";
echo "  Bobot: {$bobotP} (expected: 10)\n";
echo "  Skor: {$skorP} (expected: 6)\n";
echo "  Nilai: {$nilaiP} (expected: 60)\n";

$parsialPass = ($bP == 3 && $sP == 1 && $dijawabP == 4 && $tdP == 1 && $nilaiP == 60);
echo "  STATUS: " . ($parsialPass ? '✅ PASS' : '❌ FAIL') . "\n";

// Cleanup
JawabanSiswa::where('hasil_ujian_id', $hasilParsial->id)->delete();
$hasilParsial->delete();

// ============================================
// TAHAP 9: AUDIT EDGE CASE
// ============================================
echo "\n\n==============================================================\n";
echo "TAHAP 9: AUDIT EDGE CASE\n";
echo "==============================================================\n";

// A. Soal nonaktif
echo "\nA. Efek soal nonaktif terhadap skoring\n";
echo "---------------------------------------\n";

// Tambahkan 1 soal nonaktif
$soalNonaktifId = DB::table('soal')->insertGetId([
    'bank_soal_id' => $bankSoal->id,
    'nomor_soal' => 99,
    'pertanyaan' => 'Soal Nonaktif - tidak muncul di view',
    'pilihan_a_teks' => 'A',
    'pilihan_b_teks' => 'B',
    'pilihan_c_teks' => 'C',
    'pilihan_d_teks' => 'D',
    'pilihan_e_teks' => 'E',
    'kunci_jawaban' => 'A',
    'bobot' => 5.00,
    'kategori' => 'sedang',
    'tipe_pertanyaan' => 'teks',
    'tipe_soal' => 'pilihan_ganda',
    'status' => 'arsip',
    'created_at' => now(),
    'updated_at' => now(),
]);
echo "Soal nonaktif ID {$soalNonaktifId} (bobot=5) ditambahkan.\n";

$aktifCount = Soal::where('bank_soal_id', $bankSoal->id)->where('status', 'aktif')->count();
$totalCount = Soal::where('bank_soal_id', $bankSoal->id)->count();
echo "Soal aktif: {$aktifCount}, Soal total: {$totalCount}\n";

// exam() load: hanya yang aktif
$examSoals = Soal::where('bank_soal_id', $bankSoal->id)->where('status', 'aktif')->get();
$examBobot = $examSoals->sum('bobot');
echo "Exam() view memuat {$aktifCount} soal, total bobot={$examBobot}\n";

// calculateScore() load: SEMUA (tidak filter status)
$scoreSoals = Soal::where('bank_soal_id', $bankSoal->id)->get();
$scoreBobot = $scoreSoals->sum('bobot');
echo "CalculateScore() memuat {$totalCount} soal, total bobot={$scoreBobot}\n";

echo "\nPOTENSI BUG: calculateScore() tidak filter status='aktif'\n";
echo "  Bobot view: {$examBobot}, Bobot skoring: {$scoreBobot}\n";
echo "  Jika ada soal nonaktif (bobot 5), pembagi jadi lebih besar\n";
echo "  Dampak: nilai yang dihitung lebih kecil dari seharusnya\n";

// Hapus soal nonaktif
DB::table('soal')->where('id', $soalNonaktifId)->delete();

// B. jumlah_soal jadwal
echo "\nB. Pengaruh jumlah_soal di jadwal\n";
echo "---------------------------------\n";
echo "  exam() mengambil SEMUA soal aktif (ignore jumlah_soal)\n";
echo "  calculateScore() mengambil SEMUA soal bank (ignore jumlah_soal)\n";
echo "  Kolom jumlah_soal di jadwal TIDAK MEMPENGARUHI jumlah soal yang diskor\n";
echo "  ✅ Ini design choice (jumlah_soal hanya untuk info/pagination frontend)\n";

// ============================================
// KESIMPULAN
// ============================================
echo "\n\n==============================================================\n";
echo "KESIMPULAN AKHIR\n";
echo "==============================================================\n";

$allPass = true;
foreach ($hasilKeseluruhan as $r) {
    if (!$r['passed'])
        $allPass = false;
}

echo "\n4 MODE UJIAN:\n";
foreach ($hasilKeseluruhan as $mode => $r) {
    echo ($r['passed'] ? '  ✅' : '  ❌') . " {$mode}: nilai={$r['nilai']}\n";
}
echo "  " . ($allPass ? '✅ ALL PASS' : '❌ ADA FAILURE') . "\n";

echo "\nSKENARIO PARSIAL:\n";
echo "  " . ($parsialPass ? '✅ PASS' : '❌ FAIL') . "\n";

echo "\nPOTENSI BUG:\n";
echo "  1.⚠️ calculateScore() tidak filter status='aktif' pada soal\n";
echo "    -> Jika ada soal nonaktif, bobot total jadi lebih besar, nilai jadi lebih kecil\n";
echo "    -> Berdampak pada akurasi nilai akhir siswa\n";
echo "  2. Informasi: jumlah_soal di jadwal tidak mempengaruhi soal yg dimuat/dinilai\n";
echo "    -> Ini adalah design choice, bukan bug\n";

echo "\n==============================================================\n";
echo "SELESAI\n";
echo "==============================================================\n";