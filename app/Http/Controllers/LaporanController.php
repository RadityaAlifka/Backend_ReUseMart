<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Penitipan;
use App\Models\Barang;
use App\Models\Penitip;
use App\Models\Pegawai;
use App\Models\Jabatan;
use App\Models\RequestDonasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanController 
{
    /**
     * Menampilkan dan mengunduh laporan komisi bulanan per produk.
     * @param Request $request (param: bulan, tahun, mode = [json|pdf])
     */
    public function laporanKomisiBulananPerProduk(Request $request)
    {
        
        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));
        $mode = $request->input('mode', 'json');

        // Ambil transaksi yang lunas di bulan & tahun
        $transaksis = \App\Models\Transaksi::whereYear('tgl_lunas', $tahun)
            ->whereMonth('tgl_lunas', $bulan)
            ->with(['detailtransaksi.barang.penitipan'])
            ->get();

        $result = [];
        foreach ($transaksis as $transaksi) {
            foreach ($transaksi->detailtransaksi as $detail) {
                $barang = $detail->barang;
                if (!$barang || !$barang->penitipan) continue;
                $penitipan = $barang->penitipan;

                // Kode Produk: Huruf kapital pertama nama barang + id_barang
                $kodeProduk = strtoupper(substr($barang->nama_barang, 0, 1)) . $barang->id_barang;

                $hargaJual = $barang->harga;
                $tanggalMasuk = $penitipan->tanggal_penitipan;
                $tanggalLaku = $transaksi->tgl_lunas;
                $selisihHariJual = \Carbon\Carbon::parse($tanggalLaku)->diffInDays(\Carbon\Carbon::parse($tanggalMasuk));

                // Komisi & Bonus sesuai rumus TransaksiController
                $totalPersenKomisi = $penitipan->perpanjangan ? 0.30 : 0.20;
                $nilaiTotalKomisi = $hargaJual * $totalPersenKomisi;
                $nilaiKomisiHunter = 0;
                if (!is_null($penitipan->id_hunter)) {
                    $nilaiKomisiHunter = $nilaiTotalKomisi * 0.05;
                }
                $nilaiKomisiOwnerAwal = $nilaiTotalKomisi - $nilaiKomisiHunter;
                $pengalihanKePenitip = 0;
                if ($selisihHariJual < 7) {
                    $pengalihanKePenitip = $nilaiTotalKomisi * 0.10;
                }
                $nilaiKomisiOwnerFinal = $nilaiKomisiOwnerAwal - $pengalihanKePenitip;

                $result[] = [
                    'kode_produk' => $kodeProduk,
                    'nama_produk' => $barang->nama_barang,
                    'harga_jual' => $hargaJual,
                    'tanggal_masuk' => $tanggalMasuk,
                    'tanggal_laku' => $tanggalLaku,
                    'komisi_hunter' => $nilaiKomisiHunter,
                    'komisi_reusemart' => $nilaiKomisiOwnerFinal,
                    'bonus_penitip' => $pengalihanKePenitip,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'data' => $result,
            'cetak' => [
                'judul' => 'LAPORAN KOMISI BULANAN',
                'alamat' => 'Jl. Green Eco Park No. 456 Yogyakarta',
                'nama_toko' => 'ReUse Mart',
                'tanggal_cetak' => now()->format('j F Y'),
            ]
        ]);
    }

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

    public function laporanRequestDonasi()
    {
        try {
            // Get all request donasis with their associated organisasi and pegawai
            $requestDonasis = RequestDonasi::with(['organisasi', 'pegawai'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Data Request Donasi berhasil diambil.',
                'data' => $requestDonasis
            ], 200);

        } catch (\Exception $e) {
            // Handle any exceptions that occur during the process
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data Request Donasi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDonationReportData()
    {
        try {
            $donatedBarangs = Barang::whereNotNull('id_donasi') // Ensure it has been donated
                ->with([
                    'penitipan.penitip', // Load penitipan and its related penitip
                    'donasi.organisasi'  // Load donasi and its related organisasi
                ])
                ->get()
                ->map(function ($barang) {
                    return [
                        'kode_produk' => $barang->id_barang, // Assuming id_barang is the product code
                        'nama_produk' => $barang->nama_barang,
                        'id_penitip' => $barang->penitipan->id_penitip ?? null,
                        'nama_penitip' => $barang->penitipan->penitip->nama_penitip ?? null,
                        'tanggal_donasi' => $barang->donasi->tanggal_donasi ?? null,
                        'organisasi' => $barang->donasi->organisasi->nama_organisasi ?? null,
                        'nama_penerima' => $barang->donasi->nama_penerima ?? null,
                    ];
                });

            if ($donatedBarangs->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data donasi barang yang tersedia.'
                ], 404);
            }

            return response()->json([
                'message' => 'Data Laporan Donasi Barang',
                'data' => $donatedBarangs
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error in BarangController@getDonationReportData: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data laporan donasi barang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function generateConsignmentReport(Request $request, $id_penitip) // Nama method diubah agar lebih spesifik
    {
        // Parameter bulan dan tahun tidak relevan untuk laporan ini, tapi bisa tetap ada
        $month = $request->input('month');
        $year = $request->input('year');

        $penitip = Penitip::with([
            'penitipans.barangs' // Hanya load penitipan dan barangs
        ])
        ->find($id_penitip);

        if (!$penitip) {
            return response()->json(['message' => 'Penitip not found.'], 404);
        }

        $reportData = [
            'ID Penitip' => $penitip->id_penitip,
            'Nama Penitip' => $penitip->nama_penitip,
            // Bulan dan Tahun bisa diisi "N/A" atau disembunyikan jika tidak relevan tanpa transaksi
            'Bulan' => $month ? Carbon::create()->month($month)->locale('id')->monthName : 'N/A (Tanpa Transaksi)',
            'Tahun' => $year ? $year : 'N/A (Tanpa Transaksi)',
            'Barang Dititipkan' => [] // Mengganti 'Transaksi Produk' menjadi 'Barang Dititipkan'
        ];

        // Loop untuk mengumpulkan data barang yang dititipkan
        foreach ($penitip->penitipans as $penitipan) {
            foreach ($penitipan->barangs as $barang) {
                $reportData['Barang Dititipkan'][] = [
                    'Kode Produk' => $barang->id_barang,
                    'Nama Produk' => $barang->nama_barang,
                    'Tanggal Masuk' => $penitipan->tanggal_penitipan ? Carbon::parse($penitipan->tanggal_penitipan)->format('Y-m-d') : null,
                    // Kolom terkait transaksi akan null atau diisi nilai default karena tidak ada join ke transaksi
                    'Tanggal Laku' => 'N/A (Belum Laku/Data Transaksi Tidak Dimuat)',
                    'Harga Jual Bersih' => $barang->harga,
                    'Status Barang' => $barang->status_barang, // Menambahkan status barang untuk debugging
                    'Bonus Terjual Cepat' => $barang->tanggal_keluar ? 'Ya' : 'Tidak', // Logika tetap sama
                    'Pendapatan' => $penitip->saldo // Ini tetap saldo penitip saat ini
                ];
            }
        }

        return response()->json($reportData);
    }

}

