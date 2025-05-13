<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PegawaiController extends Controller
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

        $pegawai->delete();

        return response()->json(['message' => 'Pegawai deleted successfully']);
    }
}
