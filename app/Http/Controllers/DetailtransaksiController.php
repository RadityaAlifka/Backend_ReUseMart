<?php

namespace App\Http\Controllers;

use App\Models\Detailtransaksi;
use Illuminate\Http\Request;

class DetailtransaksiController extends Controller
{
    public function index()
    {
        $detailTransaksi = Detailtransaksi::all();
        return response()->json($detailTransaksi);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_transaksi' => 'required|integer',
            'id_barang' => 'required|integer',
            'jumlah' => 'required|integer',
            'subtotal' => 'required|numeric',
        ]);

        $detailTransaksi = Detailtransaksi::create($validatedData);

        return response()->json([
            'message' => 'Detail Transaksi created successfully',
            'data' => $detailTransaksi
        ], 201);
    }

    public function show($id)
    {
        $detailTransaksi = Detailtransaksi::find($id);

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
            'id_transaksi' => 'sometimes|required|integer',
            'id_barang' => 'sometimes|required|integer',
            'jumlah' => 'sometimes|required|integer',
            'subtotal' => 'sometimes|required|numeric',
        ]);

        $detailTransaksi->update($validatedData);

        return response()->json([
            'message' => 'Detail Transaksi updated successfully',
            'data' => $detailTransaksi
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
