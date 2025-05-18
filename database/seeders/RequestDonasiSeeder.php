<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequestDonasi;
use App\Models\Organisasi;
use App\Models\Pegawai;

class RequestDonasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data organisasi
        $organisasi1 = Organisasi::where('email', 'organisasiA@example.com')->first();
        $organisasi2 = Organisasi::where('email', 'organisasiB@example.com')->first();

        // Ambil data pegawai (admin)
        $pegawai = Pegawai::where('email', 'admin123@example.com')->first();

        // Buat request donasi untuk organisasi pertama
        RequestDonasi::create([
            'id_organisasi' => $organisasi1->id_organisasi,
            'tanggal_request' => now(),
            'detail_request' => 'Permintaan donasi untuk bantuan sosial di wilayah Jakarta.',
        ]);

        // Buat request donasi untuk organisasi kedua
        RequestDonasi::create([
            'id_organisasi' => $organisasi2->id_organisasi,
            'tanggal_request' => now()->addDays(1),
            'detail_request' => 'Permintaan donasi untuk bantuan pendidikan di wilayah Bandung.',
        ]);
    }
}