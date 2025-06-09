<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Http\Controllers\NotificationController; // Make sure this import is present
use Illuminate\Database\Eloquent\Collection;


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
            'id_alamat' => 'required|exists:alamats,id_alamat',
            'tanggal_pengiriman' => 'required|date',
            'status_pengiriman' => 'required|string|max:50',
            'ongkir' => 'required|numeric|min:0',
        ]);

        $pengiriman = Pengiriman::create($validatedData);

        // Load necessary relations for notification if status is 'dijadwalkan' at creation
        if (strtolower($pengiriman->status_pengiriman) === 'dijadwalkan') {
            $this->triggerOnTheWayNotification($pengiriman->fresh()); // Use fresh() to ensure relations are loaded after creation
        }

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

        $oldStatus = $pengiriman->status_pengiriman;

        $validatedData = $request->validate([
            'id_transaksi' => 'sometimes|required|exists:transaksis,id_transaksi',
            'id_pegawai' => 'sometimes|required|exists:pegawais,id_pegawai',
            'id_alamat' => 'sometimes|required|exists:alamats,id_alamat',
            'tanggal_pengiriman' => 'sometimes|required|date',
            'status_pengiriman' => 'sometimes|required|string|max:50',
            'ongkir' => 'sometimes|required|numeric|min:0',
        ]);

        $pengiriman->update($validatedData);

        // Check if status changed to 'dijadwalkan' and trigger notification
        if (isset($validatedData['status_pengiriman']) &&
            strtolower($validatedData['status_pengiriman']) === 'dijadwalkan' &&
            strtolower($oldStatus) !== 'dijadwalkan') {
            // Load necessary relations before sending notification
            $this->triggerOnTheWayNotification($pengiriman->fresh());
        }

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

        $oldStatus = $pengiriman->status_pengiriman; // Capture old status for comparison

        $validatedData = $request->validate([
            'id_pegawai' => 'sometimes|required|exists:pegawais,id_pegawai',
            'tanggal_pengiriman' => 'sometimes|required|date',
            'status_pengiriman' => 'sometimes|required|string|max:50',
        ]);

        $pengiriman->update($validatedData);

        // Reload the 'pegawai' relation if 'id_pegawai' was updated
        if (isset($validatedData['id_pegawai'])) {
            $pengiriman->load('pegawai');
        }

        // Kirim notifikasi jika status_pengiriman berubah menjadi 'dijadwalkan'
        // atau jika ada perubahan tanggal pengiriman (sesuai sendDeliveryScheduleNotification)
        if (isset($validatedData['status_pengiriman']) &&
            strtolower($validatedData['status_pengiriman']) === 'dijadwalkan' &&
            strtolower($oldStatus) !== 'dijadwalkan') {
            $this->triggerOnTheWayNotification($pengiriman->fresh());
        }
        // Also keep the existing delivery schedule notification if it's based on date changes
        else if (isset($validatedData['tanggal_pengiriman'])) {
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

    // This method already exists in your provided code and is correct.
    // It should be used to handle status updates specifically for 'dijadwalkan'.
    public function updateDeliveryStatus(Request $request, $id_pengiriman)
    {
        $pengiriman = Pengiriman::with(['transaksi.pembeli', 'pegawai', 'transaksi.detailtransaksi.barang.penitipan.penitip'])
                                ->findOrFail($id_pengiriman);

        $old_status = $pengiriman->status_pengiriman;
        $pengiriman->status_pengiriman = $request->input('status_pengiriman');
        $pengiriman->save();

        // Check if the status has changed to 'dijadwalkan' (scheduled/on the way)
        if (strtolower($old_status) !== 'dijadwalkan' && strtolower($pengiriman->status_pengiriman) === 'dijadwalkan') {
            // No need to instantiate NotificationController again if it's injected
            $this->notificationController->sendOnTheWayNotification($pengiriman);
        }

        return response()->json(['message' => 'Delivery status updated successfully']);
    }

    /**
     * Helper method to load relations and send 'on the way' notification.
     * This avoids code duplication and ensures necessary relations are loaded.
     *
     * @param \App\Models\Pengiriman $pengiriman
     */
    protected function triggerOnTheWayNotification(Pengiriman $pengiriman)
    {
        // Ensure all necessary relations are loaded for the notification
        $pengiriman->load([
            'transaksi.pembeli',
            'pegawai',
            'transaksi.detailtransaksi.barang.penitipan.penitip'
        ]);

        if (strtolower($pengiriman->status_pengiriman) === 'dijadwalkan') {
            $this->notificationController->sendOnTheWayNotification($pengiriman);
        }
    }
}