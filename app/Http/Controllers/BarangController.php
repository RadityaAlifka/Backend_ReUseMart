<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\Pegawai;
use App\Models\Penitipan;
use App\Models\Donasi;
use Illuminate\Http\Request;

class BarangController 
{
    public function index()
    {
        // Ambil semua barang dengan status 'tersedia'
        $barang = Barang::where('status_barang', 'Tersedia')->get();

        // Cek apakah data ditemukan
        if ($barang->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada barang yang tersedia saat ini.'
            ], 404);
        }

        // Jika ada data, kembalikan dengan response JSON
        return response()->json([
            'message' => 'Daftar barang tersedia',
            'data' => $barang
        ], 200);
    }

    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'id_kategori'      => 'required|exists:kategori_barangs,id_kategori',
            'id_penitip'       => 'required|exists:penitips,id_penitip',
            'id_pegawai'       => 'required|exists:pegawais,id_pegawai',
            'nama_barang'      => 'required|string|max:255',
            'deskripsi_barang' => 'required|string',
            'garansi'          => 'nullable|string|max:255',
            'tanggal_garansi'  => 'nullable|date',
            'harga'            => 'required|numeric|min:0',
            'status_barang'    => 'required|string|max:50',
            'berat'            => 'required|numeric|min:0',
            'gambar1'          => 'required|file|image|mimes:jpeg,png,jpg|max:2048',
            'gambar2'          => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Buat penitipan otomatis
        $now = Carbon::now();
        $penitipan = Penitipan::create([
            'id_penitip'         => $validatedData['id_penitip'],
            'id_pegawai'        => $validatedData['id_pegawai'],
            'tanggal_penitipan'  => $now,
            'batas_penitipan'    => $now->copy()->addDays(30),
        ]);

        // Proses upload gambar1
        if ($request->hasFile('gambar1')) {
            $gambar1Path = $request->file('gambar1')->store('barang', 'public');
            $validatedData['gambar1'] = $gambar1Path;
        }

        // Proses upload gambar2 (jika ada)
        if ($request->hasFile('gambar2')) {
            $gambar2Path = $request->file('gambar2')->store('barang', 'public');
            $validatedData['gambar2'] = $gambar2Path;
        } else {
            $validatedData['gambar2'] = null;
        }

        // Set id_penitipan hasil create penitipan
        $validatedData['id_penitipan'] = $penitipan->id_penitipan;

        // Simpan data barang
        $barang = Barang::create([
            'id_kategori'      => $validatedData['id_kategori'],
            'id_penitipan'     => $validatedData['id_penitipan'],
            'id_donasi'        => $validatedData['id_donasi'] ?? null,
            'nama_barang'      => $validatedData['nama_barang'],
            'deskripsi_barang' => $validatedData['deskripsi_barang'],
            'garansi'          => $validatedData['garansi'],
            'tanggal_garansi'  => $validatedData['tanggal_garansi'],
            'harga'            => $validatedData['harga'],
            'status_barang'    => $validatedData['status_barang'],
            'berat'            => $validatedData['berat'],
            'tanggal_keluar'   => null,
            'gambar1'          => $gambar1Path,
            'gambar2'          => $gambar2Path,
        ]);

        return response()->json([
            'message' => 'Barang berhasil dititipkan',
            'data'    => $barang->fresh()
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
        ->where('status_barang', 'Tersedia')
        ->whereDate('tanggal_garansi', '>=', now()->toDateString())
        ->get();


        if ($barang->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada barang dengan garansi yang masih berlaku',
                'data' => []
            ], 404);
        }

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