<?php

namespace App\Http\Controllers;

use App\Models\Penitipan;
use Illuminate\Http\Request;

class PenitipanController 
{
    // Get all penitipans
    public function index()
    {
        $penitipans = Penitipan::with('penitip')->get();
        return response()->json($penitipans);
    }

    public function extendPenitipan($id)
    {
        $penitipan = Penitipan::find($id);

        if (!$penitipan) {
            return response()->json(['message' => 'Penitipan not found'], 404);
        }

        if ($penitipan->perpanjangan) {
            return response()->json(['message' => 'Masa titipan sudah diperpanjang sebelumnya'], 400);
        }

        $penitipan->batas_penitipan = $penitipan->batas_penitipan->addDays(30);
        $penitipan->perpanjangan = true;
        $penitipan->save();

        return response()->json([
            'message' => 'Masa titipan berhasil diperpanjang',
            'data' => $penitipan
        ]);
    }
    // Store a new penitipan
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_penitip' => 'required|exists:penitips,id_penitip',
            'id_pegawai' => 'required|exists:pegawais,id_pegawai',
            'tanggal_penitipan' => 'required|date',
            'batas_penitipan' => 'required|date|after_or_equal:tanggal_penitipan',
        ]);

        $penitipan = Penitipan::create($validatedData);

        return response()->json([
            'message' => 'Penitipan created successfully',
            'data' => $penitipan->load('penitip')
        ], 201);
    }

    // Show a specific penitipan
    public function show($id)
    {
        $penitipan = Penitipan::with('penitip')->find($id);

        if (!$penitipan) {
            return response()->json(['message' => 'Penitipan not found'], 404);
        }

        return response()->json($penitipan);
    }

    // Update a specific penitipan
    public function update(Request $request, $id)
    {
        $penitipan = Penitipan::find($id);

        if (!$penitipan) {
            return response()->json(['message' => 'Penitipan not found'], 404);
        }

        $validatedData = $request->validate([
            'id_penitip' => 'sometimes|required|exists:penitips,id_penitip',
            'id_pegawai' => 'sometimes|required|exists:pegawais,id_pegawai',
            'tanggal_penitipan' => 'sometimes|required|date',
            'batas_penitipan' => 'sometimes|required|date|after_or_equal:tanggal_penitipan',
        ]);

        $penitipan->update($validatedData);

        return response()->json([
            'message' => 'Penitipan updated successfully',
            'data' => $penitipan->load('penitip')
        ]);
    }

    // Delete a specific penitipan
    public function destroy($id)
    {
        $penitipan = Penitipan::find($id);

        if (!$penitipan) {
            return response()->json(['message' => 'Penitipan not found'], 404);
        }

        $penitipan->delete();

        return response()->json(['message' => 'Penitipan deleted successfully']);
    }

    public function getIdPenitip($id)
{
    $penitipan = Penitipan::find($id);

    if (!$penitipan) {
        return response()->json(['message' => 'Penitipan not found'], 404);
    }

    // Ambil id_penitip dari penitipan
    $id_penitip = $penitipan->id_penitip;

    return response()->json([
        'id_penitip' => $id_penitip,
    ]);
}


}
