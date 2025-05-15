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
            'level' => 'penitip',
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
            'nama_penitip' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:penitips,email,' . $id . ',id_penitip',
            'password' => 'sometimes|required|string|min:8',
            'no_telp' => 'sometimes|required|string|max:15',
            'nik' => 'sometimes|required|string|max:16|unique:penitips,nik,' . $id . ',id_penitip',
            'saldo' => 'sometimes|required|numeric|min:0',
            'poin' => 'sometimes|required|integer|min:0',
            'akumulasi_rating' => 'sometimes|required|integer|min:0',
        ]);

        if (isset($validatedData['password'])) {
            $hashedPassword = \Hash::make($validatedData['password']);
            $validatedData['password'] = $hashedPassword;
    
            // Update password di tabel users
            $user = \App\Models\User::find($pegawai->user_id);
            if ($user) {
                $user->password = $hashedPassword;
                $user->save();
            }
        }
        $penitip->update($validatedData);

        return response()->json([
            'message' => 'Penitip updated successfully',
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
    
}
