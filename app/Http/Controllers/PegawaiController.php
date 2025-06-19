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
        try {
            $validatedData = $request->validate([
                'id_jabatan' => 'required|exists:jabatans,id_jabatan',
                'nama_pegawai' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'no_telp' => 'required|string|max:15',
                'tanggal_lahir' => 'required|date', // Added tanggal_lahir validation
                'password' => 'required|string|min:8',
                'komisi' => 'required|numeric|min:0',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        }

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
            'email' => $validatedData['email'],
            'nama_pegawai' => $validatedData['nama_pegawai'],
            'no_telp' => $validatedData['no_telp'],
            'tanggal_lahir' => $validatedData['tanggal_lahir'], // Added tanggal_lahir to pegawai
            'password' => $user->password, // Storing hashed password in pegawai table (optional, but consistent with original code)
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

        try {
            $validatedData = $request->validate([
                'id_jabatan' => 'sometimes|required|exists:jabatans,id_jabatan', // Allow updating jabatan
                'nama_pegawai' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $pegawai->user_id, // Unique against users table, exclude current user
                'no_telp' => 'sometimes|required|string|max:15',
                'tanggal_lahir' => 'sometimes|required|date', // Added tanggal_lahir for update
                // Password is handled separately for reset, but can be updated here too
                'password' => 'sometimes|required|string|min:8',
                'komisi' => 'sometimes|required|numeric|min:0', // Allow updating komisi
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        }

        // Update email in users table if changed
        if (isset($validatedData['email'])) {
            $user = User::find($pegawai->user_id);
            if ($user) {
                $user->email = $validatedData['email'];
                $user->save();
            }
        }

        // Update password in users table if changed
        if (isset($validatedData['password'])) {
            $hashedPassword = Hash::make($validatedData['password']);
            $validatedData['password'] = $hashedPassword; // Update password in pegawai's validated data
            $user = User::find($pegawai->user_id);
            if ($user) {
                $user->password = $hashedPassword;
                $user->save();
            }
        }

        $pegawai->update($validatedData);

        return response()->json([
            'message' => 'Pegawai updated successfully',
            'data' => $pegawai->load('jabatan') // Reload jabatan relationship after update
        ]);
    }

    public function destroy($id)
    {
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response()->json(['message' => 'Pegawai not found'], 404);
        }

        // Hapus user yang terkait terlebih dahulu
        // Ensure user_id exists before attempting to delete the user
        if ($pegawai->user_id) {
            $user = User::find($pegawai->user_id);
            if ($user) {
                $user->delete();
            }
        }

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

    public function getPegawaiCSFromToken(Request $request)
    {
        // Misal kamu menggunakan Laravel Sanctum atau JWT, ambil user dari token
        $user = $request->user(); // user yang sudah terautentikasi dari token

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan dari token'], 401);
        }

        // Cari jabatan CS dulu
        $jabatanCs = Jabatan::where('nama_jabatan', 'cs')->first();
        if (!$jabatanCs) {
            return response()->json(['message' => 'Jabatan CS tidak ditemukan'], 404);
        }

        // Cari pegawai yang user_id-nya sama dan id_jabatan = CS
        $pegawai = Pegawai::where('user_id', $user->id)
            ->where('id_jabatan', $jabatanCs->id_jabatan)
            ->first();

        if (!$pegawai) {
            return response()->json(['message' => 'Pegawai CS tidak ditemukan untuk user ini'], 404);
        }

        return response()->json([
            'message' => 'Berhasil mendapatkan pegawai CS dari token',
            'data' => $pegawai,
        ]);
    }

    public function getKurir()
    {
        $kurir = Pegawai::whereHas('jabatan', function ($query) {
            $query->where('nama_jabatan', 'kurir');
        })->with('jabatan')->get();

        return response()->json([
            'message' => 'Berhasil mengambil pegawai dengan jabatan Kurir',
            'data' => $kurir
        ]);
    }

    public function resetPassword(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email|exists:users,email',
                'new_password' => 'required|string|min:8|confirmed',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        }

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'User with this email not found'], 404);
        }

        // Update password in the users table
        $user->password = Hash::make($validatedData['new_password']);
        $user->save();

        // Optionally, update password in the pegawais table if it's explicitly stored there
        $pegawai = Pegawai::where('user_id', $user->id)->first();
        if ($pegawai) {
            $pegawai->password = $user->password; // Update with the new hashed password
            $pegawai->save();
        }

        return response()->json(['message' => 'Password has been reset successfully.']);
    }

}


