<?php

namespace App\Http\Controllers;

use App\Models\Diskusi;
use Illuminate\Http\Request;

class DiskusiController extends Controller
{
    public function index()
    {
        $diskusi = Diskusi::all();
        return response()->json($diskusi);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_barang' => 'required|integer',
            'id_user' => 'required|integer',
            'isi_diskusi' => 'required|string',
            'tanggal_diskusi' => 'required|date',
        ]);

        $diskusi = Diskusi::create($validatedData);

        return response()->json([
            'message' => 'Diskusi created successfully',
            'data' => $diskusi
        ], 201);
    }

    public function show($id)
    {
        $diskusi = Diskusi::find($id);

        if (!$diskusi) {
            return response()->json(['message' => 'Diskusi not found'], 404);
        }

        return response()->json($diskusi);
    }

    public function update(Request $request, $id)
    {
        $diskusi = Diskusi::find($id);

        if (!$diskusi) {
            return response()->json(['message' => 'Diskusi not found'], 404);
        }

        $validatedData = $request->validate([
            'id_barang' => 'sometimes|required|integer',
            'id_user' => 'sometimes|required|integer',
            'isi_diskusi' => 'sometimes|required|string',
            'tanggal_diskusi' => 'sometimes|required|date',
        ]);

        $diskusi->update($validatedData);

        return response()->json([
            'message' => 'Diskusi updated successfully',
            'data' => $diskusi
        ]);
    }

    public function destroy($id)
    {
        $diskusi = Diskusi::find($id);

        if (!$diskusi) {
            return response()->json(['message' => 'Diskusi not found'], 404);
        }

        $diskusi->delete();

        return response()->json(['message' => 'Diskusi deleted successfully']);
    }
}
