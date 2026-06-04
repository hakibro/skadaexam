<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Schedule::command('app:clear-keterangan-sesi-ruangan-siswa')
    ->dailyAt('20:46');

Schedule::command('siswa:quick-sync')
    ->everyMinute()
    ->withoutOverlapping(30);

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
