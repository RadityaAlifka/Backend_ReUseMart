<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Organisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class OrganisasiController 
{
    // Get all organisasi
    public function index()
    {
        $organisasis = Organisasi::all();
        return response()->json($organisasis);
    }

    // Store a new organisasi
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_organisasi' => 'required|string|max:255',
            'alamat' => 'required|string',
            'email' => 'required|email|unique:organisasis,email',
            'no_telp' => 'required|string|max:15',
            'password' => 'required|string|min:8',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        $organisasi = Organisasi::create($validatedData);

        return response()->json([
            'message' => 'Organisasi created successfully',
            'data' => $organisasi
        ], 201);
    }
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'nama_organisasi' => 'required|string|max:255',
            'alamat' => 'required|string',
            'email' => 'required|email|unique:users',
            'no_telp' => 'required|string|max:15',
            'password' => 'required|string|min:8',
        ]);

        // Buat akun pengguna
        $user = User::create([
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'level' => 'organisasi',
        ]);

        // Buat data organisasi terkait
        $organisasi = Organisasi::create([
            'user_id' => $user->id,
            'nama_organisasi' => $validatedData['nama_organisasi'],
            'email' => $validatedData['email'],
            'alamat' => $validatedData['alamat'],
            'no_telp' => $validatedData['no_telp'],
            'password' => $user->password,

        ]);

        return response()->json([
            'message' => 'Organisasi registered successfully',
            'user' => $user,
            'organisasi' => $organisasi,
        ], 201);
    }

    // Show a specific organisasi
    public function show($id)
    {
        $organisasi = Organisasi::find($id);

        if (!$organisasi) {
            return response()->json(['message' => 'Organisasi not found'], 404);
        }

        return response()->json($organisasi);
    }

    // Update a specific organisasi
    public function update(Request $request, $id)
{
    $organisasi = Organisasi::find($id);

    if (!$organisasi) {
        return response()->json(['message' => 'Organisasi not found'], 404);
    }

    $validatedData = $request->validate([
        'nama_organisasi' => 'sometimes|required|string|max:255',
        'alamat' => 'sometimes|required|string',
        'email' => 'sometimes|required|email|unique:organisasis,email,' . $id . ',id_organisasi',
        'no_telp' => 'sometimes|required|string|max:15',
        'password' => 'sometimes|required|string|min:8',
    ]);

    // Update email di tabel users jika berubah
    if (isset($validatedData['email'])) {
        $user = \App\Models\User::find($organisasi->user_id);
        if ($user) {
            $user->email = $validatedData['email'];
            $user->save();
        }
    }

    // Update password di tabel users jika berubah
    if (isset($validatedData['password'])) {
        $hashedPassword = \Hash::make($validatedData['password']);
        $validatedData['password'] = $hashedPassword;

        $user = \App\Models\User::find($organisasi->user_id);
        if ($user) {
            $user->password = $hashedPassword;
            $user->save();
        }
    }

    $organisasi->update($validatedData);

    return response()->json([
        'message' => 'Organisasi updated successfully',
        'data' => $organisasi
    ]);
}


    // Delete a specific organisasi
    public function destroy($id)
    {
        $organisasi = Organisasi::find($id);

        if (!$organisasi) {
            return response()->json(['message' => 'Organisasi not found'], 404);
        }

        $organisasi->user()->delete();

        $organisasi->delete();

        return response()->json(['message' => 'Organisasi deleted successfully']);
    }

}
