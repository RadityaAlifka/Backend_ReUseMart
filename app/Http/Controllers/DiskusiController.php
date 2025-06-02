<?php

namespace App\Http\Controllers;

use App\Models\Diskusi;
use Illuminate\Http\Request;

class DiskusiController 
{
    public function index()
    {
        $diskusi = Diskusi::with(['barang', 'pembeli', 'pegawai'])->get();
        return response()->json($diskusi);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_barang' => 'required|exists:barangs,id_barang',
            'id_pembeli' => 'required|exists:pembelis,id_pembeli',
            'id_pegawai' => 'nullable|exists:pegawais,id_pegawai',
            'detail_diskusi' => 'required|string',
            'reply' => 'nullable|string',
        ]);

        $diskusi = Diskusi::create($validatedData);

        return response()->json([
            'message' => 'Diskusi created successfully',
            'data' => $diskusi->load(['barang', 'pembeli', 'pegawai'])
        ], 201);
    }

    public function show($id)
    {
        $diskusi = Diskusi::with(['barang', 'pembeli', 'pegawai'])->find($id);

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

    // Validasi token jika perlu
    if ($request->token !== $diskusi->token) {
        return response()->json(['message' => 'Token mismatch'], 403);
    }

    // Tambahkan log input request
    \Log::info('Headers:', $request->headers->all());
    
    \Log::info('All Input:', $request->all());
    \Log::info('Update Diskusi Request:', $request->all());

    $validatedData = $request->validate([
        'id_barang' => 'sometimes|required|exists:barangs,id_barang',
        'id_pembeli' => 'sometimes|required|exists:pembelis,id_pembeli',
        'id_pegawai' => 'nullable|exists:pegawais,id_pegawai',
        'detail_diskusi' => 'sometimes|required|string',
        'reply' => 'nullable|string',
    ]);
    

    $diskusi->update($validatedData);

    return response()->json([
        'message' => 'Diskusi updated successfully',
        'data' => $diskusi->load(['barang', 'pembeli', 'pegawai'])
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
