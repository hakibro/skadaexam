<?php
// filepath: database/seeders/SesiUjianSeeder.php

namespace Database\Seeders;

use App\Models\JadwalUjian;
use App\Models\SesiUjian;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SesiUjianSeeder extends Seeder
{
    public function run(): void
    {
        echo "⏱️ Seeding Sesi Ujian data...\n";

        $jadwalUjianList = JadwalUjian::all();
        if ($jadwalUjianList->isEmpty()) {
            echo "❌ No Jadwal Ujian found! Please run the JadwalUjianSeeder first.\n";
            return;
        }

        $count = 0;

        foreach ($jadwalUjianList as $jadwal) {
            $sessionCount = rand(2, 3); // 2-3 sesi per jadwal
            $startTime = Carbon::parse($jadwal->tanggal_mulai);

            for ($i = 0; $i < $sessionCount; $i++) {
                $sessionDate = $startTime->copy()->addDays($i);
                $sessionStartTime = match ($i) {
                    0 => $sessionDate->copy()->setHour(8)->setMinute(0),
                    1 => $sessionDate->copy()->setHour(10)->setMinute(30),
                    default => $sessionDate->copy()->setHour(13)->setMinute(0),
                };
                $sessionEndTime = $sessionStartTime->copy()->addMinutes($jadwal->durasi_menit);

                SesiUjian::create([
                    'jadwal_ujian_id'  => $jadwal->id,
                    'kode_sesi'        => 'S' . ($i + 1) . '-J' . $jadwal->id,
                    'nama_sesi'        => 'Sesi ' . ($i + 1) . ' - ' . $jadwal->judul,
                    'tanggal_sesi'     => $sessionDate->format('Y-m-d'),
                    'waktu_mulai'      => $sessionStartTime->format('H:i:s'),
                    'waktu_selesai'    => $sessionEndTime->format('H:i:s'),
                    'token_ujian'      => strtoupper(Str::random(6)),
                    'token_expired_at' => $sessionEndTime->format('Y-m-d H:i:s'),
                    'pengawas_id'      => null, // bisa diisi NIP guru nanti
                    'status'           => 'aktif', // contoh enum: aktif/nonaktif
                    'pengaturan'       => json_encode([]), // default kosong
                ]);

                $count++;
            }
        }

        echo "✅ {$count} sesi ujian seeded successfully!\n";
    }
}
