<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Penitip;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PenitipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Buat akun user pertama
        $user1 = User::create([
            'email' => 'penitip1@example.com',
            'password' => Hash::make('password123'),
            'level' => 'penjual',
        ]);

        // Buat data penitip pertama
        Penitip::create([
            'user_id' => $user1->id,
            'nama_penitip' => 'John Doe',
            'email' => $user1->email,
            'password' => $user1->password,
            'no_telp' => '081234567890',
            'nik' => '1234567890123456',
            'saldo' => 100000.00,
            'poin' => 50,
            'akumulasi_rating' => 5,
        ]);

        // Buat akun user kedua
        $user2 = User::create([
            'email' => 'penitip2@example.com',
            'password' => Hash::make('password123'),
            'level' => 'penjual',
        ]);

        // Buat data penitip kedua
        Penitip::create([
            'user_id' => $user2->id,
            'nama_penitip' => 'Jane Smith',
            'email' => $user2->email,
            'password' => $user2->password,
            'no_telp' => '081298765432',
            'nik' => '6543210987654321',
            'saldo' => 200000.00,
            'poin' => 100,
            'akumulasi_rating' => 10,
        ]);
    }
}