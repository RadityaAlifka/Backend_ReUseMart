<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat akun pengguna untuk admin
        $user = User::create([
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'), // Password terenkripsi
            'level' => 'pegawai',
        ]);

        // Buat data pegawai terkait
        Pegawai::create([
            'user_id' => $user->id,
            'id_jabatan' => 1, // Pastikan ID jabatan admin ada di tabel jabatans
            'nama_pegawai' => 'Admin',
            'email' => $user->email,
            'no_telp' => '081234567890',
            'password' => $user->password, // Password terenkripsi
            'komisi' => 0, // Admin biasanya tidak memiliki komisi
        ]);
    }
}