<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Http\Controllers\NotificationController;

class PengirimanController 
{
    protected $notificationController;

    public function __construct(NotificationController $notificationController)
    {
        $this->notificationController = $notificationController;
    }

    // Get all pengirimans
    public function index()
    {
        $pengirimans = Pengiriman::with(['pegawai', 'transaksi'])->get();
        return response()->json($pengirimans);
    }

    // Store a new pengiriman
   public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_transaksi' => 'required|exists:transaksis,id_transaksi',
            'id_pegawai' => 'required|exists:pegawais,id_pegawai',
            'id_alamat' => 'required|exists:alamats,id_alamat', // Tambah validasi id_alamat
            'tanggal_pengiriman' => 'required|date',
            'status_pengiriman' => 'required|string|max:50',
            'ongkir' => 'required|numeric|min:0',
        ]);

        $pengiriman = Pengiriman::create($validatedData);

        return response()->json([
            'message' => 'Pengiriman created successfully',
            'data' => $pengiriman->load(['pegawai', 'transaksi'])
        ], 201);
    }

    // Show a specific pengiriman
    public function show($id)
    {
        $pengiriman = Pengiriman::with(['pegawai', 'transaksi'])->find($id);

        if (!$pengiriman) {
            return response()->json(['message' => 'Pengiriman not found'], 404);
        }

        return response()->json($pengiriman);
    }

    // Update a specific pengiriman
   public function update(Request $request, $id)
    {
        $pengiriman = Pengiriman::find($id);

        if (!$pengiriman) {
            return response()->json(['message' => 'Pengiriman not found'], 404);
        }

        $validatedData = $request->validate([
            'id_transaksi' => 'sometimes|required|exists:transaksis,id_transaksi',
            'id_pegawai' => 'sometimes|required|exists:pegawais,id_pegawai',
            'id_alamat' => 'sometimes|required|exists:alamats,id_alamat', // Tambah validasi id_alamat
            'tanggal_pengiriman' => 'sometimes|required|date',
            'status_pengiriman' => 'sometimes|required|string|max:50',
            'ongkir' => 'sometimes|required|numeric|min:0',
        ]);

        $pengiriman->update($validatedData);

        return response()->json([
            'message' => 'Pengiriman updated successfully',
            'data' => $pengiriman->load(['pegawai', 'transaksi'])
        ]);
    }

    // Delete a specific pengiriman
    public function destroy($id)
    {
        $pengiriman = Pengiriman::find($id);

        if (!$pengiriman) {
            return response()->json(['message' => 'Pengiriman not found'], 404);
        }

        $pengiriman->delete();

        return response()->json(['message' => 'Pengiriman deleted successfully']);
    }
    public function editPengiriman(Request $request, $id)
    {
        $pengiriman = Pengiriman::with([
            'pegawai', 
            'transaksi.pembeli',
            'transaksi.detailtransaksi.barang.penitipan.penitip'
        ])->find($id);

        if (!$pengiriman) {
            return response()->json(['message' => 'Pengiriman not found'], 404);
        }

        $validatedData = $request->validate([
            'id_pegawai' => 'sometimes|required|exists:pegawais,id_pegawai',
            'tanggal_pengiriman' => 'sometimes|required|date',
            'status_pengiriman' => 'sometimes|required|string|max:50',
        ]);

        $pengiriman->update($validatedData);

        // Kirim notifikasi jika ada perubahan tanggal atau status pengiriman
        if (isset($validatedData['tanggal_pengiriman']) || isset($validatedData['status_pengiriman'])) {
            $notificationSent = $this->notificationController->sendDeliveryScheduleNotification($pengiriman);
            
            if (!$notificationSent) {
                \Log::warning('Failed to send delivery schedule notifications for pengiriman ID: ' . $id);
            }
        }

        return response()->json([
            'message' => 'Pengiriman updated successfully',
            'data' => $pengiriman->load(['pegawai', 'transaksi'])
        ]);
    }
}
