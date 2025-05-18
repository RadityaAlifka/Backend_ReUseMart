<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembeli;
use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\Detailtransaksi;
use App\Models\Organisasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class HistoryController 
{
    public function index()
    {
        $user = Auth::user();
        $pembeli = Pembeli::where('user_id', $user->id)->first();
    if (!$pembeli) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    // Debugging: Log pembeli
    \Log::info('Pembeli:', ['id' => $pembeli->id_pembeli]);

    $history = Detailtransaksi::with(['barang', 'transaksi'])
        ->whereHas('transaksi', function ($query) use ($pembeli) {
            $query->where('id_pembeli', $pembeli->id_pembeli);
        })
        ->get();

    // Debugging: Log hasil query
    \Log::info('History:', $history->toArray());

    return response()->json($history);
    }
    
    public function donasiHistoryByOrganisasi(Request $request)
        {
            // Ambil nama_organisasi dari input request
            $nama_organisasi = $request->input('nama_organisasi');
        
            if (!$nama_organisasi) {
                return response()->json(['message' => 'nama_organisasi is required'], 400);
            }
        
            // Cari data organisasi berdasarkan nama_organisasi
            $organisasi = Organisasi::whereRaw('LOWER(nama_organisasi) LIKE ?', ['%' . strtolower($nama_organisasi) . '%'])->first();
        
            if (!$organisasi) {
                return response()->json(['message' => 'Organisasi not found'], 404);
            }
        
            // Debugging: Log organisasi
            \Log::info('Organisasi:', ['id_organisasi' => $organisasi->id_organisasi]);
        
            // Ambil barang yang terkait dengan donasi ke organisasi tertentu
            $historyDonasi = Barang::with(['donasi', 'donasi.organisasi'])
                ->whereHas('donasi', function ($query) use ($organisasi) {
                    $query->where('id_organisasi', $organisasi->id_organisasi);
                })
                ->get();
        
            // Debugging: Log hasil query
            \Log::info('History Donasi:', $historyDonasi->toArray());
        
            return response()->json($historyDonasi);
    }

    public function penjualanHistoryPenitip()
    {
        $user = Auth::user();
        // Pastikan user adalah penitip
        if (!$user || $user->level !== 'penjual') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ambil data penitip berdasarkan user_id
        $penitip = \App\Models\Penitip::where('user_id', $user->id)->first();
        if (!$penitip) {
            return response()->json(['message' => 'Penitip not found'], 404);
        }

        // Ambil semua penitipan milik penitip
        $penitipanIds = \App\Models\Penitipan::where('id_penitip', $penitip->id_penitip)->pluck('id_penitipan');

        // Ambil semua barang milik penitip (dari penitipan) dengan status "laku"
        $barangIds = \App\Models\Barang::whereIn('id_penitipan', $penitipanIds)
            ->where('status_barang', 'laku')
            ->pluck('id_barang');

        // Ambil detail transaksi yang terkait dengan barang penitip
        $history = \App\Models\Detailtransaksi::with(['barang', 'transaksi'])
            ->whereIn('id_barang', $barangIds)
            ->get();

        return response()->json([
            'message' => 'History penjualan penitip (status laku)',
            'data' => $history
        ]);
    }
}