<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaksi;
use App\Models\Detailtransaksi;
use App\Models\Barang;
use App\Models\Pembeli;

class TransaksiDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data pembeli
        $pembeli = Pembeli::where('email', 'pembeli123@example.com')->first();

        // Ambil barang
        $barang1 = Barang::where('nama_barang', 'Smartphone Samsung Galaxy')->first();
        $barang2 = Barang::where('nama_barang', 'Jaket Kulit')->first();

        // Buat transaksi
        $transaksi = Transaksi::create([
            'id_pembeli' => $pembeli->id_pembeli,
            'tgl_pesan' => now(),
            'tgl_lunas' => now()->addDays(1),
            'diskon_poin' => 15.0,
            'bukti_pembayaran' => 'bukti_pembayaran.jpg',
            'status_pembayaran' => 'Lunas',
        ]);

        // Tambahkan detail transaksi untuk barang pertama
        Detailtransaksi::create([
            'id_transaksi' => $transaksi->id_transaksi,
            'id_barang' => $barang1->id_barang,
        ]);

        // Tambahkan detail transaksi untuk barang kedua
        Detailtransaksi::create([
            'id_transaksi' => $transaksi->id_transaksi,
            'id_barang' => $barang2->id_barang,
        ]);
    }
}