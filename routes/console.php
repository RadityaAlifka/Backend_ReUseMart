<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use App\Console\Commands\UbahOtomatisStatusBarang;
use Illuminate\Console\Scheduling\Schedule;
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwalkan command barang:auto-donate
app()->singleton(Schedule::class, function ($app) {
    $schedule = new Schedule();

    // Menjadwalkan command untuk dijalankan setiap hari pukul 00:00
    $schedule->command('barang:auto-donate')->dailyAt('00:00')->timezone('Asia/Jakarta');

    // Notifikasi H-3 setiap hari jam 9 pagi
    $schedule->command('notification:check-h3')->dailyAt('09:00')->timezone('Asia/Jakarta');

    // Notifikasi Hari H setiap hari jam 9 pagi
    $schedule->command('notification:check-hari-h')->dailyAt('09:00')->timezone('Asia/Jakarta');

    return $schedule;
});