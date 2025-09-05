<?php

namespace Database\Seeders;

use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use App\Models\Ruangan;
use App\Models\Guru;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SesiRuanganSeeder extends Seeder
{
    public function run(): void
    {
        echo "🏫 Seeding Sesi Ruangan data...\n";

        $jadwalUjianList = JadwalUjian::all();
        if ($jadwalUjianList->isEmpty()) {
            echo "❌ No Jadwal Ujian found! Please run the JadwalUjianSeeder first.\n";
            return;
        }

        $ruanganList = Ruangan::where('status', 'aktif')->get();
        if ($ruanganList->isEmpty()) {
            echo "❌ No active Ruangan found! Please run the RuanganSeeder first.\n";
            return;
        }

        $guruList = Guru::all();
        $count = 0;

        foreach ($jadwalUjianList as $jadwal) {
            $sessionCount = rand(2, 4); // 2-4 sesi ruangan per jadwal
            $examDate = Carbon::parse($jadwal->tanggal);

            // Create multiple sessions for each exam schedule
            for ($i = 0; $i < $sessionCount; $i++) {
                $sessionDate = $examDate->copy()->addDays(rand(0, 2)); // Spread over a few days

                // Different time slots for different sessions
                $timeSlots = [
                    ['start' => '08:00:00', 'name' => 'Sesi Pagi'],
                    ['start' => '10:30:00', 'name' => 'Sesi Tengah'],
                    ['start' => '13:00:00', 'name' => 'Sesi Siang'],
                    ['start' => '15:30:00', 'name' => 'Sesi Sore'],
                ];

                $timeSlot = $timeSlots[$i % count($timeSlots)];
                $startTime = $timeSlot['start'];
                $endTime = Carbon::createFromFormat('H:i:s', $startTime)
                    ->addMinutes($jadwal->durasi_menit)
                    ->format('H:i:s');

                // Randomly assign a room
                $ruangan = $ruanganList->random();

                // Randomly assign a supervisor (pengawas)
                $pengawas = $guruList->isNotEmpty() ? $guruList->random() : null;

                $sesiRuangan = new SesiRuangan([
                    'kode_sesi' => 'SR-' . $jadwal->id . '-' . ($i + 1),
                    'nama_sesi' => $timeSlot['name'] . ' - ' . $jadwal->judul,
                    'tanggal' => $sessionDate->format('Y-m-d'),
                    'waktu_mulai' => $startTime,
                    'waktu_selesai' => $endTime,
                    'token_ujian' => strtoupper(Str::random(8)),
                    'token_expired_at' => $sessionDate->copy()
                        ->setTimeFromTimeString($endTime)
                        ->addHours(2), // Token expires 2 hours after session ends
                    'status' => rand(0, 10) > 2 ? 'belum_mulai' : 'berlangsung', // 80% belum_mulai, 20% berlangsung
                    'pengaturan' => json_encode([
                        'max_peserta' => $ruangan->kapasitas,
                        'jadwal_ujian_id' => $jadwal->id,
                        'mapel_id' => $jadwal->mapel_id,
                        'bank_soal_id' => $jadwal->bank_soal_id,
                    ]),
                    'ruangan_id' => $ruangan->id,
                    'pengawas_id' => $pengawas?->id,
                ]);

                $sesiRuangan->save();

                $count++;
            }
        }

        echo "✅ {$count} sesi ruangan seeded successfully!\n";
    }
}
