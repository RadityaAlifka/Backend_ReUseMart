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
use Carbon\Carbon;

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
        $transaksi = Transaksi::with(['detailtransaksi.barang.penitipan', 'pembeli'])->find($id_transaksi);
        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi not found'], 404);
        }

        $owner = Pegawai::whereHas('jabatan', function($q) {
            $q->where('nama_jabatan', 'owner');
        })->first();

        if (!$owner) {
            throw new \Exception("Data 'owner' tidak ditemukan di tabel pegawai.");
        }

        $totalPembelanjaan = 0;
        $poinDitambahkan = 0;

        foreach ($transaksi->detailtransaksi as $detail) {
            $barang = $detail->barang;
            $penitipan = $barang->penitipan ?? null;

            if (!$barang || !$penitipan) continue;

            $hargaBarang = $barang->harga;
            $selisihHariJual = Carbon::parse($transaksi->tgl_lunas)->diffInDays(Carbon::parse($penitipan->tanggal_penitipan));
            
            // 1. Tentukan TOTAL Persen Komisi untuk ReUseMart berdasarkan perpanjangan
            $totalPersenKomisi = $penitipan->perpanjangan ? 0.30 : 0.20;

            // 2. Hitung TOTAL Nilai Komisi dalam Rupiah
            $nilaiTotalKomisi = $hargaBarang * $totalPersenKomisi;

            // 3. Hitung bagian untuk Hunter (jika ada) dari TOTAL nilai komisi
            $nilaiKomisiHunter = 0;
            if (!is_null($penitipan->id_hunter)) {
                // Hunter dapat 5% DARI NILAI TOTAL KOMISI
                $nilaiKomisiHunter = $nilaiTotalKomisi * 0.05;
            }

            // 4. Hitung bagian awal untuk Owner (Total Komisi - Jatah Hunter)
            $nilaiKomisiOwnerAwal = $nilaiTotalKomisi - $nilaiKomisiHunter;

            // 5. Hitung pengalihan ke penitip jika penjualan cepat (< 7 hari)
            $pengalihanKePenitip = 0;
            if ($selisihHariJual < 7) {
                // Bonus 10% dihitung dari TOTAL nilai komisi
                $pengalihanKePenitip = $nilaiTotalKomisi * 0.10;
            }

            // 6. Hitung bagian final untuk Owner (Bagian awal - pengalihan ke penitip)
            $nilaiKomisiOwnerFinal = $nilaiKomisiOwnerAwal - $pengalihanKePenitip;

            // 7. Update semua pihak
            $owner->komisi += $nilaiKomisiOwnerFinal;

            if ($nilaiKomisiHunter > 0) {
                $hunter = Pegawai::find($penitipan->id_hunter);
                if ($hunter) {
                    $hunter->komisi += $nilaiKomisiHunter;
                    $hunter->save();
                }
            }

            $penitip = Penitip::find($penitipan->id_penitip);
            if ($penitip) {
                $saldoDasar = $hargaBarang - $nilaiTotalKomisi;
                $penitip->saldo += ($saldoDasar + $pengalihanKePenitip);
                $penitip->save();
            }

            $totalPembelanjaan += $hargaBarang;
        }

        $owner->save();

        $pembeli = $transaksi->pembeli;
        if ($pembeli) {
            $poinDitambahkan = floor($totalPembelanjaan / 10000);
            $pembeli->poin += $poinDitambahkan;
            $pembeli->save();
        }

        DB::commit();
        return response()->json([
            'message' => 'Komisi, saldo penitip, dan poin pembeli berhasil diproses (logika final).',
            'total_pembelanjaan' => $totalPembelanjaan,
            'poin_ditambahkan' => $poinDitambahkan
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Gagal memproses transaksi', 'error' => $e->getMessage()], 500);
    }
}

public function getConsignorSoldItemsReport($id_penitip)
    {
        try {
            $penitip = Penitip::find($id_penitip);

            if (!$penitip) {
                return response()->json(['success' => false, 'message' => 'Penitip not found'], 404);
            }

            // --- PERBAIKAN DI SINI: INISIALISASI $formattedItems ---
            $formattedItems = []; // <-- Tambahkan baris ini
            // --- END PERBAIKAN ---

            $totalHargaJualBersih = 0;
            $totalBonusTerjualCepat = 0;
            $totalPendapatanItem = 0;

            $barangs = Barang::whereHas('penitipan', function($query) use ($id_penitip) {
                                $query->where('id_penitip', $id_penitip);
                            })
                            ->whereHas('detailtransaksis.transaksi', function($query) {
                                $query->whereNotNull('tgl_lunas');
                            })
                            ->with(['penitipan', 'detailtransaksis.transaksi'])
                            ->get();

            foreach ($barangs as $barang) {
                $penitipan = $barang->penitipan;
                $detailTransaksi = $barang->detailtransaksis->first();
                $transaksi = $detailTransaksi ? $detailTransaksi->transaksi : null;

                if (!$penitipan || !$transaksi) {
                    continue;
                }

                $hargaJualBersih = $barang->harga; 

                $tanggalPenitipan = Carbon::parse($penitipan->tanggal_penitipan);
                $tanggalLunas = Carbon::parse($transaksi->tgl_lunas);
                $selisihHariJual = $tanggalLunas->diffInDays($tanggalPenitipan);

                $totalPersenKomisi = $penitipan->perpanjangan ? 0.30 : 0.20;
                $nilaiTotalKomisi = $hargaJualBersih * $totalPersenKomisi;
                
                $bonusTerjualCepat = 0;
                if ($selisihHariJual < 7) {
                    $bonusTerjualCepat = $nilaiTotalKomisi * 0.10;
                }

                $saldoDasar = $hargaJualBersih - $nilaiTotalKomisi;
                $pendapatanItem = $saldoDasar + $bonusTerjualCepat;

                $formattedItems[] = [
                    'Kode Produk' => $barang->id_barang, 
                    'Nama Produk' => $barang->nama_barang,
                    'Tanggal Masuk' => $tanggalPenitipan->translatedFormat('d F Y'),
                    'Tanggal Laku' => $tanggalLunas->translatedFormat('d F Y'),
                    'Harga Jual Bersih' => (float) $hargaJualBersih,
                    'Bonus Terjual Cepat' => (float) $bonusTerjualCepat,
                    'Pendapatan Item' => (float) $pendapatanItem,
                ];

                $totalHargaJualBersih += $hargaJualBersih;
                $totalBonusTerjualCepat += $bonusTerjualCepat;
                $totalPendapatanItem += $pendapatanItem;
            }

            $summary = [
                'total_harga_jual_bersih' => (float) $totalHargaJualBersih,
                'total_bonus_terjual_cepat' => (float) $totalBonusTerjualCepat,
                'total_pendapatan_item' => (float) $totalPendapatanItem,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'Barang Dititipkan' => $formattedItems,
                    'Ringkasan' => $summary,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to retrieve consignor sales report: ' . $e->getMessage()], 500);
        }
    }

}
