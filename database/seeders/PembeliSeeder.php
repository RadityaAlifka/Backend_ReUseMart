<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pembeli;
use Illuminate\Support\Facades\Hash;

class PembeliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat akun pengguna untuk pembeli
        $user = User::create([
            'email' => 'pembeli123@example.com',
            'password' => Hash::make('pembeli123'), // Password terenkripsi
            'level' => 'pembeli',
        ]);

        // Buat data pembeli terkait
        Pembeli::create([
            'user_id' => $user->id, // Relasi ke tabel users
            'nama_pembeli' => 'Pembeli Contoh',
            'email' => $user->email,
            'no_telp' => '081234567891',
            'password' => $user->password, // Password terenkripsi
            'poin' => 100, // Poin awal
        ]);
    }
}