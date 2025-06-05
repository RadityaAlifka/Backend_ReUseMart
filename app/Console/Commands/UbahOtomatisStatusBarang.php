<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Barang;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UbahOtomatisStatusBarang extends Command
{
    protected $signature = 'barang:auto-donate';
    protected $description = 'Otomatis mendonasikan barang jika tidak diambil dalam 2 hari';

    public function handle()
    {
        $today = Carbon::today();
        $twoDaysAgo = $today->copy()->subDays(2);

        // Log the start of the process with more details
        \Log::info('Starting auto-donate check process', [
            'current_date' => $today->toDateString(),
            'checking_before_date' => $twoDaysAgo->toDateString()
        ]);

        // Get all barang that need to be checked first
        $barangsToCheck = Barang::whereRaw('LOWER(status_barang) IN (?, ?, ?)', ['tersedia', 'menunggu diambil', 'masa titip habis'])
            ->with('penitipan')  // Eager load penitipan
            ->get();

        \Log::info('Found barang to check', [
            'total_barang' => $barangsToCheck->count()
        ]);

        // Filter manually and log each case
        $count = 0;
        foreach ($barangsToCheck as $barang) {
            if (!$barang->penitipan) {
                \Log::warning('Barang has no penitipan record', [
                    'id_barang' => $barang->id_barang,
                    'status' => $barang->status_barang
                ]);
                continue;
            }

            // Convert to date only for comparison
            $batasPenitipan = Carbon::parse($barang->penitipan->batas_penitipan)->startOfDay();
            $twoDaysAgoDate = $twoDaysAgo->copy()->startOfDay();
            
            \Log::info('Checking barang', [
                'id_barang' => $barang->id_barang,
                'nama_barang' => $barang->nama_barang,
                'current_status' => $barang->status_barang,
                'batas_penitipan' => $batasPenitipan->toDateString(),
                'two_days_ago' => $twoDaysAgoDate->toDateString(),
                'is_expired' => $batasPenitipan->lte($twoDaysAgoDate),
                'days_difference' => $batasPenitipan->diffInDays($today, false)
            ]);

            // Check if batas_penitipan is more than 2 days ago
            if ($batasPenitipan->lte($twoDaysAgoDate)) {
                $oldStatus = $barang->status_barang;
                $barang->status_barang = 'menunggu donasi';
                $barang->save();
                
                \Log::info('Barang status updated', [
                    'id_barang' => $barang->id_barang,
                    'nama_barang' => $barang->nama_barang,
                    'old_status' => $oldStatus,
                    'new_status' => 'menunggu donasi',
                    'batas_penitipan' => $batasPenitipan->toDateString(),
                    'days_since_expiry' => $batasPenitipan->diffInDays($today)
                ]);
                
                $count++;
            }
        }

        $message = "Processed {$count} barang(s): Status updated to 'menunggu donasi'";
        \Log::info($message);
        $this->info($message);
    }
}