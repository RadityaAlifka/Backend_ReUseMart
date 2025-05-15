<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\Penitipan;
use App\Models\Donasi;
use Illuminate\Http\Request;

class BarangController 
{
    public function index()
    {
        $barang = Barang::with(['kategori_barang', 'penitipan', 'donasi'])->get();
        return response()->json($barang);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_kategori' => 'required|exists:kategori_barangs,id_kategori',
            'id_penitipan' => 'required|exists:penitipans,id_penitipan',
            'id_donasi' => 'nullable|exists:donasis,id_donasi',
            'nama_barang' => 'required|string|max:255',
            'deskripsi_barang' => 'required|string',
            'garansi' => 'nullable|string|max:255',
            'tanggal_garansi' => 'nullable|date',
            'harga' => 'required|numeric',
            'status_barang' => 'required|string|max:50',
            'berat' => 'required|numeric',
            'tanggal_keluar' => 'nullable|date',
            'gambar1' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Validasi untuk gambar1
            'gambar2' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Validasi untuk gambar2
        ]);

        // Simpan gambar ke direktori
        $gambar1Path = $request->file('gambar1')->store('images/barang');
        $gambar2Path = $request->file('gambar2')->store('images/barang');

        // Tambahkan path gambar ke data yang divalidasi
        $validatedData['gambar1'] = $gambar1Path;
        $validatedData['gambar2'] = $gambar2Path;

        $barang = Barang::create($validatedData);

        return response()->json([
            'message' => 'Barang created successfully',
            'data' => $barang->load(['kategori_barang', 'penitipan', 'donasi'])
        ], 201);
    }

    public function show($id)
    {
        $barang = Barang::with(['kategori_barang', 'penitipan', 'donasi'])->find($id);

        if (!$barang) {
            return response()->json(['message' => 'Barang not found'], 404);
        }

        return response()->json($barang);
    }

    public function pengambilanBarang($id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json(['message' => 'Barang not found'], 404);
        }

        if ($barang->status_barang !== 'Masa Titip Habis') {
            return response()->json(['message' => 'Barang tidak dalam status Masa Titip Habis'], 400);
        }

        $barang->updateStatus('Diambil');

        return response()->json([
            'message' => 'Barang berhasil diambil',
            'data' => $barang
        ]);
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json(['message' => 'Barang not found'], 404);
        }

        $validatedData = $request->validate([
            'id_kategori' => 'sometimes|required|exists:kategori_barangs,id_kategori',
            'id_penitipan' => 'sometimes|required|exists:penitipans,id_penitipan',
            'id_donasi' => 'nullable|exists:donasis,id_donasi',
            'nama_barang' => 'sometimes|required|string|max:255',
            'deskripsi_barang' => 'sometimes|required|string',
            'garansi' => 'nullable|string|max:255',
            'tanggal_garansi' => 'nullable|date',
            'harga' => 'sometimes|required|numeric',
            'status_barang' => 'sometimes|required|string|max:50',
            'berat' => 'sometimes|required|numeric',
            'tanggal_keluar' => 'nullable|date',
            'gambar1' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048', // Validasi untuk gambar1
            'gambar2' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048', // Validasi untuk gambar2
        ]);

        // Perbarui gambar jika ada file baru
        if ($request->hasFile('gambar1')) {
            $validatedData['gambar1'] = $request->file('gambar1')->store('images/barang');
        }

        if ($request->hasFile('gambar2')) {
            $validatedData['gambar2'] = $request->file('gambar2')->store('images/barang');
        }

        $barang->update($validatedData);

        return response()->json([
            'message' => 'Barang updated successfully',
            'data' => $barang->load(['kategori_barang', 'penitipan', 'donasi'])
        ]);
    }
    public function barangBergaransi()
    {
        $barang = Barang::with(['kategori_barang', 'penitipan', 'donasi'])
            ->where('tanggal_garansi', '>=', now()) // Filter barang dengan garansi yang masih berlaku
            ->get();

        return response()->json([
            'message' => 'Barang dengan garansi yang masih berlaku',
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

    public function barangMenungguDonasi()
{
    $barang = Barang::with(['kategori_barang', 'penitipan', 'donasi'])
        ->where('status_barang', 'Menunggu Donasi')
        ->get();

    return response()->json([
        'message' => 'Barang dengan status Menunggu Donasi',
        'data' => $barang
    ]);
}

}