<?php

namespace App\Http\Controllers;

use App\Models\Detailtransaksi;
use Illuminate\Http\Request;

class DetailtransaksiController 
{
    public function index()
    {
        // Include related models using eager loading
        $detailTransaksi = Detailtransaksi::with(['barang', 'transaksi'])->get();
        return response()->json($detailTransaksi);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_transaksi' => 'required|exists:transaksis,id_transaksi',
            'id_barang' => 'required|exists:barangs,id_barang',
            'jumlah' => 'required|integer',
            'subtotal' => 'required|numeric',
        ]);

        $detailTransaksi = Detailtransaksi::create( $validatedData);

        return response()->json([
            'message' => 'Detail Transaksi created successfully',
            'data' => $detailTransaksi->load(['barang', 'transaksi'])
        ], 201);
    }

    public function show($id)
    {
        $detailTransaksi = Detailtransaksi::with(['barang', 'transaksi'])->find($id);

        if (!$detailTransaksi) {
            return response()->json(['message' => 'Detail Transaksi not found'], 404);
        }

        return response()->json($detailTransaksi);
    }

    public function update(Request $request, $id)
    {
        $detailTransaksi = Detailtransaksi::find($id);

        if (!$detailTransaksi) {
            return response()->json(['message' => 'Detail Transaksi not found'], 404);
        }

        $validatedData = $request->validate([
            'id_transaksi' => 'sometimes|required|exists:transaksis,id_transaksi',
            'id_barang' => 'sometimes|required|exists:barangs,id_barang',
            'jumlah' => 'sometimes|required|integer',
            'subtotal' => 'sometimes|required|numeric',
        ]);

        $detailTransaksi->update($validatedData);

        return response()->json([
            'message' => 'Detail Transaksi updated successfully',
            'data' => $detailTransaksi->load(['barang', 'transaksi'])
        ]);
    }

    public function destroy($id)
    {
        $detailTransaksi = Detailtransaksi::find($id);

        if (!$detailTransaksi) {
            return response()->json(['message' => 'Detail Transaksi not found'], 404);
        }

        $detailTransaksi->delete();

        return response()->json(['message' => 'Detail Transaksi deleted successfully']);
    }
}
