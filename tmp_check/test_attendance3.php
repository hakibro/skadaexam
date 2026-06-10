<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$pivot = \App\Models\JadwalUjianSesiRuangan::whereNotNull('pengawas_id')->first();
echo "pengawas_id={$pivot->pengawas_id}, sesi_ruangan_id={$pivot->sesi_ruangan_id}\n";

$guru = \App\Models\Guru::find($pivot->pengawas_id);
$user = $guru->user;
echo "user: {$user->id}\n";

$sesi = \App\Models\SesiRuangan::with('sesiRuanganSiswa')->find($pivot->sesi_ruangan_id);
echo "siswa count: " . $sesi->sesiRuanganSiswa->count() . "\n";
foreach ($sesi->sesiRuanganSiswa->take(5) as $s) {
    echo " - siswa_id={$s->siswa_id} status_kehadiran=" . var_export($s->status_kehadiran, true) . "\n";
}

$request = Illuminate\Http\Request::create("/features/pengawas/assignment/{$sesi->id}/attendance-summary", 'GET');
$request->headers->set('Accept', 'application/json');
$app->instance('request', $request);

Illuminate\Support\Facades\Auth::guard('web')->login($user);

$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo $response->getContent() . "\n";
