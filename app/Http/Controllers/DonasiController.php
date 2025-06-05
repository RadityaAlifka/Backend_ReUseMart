<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use App\Models\Barang;
use Illuminate\Http\Request;
use App\Http\Controllers\NotificationController;

class DonasiController  
{
    protected $notificationController;

    public function __construct(NotificationController $notificationController)
    {
        $this->notificationController = $notificationController;
    }

    public function index()
    {
        $donasi = Donasi::with('organisasi')->get();
        return response()->json($donasi);
    }
    
    public function donasikanBarang(Request $request, $id)
    {   
        
        $barang = Barang::find($id);
        
        if (!$barang) {
            return response()->json(['message' => 'Barang not found'], 404);
        }
    
        if (!in_array($barang->status_barang, ['Masa Titip Habis', 'Menunggu Donasi'])) {
            return response()->json(['message' => 'Barang tidak dapat didonasikan'], 400);
        }
    
        $validatedData = $request->validate([
            'id_organisasi' => 'required|exists:organisasis,id_organisasi',
            'nama_penerima' => 'required|string|max:255',
        ]);
    
        // Buat entri baru di tabel donasi
        $donasi = Donasi::create([
            'id_organisasi' => $validatedData['id_organisasi'],
            'tanggal_donasi' => now(),
            'nama_penerima' => $validatedData['nama_penerima'],
        ]);
    
        // Perbarui barang dengan id_donasi yang baru dibuat
        $barang->update([
            'status_barang' => 'Didonasikan',
            'id_donasi' => $donasi->id_donasi,
        ]);
    
        // Berikan poin kepada pemilik barang
        $penitip = $barang->penitipan->penitip; // Ambil pemilik barang melalui relasi penitipan
        if ($penitip) {
            $poin = floor($barang->harga / 10000); // Hitung poin berdasarkan harga barang
            $penitip->increment('poin', $poin); // Tambahkan poin ke penitip
            
            // Kirim notifikasi ke penitip
            $this->notificationController->sendDonasiNotification($penitip->id_penitip);
        }
    
        return response()->json([
            'message' => 'Barang berhasil didonasikan dan poin diberikan kepada pemilik barang',
            'data' => $barang->load('donasi.organisasi'), // Memuat relasi donasi dan organisasi
        ]);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_organisasi' => 'required|exists:organisasis,id_organisasi',
            'tanggal_donasi' => 'required|date',
            'nama_penerima' => 'required|string|max:255',
        ]);

        $donasi = Donasi::create($validatedData);

        return response()->json([
            'message' => 'Donasi created successfully',
            'data' => $donasi->load('organisasi')
        ], 201);
    }

    public function show($id)
    {
        $donasi = Donasi::with('organisasi')->find($id);

        if (!$donasi) {
            return response()->json(['message' => 'Donasi not found'], 404);
        }

        return response()->json($donasi);
    }

    public function update(Request $request, $id)
    {
        $donasi = Donasi::find($id);

        if (!$donasi) {
            return response()->json(['message' => 'Donasi not found'], 404);
        }

        $validatedData = $request->validate([
            'id_organisasi' => 'sometimes|required|exists:organisasis,id_organisasi',
            'tanggal_donasi' => 'sometimes|required|date',
            'nama_penerima' => 'sometimes|required|string|max:255',
        ]);

        $donasi->update($validatedData);

        return response()->json([
            'message' => 'Donasi updated successfully',
            'data' => $donasi->load('organisasi')
        ]);
    }

    public function destroy($id)
    {
        $donasi = Donasi::find($id);

        if (!$donasi) {
            return response()->json(['message' => 'Donasi not found'], 404);
        }

        $donasi->delete();

        return response()->json(['message' => 'Donasi deleted successfully']);
    }
}
