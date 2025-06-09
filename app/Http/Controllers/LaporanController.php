<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Penitipan;
use App\Models\Penitip;
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
     * Get warehouse stock report
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function laporanStokGudang(Request $request)
    {
        try {
            // Validate status parameter exists
            if (!$request->has('status')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status parameter is required'
                ], 400);
            }

            // Query products with status 'tersedia'
            $products = Barang::where('status_barang', 'tersedia')
                ->with([
                    'penitipan' => function($query) {
                        $query->select('id_penitipan', 'id_penitip', 'id_hunter', 'tanggal_penitipan', 'batas_penitipan');
                    },
                    'penitipan.penitip:id_penitip,nama_penitip',
                    'penitipan.hunter:id_hunter,nama_hunter'
                ])
                ->select([
                    'id_barang',
                    'kode_barang',
                    'nama_barang',
                    'harga',
                    'penitipan_id',
                    'created_at'
                ])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            // Transform the data to flat array structure
            $formattedData = $products->map(function($product) {
                return [
                    'kode_produk' => $product->kode_barang,
                    'nama_produk' => $product->nama_barang,
                    'id_penitip' => $product->penitipan->id_penitip ?? null,
                    'nama_penitip' => $product->penitipan->penitip->nama_penitip ?? null,
                    'tanggal_masuk' => $product->penitipan->tanggal_penitipan->format('Y-m-d') ?? null,
                    'perpanjangan' => $product->penitipan->batas_penitipan->format('Y-m-d') ?? null,
                    'id_hunter' => $product->penitipan->id_hunter ?? null,
                    'nama_hunter' => $product->penitipan->hunter->nama_hunter ?? null,
                    'harga' => $product->harga
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stock report: ' . $e->getMessage()
            ], 500);
        }
    }
}
