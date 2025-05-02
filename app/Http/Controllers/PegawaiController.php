<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PegawaiController extends Controller
{
    // Get all pegawai
    public function index()
    {
        $pegawais = Pegawai::with('jabatan')->get();
        return response()->json($pegawais);
    }

    // Store a new pegawai
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_jabatan' => 'required|exists:jabatans,id_jabatan',
            'nama_pegawai' => 'required|string|max:255',
            'email' => 'required|email|unique:pegawais,email',
            'no_telp' => 'required|string|max:15',
            'password' => 'required|string|min:8',
            'komisi' => 'required|numeric|min:0',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        $pegawai = Pegawai::create($validatedData);

        return response()->json([
            'message' => 'Pegawai created successfully',
            'data' => $pegawai->load('jabatan')
        ], 201);
    }

    // Show a specific pegawai
    public function show($id)
    {
        $pegawai = Pegawai::with('jabatan')->find($id);

        if (!$pegawai) {
            return response()->json(['message' => 'Pegawai not found'], 404);
        }

        return response()->json($pegawai);
    }

    // Update a specific pegawai
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

        return response()->json([
            'message' => 'Pegawai updated successfully',
            'data' => $pegawai->load('jabatan')
        ]);
    }

    // Delete a specific pegawai
    public function destroy($id)
    {
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response()->json(['message' => 'Pegawai not found'], 404);
        }

        $pegawai->delete();

        return response()->json(['message' => 'Pegawai deleted successfully']);
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $pegawai = Pegawai::where('email', $validatedData['email'])->first();

        if (!$pegawai || !Hash::check($validatedData['password'], $pegawai->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $pegawai->createToken('pegawai-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'pegawai' => $pegawai
        ]);
    }

    // Logout for pegawai
    public function logout(Request $request)
    {
        // Revoke all tokens for the authenticated pegawai
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}
