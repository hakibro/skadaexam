<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'pengawas'))->first();
echo "user: {$user->id} {$user->name}\n";

$sesi = \App\Models\SesiRuangan::with('sesiRuanganSiswa')->whereHas('sesiRuanganSiswa')->first();
echo "sesi id: {$sesi->id}, siswa count: " . $sesi->sesiRuanganSiswa->count() . "\n";

$request = Illuminate\Http\Request::create("/features/pengawas/assignment/{$sesi->id}/attendance-summary", 'GET');
$request->headers->set('Accept', 'application/json');
$app->instance('request', $request);

Illuminate\Support\Facades\Auth::guard('web')->login($user);

$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo $response->getContent() . "\n";
