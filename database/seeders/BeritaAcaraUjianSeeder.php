<?php

namespace Database\Seeders;

use App\Models\BeritaAcaraUjian;
use App\Models\SesiRuangan;
use Illuminate\Database\Seeder;

class BeritaAcaraUjianSeeder extends Seeder
{
    public function run(): void
    {
        echo "📋 Seeding Berita Acara Ujian data...\n";

        // Get session rooms that have a supervisor
        $sesiRuanganWithPengawas = SesiRuangan::whereNotNull('pengawas_id')->get();

        if ($sesiRuanganWithPengawas->isEmpty()) {
            echo "❌ No Sesi Ruangan with pengawas found! Please ensure GuruSeeder and SesiRuanganSeeder ran successfully.\n";
            return;
        }

        $count = 0;
        $statusOptions = ['lancar', 'kurang_lancar', 'tidak_lancar'];

        foreach ($sesiRuanganWithPengawas as $sesiRuangan) {
            // Skip if berita acara already exists
            if (BeritaAcaraUjian::where('sesi_ruangan_id', $sesiRuangan->id)->exists()) {
                continue;
            }

            // Count attendees from sesi_ruangan_siswa
            $totalTerdaftar = $sesiRuangan->sesiRuanganSiswa()->count();
            $totalHadir = $sesiRuangan->sesiRuanganSiswa()->where('status_kehadiran', 'hadir')->count();
            $totalTidakHadir = $totalTerdaftar - $totalHadir;

            $status = $statusOptions[array_rand($statusOptions)];
            $isFinalized = rand(0, 10) > 3; // 70% finalized

            BeritaAcaraUjian::create([
                'sesi_ruangan_id' => $sesiRuangan->id,
                'pengawas_id' => $sesiRuangan->pengawas_id,
                'catatan_pembukaan' => $this->generateCatatanPembukaan($status),
                'catatan_pelaksanaan' => $this->generateCatatanPelaksanaan($status),
                'catatan_penutupan' => $isFinalized ? $this->generateCatatanPenutupan($status) : null,
                'jumlah_peserta_terdaftar' => $totalTerdaftar,
                'jumlah_peserta_hadir' => $totalHadir,
                'jumlah_peserta_tidak_hadir' => $totalTidakHadir,
                'status_pelaksanaan' => $status,
                'is_final' => $isFinalized,
                'waktu_finalisasi' => $isFinalized ? now()->subHours(rand(1, 24)) : null,
            ]);

            $count++;
        }

        echo "✅ {$count} berita acara ujian seeded successfully!\n";
    }

    private function generateCatatanPembukaan($status): string
    {
        $templates = [
            'lancar' => [
                'Ujian dimulai tepat waktu. Semua peserta hadir dan siap.',
                'Persiapan ujian berjalan lancar. Tidak ada kendala teknis.',
                'Peserta tertib masuk ruangan dan mengikuti protokol ujian.',
            ],
            'kurang_lancar' => [
                'Ujian dimulai terlambat 15 menit karena ada peserta yang terlambat.',
                'Ada sedikit kebingungan terkait tempat duduk peserta.',
                'Beberapa peserta membutuhkan penjelasan tambahan tentang tata cara ujian.',
            ],
            'tidak_lancar' => [
                'Ujian terlambat dimulai 30 menit karena masalah teknis.',
                'Banyak peserta yang datang terlambat dan tidak siap.',
                'Ada gangguan di awal ujian yang memerlukan penanganan khusus.',
            ],
        ];

        return $templates[$status][array_rand($templates[$status])];
    }

    private function generateCatatanPelaksanaan($status): string
    {
        $templates = [
            'lancar' => [
                'Ujian berjalan tertib. Peserta mengikuti aturan dengan baik.',
                'Tidak ada pelanggaran atau gangguan selama pelaksanaan ujian.',
                'Semua peserta fokus dan kondusif selama ujian berlangsung.',
            ],
            'kurang_lancar' => [
                'Ada beberapa peserta yang perlu ditegur karena tidak fokus.',
                'Terdapat sedikit keributan namun dapat diatasi dengan baik.',
                'Beberapa peserta memerlukan bimbingan tambahan.',
            ],
            'tidak_lancar' => [
                'Terjadi gangguan teknis yang menghambat pelaksanaan ujian.',
                'Ada peserta yang melanggar aturan ujian dan perlu ditindak.',
                'Kondisi ruangan tidak kondusif untuk pelaksanaan ujian.',
            ],
        ];

        return $templates[$status][array_rand($templates[$status])];
    }

    private function generateCatatanPenutupan($status): string
    {
        $templates = [
            'lancar' => [
                'Ujian selesai tepat waktu. Semua lembar jawaban terkumpul dengan baik.',
                'Penutupan ujian berjalan tertib. Tidak ada kendala.',
                'Seluruh peserta dapat menyelesaikan ujian sesuai waktu yang ditetapkan.',
            ],
            'kurang_lancar' => [
                'Ujian selesai dengan sedikit perpanjangan waktu.',
                'Ada beberapa lembar jawaban yang perlu pengecekan ulang.',
                'Penutupan sedikit terlambat namun masih dalam batas wajar.',
            ],
            'tidak_lancar' => [
                'Ujian terpaksa diperpanjang waktu karena gangguan sebelumnya.',
                'Beberapa peserta tidak dapat menyelesaikan ujian dengan optimal.',
                'Penutupan ujian mengalami kendala dan memerlukan penanganan khusus.',
            ],
        ];

        return $templates[$status][array_rand($templates[$status])];
    }
}
