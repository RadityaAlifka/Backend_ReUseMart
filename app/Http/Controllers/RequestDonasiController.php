<?php

namespace App\Http\Controllers;

use App\Models\RequestDonasi;
use Illuminate\Http\Request;

class RequestDonasiController 
{
    // Get all request donasis
    public function index()
    {
        $requestDonasis = RequestDonasi::with(['organisasi', 'pegawai'])->get();
        return response()->json($requestDonasis);
    }

    // Store a new request donasi
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_organisasi' => 'required|exists:organisasis,id_organisasi',
            'tanggal_request' => 'required|date',
            'detail_request' => 'required|string',
        ]);

        $requestDonasi = RequestDonasi::create($validatedData);

        return response()->json([
            'message' => 'Request Donasi created successfully',
            'data' => $requestDonasi->load(['organisasi', 'pegawai'])
        ], 201);
    }

    // Show a specific request donasi
    public function show($id)
    {
        $requestDonasi = RequestDonasi::with(['organisasi', 'pegawai'])->find($id);

        if (!$requestDonasi) {
            return response()->json(['message' => 'Request Donasi not found'], 404);
        }

        return response()->json($requestDonasi);
    }

    // Update a specific request donasi
    public function update(Request $request, $id)
    {
        $requestDonasi = RequestDonasi::find($id);

        if (!$requestDonasi) {
            return response()->json(['message' => 'Request Donasi not found'], 404);
        }

        $validatedData = $request->validate([
            'id_organisasi' => 'sometimes|required|exists:organisasis,id_organisasi',
            'tanggal_request' => 'sometimes|required|date',
            'detail_request' => 'sometimes|required|string',
        ]);

        $requestDonasi->update($validatedData);

        return response()->json([
            'message' => 'Request Donasi updated successfully',
            'data' => $requestDonasi->load(['organisasi', 'pegawai'])
        ]);
    }

    // Delete a specific request donasi
    public function destroy($id)
    {
        $requestDonasi = RequestDonasi::find($id);

        if (!$requestDonasi) {
            return response()->json(['message' => 'Request Donasi not found'], 404);
        }

        $requestDonasi->delete();

        return response()->json(['message' => 'Request Donasi deleted successfully']);
    }
}
