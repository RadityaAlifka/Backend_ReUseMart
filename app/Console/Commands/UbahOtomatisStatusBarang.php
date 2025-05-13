<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Barang;
use Carbon\Carbon;

class UbahOtomatisStatusBarang extends Command
{
    protected $signature = 'barang:auto-donate';
    protected $description = 'Otomatis mendonasikan barang jika tidak diambil dalam 2 hari';

    public function handle()
    {
        $today = Carbon::today();

        // Barang dengan status "Masa Titip Habis" yang melewati batas_penitipan
        $barangsToDonate = Barang::where('status_barang', 'Masa Titip Habis')
            ->whereHas('penitipan', function ($query) use ($today) {
                $query->where('batas_penitipan', '<', $today->subDays(2));
            })
            ->get();

        foreach ($barangsToDonate as $barang) {
            $barang->updateStatus('Menunggu Donasi');
        }

        $this->info('Barang successfully auto-donated or moved to Menunggu Donasi.');
    }
}