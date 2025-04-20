<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use Illuminate\Http\Request;

class DonasiController extends Controller
{
    public function index()
    {
        $donasi = Donasi::all();
        return response()->json($donasi);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_user' => 'required|integer',
            'tanggal_donasi' => 'required|date',
            'total_donasi' => 'required|numeric',
        ]);

        $donasi = Donasi::create($validatedData);

        return response()->json([
            'message' => 'Donasi created successfully',
            'data' => $donasi
        ], 201);
    }

    public function show($id)
    {
        $donasi = Donasi::find($id);

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
            'id_user' => 'sometimes|required|integer',
            'tanggal_donasi' => 'sometimes|required|date',
            'total_donasi' => 'sometimes|required|numeric',
        ]);

        $donasi->update($validatedData);

        return response()->json([
            'message' => 'Donasi updated successfully',
            'data' => $donasi
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
