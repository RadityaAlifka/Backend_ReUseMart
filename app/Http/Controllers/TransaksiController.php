<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Penitipan;
use App\Models\Barang;
use App\Models\Detailtransaksi;
use App\Models\Pegawai;
use App\Models\Penitip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pembeli;
use App\Http\Controllers\NotificationController;

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
        'total_harga' => 'required|numeric|min:0',
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

    if (isset($validatedData['diskon_poin']) && $validatedData['diskon_poin'] > 0) {
        $pembeli = Pembeli::find($validatedData['id_pembeli']);
        if ($pembeli) {
            $pembeli->poin = max(0, $pembeli->poin - $validatedData['diskon_poin']);
            $pembeli->save();
        }
    }

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
            'total_harga' => 'sometimes|required|numeric|min:0',
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

    public function getAllTransaksiWithBarang()
    {
        $transaksis = Transaksi::with(['detailtransaksi.barang', 'pengambilans', 'pengirimen', 'pembeli'])->get();
        return response()->json($transaksis);
    }

    public function getTransaksiById($id)
    {
        $transaksi = Transaksi::with(['detailtransaksi.barang', 'pengambilans', 'pengirimen', 'pembeli'])
                        ->find($id); // atau ->where('id', $id)->first()

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        return response()->json($transaksi);
    }

    public function prosesKomisiTransaksi($id_transaksi)
    {
        DB::beginTransaction();
        try {
            $transaksi = Transaksi::with(['detailtransaksi.barang', 'pembeli'])->find($id_transaksi);
            if (!$transaksi) {
                return response()->json(['message' => 'Transaksi not found'], 404);
            }

            $totalKomisi = 0;
            $totalPembelanjaan = 0;

            foreach ($transaksi->detailtransaksi as $detail) {
                $barang = $detail->barang;
                if (!$barang) continue;

                $penitipan = Penitipan::find($barang->id_penitipan);
                if (!$penitipan) continue;

                // Cek owner (pegawai dengan jabatan owner)
                $owner = Pegawai::whereHas('jabatan', function($q) {
                    $q->where('nama_jabatan', 'owner');
                })->first();

                // Hitung komisi
                $komisiPersen = $penitipan->perpanjangan ? 0.3 : 0.2;
                $komisi = $barang->harga * $komisiPersen;
                $totalKomisi += $komisi;

                // Tambah komisi ke owner
                if ($owner) {
                    $owner->komisi += $komisi;
                    $owner->save();
                }

                // Tambah saldo ke penitip
                $penitip = Penitip::find($penitipan->id_penitip);
                if ($penitip) {
                    $saldoPenitip = $barang->harga - $komisi;

                    // Cek bonus: jika barang laku < 7 hari sejak tanggal_penitipan
                    if (
                        $penitipan->tanggal_penitipan &&
                        $transaksi->tgl_lunas &&
                        \Carbon\Carbon::parse($transaksi->tgl_lunas)->diffInDays(\Carbon\Carbon::parse($penitipan->tanggal_penitipan)) < 7
                    ) {
                        $bonus = $komisi * 0.1;
                        $saldoPenitip += $bonus;
                    }

                    $penitip->saldo += $saldoPenitip;
                    $penitip->save();
                }

                $totalPembelanjaan += $barang->harga;
            }

            // Tambah poin ke pembeli
            $pembeli = $transaksi->pembeli;
            if ($pembeli) {
                $poinBaru = floor($totalPembelanjaan / 10000);
                $pembeli->poin += $poinBaru;
                $pembeli->save();
            }

            DB::commit();
            return response()->json([
                'message' => 'Komisi, saldo penitip, dan poin pembeli berhasil diproses',
                'total_komisi' => $totalKomisi,
                'total_pembelanjaan' => $totalPembelanjaan,
                'poin_ditambahkan' => $poinBaru ?? 0
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memproses', 'error' => $e->getMessage()], 500);
        }
    }

}
