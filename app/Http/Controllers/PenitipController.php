<?php

namespace App\Http\Controllers;

use App\Models\Penitip;
use App\Models\User;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenitipController 
{
    public function index()
    {
        $penitips = Penitip::all();
        return response()->json($penitips);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_penitip' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'no_telp' => 'required|string|max:15',
            'nik' => 'required|string|max:16|unique:penitips,nik',
            'saldo' => 'required|numeric|min:0',
            'poin' => 'required|integer|min:0',
            'akumulasi_rating' => 'required|integer|min:0',
        ]);

        // Buat akun pengguna
        $user = User::create([
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'level' => 'penjual',
        ]);

        // Buat data penitip terkait
        $penitip = Penitip::create([
            'user_id' => $user->id,
            'nama_penitip' => $validatedData['nama_penitip'],
            'email' => $validatedData['email'],
            'password' => $user->password,
            'no_telp' => $validatedData['no_telp'],
            'nik' => $validatedData['nik'],
            'saldo' => $validatedData['saldo'],
            'poin' => $validatedData['poin'],
            'akumulasi_rating' => $validatedData['akumulasi_rating'],
        ]);

        return response()->json([
            'message' => 'Penitip created successfully',
            'user' => $user,
            'penitip' => $penitip,
        ], 201);
    }

    public function show($id)
    {
        $penitip = Penitip::find($id);

        if (!$penitip) {
            return response()->json(['message' => 'Penitip not found'], 404);
        }

        return response()->json($penitip);
    }

    public function update(Request $request, $id)
    {
        $penitip = Penitip::find($id);

        if (!$penitip) {
            return response()->json(['message' => 'Penitip not found'], 404);
        }

        $validatedData = $request->validate([
            'nama_penitip' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:penitips,email,' . $id . ',id_penitip',
            'password' => 'sometimes|string|min:8',
            'no_telp' => 'sometimes|string|max:15',
            'nik' => 'sometimes|string|max:16|unique:penitips,nik,' . $id . ',id_penitip',
            'saldo' => 'sometimes|numeric|min:0',
            'poin' => 'sometimes|integer|min:0',
            'akumulasi_rating' => 'sometimes|integer|min:0',
        ]);

        // Update email di tabel users jika berubah
    if (isset($validatedData['email'])) {
        $user = \App\Models\User::find($penitip->user_id);
        if ($user) {
            $user->email = $validatedData['email'];
            $user->save();
        }
    }

    // Update password di tabel users jika berubah
        if (isset($validatedData['password'])) {
            $hashedPassword = \Hash::make($validatedData['password']);
            $validatedData['password'] = $hashedPassword;

            $user = \App\Models\User::find($penitip->user_id);
            if ($user) {
                $user->password = $hashedPassword;
                $user->save();
            }
        }
    
        $pegawai->update($validatedData);
    
        // Update juga pada tabel users jika ada
        $user = $penitip->user;
        if ($user) {
            $user->name = $penitip->nama_pegawai;
            $user->email = $penitip->email;
            if (isset($request->password)) {
                $user->password = Hash::make($request->password);
            }
            $user->save();
        }
        $penitip->update($validatedData);

        return response()->json([
            'message' => 'Organisasi updated successfully',
            'data' => $penitip
        ]);
    }

    public function destroy($id)
    {
        $penitip = Penitip::find($id);

        if (!$penitip) {
            return response()->json(['message' => 'Penitip not found'], 404);
        }

        $penitip->delete();

        return response()->json(['message' => 'Penitip deleted successfully']);
    }
    public function profil(Request $request)
    {
        $user = $request->user();

        $penitip = Penitip::where('user_id', $user->id)->first();

        if (!$penitip) {
            return response()->json(['message' => 'Penitip not found'], 404);
        }

        return response()->json($penitip);
    }
    
    public function getByUserId($user_id)
{
    $penitip = Penitip::where('user_id', $user_id)->first();

    if (!$penitip) {
        return response()->json(['message' => 'Penitip not found'], 404);
    }

    return response()->json($penitip);
}

}
