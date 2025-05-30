<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;

class TransaksiController 
{
    // Get all transaksis
    public function index()
    {
        $transaksis = Transaksi::with(['detailtransaksi', 'pengambilans', 'pengirimen'])->get();
        return response()->json($transaksis);
    }

    // Store a new transaksi
    public function store(Request $request)
{
    $validatedData = $request->validate([
        'id_pembeli' => 'required|exists:pembelis,id_pembeli',
        'id_penitip' => 'required|exists:penitips,id_penitip',
        'tgl_pesan' => 'required|date',
        'tgl_lunas' => 'nullable|date|after_or_equal:tgl_pesan',
        'diskon_poin' => 'nullable|numeric|min:0',
        'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        'status_pembayaran' => 'required|string|max:50',
    ]);

    // Jika ada file bukti_pembayaran, simpan file dan update data
    if ($request->hasFile('bukti_pembayaran')) {
        $file = $request->file('bukti_pembayaran');
        $filename = time() . '_' . $file->getClientOriginalName();
        // Simpan di storage/app/public/bukti_pembayaran
        $path = $file->storeAs('bukti_pembayaran', $filename, 'public');
        // Simpan path relatif ke database
        $validatedData['bukti_pembayaran'] = $path;
    }

    $transaksi = Transaksi::create($validatedData);

    return response()->json([
        'message' => 'Transaksi created successfully',
        'data' => $transaksi->load(['detailtransaksi', 'pengambilans', 'pengirimen'])
    ], 201);
}

    // Show a specific transaksi
    public function show($id)
    {
        $transaksi = Transaksi::with(['detailtransaksi', 'pengambilans', 'pengirimen'])->find($id);

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi not found'], 404);
        }

        return response()->json($transaksi);
    }

    // Update a specific transaksi
    public function update(Request $request, $id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi not found'], 404);
        }

        $validatedData = $request->validate([
            'id_pembeli' => 'sometimes|required|exists:pembelis,id_pembeli',
            'id_penjual' => 'sometimes|required|exists:penitips,id_penitip',
            'tgl_pesan' => 'sometimes|required|date',
            'tgl_lunas' => 'nullable|date|after_or_equal:tgl_pesan',
            'diskon_poin' => 'nullable|numeric|min:0',
            'bukti_pembayaran' => 'nullable|string|max:255',
            'status_pembayaran' => 'sometimes|required|string|max:50',
        ]);

        $transaksi->update($validatedData);

        return response()->json([
            'message' => 'Transaksi updated successfully',
            'data' => $transaksi->load(['detailtransaksi', 'pengambilans', 'pengirimen'])
        ]);
    }

    // Delete a specific transaksi
    public function destroy($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi not found'], 404);
        }

        $transaksi->delete();

        return response()->json(['message' => 'Transaksi deleted successfully']);
    }

    public function verifikasiBukti(Request $request, $id)
{
    $transaksi = Transaksi::find($id);

    if (!$transaksi) {
        return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
    }

    $validatedData = $request->validate([
        'status_bukti' => 'required|in:pending,valid,tidak valid',
    ]);

    $transaksi->status_bukti = $validatedData['status_bukti'];
    $transaksi->save();

    return response()->json([
        'message' => 'Status verifikasi bukti pembayaran berhasil diperbarui',
        'data' => $transaksi,
    ]);
}


}
