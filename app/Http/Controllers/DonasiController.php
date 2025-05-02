<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use Illuminate\Http\Request;

class DonasiController extends Controller
{
    public function index()
    {
        $donasi = Donasi::with('organisasi')->get();
        return response()->json($donasi);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_organisasi' => 'required|exists:organisasis,id_organisasi',
            'tanggal_donasi' => 'required|date',
            'nama_penerima' => 'required|string|max:255',
        ]);

        $donasi = Donasi::create($validatedData);

        return response()->json([
            'message' => 'Donasi created successfully',
            'data' => $donasi->load('organisasi')
        ], 201);
    }

    public function show($id)
    {
        $donasi = Donasi::with('organisasi')->find($id);

        if (!$donasi) {
            return response()->json(['message' => 'Donasi not found'], 404);
        }

        return response()->json($donasi);
    }

    public function update(Request $request, $id)
    {
        $donasi = Donasi::find($id);

        if (!$donasi) {
            return response()->json(['message' => 'Donasi not found'], 404);
        }

        $validatedData = $request->validate([
            'id_organisasi' => 'sometimes|required|exists:organisasis,id_organisasi',
            'tanggal_donasi' => 'sometimes|required|date',
            'nama_penerima' => 'sometimes|required|string|max:255',
        ]);

        $donasi->update($validatedData);

        return response()->json([
            'message' => 'Donasi updated successfully',
            'data' => $donasi->load('organisasi')
        ]);
    }

    public function destroy($id)
    {
        $donasi = Donasi::find($id);

        if (!$donasi) {
            return response()->json(['message' => 'Donasi not found'], 404);
        }

        $donasi->delete();

        return response()->json(['message' => 'Donasi deleted successfully']);
    }
}
