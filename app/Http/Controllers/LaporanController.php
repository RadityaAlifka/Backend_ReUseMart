<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Penitipan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanController 
{
    /**
     * Display monthly sales report
     *
     * @return \Illuminate\Http\Response
     */
    public function getMonthlySalesReport()
    {
        // Get current year
        $currentYear = date('Y');
        
        // Get monthly sales data
        $monthlySales = DB::table('transaksis')
            ->select(
                DB::raw('MONTH(tgl_pesan) as month'),
                DB::raw('SUM(total_harga) as total_sales'),
                DB::raw('COUNT(*) as total_transactions')
            )
            ->whereYear('tgl_pesan', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Format the data
        $formattedData = [];
        foreach ($monthlySales as $sale) {
            $formattedData[] = [
                'month' => date('F', mktime(0, 0, 0, $sale->month, 1)),
                'total_sales' => $sale->total_sales,
                'total_transactions' => $sale->total_transactions
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'year' => $currentYear,
                'monthly_sales' => $formattedData
            ]
        ]);
    }

     public function getYearlyReportByCategory()
    {
        try {
            // --- 1. NILAI STATUS DIPERBAIKI ---
            $statusBerhasil = 'transaksi selesai';
            $statusGagal = 'transaksi dibatalkan'; // Asumsi, sesuaikan jika perlu

            // --- 2. KUERI UNTUK RINCIAN PER TAHUN & KATEGORI (TETAP SAMA) ---
            $yearlyReportData = DB::table('detailtransaksis')
                ->join('barangs', 'detailtransaksis.id_barang', '=', 'barangs.id_barang')
                ->join('kategori_barangs', 'barangs.id_kategori', '=', 'kategori_barangs.id_kategori')
                ->join('transaksis', 'detailtransaksis.id_transaksi', '=', 'transaksis.id_transaksi')
                ->select(
                    DB::raw('YEAR(transaksis.tgl_pesan) as year'),
                    'kategori_barangs.nama_kategori as category_name',
                    DB::raw("SUM(CASE WHEN transaksis.status_transaksi = '{$statusBerhasil}' THEN 1 ELSE 0 END) as items_sold"),
                    DB::raw("SUM(CASE WHEN transaksis.status_transaksi = '{$statusGagal}' THEN 1 ELSE 0 END) as items_failed")
                )
                ->whereIn('transaksis.status_transaksi', [$statusBerhasil, $statusGagal])
                ->groupBy('year', 'category_name')
                ->orderBy('year', 'desc')
                ->orderBy('items_sold', 'desc')
                ->get();
            
            // Format data rincian per tahun
            $formattedYearlyData = [];
            foreach ($yearlyReportData as $data) {
                $year = $data->year;
                if (!isset($formattedYearlyData[$year])) {
                    $formattedYearlyData[$year] = ['year' => $year, 'categories' => []];
                }
                $formattedYearlyData[$year]['categories'][] = [
                    'name' => $data->category_name,
                    'items_sold' => (int) $data->items_sold,
                    'items_failed' => (int) $data->items_failed
                ];
            }

            // --- 3. KUERI BARU UNTUK MENGHITUNG GRAND TOTAL ---
            $grandTotalData = DB::table('transaksis')
                ->join('detailtransaksis', 'transaksis.id_transaksi', '=', 'detailtransaksis.id_transaksi')
                ->select(
                    DB::raw("SUM(CASE WHEN transaksis.status_transaksi = '{$statusBerhasil}' THEN 1 ELSE 0 END) as total_items_sold"),
                    DB::raw("SUM(CASE WHEN transaksis.status_transaksi = '{$statusGagal}' THEN 1 ELSE 0 END) as total_items_failed")
                )
                ->whereIn('transaksis.status_transaksi', [$statusBerhasil, $statusGagal])
                ->first(); // Menggunakan first() karena hanya mengharapkan 1 baris hasil

            // --- 4. MENYUSUN JSON RESPONSE DENGAN STRUKTUR BARU ---
            return response()->json([
                'success' => true,
                'data' => [
                    'yearly_breakdown' => array_values($formattedYearlyData),
                    'grand_total' => [
                        'total_items_sold' => (int) ($grandTotalData->total_items_sold ?? 0),
                        'total_items_failed' => (int) ($grandTotalData->total_items_failed ?? 0),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data laporan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

     public function laporanPenitipanHabis()
    {
        try {
            // 1. Ambil data penitipan yang kadaluarsa, panggil relasi 'barangs' (plural) dan 'penitip'
            $penitipanKadaluarsa = Penitipan::with(['barangs', 'penitip'])
                ->where('batas_penitipan', '<', Carbon::today())
                ->get();

            // 2. Gunakan flatMap untuk membuat satu baris laporan untuk setiap barang
            $laporan = $penitipanKadaluarsa->flatMap(function ($penitipan) {
                // Hitung 'batas_ambil' sekali untuk setiap transaksi penitipan
                $batasAmbil = Carbon::parse($penitipan->batas_penitipan)->addDays(7)->toDateString();

                // Jika tidak ada barang dalam penitipan ini, lewati.
                if ($penitipan->barangs->isEmpty()) {
                    return [];
                }

                // Buat array baru untuk setiap barang di dalam koleksi 'barangs'
                return $penitipan->barangs->map(function ($barang) use ($penitipan, $batasAmbil) {
                    return [
                        'id_barang' => $barang->id_barang,
                        'nama_barang' => $barang->nama_barang,
                        'id_penitip' => $penitipan->penitip->id_penitip,
                        'nama_penitip' => $penitipan->penitip->nama_penitip,
                        'tanggal_penitipan' => $penitipan->tanggal_penitipan->toDateString(),
                        'batas_penitipan' => $penitipan->batas_penitipan->toDateString(),
                        'batas_ambil' => $batasAmbil,
                    ];
                });
            });

            // 3. Kembalikan data dalam format JSON
            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil diambil',
                'data' => $laporan->values() // gunakan values() untuk mereset keys array
            ], 200);

        } catch (\Exception $e) {
            // Penanganan jika terjadi error
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
