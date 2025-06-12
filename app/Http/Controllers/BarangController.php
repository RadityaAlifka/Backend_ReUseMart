<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Rating;
use App\Models\KategoriBarang;
use App\Models\Pegawai;
use App\Models\Penitipan;
use App\Models\Penitip;
use App\Models\Donasi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\NotificationController;



class BarangController 
{
    protected $penitipController;
    protected $notificationController;

    public function __construct(NotificationController $notificationController, PenitipController $penitipController)
{
    $this->notificationController = $notificationController;
    $this->penitipController = $penitipController;
}


public function index()
{
    try {
        // Langkah 1: Ambil data barang dan relasinya secara efisien (Eager Loading)
        $barangs = Barang::where('status_barang', 'Tersedia')
            ->with(['penitipan.penitip']) // Memuat penitipan DAN data penitip di dalamnya
            ->get();

        // Cek jika tidak ada barang sama sekali
        if ($barangs->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada barang yang tersedia saat ini.'
            ], 404);
        }

        // Langkah 2: Gunakan ->map() untuk mengubah setiap item barang
        $data = $barangs->map(function ($barang) {
            // Akses data penitip yang sudah di-load
            $penitip = $barang->penitipan?->penitip;

            $rating_penitip = 0;
            $jumlah_rating = 0;

            // Hitung rating hanya jika penitip ada.
            // CATATAN: Baris kode di bawah ini akan menjalankan 1 query tambahan
            // untuk SETIAP barang. Ini adalah contoh N+1 query problem.
            // Untuk performa terbaik, gunakan API Resource atau optimasi query lebih lanjut.
            if ($penitip) {
                $ratingInfo = Rating::selectRaw('AVG(ratings.rating) as average_rating, COUNT(ratings.id_rating) as total_ratings')
                    ->join('barangs', 'ratings.id_barang', '=', 'barangs.id_barang')
                    ->join('penitipans', 'barangs.id_penitipan', '=', 'penitipans.id_penitipan')
                    ->where('penitipans.id_penitip', $penitip->id_penitip)
                    ->first();

                $rating_penitip = $ratingInfo->average_rating ?? 0;
                $jumlah_rating = $ratingInfo->total_ratings ?? 0;
            }

            // Bentuk struktur JSON yang diinginkan
            return [
                // Data Barang Utama
                'id_barang' => $barang->id_barang,
                'nama_barang' => $barang->nama_barang,
                'deskripsi_barang' => $barang->deskripsi_barang,
                'harga' => $barang->harga,
                'status_barang' => $barang->status_barang,
                'berat' => $barang->berat,
                'garansi' => $barang->garansi,
                'gambar1_url' => $barang->gambar1_url, // Asumsi Anda punya accessor ini

                // Data Tambahan
                'rating_penitip' => (float) $rating_penitip,
                'jumlah_rating' => (int) $jumlah_rating,

                // Objek Penjual (Penitip)
                'penjual' => [
                    'id_penitip' => $penitip?->id_penitip,
                    'nama_penitip' => $penitip?->nama_penitip,
                    'is_top_seller' => (bool) $penitip?->top_seller,
                ]
            ];
        });

        // Langkah 3: Kembalikan data yang sudah di-transform
        return response()->json([
            'message' => 'Daftar barang tersedia',
            'data' => $data
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error in BarangController@index: ' . $e->getMessage());
        
        return response()->json([
            'message' => 'Terjadi kesalahan saat mengambil data barang',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barangs' => 'required|array|min:1',
            'barangs.*.id_kategori' => 'required|exists:kategori_barangs,id_kategori',
            'barangs.*.id_penitip' => 'required|exists:penitips,id_penitip',
            'barangs.*.id_pegawai' => 'required|exists:pegawais,id_pegawai',
            'barangs.*.id_hunter' => 'nullable|exists:pegawais,id_pegawai',
            'barangs.*.nama_barang' => 'required|string|max:255',
            'barangs.*.deskripsi_barang' => 'required|string',
            'barangs.*.garansi' => 'nullable|string|max:255',
            'barangs.*.tanggal_garansi' => 'nullable|date',
            'barangs.*.harga' => 'required|numeric|min:0',
            'barangs.*.status_barang' => 'required|string|max:50',
            'barangs.*.berat' => 'required|numeric|min:0',
        ]);

        $now = now();
        $createdBarangs = [];

        // Ambil data penitipan dari barang pertama
        $firstBarang = $validated['barangs'][0];

        // Buat penitipan baru satu kali
        $penitipan = Penitipan::create([
            'id_penitip' => $firstBarang['id_penitip'],
            'id_pegawai' => $firstBarang['id_pegawai'],
            'id_hunter' => $firstBarang['id_hunter'] ?? null,
            'tanggal_penitipan' => $now->toDateString(),
            'batas_penitipan' => $now->copy()->addDays(30),
        ]);

        // Loop dan simpan semua barang
        foreach ($validated['barangs'] as $index => $barangData) {
            $gambar1Key = "gambar1_$index";
            $gambar2Key = "gambar2_$index";

            $gambar1Path = null;
            $gambar2Path = null;

            if ($request->hasFile($gambar1Key)) {
                $gambar1Path = $request->file($gambar1Key)->store('image/barang', 'public');
            }

            if ($request->hasFile($gambar2Key)) {
                $gambar2Path = $request->file($gambar2Key)->store('image/barang', 'public');
            }

            $barang = Barang::create([
                'id_kategori'      => $barangData['id_kategori'],
                'id_penitipan'     => $penitipan->id_penitipan,
                'id_donasi'        => $barangData['id_donasi'] ?? null,
                'nama_barang'      => $barangData['nama_barang'],
                'deskripsi_barang' => $barangData['deskripsi_barang'],
                'garansi'          => $barangData['garansi'] ?? null,
                'tanggal_garansi'  => $barangData['tanggal_garansi'] ?? null,
                'harga'            => $barangData['harga'],
                'status_barang'    => $barangData['status_barang'],
                'berat'            => $barangData['berat'],
                'tanggal_keluar'   => null,
                'gambar1'          => $gambar1Path,
                'gambar2'          => $gambar2Path,
            ]);

            $createdBarangs[] = $barang->fresh();
        }

        return response()->json([
            'message' => 'Semua barang berhasil dititipkan',
            'penitipan' => $penitipan,
            'data' => $createdBarangs
        ], 201);
    }
    public function show($id)
    {
        $barang = Barang::with(['kategori_barang', 'penitipan', 'donasi'])->find($id);

        if (!$barang) {
            return response()->json(['message' => 'Barang not found'], 404);
        }

        return response()->json($barang);
    }

    public function pengambilanBarang($id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json(['message' => 'Barang not found'], 404);
        }

        if ($barang->status_barang !== 'Masa Titip Habis') {
            return response()->json(['message' => 'Barang tidak dalam status Masa Titip Habis'], 400);
        }

        $barang->updateStatus('Diambil');

        return response()->json([
            'message' => 'Barang berhasil diambil',
            'data' => $barang
        ]);
    }

    public function update(Request $request, $id)
{
    $barang = Barang::find($id);

    if (!$barang) {
        return response()->json(['message' => 'Barang not found'], 404);
    }

    $validatedData = $request->validate([
        'id_kategori' => 'sometimes|required|exists:kategori_barangs,id_kategori',
        'id_penitipan' => 'sometimes|required|exists:penitipans,id_penitipan',
        'id_donasi' => 'nullable|exists:donasis,id_donasi',
        'nama_barang' => 'sometimes|required|string|max:255',
        'deskripsi_barang' => 'sometimes|required|string',
        'garansi' => 'nullable|string|max:255',
        'tanggal_garansi' => 'nullable|date',
        'harga' => 'sometimes|required|numeric',
        'status_barang' => 'sometimes|required|string|max:50',
        'berat' => 'sometimes|required|numeric',
        'tanggal_keluar' => 'nullable|date',
        'gambar1' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        'gambar2' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    // Perbarui gambar jika ada file baru
    if ($request->hasFile('gambar1')) {
        $validatedData['gambar1'] = $request->file('gambar1')->store('images/barang');
    }

    if ($request->hasFile('gambar2')) {
        $validatedData['gambar2'] = $request->file('gambar2')->store('images/barang');
    }

    $oldStatus = $barang->status_barang;

$barang->update($validatedData);
$barang->load('penitipan'); // pastikan relasi dimuat

if ($oldStatus !== 'laku' && isset($validatedData['status_barang']) && $validatedData['status_barang'] === 'laku') {
    $this->notificationController->sendBarangLakuNotification(
        $barang->penitipan->id_penitip ?? null,
        $barang->nama_barang
    );
}




    return response()->json([
        'message' => 'Barang updated successfully',
        'data' => $barang->load(['kategori_barang', 'penitipan', 'donasi'])
    ]);
}

    public function barangBergaransi()
    {
        $barang = Barang::with(['kategori_barang', 'penitipan', 'donasi'])
        ->where('status_barang', 'Tersedia')
        ->whereDate('tanggal_garansi', '>=', now()->toDateString())
        ->get();


        if ($barang->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada barang dengan garansi yang masih berlaku',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Barang dengan garansi yang masih berlaku',
            'data' => $barang
        ]);
    }

    public function destroy($id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json(['message' => 'Barang not found'], 404);
        }

        $barang->delete();

        return response()->json(['message' => 'Barang deleted successfully']);
    }

    public function barangMenungguDonasi()
    {
        $barang = Barang::with(['kategori_barang', 'penitipan', 'donasi'])
            ->where('status_barang', 'Menunggu Donasi')
            ->get();

    return response()->json([
        'message' => 'Barang dengan status Menunggu Donasi',
        'data' => $barang
    ]);
}

public function checkStokBarang($id)
{
    $barang = Barang::find($id);

    if (!$barang) {
        return response()->json([
            'message' => 'Barang tidak ditemukan'
        ], 404);
    }

    // Cek status barang, misal 'Tersedia' berarti stok masih ada
    if ($barang->status_barang === 'Tersedia') {
        return response()->json([
            'message' => 'Barang tersedia',
            'available' => true
        ], 200);
    } else {
        return response()->json([
            'message' => 'Barang tidak tersedia',
            'available' => false
        ], 200);
    }
}


    public function showAllBarang()
    {

        $barang = Barang::all();
        if ($barang->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada barang yang tersedia saat ini.'
            ], 404);
        }

        // Jika ada data, kembalikan dengan response JSON
        return response()->json([
            'message' => 'Daftar semua barang',
            'data' => $barang
        ], 200);
    }

    // Mengambil id_penitip berdasarkan id_barang
    public function getIdPenitipByBarang($id_barang)
    {
        $barang = \App\Models\Barang::find($id_barang);
        if (!$barang) {
            return response()->json(['message' => 'Barang not found'], 404);
        }

        $penitipan = $barang->penitipan;
        if (!$penitipan) {
            return response()->json(['message' => 'Penitipan not found for this barang'], 404);
        }

        $id_penitip = $penitipan->id_penitip;
        return response()->json(['id_penitip' => $id_penitip]);
    }

    // Filter barang berdasarkan kategori
    public function filterBarangPerKategori($id_kategori)
    {
        $barang = Barang::with(['kategori_barang', 'penitipan', 'donasi'])
            ->where('id_kategori', $id_kategori)
            ->where('status_barang', 'tersedia')
            ->get();

        if ($barang->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada barang pada kategori ini',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Barang pada kategori',
            'data' => $barang
        ]);
    }
    public function search(Request $request)
    {
        $request->validate([
            'keyword' => 'nullable|string|max:255'
        ]);

        $keyword = $request->input('keyword');

        if (empty($keyword)) {
          
             return response()->json([
                'message' => 'Masukkan kata kunci untuk pencarian.',
                'data' => []
            ], 200); 
        }

        $barang = Barang::with(['kategori_barang', 'penitipan']) 
            ->where('status_barang', 'Tersedia')
            ->where('nama_barang', 'LIKE', "%{$keyword}%")
            ->take(20) 
            ->get();

        if ($barang->isEmpty()) {
            return response()->json([
                'message' => 'Barang tidak ditemukan untuk kata kunci: ' . $keyword,
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Hasil pencarian untuk: ' . $keyword,
            'data' => $barang
        ], 200);
 
    }


    public function getBarangForTanggal()
{
    $barang = Barang::where('status_barang', 'Tersedia')
        ->with('penitipan')
        ->get()
        ->map(function ($item) {
            return [
                'id_barang' => $item->id_barang,
                'nama_barang' => $item->nama_barang,
                'harga' => $item->harga,
                'status_barang' => $item->status_barang,
                'tanggal_penitipan' => $item->penitipan?->tanggal_penitipan,
            ];
        });

    return response()->json([
        'message' => 'Daftar barang tersedia',
        'data' => $barang
    ]);
}



}