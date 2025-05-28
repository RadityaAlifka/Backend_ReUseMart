<?php

namespace App\Http\Controllers;

use App\Models\Pengambilan;
use Illuminate\Http\Request;

class PengambilanController 
{
    // Get all pengambilans
    public function index()
    {
        $pengambilans = Pengambilan::with(['pembeli', 'penitip', 'transaksi'])->get();
        return response()->json($pengambilans);
    }

    // Store a new pengambilan
   public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
            'id_transaksi' => 'required|exists:transaksis,id_transaksi',
            'id_penitip' => 'required|exists:penitips,id_penitip',
            'id_pembeli' => 'required|exists:pembelis,id_pembeli',
            'tanggal_pengambilan' => 'required|date',
            'batas_pengambilan' => 'required|date|after_or_equal:tanggal_pengambilan',
            'status_pengambilan' => 'required|string|max:50',
        ]);

        $pengambilan = Pengambilan::create($validatedData);

        return response()->json([
            'message' => 'Pengambilan created successfully',
            'data' => $pengambilan->load(['pembeli', 'penitip', 'transaksi'])
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal menyimpan pengambilan',
            'error' => $e->getMessage()
        ], 500);
    }
}


    // Show a specific pengambilan
    public function show($id)
    {
        $pengambilan = Pengambilan::with(['pembeli', 'penitip', 'transaksi'])->find($id);

        if (!$pengambilan) {
            return response()->json(['message' => 'Pengambilan not found'], 404);
        }

        return response()->json($pengambilan);
    }

    // Update a specific pengambilan
    public function update(Request $request, $id)
    {
        $pengambilan = Pengambilan::find($id);

        if (!$pengambilan) {
            return response()->json(['message' => 'Pengambilan not found'], 404);
        }

        $validatedData = $request->validate([
            'id_transaksi' => 'sometimes|required|exists:transaksis,id_transaksi',
            'id_penitip' => 'sometimes|required|exists:penitips,id_penitip',
            'id_pembeli' => 'sometimes|required|exists:pembelis,id_pembeli',
            'tanggal_pengambilan' => 'sometimes|required|date',
            'batas_pengambilan' => 'sometimes|required|date|after_or_equal:tanggal_pengambilan',
            'status_pengambilan' => 'sometimes|required|string|max:50',
        ]);

        $pengambilan->update($validatedData);

        return response()->json([
            'message' => 'Pengambilan updated successfully',
            'data' => $pengambilan->load(['pembeli', 'penitip', 'transaksi'])
        ]);
    }

    // Delete a specific pengambilan
    public function destroy($id)
    {
        $pengambilan = Pengambilan::find($id);

        if (!$pengambilan) {
            return response()->json(['message' => 'Pengambilan not found'], 404);
        }

        $pengambilan->delete();

        return response()->json(['message' => 'Pengambilan deleted successfully']);
    }
}
