<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Alamat;
use App\Models\Pembeli;

class AlamatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil pembeli dari seeder sebelumnya
        $pembeli = Pembeli::where('email', 'pembeli123@example.com')->first();

        if ($pembeli) {
            Alamat::create([
                'id_pembeli'    => $pembeli->id_pembeli,
                'kabupaten'     => 'Bandung',
                'kecamatan'     => 'Coblong',
                'kelurahan'     => 'Dago',
                'detail_alamat' => 'Jl. Contoh No. 123',
                'kode_pos'      => 40135,
                'label_alamat'  => 'Rumah Utama',
            ]);
            Alamat::create([
                'id_pembeli'    => $pembeli->id_pembeli,
                'kabupaten'     => 'Bandung',
                'kecamatan'     => 'Sukajadi',
                'kelurahan'     => 'Pasteur',
                'detail_alamat' => 'Jl. Pasteur No. 45',
                'kode_pos'      => 40161,
                'label_alamat'  => 'Kantor',
            ]);
            Alamat::create([
                'id_pembeli'    => $pembeli->id_pembeli,
                'kabupaten'     => 'Bandung',
                'kecamatan'     => 'Lengkong',
                'kelurahan'     => 'Cijagra',
                'detail_alamat' => 'Jl. Cijagra No. 78',
                'kode_pos'      => 40265,
                'label_alamat'  => 'Rumah Orang Tua',
            ]);
        }
    }
}   