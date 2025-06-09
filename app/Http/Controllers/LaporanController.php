<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Barang;
use App\Models\Pegawai;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * Laporan stok barang tersedia.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function laporanStokGudang()
    {
        
        // Query barang dengan status tersedia
        $barangs = \App\Models\Barang::where('status_barang', 'tersedia')
            // DIUBAH: Eager loading disesuaikan untuk memuat relasi penitip dan hunter secara langsung
            ->with(['penitipan.penitip', 'penitipan.hunter'])
            ->get();

        $result = $barangs->map(function($barang) {
            $penitipan = $barang->penitipan;
            $penitip = $penitipan ? $penitipan->penitip : null;
            // DIUBAH: Mengambil data hunter langsung dari relasi yang baru
            $hunter = $penitipan ? $penitipan->hunter : null;

            return [
                'kode_produk'   => strtoupper(substr($barang->nama_barang, 0, 1)) . $barang->id_barang,
                'nama_produk'   => $barang->nama_barang,
                'id_penitip'    => $penitip ? $penitip->id_penitip : null,
                'nama_penitip'  => $penitip ? $penitip->nama_penitip : null,
                'tanggal_masuk' => $penitipan ? $penitipan->tanggal_penitipan : null,
                'perpanjangan'  => $penitipan ? $penitipan->perpanjangan : null,
                // DIUBAH: Mengambil data dari objek hunter
                'id_hunter'     => $hunter ? $hunter->id_pegawai : null, // id_pegawai adalah primary key di tabel pegawais
                'nama_hunter'   => $hunter ? $hunter->nama_pegawai : null,
                'harga'         => $barang->harga,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}