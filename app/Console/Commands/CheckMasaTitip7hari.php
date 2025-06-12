<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penitipan;
use App\Models\Barang;
use Carbon\Carbon;

class CheckMasaTitip7hari extends Command
{
    protected $signature = 'check:masatitip7hari';
    protected $description = 'Ubah status barang menjadi donasi jika masa titip habis > 7 hari & belum diambil';

    public function handle()
    {
        $now = Carbon::now();
        // Ambil semua penitipan yang sudah lewat batas penitipan + 7 hari
        $penitipans = Penitipan::where('batas_penitipan', '<', $now->copy()->subDays(7))
            ->get();
        $count = 0;
        foreach ($penitipans as $penitipan) {
            // Ambil semua barang dari penitipan ini yang statusnya belum diambil (misal: "dititipkan")
            foreach ($penitipan->barangs as $barang) {
                if ($barang->status_barang !== 'barang untuk donasi' || $barang->status_barang === 'dititipkan') {
                    // Ubah status barang
                    $barang->updateStatus('menunggu donasi');
                    $count++;
                }
            }
        }
        $this->info("Berhasil mengubah status $count barang menjadi 'menunggu donasi'.");
    }
}
