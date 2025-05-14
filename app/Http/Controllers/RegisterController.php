<?
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Penitip;
use App\Models\Pembeli;
use App\Models\Pegawai;
use App\Models\Organisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController 
{
    public function registerPenitip(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'nama' => 'required',
            'no_telp' => 'required',
            'nik' => 'required',
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'level' => 'penitip',
        ]);

        Penitip::create([
            'user_id' => $user->id,
            'nama_penitip' => $validated['nama'],
            'email' => $user->email,
            'password' => $user->password,
            'no_telp' => $validated['no_telp'],
            'nik' => $validated['nik'],
            'saldo' => 0,
            'poin' => 0,
            'akumulasi_rating' => 0,
        ]);

        return response()->json(['message' => 'Penitip registered successfully']);
    }

    public function registerPembeli(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'nama' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'level' => 'pembeli',
        ]);

        Pembeli::create([
            'user_id' => $user->id,
            'password' => $user->password,
            'nama_pembeli' => $validated['nama'],
            'email' => $user->email,
            'alamat' => $validated['alamat'],
            'no_telp' => $validated['no_telp'],
            'saldo' => 0,
            'poin' => 0,
        ]);

        return response()->json(['message' => 'Pembeli registered successfully']);
    }

    public function registerPegawai(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'nama' => 'required',
            'no_telp' => 'required',
            'jabatan_id' => 'required|exists:jabatans,id',
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'level' => 'pegawai',
        ]);

        Pegawai::create([
            'user_id' => $user->id,
            'nama_pegawai' => $validated['nama'],
            'email' => $user->email,
            'password' => $user->password,
            'no_telp' => $validated['no_telp'],
            'jabatan_id' => $validated['jabatan_id'],
        ]);

        return response()->json(['message' => 'Pegawai registered successfully']);
    }

    public function registerOrganisasi(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'nama' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'level' => 'organisasi',
        ]);

        Organisasi::create([
            'user_id' => $user->id,
            'nama_organisasi' => $validated['nama'],
            'password' => $user->password,
            'email' => $user->email,
            'alamat' => $validated['alamat'],
            'no_telp' => $validated['no_telp'],
        ]);

        return response()->json(['message' => 'Organisasi registered successfully']);
    }
}
