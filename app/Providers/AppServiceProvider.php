<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword; // Jangan lupa import ini

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pastikan Anda mengimpor ResetPassword di bagian atas file

        // Ganti URL menjadi alamat frontend lokal Anda
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            // Dapatkan URL frontend dari konfigurasi.
            // Lebih baik definisikan variabel environment khusus untuk URL frontend,
            // contohnya di .env: FRONTEND_URL=http://localhost:5173
            // Dan di config/app.php: 'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),

            $frontendBaseUrl = config('app.frontend_url', 'http://localhost:5173'); // Gunakan ini

            // Anda juga harus memastikan rute di frontend React Anda adalah /reset-password,
            // bukan /forgot-password, untuk form pengisian password baru.

            // Contoh untuk pengembangan (local/development):
            if (env('APP_ENV') === 'local' || env('APP_ENV') === 'development') {
                // Pastikan ini juga mengarah ke halaman reset password sebenarnya
                return 'http://localhost:5173/reset-password?token=' . $token . '&email=' . urlencode($notifiable->email);
            }

            // Untuk lingkungan selain local/development, gunakan URL frontend yang telah dikonfigurasi.
            // **PENTING:** Pastikan 'frontend_url' di config/app.php mengambil nilai dari FRONTEND_URL di .env
            // yang menunjuk ke domain frontend produksi Anda (misal: https://your-frontend-domain.com).
            return $frontendBaseUrl . '/reset-password?token=' . $token . '&email=' . urlencode($notifiable->email);
        });
    }
}