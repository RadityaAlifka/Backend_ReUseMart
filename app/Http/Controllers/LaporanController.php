<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
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
}
