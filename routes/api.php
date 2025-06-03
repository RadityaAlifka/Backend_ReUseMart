<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    PegawaiController,
    PenitipController,
    PembeliController,
    OrganisasiController,
    AlamatController,
    DiskusiController,
    RequestDonasiController,
    DonasiController,
    BarangController,
    HistoryController,
    ForgotPasswordController,
    ResetPasswordController,
    TransaksiController,
    PengirimanController,
    PenitipanController, 
    DetailTransaksiController,
    PengambilanController, 
    RatingController

};


// Routes untuk umum
Route::prefix('public')->group(function () {
    Route::get('/barang/bergaransi', [BarangController::class, 'barangBergaransi']);
    Route::get('/barang', [BarangController::class, 'index']); // Menampilkan barang yang bisa dibeli
    Route::get('/barang/{id}', [BarangController::class, 'show']); // Menampilkan detail per barang
    Route::get('/diskusi', [DiskusiController::class, 'index']); // Menampilkan semua diskusi
    Route::get('/rating/akumulasi-rating/{id}', [PenitipController::class, 'getAkumulasiRating']);
    Route::get('/barang/id-penitip/{id}', [BarangController::class, 'getIdPenitipByBarang']);
});



// Route untuk login
Route::post('/login', [AuthController::class, 'login']);

// Route untuk logout
Route::middleware(['auth:sanctum'])->post('/logout', [AuthController::class, 'logout']);

// Registrasi pembeli
Route::post('/register/pembeli', [PembeliController::class, 'register']);

// Registrasi organisasi
Route::post('/register/organisasi', [OrganisasiController::class, 'register']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/getPegawaiLogin', [PegawaiController::class, 'getPegawaiLogin']);
});
// Kirim link reset password ke email
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);

// Reset password menggunakan token dari email
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
// Routes untuk admin (Pegawai dengan jabatan admin)
Route::middleware(['auth:sanctum', 'checkRole:pegawai', 'checkJabatan:admin,owner'])->group(function () {
    Route::post('/pegawai', [PegawaiController::class, 'store']);
    Route::put('/pegawai/{id}', [PegawaiController::class, 'update']);
    Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy']);
    Route::get('/pegawai/{id}', [PegawaiController::class, 'show']);
    Route::get('/pegawai', [PegawaiController::class, 'index']);
    Route::get('/pegawai/search', [PegawaiController::class, 'search']);
    Route::get('/organisasi/{id}', [OrganisasiController::class, 'show']);
    Route::put('/organisasi/{id}', [OrganisasiController::class, 'update']);
    Route::delete('/organisasi/{id}', [OrganisasiController::class, 'destroy']);
    Route::get('/organisasi', [OrganisasiController::class, 'index']);
});

// Routes untuk CS (Pegawai dengan jabatan CS)
Route::middleware(['auth:sanctum', 'checkRole:pegawai', 'checkJabatan:cs'])->group(function () {
    Route::post('/cs/penitip', [PenitipController::class, 'store']);
    Route::get('/cs/penitip/{id}', [PenitipController::class, 'show']);
    Route::put('/cs/penitip/{id}', [PenitipController::class, 'update']);
    Route::delete('/penitip/{id}', [PenitipController::class, 'destroy']);
    Route::get('/cs/penitip', [PenitipController::class, 'index']);
    Route::get('/penitip/search', [PenitipController::class, 'search']);
    Route::put('/diskusi/{id}', [DiskusiController::class, 'update']);
    Route::get('/cs/diskusi', [DiskusiController::class, 'index']);
    Route::delete('/penitip/{id}', [PenitipController::class, 'de   stroy']);
    Route::get('/cs/pegawai', [PegawaiController::class,'getPegawaiCSFromToken']);
    
});// Route
// 
// 
// s untuk penjual
Route::middleware(['auth:sanctum', 'checkRole:penjual'])->group(function () {
    Route::get('/penitip', [PenitipController::class, 'index']); // Penjual bisa mengakses
    Route::get('/penitip/history', [HistoryController::class, 'penjualanHistoryPenitip']); // Penjual bisa mengakses
    Route::get('/penitip/profil', [PenitipController::class, 'profil']);
    Route::get('/penitip/search', [PenitipController::class, 'search']); // Penjual bisa mengakses
    Route::put('/penitip/{id}', [PenitipController::class, 'update']); // Penjual bisa mengakses
    Route::get('/diskusi', [DiskusiController::class, 'index']); // Penjual bisa mengakses
    Route::get('/barang-penitip', [PenitipController::class, 'getBarangPenitip']);
    Route::put('/perpanjang/{id}', [PenitipanController::class, 'extendPenitipan']);
    Route::post('/barang-penitip/pengambilan', [PengambilanController::class, 'addPengambilanFromPenitip']);
    Route::get('/barang-penitip/pengambilan/{id}', [PengambilanController::class, 'show']);
});

Route::get('/penitip/user/{user_id}', [PenitipController::class, 'getByUserId']);   

// Routes untuk pembeli
Route::middleware(['auth:sanctum', 'checkRole:pembeli'])->group(function () {
    Route::get('/pembeli/profil', [PembeliController::class, 'profil']);
    Route::put('/pembeli/profil/{id}', [PembeliController::class, 'update']);
    Route::get('/transaksi', [PembeliController::class, 'history']);
    Route::post('/alamat', [AlamatController::class, 'store']);
    Route::put('/alamat/{id}', [AlamatController::class, 'update']);
    Route::delete('/alamat/{id}', [AlamatController::class, 'destroy']);
    Route::get('/alamat', [AlamatController::class, 'index']);
    Route::get('/alamat/search', [AlamatController::class, 'search']);
    Route::post('/pembeli/diskusi', [DiskusiController::class, 'store']);
    Route::get('/pembeli/diskusi', [DiskusiController::class, 'index']);
    Route::get('/pembeli/history', [HistoryController::class, 'index']);
    Route::post('/pembeli/rating', [RatingController::class, 'store']);
    Route::get('/pembeli/user/{userId}', [PembeliController::class, 'getByUserId']);
    Route::get('/alamat/pembeli/{id_pembeli}', [AlamatController::class, 'getByPembeli']);
    Route::post('/detailTransaksis', [DetailTransaksiController::class, 'store']);
    Route::get('/detailTransaksis/{id}', [DetailTransaksiController::class, 'show']);
    Route::put('/detailTransaksis/{id}', [DetailTransaksiController::class, 'update']);
    Route::delete('/detailTransaksis/{id}', [DetailTransaksiController::class, 'destroy']);
    Route::get('/rating/barang/{id_barang}', [RatingController::class, 'showByBarang']);
    Route::get('/kurir/pegawai', [PegawaiController::class, 'getKurir']);

}); 



// Routes untuk organisasi
Route::middleware(['auth:sanctum', 'checkRole:organisasi,pegawai', 'checkJabatan:owner'])->group(function () {
    Route::post('/request-donasi', [RequestDonasiController::class, 'store']);
    Route::put('/request-donasi/{id}', [RequestDonasiController::class, 'update']);
    Route::delete('/request-donasi/{id}', [RequestDonasiController::class, 'destroy']);
    Route::get('/request-donasi', [RequestDonasiController::class, 'index']);
    Route::get('/request-donasi/{id}', [RequestDonasiController::class, 'show']);
    Route::get('/get-org', [RequestDonasiController::class, 'myRequests']);
    Route::get('/request-donasi/search', [RequestDonasiController::class, 'search']);
    Route::get('/get-organisasi/{id}', [OrganisasiController::class, 'show']);
    Route::get('/org/profil', [OrganisasiController::class, 'profil']);
    Route::get('/org/user/{user_id}', [OrganisasiController::class, 'getOrganisasiByUserId']);
});


Route::middleware(['auth:sanctum', 'checkRole:pegawai', 'checkJabatan:owner'])->group(function () {
    //Route::get('/request-donasi', [RequestDonasiController::class, 'index']);
    Route::get('/donasi/history', [HistoryController::class, 'donasiHistoryByOrganisasi']);
    Route::put('/donasi/donasikan-barang/{id}', [DonasiController::class, 'donasikanBarang']);
    //Route::get('/organisasi', [OrganisasiController::class, 'index']);
    Route::get('/barang/menunggu-donasi', [BarangController::class, 'barangMenungguDonasi']);
    Route::put('/donasi/{id}', [DonasiController::class, 'update']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/transaksis', [TransaksiController::class, 'index']);
    Route::post('/transaksis', [TransaksiController::class, 'store']);
    Route::get('/transaksis/{id}', [TransaksiController::class, 'show']);
    Route::put('/transaksis/{id}', [TransaksiController::class, 'update']);
    Route::delete('/transaksis/{id}', [TransaksiController::class, 'destroy']);
    Route::patch('/transaksis/{id}/verifikasi-bukti', [TransaksiController::class, 'verifikasiBukti']);

});

Route::middleware('auth:sanctum')->prefix('pengiriman')->group(function () {
    Route::get('/', [PengirimanController::class, 'index']);         
    Route::post('/', [PengirimanController::class, 'store']);        
    Route::get('/{id}', [PengirimanController::class, 'show']);      
    Route::put('/{id}', [PengirimanController::class, 'update']);    
    Route::delete('/{id}', [PengirimanController::class, 'destroy']);
});

// Routes untuk Pegawai Gudang
Route::middleware(['auth:sanctum', 'checkRole:pegawai', 'checkJabatan:pegawai gudang'])->group(function () {
    Route::post('/gudang/barang', [BarangController::class, 'store']);
    Route::put('/gudang/barang/{id}', [BarangController::class, 'update']);
    Route::delete('/gudang/barang/{id}', [BarangController::class, 'destroy']);
    Route::get('/gudang/barang', [BarangController::class, 'showAllBarang']);
    Route::put('/gudang/penitipan/{id}', [PenitipanController::class, 'update']);
    Route::get('/gudang/penitipan', [PenitipanController::class, 'index']);
    Route::get('/gudang/penitipan/{id}', [PenitipanController::class, 'show']);
    Route::get('/gudang/penitip/search/{id}', [PenitipController::class, 'show']);
    Route::get('/get-transaksi', [TransaksiController::class, 'getAllTransaksiWithBarang']);
    Route::get('/gudang/pengambilan', [PengambilanController::class, 'index']);
    Route::put('/pengambilan/konfirmasi/{id}', [PengambilanController::class, 'konfirmasiPengambilan']);
    Route::get('/get-transaksi/{id}', [TransaksiController::class, 'getTransaksiById']);

});



Route::get('/barang/check-stok/{id}', [BarangController::class, 'checkStokBarang']);

Route::get('/penitipan/{id}', [PenitipanController::class, 'getIdPenitip']);



