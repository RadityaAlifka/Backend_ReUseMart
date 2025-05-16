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
    HistoryController
};

// Routes untuk umum
Route::prefix('public')->group(function () {
    Route::get('/barang', [BarangController::class, 'index']); // Menampilkan barang yang bisa dibeli
    Route::get('/barang/{id}', [BarangController::class, 'show']); // Menampilkan detail per barang
    Route::get('/barang/bergaransi', [BarangController::class, 'barangBergaransi']);
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
    Route::get('/organisasi/search', [OrganisasiController::class, 'search']);
});

// Routes untuk CS (Pegawai dengan jabatan CS)
Route::middleware(['auth:sanctum', 'checkRole:pegawai', 'checkJabatan:cs'])->group(function () {
    Route::post('/cs/penitip', [PenitipController::class, 'store']);
    Route::get('/cs/penitip/{id}', [PenitipController::class, 'show']);
    Route::put('/cs/penitip/{id}', [PenitipController::class, 'update']);
    Route::delete('/penitip/{id}', [PenitipController::class, 'destroy']);
    Route::get('/cs/penitip', [PenitipController::class, 'index']);
    Route::get('/penitip/search', [PenitipController::class, 'search']);
    Route::post('/diskusi', [DiskusiController::class, 'store']);
    Route::get('/diskusi', [DiskusiController::class, 'index']);
    Route::delete('/penitip/{id}', [PenitipController::class, 'destroy']); // Penjual bisa mengakses
});// Routes untuk penjual
Route::middleware(['auth:sanctum', 'checkRole:penjual'])->group(function () {
    Route::get('/penitip', [PenitipController::class, 'index']); // Penjual bisa mengakses
    Route::get('/penitip/history', [HistoryController::class, 'penjualanHistoryPenitip']); // Penjual bisa mengakses
    Route::get('/penitip/profil', [PenitipController::class, 'profil']);
    Route::get('/penitip/search', [PenitipController::class, 'search']); // Penjual bisa mengakses
    Route::put('/penitip/{id}', [PenitipController::class, 'update']); // Penjual bisa mengakses
    Route::get('/diskusi', [DiskusiController::class, 'index']); // Penjual bisa mengakses
});

// Routes untuk pembeli
Route::middleware(['auth:sanctum', 'checkRole:pembeli'])->group(function () {
    Route::get('/pembeli/profil', [PembeliController::class, 'profil']);
    Route::get('/transaksi', [PembeliController::class, 'history']);
    Route::post('/alamat', [AlamatController::class, 'store']);
    Route::put('/alamat/{id}', [AlamatController::class, 'update']);
    Route::delete('/alamat/{id}', [AlamatController::class, 'destroy']);
    Route::get('/alamat', [AlamatController::class, 'index']);
    Route::get('/alamat/search', [AlamatController::class, 'search']);
    Route::post('/pembeli/diskusi', [DiskusiController::class, 'store']);
    Route::get('/pembeli/diskusi', [DiskusiController::class, 'index']);
    Route::get('/pembeli/history', [HistoryController::class, 'index']);
});

// Routes untuk organisasi
Route::middleware(['auth:sanctum', 'checkRole:organisasi,pegawai', 'checkJabatan:owner'])->group(function () {
    Route::post('/request-donasi', [RequestDonasiController::class, 'store']);
    Route::put('/request-donasi/{id}', [RequestDonasiController::class, 'update']);
    Route::delete('/request-donasi/{id}', [RequestDonasiController::class, 'destroy']);
    Route::get('/request-donasi', [RequestDonasiController::class, 'index']);
    Route::get('/request-donasi/search', [RequestDonasiController::class, 'search']);
    Route::get('/get-organisasi/{id}', [OrganisasiController::class, 'show']);
});

// Routes untuk admin
Route::middleware(['auth:sanctum', 'checkRole:pegawai', 'checkJabatan:owner'])->group(function () {
    Route::get('/request-donasi', [RequestDonasiController::class, 'index']);
    Route::get('/donasi/history', [HistoryController::class, 'donasiHistoryByOrganisasi']);
    Route::put('/donasi/donasikan-barang/{id}', [DonasiController::class, 'donasikanBarang']);
    Route::get('/organisasi', [OrganisasiController::class, 'index']);
    Route::get('/barang/menunggu-donasi', [BarangController::class, 'barangMenungguDonasi']);
    Route::put('/donasi/{id}', [DonasiController::class, 'update']);
});

//tambahan
Route::middleware(['auth:sanctum', 'checkRole:organisasi'])->group(function () {
    Route::post('/post-org', [RequestDonasiController::class, 'store']);
    Route::put('/put-org/{id}', [RequestDonasiController::class, 'update']);
    Route::delete('/request-donasi/{id}', [RequestDonasiController::class, 'destroy']);
    Route::get('/get-org', [RequestDonasiController::class, 'index']);
    Route::get('/request-donasi/search', [RequestDonasiController::class, 'search']);
    Route::get('/get-organisasi/{id}', [OrganisasiController::class, 'show']);
});
