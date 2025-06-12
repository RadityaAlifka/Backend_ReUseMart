<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use App\Console\Commands\UbahOtomatisStatusBarang;
use Illuminate\Console\Scheduling\Schedule;
use app\model\notificationController;
use App\Console\Commands\CheckMasaTitip7hari;
app(Schedule::class)->call(function () {
    $notificationController = resolve(NotificationController::class); 
    $notificationController->sendDonasiNotification();
})->everyMinute()
  ->name('send_donasi_notification_closure') 
  ->withoutOverlapping(); 

app()->singleton(Schedule::class, function ($app) {
    $schedule = new Schedule();
    $schedule->command('notification:check-h3')->everyTenSeconds();

    $schedule->command('notification:check-hari-h')->everyTenSeconds();

    $schedule->command('barang:auto-donate')->everyTenMinutes();

    $schedule->command('check:masatitip7hari')->everyTenMinutes();

    return $schedule;
});