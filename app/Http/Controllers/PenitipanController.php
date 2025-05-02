<?php

namespace App\Http\Controllers;

use App\Models\Penitipan;
use Illuminate\Http\Request;

class PenitipanController extends Controller
{
    // Get all penitipans
    public function index()
    {
        $penitipans = Penitipan::with('penitip')->get();
        return response()->json($penitipans);
    }

    // Store a new penitipan
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_penitip' => 'required|exists:penitips,id_penitip',
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
}
