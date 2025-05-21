<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat akun pengguna untuk owner
        $user = User::create([
            'email' => 'owner1@example.com',
            'password' => Hash::make('owner123'), // Password terenkripsi
            'level' => 'pegawai',
        ]);

        // Buat data pegawai terkait
        Pegawai::create([
            'user_id' => $user->id,
            'id_jabatan' => 6, // Pastikan ID jabatan owner ada di tabel jabatans
            'nama_pegawai' => 'Owner',
            'email' => $user->email,
            'no_telp' => '081234567891',
            'password' => $user->password, // Password terenkripsi
            'komisi' => 0, // Owner biasanya tidak memiliki komisi
        ]);
    }
}
