<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Penitip;
use Carbon\Carbon;

class UpdateTopSeller extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'topseller:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghitung top seller bulan lalu, menetapkan status, dan memberikan bonus poin 1% dari total penjualan.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses pembaruan top seller...');

        // Tentukan periode (bulan lalu)
        $periode = Carbon::now()->subMonth();
        $awalBulan = $periode->copy()->startOfMonth()->format('Y-m-d');
        $akhirBulan = $periode->copy()->endOfMonth()->format('Y-m-d');

        $this->info("Menghitung penjualan untuk periode: $awalBulan s/d $akhirBulan");

        // Query untuk mencari penitip dengan penjualan tertinggi pada periode tersebut
        $topSeller = Penitip::query()
            ->select(
                'penitips.id_penitip',
                DB::raw('SUM(barangs.harga) as total_penjualan')
            )
            ->join('penitipans', 'penitips.id_penitip', '=', 'penitipans.id_penitip')
            ->join('barangs', 'penitipans.id_penitipan', '=', 'barangs.id_penitipan')
            ->join('detailtransaksis', 'barangs.id_barang', '=', 'detailtransaksis.id_barang')
            ->join('transaksis', 'detailtransaksis.id_transaksi', '=', 'transaksis.id_transaksi')
            ->whereBetween('transaksis.tgl_lunas', [$awalBulan, $akhirBulan])
            ->where('transaksis.status_pembayaran', 'Lunas')
            ->groupBy('penitips.id_penitip')
            ->orderBy('total_penjualan', 'desc')
            ->first();

        DB::transaction(function () use ($topSeller) {
            $this->info('Mereset status top seller sebelumnya...');
            Penitip::where('top_seller', true)->update(['top_seller' => false]);

            if ($topSeller) {
                // Ambil model Eloquent dari penitip yang menang
                $pemenang = Penitip::find($topSeller->id_penitip);

                if ($pemenang) {
                    // <-- LOGIKA BARU DIMULAI DI SINI -->

                    // 1. Ambil total penjualan dari hasil query
                    $totalPenjualan = $topSeller->total_penjualan;

                    // 2. Hitung bonus poin (1% dari total penjualan).
                    //    Gunakan floor() untuk membulatkan ke bawah agar tidak ada poin desimal.
                    $bonusPoin = floor($totalPenjualan * 0.01);

                    // 3. Update status top_seller dan tambahkan bonus ke poin yang sudah ada
                    $pemenang->top_seller = true;
                    $pemenang->poin += $bonusPoin; // Menambahkan bonus ke poin yang ada
                    $pemenang->save(); // Menyimpan perubahan ke database

                    // <-- LOGIKA BARU BERAKHIR DI SINI -->


                    // Berikan pesan sukses yang lebih informatif di konsol
                    $this->info("Top seller baru ditetapkan: {$pemenang->nama_penitip} (ID: {$pemenang->id_penitip}).");
                    $this->info("Total penjualan: Rp " . number_format($totalPenjualan) . ". Bonus sebesar {$bonusPoin} poin telah ditambahkan.");
                }
            } else {
                $this->warn('Tidak ada penjualan yang tercatat pada bulan lalu. Tidak ada top seller yang ditetapkan.');
            }
        });

        $this->info('Proses pembaruan top seller selesai.');
        return 0;
    }
}   