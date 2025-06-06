<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use App\Console\Commands\UbahOtomatisStatusBarang;
use Illuminate\Console\Scheduling\Schedule;
use app\model\notificationController;

app(Schedule::class)->call(function () {
    $notificationController = resolve(NotificationController::class); // atau app(NotificationController::class);
    $notificationController->sendDonasiNotification();
})->everyMinute()
  ->name('send_donasi_notification_closure') // Memberi nama pada task (opsional tapi bagus)
  ->withoutOverlapping(); 

// Jadwalkan command barang:auto-donate
app()->singleton(Schedule::class, function ($app) {
    $schedule = new Schedule();

    // Menjadwalkan command untuk dijalankan setiap hari pukul 00:00
    $schedule->command('barang:auto-donate')->everyMinute();

    // Notifikasi H-3 setiap hari jam 9 pagi
    $schedule->command('notification:check-h3')->everyMinute();

    // Notifikasi Hari H setiap hari jam 9 pagi
    $schedule->command('notification:check-hari-h')->everyMinute();
    return $schedule;
});