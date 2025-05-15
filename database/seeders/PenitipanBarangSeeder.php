<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Penitipan;
use App\Models\Barang;
use App\Models\Penitip;

class PenitipanBarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ambil data penitip yang sudah ada
        $penitip1 = Penitip::where('email', 'penitip123@example.com')->first();
        $penitip2 = Penitip::where('email', 'penitip2123@example.com')->first();

        // Buat data penitipan untuk penitip pertama
        $penitipan1 = Penitipan::create([
            'id_penitip' => $penitip1->id_penitip,
            'tanggal_penitipan' => now(),
            'batas_penitipan' => now()->addDays(30),
        ]);

        // Buat data barang untuk penitipan pertama
        Barang::create([
            'id_kategori' => 1, // Elektronik & Gadget
            'id_penitipan' => $penitipan1->id_penitipan,
            'nama_barang' => 'Smartphone Samsung Galaxy',
            'deskripsi_barang' => 'Smartphone dengan layar AMOLED 6.5 inci.',
            'garansi' => '1 Tahun',
            'tanggal_garansi' => now()->addYear(),
            'harga' => 5000000,
            'status_barang' => 'Tersedia',
            'berat' => 0.5,
            'gambar1' => 'images/barang/smartphone1.jpg',
            'gambar2' => 'images/barang/smartphone2.jpg',
        ]);

        Barang::create([
            'id_kategori' => 2, // Pakaian & Aksesoris
            'id_penitipan' => $penitipan1->id_penitipan,
            'nama_barang' => 'Jaket Kulit',
            'deskripsi_barang' => 'Jaket kulit asli berwarna hitam.',
            'garansi' => 'tidak tersedia',
            'tanggal_garansi' => null,
            'harga' => 1500000,
            'status_barang' => 'Tersedia',
            'berat' => 1.2,
            'gambar1' => 'images/barang/jaket1.jpg',
            'gambar2' => 'images/barang/jaket2.jpg',
        ]);

        // Buat data penitipan untuk penitip kedua
        $penitipan2 = Penitipan::create([
            'id_penitip' => $penitip2->id_penitip,
            'tanggal_penitipan' => now(),
            'batas_penitipan' => now()->addDays(30),
        ]);

        // Buat data barang untuk penitipan kedua
        Barang::create([
            'id_kategori' => 3, // Perabotan Rumah Tangga
            'id_penitipan' => $penitipan2->id_penitipan,
            'nama_barang' => 'Blender Philips',
            'deskripsi_barang' => 'Blender dengan kapasitas 1.5 liter.',
            'garansi' => '2 Tahun',
            'tanggal_garansi' => now()->addYears(2),
            'harga' => 700000,
            'status_barang' => 'Tersedia',
            'berat' => 2.0,
            'gambar1' => 'images/barang/blender1.jpg',
            'gambar2' => 'images/barang/blender2.jpg',
        ]);

        Barang::create([
            'id_kategori' => 4, // Buku, Alat Tulis, & Peralatan Sekolah
            'id_penitipan' => $penitipan2->id_penitipan,
            'nama_barang' => 'Buku Novel',
            'deskripsi_barang' => 'Novel fiksi dengan 300 halaman.',
            'garansi' => 'tidak tersedia',
            'tanggal_garansi' => null,
            'harga' => 80000,
            'status_barang' => 'Tersedia',
            'berat' => 0.3,
            'gambar1' => 'images/barang/novel1.jpg',
            'gambar2' => 'images/barang/novel2.jpg',
        ]);
    }
}