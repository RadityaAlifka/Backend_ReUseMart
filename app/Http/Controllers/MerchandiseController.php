<?php

namespace App\Http\Controllers;

use App\Models\Merchandise;
use Illuminate\Http\Request;

class MerchandiseController 
{
    public function index()
    {
        $merchandises = Merchandise::with(['pegawai', 'pembeli'])->get();
        return response()->json($merchandises);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_pembeli' => 'required|exists:pembelis,id_pembeli',
            'id_pegawai' => 'required|exists:pegawais,id_pegawai',
            'nama_merchandise' => 'required|string|max:255',
            'stock_merchandise' => 'required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $merchandise = Merchandise::create($validatedData);

        return response()->json([
            'message' => 'Merchandise created successfully',
            'data' => $merchandise->load(['pegawai', 'pembeli'])
        ], 201);
    }

    public function show($id)
    {
        $merchandise = Merchandise::with(['pegawai', 'pembeli'])->find($id);

        if (!$merchandise) {
            return response()->json(['message' => 'Merchandise not found'], 404);
        }

        return response()->json($merchandise);
    }

    public function update(Request $request, $id)
    {
        $merchandise = Merchandise::find($id);

        if (!$merchandise) {
            return response()->json(['message' => 'Merchandise not found'], 404);
        }

        $validatedData = $request->validate([
            'id_pembeli' => 'sometimes|required|exists:pembelis,id_pembeli',
            'id_pegawai' => 'sometimes|required|exists:pegawais,id_pegawai',
            'nama_merchandise' => 'sometimes|required|string|max:255',
            'stock_merchandise' => 'sometimes|required|integer|min:0',
        ]);

        $merchandise->update($validatedData);

        return response()->json([
            'message' => 'Merchandise updated successfully',
            'data' => $merchandise->load(['pegawai', 'pembeli'])
        ]);
    }

    public function destroy($id)
    {
        $merchandise = Merchandise::find($id);

        if (!$merchandise) {
            return response()->json(['message' => 'Merchandise not found'], 404);
        }

        $merchandise->delete();

        return response()->json(['message' => 'Merchandise deleted successfully']);
    }
}
