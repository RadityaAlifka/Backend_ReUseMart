<?php

namespace App\Http\Controllers;

use App\Models\Pengambilan;
use App\Models\Barang;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Http\Controllers\NotificationController;
class PengambilanController 
{
    protected $notificationController;

    public function __construct(NotificationController $notificationController)
    {
        $this->notificationController = $notificationController;
    }
    // Get all pengambilans
    public function index()
    {
        $pengambilans = Pengambilan::with([
            'penitip.penitipans.barangs', // Nested eager loading
            'pembeli',
            'transaksi'
        ])->get();

        return response()->json($pengambilans);
    }


    // Store a new pengambilan
   public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
            'id_transaksi' => 'nullable|exists:transaksis,id_transaksi',
            'id_penitip' => 'required|exists:penitips,id_penitip',
            'id_pembeli' => 'nullable|exists:pembelis,id_pembeli',
            'tanggal_pengambilan' => 'required|date',
            'batas_pengambilan' => 'required|date|after_or_equal:tanggal_pengambilan',
            'status_pengambilan' => 'required|string|max:50',
        ]);

        $pengambilan = Pengambilan::create($validatedData);

        return response()->json([
            'message' => 'Pengambilan created successfully',
            'data' => $pengambilan->load(['pembeli', 'penitip', 'transaksi'])
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal menyimpan pengambilan',
            'error' => $e->getMessage()
        ], 500);
    }
}


    // Show a specific pengambilan
    public function show($id)
    {
        $pengambilan = Pengambilan::with(['pembeli', 'penitip', 'transaksi'])->find($id);

        if (!$pengambilan) {
            return response()->json(['message' => 'Pengambilan not found'], 404);
        }

        return response()->json($pengambilan);
    }
    public function getPengambilanMerchandise() {
        try {
            // Join tabel pengambilans dengan tabel pembelis
            $pengambilanDiklaim = Pengambilan::join('pembelis', 'pengambilans.id_pembeli', '=', 'pembelis.id_pembeli')
                ->where('pengambilans.status_pengambilan', 'Merchandise diKlaim')
                ->select(
                    'pengambilans.id_pengambilan',
                    'pembelis.nama_pembeli', 
                    'pengambilans.tanggal_pengambilan',
                    'pengambilans.status_pengambilan'
                )
                ->get();

            if ($pengambilanDiklaim->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data pengambilan dengan status "Merchandise diKlaim" yang ditemukan.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $pengambilanDiklaim
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Update a specific pengambilan
    public function update(Request $request, $id)
    {
        $pengambilan = Pengambilan::find($id);

        if (!$pengambilan) {
            return response()->json(['message' => 'Pengambilan not found'], 404);
        }

        $validatedData = $request->validate([
            'id_transaksi' => 'sometimes|required|exists:transaksis,id_transaksi',
            'id_penitip' => 'sometimes|required|exists:penitips,id_penitip',
            'id_pembeli' => 'sometimes|required|exists:pembelis,id_pembeli',
            'tanggal_pengambilan' => 'sometimes|required|date',
            'batas_pengambilan' => 'sometimes|required|date|after_or_equal:tanggal_pengambilan',
            'status_pengambilan' => 'sometimes|required|string|max:50',
        ]);

        $pengambilan->update($validatedData);

        return response()->json([
            'message' => 'Pengambilan updated successfully',
            'data' => $pengambilan->load(['pembeli', 'penitip', 'transaksi'])
        ]);
    }

    // Delete a specific pengambilan
    public function destroy($id)
    {
        $pengambilan = Pengambilan::find($id);

        if (!$pengambilan) {
            return response()->json(['message' => 'Pengambilan not found'], 404);
        }

        $pengambilan->delete();

        return response()->json(['message' => 'Pengambilan deleted successfully']);
    }

    public function addPengambilanFromPenitip(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'id_penitip' => 'required|exists:penitips,id_penitip',
                'tanggal_pengambilan' => 'required|date',
                'batas_pengambilan' => 'required|date|after_or_equal:tanggal_pengambilan',
                'status_pengambilan' => 'required|string|max:50',
                'id_barang' => 'required|exists:barangs,id_barang', // Barang yang diambil
            ]);

            // Simpan data pengambilan
            $pengambilan = Pengambilan::create([
                'id_penitip' => $validatedData['id_penitip'],
                'tanggal_pengambilan' => $validatedData['tanggal_pengambilan'],
                'batas_pengambilan' => $validatedData['batas_pengambilan'],
                'status_pengambilan' => $validatedData['status_pengambilan'],
            ]);

            // Update status barang yang diambil
            $barang = Barang::find($validatedData['id_barang']);
            if ($barang) {
                $barang->status_barang = 'menunggu diambil'; // misal status baru
                $barang->save();
            }

            return response()->json([
                'message' => 'Pengambilan berhasil dibuat dan status barang diupdate',
                'data' => $pengambilan,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menyimpan pengambilan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function konfirmasiPengambilan(Request $request, $id)
    {
        try {
            \Log::info('Starting konfirmasi pengambilan', [
                'pengambilan_id' => $id,
                'request_data' => $request->all()
            ]);

            // Validasi input
            $validatedData = $request->validate([
                'tanggal_pengambilan' => 'required|date',
                'status_pengambilan' => 'required|string|max:50',
                'id_barang' => 'required|exists:barangs,id_barang',
                'tanggal_keluar_barang' => 'nullable|date', // Tambahkan validasi untuk properti baru
            ]);

            // Ambil data pengambilan
            $pengambilan = Pengambilan::with(['pembeli', 'penitip', 'transaksi'])->find($id);

            if (!$pengambilan) {
                \Log::warning('Pengambilan not found', ['id' => $id]);
                return response()->json(['message' => 'Pengambilan not found'], 404);
            }

            // Update data pengambilan
            $pengambilan->tanggal_pengambilan = $validatedData['tanggal_pengambilan'];
            $pengambilan->status_pengambilan = $validatedData['status_pengambilan'];
            $pengambilan->save();

            // Update status barang dan TANGGAL KELUAR BARANG
            $barang = Barang::find($validatedData['id_barang']);
            if ($barang) {
                $oldStatus = $barang->status_barang;
                $barang->status_barang = 'diambil kembali';
                // **PENTING: Perbarui kolom tanggal_keluar di tabel barangs**
                $barang->tanggal_keluar = $validatedData['tanggal_keluar_barang'] ?? null; // Gunakan properti baru, jika null biarkan null
                $barang->save();

                \Log::info('Updated barang status and tanggal_keluar', [
                    'barang_id' => $barang->id_barang,
                    'old_status' => $oldStatus,
                    'new_status' => 'diambil kembali',
                    'tanggal_keluar_in_db' => $barang->tanggal_keluar // Tambahkan ini ke log untuk debugging
                ]);

                // Logika update transaksi tetap sama
                if ($pengambilan->id_pembeli && $pengambilan->id_transaksi) {
                    $transaksi = Transaksi::find($pengambilan->id_transaksi);
                    if ($transaksi) {
                        $transaksi->status_transaksi = 'transaksi selesai';
                        $transaksi->save();
                        \Log::info('Updated transaksi status', [
                            'transaksi_id' => $transaksi->id_transaksi,
                            'new_status' => 'selesai'
                        ]);
                    }
                }
            } else {
                \Log::warning('Barang not found', ['id_barang' => $validatedData['id_barang']]);
            }

            // ... (Kode notifikasi tetap sama)
            try {
                if ($pengambilan->id_pembeli) {
                    \Log::info('Sending notifications for pembeli pengambilan', [
                        'pembeli_id' => $pengambilan->id_pembeli,
                        'penitip_id' => $pengambilan->id_penitip
                    ]);

                    $pembeli_topic = 'pembeli_' . $pengambilan->id_pembeli;
                    $penitip_topic = 'penitip_' . $pengambilan->id_penitip;

                    $pembeli_subscribed = $this->notificationController->verifyTopicSubscription($pembeli_topic);
                    $penitip_subscribed = $this->notificationController->verifyTopicSubscription($penitip_topic);

                    \Log::info('Subscription status', [
                        'pembeli_topic' => $pembeli_topic,
                        'pembeli_subscribed' => $pembeli_subscribed,
                        'penitip_topic' => $penitip_topic,
                        'penitip_subscribed' => $penitip_subscribed
                    ]);

                    if ($pembeli_subscribed) {
                        $this->notificationController->NotifyPengambilanPembeli($pengambilan->id_pembeli);
                    }
                    if ($penitip_subscribed) {
                        $this->notificationController->NotifyPengambilanPenitip($pengambilan->id_penitip);
                    }
                } else {
                    \Log::info('Sending notification for penitip pengambilan', [
                        'penitip_id' => $pengambilan->id_penitip
                    ]);

                    $penitip_topic = 'penitip_' . $pengambilan->id_penitip;
                    $penitip_subscribed = $this->notificationController->verifyTopicSubscription($penitip_topic);

                    \Log::info('Penitip subscription status', [
                        'topic' => $penitip_topic,
                        'subscribed' => $penitip_subscribed
                    ]);

                    if ($penitip_subscribed) {
                        $this->notificationController->NotifyPengambilanPenitip($pengambilan->id_penitip);
                    }
                }
            } catch (\Exception $notifError) {
                \Log::error('Failed to send notifications', [
                    'error' => $notifError->getMessage(),
                    'pengambilan_id' => $id,
                    'trace' => $notifError->getTraceAsString()
                ]);
            }

            \Log::info('Successfully processed pengambilan confirmation', [
                'pengambilan_id' => $id
            ]);

            return response()->json([
                'message' => 'Data pengambilan dan status barang berhasil diupdate',
                'data' => $pengambilan->load(['pembeli', 'penitip', 'transaksi']),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in konfirmasi pengambilan', [
                'errors' => $e->errors(),
                'pengambilan_id' => $id
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error in konfirmasi pengambilan', [
                'error' => $e->getMessage(),
                'pengambilan_id' => $id
            ]);
            return response()->json([
                'message' => 'Gagal mengupdate data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function editPengambilan(Request $request, $id)
    {
        $pengambilan = Pengambilan::find($id);

        if (!$pengambilan) {
            return response()->json(['message' => 'Pengambilan not found'], 404);
        }

        $validatedData = $request->validate([
            'batas_pengambilan' => 'sometimes|required|date|after_or_equal:tanggal_pengambilan',
            'status_pengambilan' => 'sometimes|required|string|max:50',
        ]);

        $pengambilan->update($validatedData);

        return response()->json([
            'message' => 'Pengambilan updated successfully',
            'data' => $pengambilan->load(['pembeli', 'penitip', 'transaksi'])
        ]);
    }

    public function pengambilanMerchandise(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id_pembeli' => 'nullable|exists:pembelis,id_pembeli',
                'batas_pengambilan' => 'required|date|after_or_equal:tanggal_pengambilan',
            ]);

            // Set status_pengambilan default
            $validatedData['status_pengambilan'] = 'Merchandise diKlaim';

            $pengambilan = Pengambilan::create($validatedData);

            return response()->json([
                'message' => 'Pengambilan merchandise berhasil dibuat',
                'data' => $pengambilan->load(['pembeli', 'penitip', 'transaksi'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menyimpan pengambilan merchandise',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function verifikasiPengambilan($id)
    {
        try {
            $pengambilan = \App\Models\Pengambilan::findOrFail($id);

            // Update tanggal pengambilan dengan waktu saat ini
            $pengambilan->tanggal_pengambilan = now();
            $pengambilan->save();

            return response()->json([
                'success' => true,
                'message' => 'Pengambilan berhasil diverifikasi.',
                'data' => $pengambilan // Mengembalikan data yang sudah diupdate
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengambilan tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
