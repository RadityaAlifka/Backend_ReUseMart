<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index()
    {
        $barang = Barang::all();
        return response()->json($barang);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_kategori' => 'required|integer',
            'id_penitipan' => 'required|integer',
            'id_donasi' => 'nullable|integer',
            'nama_barang' => 'required|string|max:255',
            'deskripsi_barang' => 'required|string',
            'garansi' => 'nullable|string|max:255',
            'tanggal_garansi' => 'nullable|date',
            'harga' => 'required|numeric',
            'status_barang' => 'required|string|max:50',
            'berat' => 'required|numeric',
            'tanggal_keluar' => 'nullable|date',
        ]);

        $barang = Barang::create($validatedData);

        return response()->json([
            'message' => 'Barang created successfully',
            'data' => $barang
        ], 201);
    }

    public function show($id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json(['message' => 'Barang not found'], 404);
        }

        return response()->json($barang);
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json(['message' => 'Barang not found'], 404);
        }

        $validatedData = $request->validate([
            'id_kategori' => 'sometimes|required|integer',
            'id_penitipan' => 'sometimes|required|integer',
            'id_donasi' => 'nullable|integer',
            'nama_barang' => 'sometimes|required|string|max:255',
            'deskripsi_barang' => 'sometimes|required|string',
            'garansi' => 'nullable|string|max:255',
            'tanggal_garansi' => 'nullable|date',
            'harga' => 'sometimes|required|numeric',
            'status_barang' => 'sometimes|required|string|max:50',
            'berat' => 'sometimes|required|numeric',
            'tanggal_keluar' => 'nullable|date',
        ]);

        $barang->update($validatedData);

        return response()->json([
            'message' => 'Barang updated successfully',
            'data' => $barang
        ]);
    }

    public function destroy($id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json(['message' => 'Barang not found'], 404);
        }

        $barang->delete();

        return response()->json(['message' => 'Barang deleted successfully']);
    }
}
