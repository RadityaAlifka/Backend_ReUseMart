<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PegawaiController 
{
    public function index()
    {
        $pegawais = Pegawai::with('jabatan')->get();
        return response()->json($pegawais);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_jabatan' => 'required|exists:jabatans,id_jabatan',
            'nama_pegawai' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'no_telp' => 'required|string|max:15',
            'password' => 'required|string|min:8',
            'komisi' => 'required|numeric|min:0',
        ]);

        // Buat akun pengguna
        $user = User::create([
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'level' => 'pegawai',
        ]);

        // Buat data pegawai terkait
        $pegawai = Pegawai::create([
            'user_id' => $user->id,
            'id_jabatan' => $validatedData['id_jabatan'],
            'nama_pegawai' => $validatedData['nama_pegawai'],
            'email' => $validatedData['email'],
            'no_telp' => $validatedData['no_telp'],
            'password' => $user->password,
            'komisi' => $validatedData['komisi'],
        ]);

        return response()->json([
            'message' => 'Pegawai created successfully',
            'user' => $user,
            'pegawai' => $pegawai->load('jabatan'),
        ], 201);
    }

    public function show($id)
    {
        $pegawai = Pegawai::with('jabatan')->find($id);

        if (!$pegawai) {
            return response()->json(['message' => 'Pegawai not found'], 404);
        }

        return response()->json($pegawai);
    }

    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response()->json(['message' => 'Pegawai not found'], 404);
        }

        $validatedData = $request->validate([
            'id_jabatan' => 'sometimes|required|exists:jabatans,id_jabatan',
            'nama_pegawai' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:pegawais,email,' . $id . ',id_pegawai',
            'no_telp' => 'sometimes|required|string|max:15',
            'password' => 'sometimes|required|string|min:8',
            'komisi' => 'sometimes|required|numeric|min:0',
        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $pegawai->update($validatedData);

        // Update juga pada tabel users jika ada
        $user = $pegawai->user;
        if ($user) {
            $user->name = $pegawai->nama_pegawai;
            $user->email = $pegawai->email;
            if (isset($request->password)) {
                $user->password = Hash::make($request->password);
            }
            $user->save();
        }

        return response()->json([
            'message' => 'Pegawai updated successfully',
            'data' => $pegawai->load('jabatan')
        ]);
    }

    public function destroy($id)
    {
        $pegawai = Pegawai::find($id);
    
        if (!$pegawai) {
            return response()->json(['message' => 'Pegawai not found'], 404);
        }
    
        // Hapus user yang terkait terlebih dahulu
        $pegawai->user()->delete();
    
        // Lalu hapus pegawainya
        $pegawai->delete();
    
        return response()->json(['message' => 'Pegawai dan user terkait berhasil dihapus']);
    }
    

    public function getPegawaiLogin()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $pegawai = Pegawai::with('jabatan')->where('user_id', $user->id)->first();

        if (!$pegawai) {
            return response()->json(['message' => 'Pegawai not found'], 404);
        }
        return response()->json($pegawai);
    }
}
