<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organisasi;
use App\Models\Donasi;
use App\Models\Barang;
use Illuminate\Support\Facades\Hash;

class OrganisasiDonasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat akun pengguna untuk organisasi pertama
        $user1 = User::create([
            'email' => 'organisasiA@example.com',
            'password' => Hash::make('organisasi123'), // Password terenkripsi
            'level' => 'organisasi',
        ]);

        // Buat data organisasi pertama
        $organisasi1 = Organisasi::create([
            'user_id' => $user1->id, // Relasi ke tabel users
            'nama_organisasi' => 'Organisasi Sosial A',
            'alamat' => 'Jl. Sosial No. 123, Jakarta',
            'email' => $user1->email,
            'no_telp' => '081234567890',
            'password' => $user1->password, // Password terenkripsi
        ]);

        // Buat akun pengguna untuk organisasi kedua
        $user2 = User::create([
            'email' => 'organisasiB@example.com',
            'password' => Hash::make('organisasi123'), // Password terenkripsi
            'level' => 'organisasi',
        ]);

        // Buat data organisasi kedua
        $organisasi2 = Organisasi::create([
            'user_id' => $user2->id, // Relasi ke tabel users
            'nama_organisasi' => 'Organisasi Sosial B',
            'alamat' => 'Jl. Kemanusiaan No. 456, Bandung',
            'email' => $user2->email,
            'no_telp' => '081234567891',
            'password' => $user2->password, // Password terenkripsi
        ]);

        // Buat donasi untuk organisasi pertama
        $donasi1 = Donasi::create([
            'id_organisasi' => $organisasi1->id_organisasi,
            'tanggal_donasi' => now(),
            'nama_penerima' => 'Penerima A',
        ]);

        // Buat donasi untuk organisasi kedua
        $donasi2 = Donasi::create([
            'id_organisasi' => $organisasi2->id_organisasi,
            'tanggal_donasi' => now(),
            'nama_penerima' => 'Penerima B',
        ]);

        // Ambil barang yang belum digunakan pada transaksi
        $barangDonasi = Barang::whereNotIn('nama_barang', [
            'Smartphone Samsung Galaxy', // Barang yang digunakan di transaksi
            'Jaket Kulit',               // Barang yang digunakan di transaksi
        ])->get();

        // Hubungkan barang ke donasi pertama dan ubah status menjadi "didonasikan"
        $barangDonasi->take(2)->each(function ($barang) use ($donasi1) {
            $barang->update([
                'id_donasi' => $donasi1->id_donasi,
                'status_barang' => 'didonasikan',
            ]);
        });

        // Hubungkan barang ke donasi kedua dan ubah status menjadi "didonasikan"
        $barangDonasi->skip(2)->take(2)->each(function ($barang) use ($donasi2) {
            $barang->update([
                'id_donasi' => $donasi2->id_donasi,
                'status_barang' => 'didonasikan',
            ]);
        });
    }
}