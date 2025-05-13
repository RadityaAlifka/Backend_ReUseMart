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
    DonasiController
};

// Routes untuk umum
Route::prefix('public')->group(function () {
    Route::get('/barang', [BarangController::class, 'index']); // Menampilkan barang yang bisa dibeli
    Route::get('/barang/{id}', [BarangController::class, 'show']); // Menampilkan detail per barang
    Route::get('/barang/{id}/garansi', [BarangController::class, 'checkGaransi']); // Memeriksa status garansi
});

// Route untuk login
Route::post('/login', [AuthController::class, 'login']);

// Registrasi pembeli
Route::post('/register/pembeli', [PembeliController::class, 'register']);

// Registrasi organisasi
Route::post('/register/organisasi', [OrganisasiController::class, 'register']);

// Routes untuk admin (Pegawai dengan jabatan admin)
Route::middleware(['auth:sanctum', 'checkRole:pegawai', 'checkJabatan:admin'])->group(function () {
    Route::post('/pegawai', [PegawaiController::class, 'store']); // Menambah data pegawai
    Route::put('/pegawai/{id}', [PegawaiController::class, 'update']); // Mengubah data pegawai
    Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy']); // Menghapus data pegawai
    Route::get('/pegawai', [PegawaiController::class, 'index']); // Menampilkan data pegawai
    Route::get('/pegawai/search', [PegawaiController::class, 'search']); // Mencari data pegawai
    Route::put('/organisasi/{id}', [OrganisasiController::class, 'update']); // Mengubah organisasi
    Route::delete('/organisasi/{id}', [OrganisasiController::class, 'destroy']); // Menghapus organisasi
    Route::get('/organisasi', [OrganisasiController::class, 'index']); // Menampilkan organisasi
    Route::get('/organisasi/search', [OrganisasiController::class, 'search']); // Mencari organisasi
});

// Routes untuk CS (Pegawai dengan jabatan CS)
Route::middleware(['auth:sanctum', 'checkRole:pegawai', 'checkJabatan:cs'])->group(function () {
    Route::post('/penitip', [PenitipController::class, 'store']); // Menambah data penitip
    Route::put('/penitip/{id}', [PenitipController::class, 'update']); // Mengubah data penitip
    Route::delete('/penitip/{id}', [PenitipController::class, 'destroy']); // Menghapus data penitip
    Route::get('/penitip', [PenitipController::class, 'index']); // Menampilkan data penitip
    Route::get('/penitip/search', [PenitipController::class, 'search']); // Mencari data penitip
    Route::post('/diskusi', [DiskusiController::class, 'store']); // Menambah pesan diskusi
    Route::get('/diskusi', [DiskusiController::class, 'index']); // Menampilkan pesan diskusi
});

// Routes untuk pembeli
Route::middleware(['auth:sanctum', 'checkRole:pembeli'])->group(function () {
    Route::get('/profil', [PembeliController::class, 'show']); // Menampilkan profil pembeli
    Route::get('/transaksi', [PembeliController::class, 'history']); // Menampilkan history transaksi pembelian
    Route::post('/alamat', [AlamatController::class, 'store']); // Menambah alamat
    Route::put('/alamat/{id}', [AlamatController::class, 'update']); // Mengubah alamat
    Route::delete('/alamat/{id}', [AlamatController::class, 'destroy']); // Menghapus alamat
    Route::get('/alamat', [AlamatController::class, 'index']); // Menampilkan alamat
    Route::get('/alamat/search', [AlamatController::class, 'search']); // Mencari alamat
    Route::post('/diskusi', [DiskusiController::class, 'store']); // Menambah pesan diskusi
    Route::get('/diskusi', [DiskusiController::class, 'index']); // Menampilkan pesan diskusi
});

// Routes untuk organisasi
Route::middleware(['auth:sanctum', 'checkRole:organisasi'])->group(function () {
    Route::post('/request-donasi', [RequestDonasiController::class, 'store']); // Menambah data request donasi
    Route::put('/request-donasi/{id}', [RequestDonasiController::class, 'update']); // Mengubah data request donasi
    Route::delete('/request-donasi/{id}', [RequestDonasiController::class, 'destroy']); // Menghapus data request donasi
    Route::get('/request-donasi', [RequestDonasiController::class, 'index']); // Menampilkan data request donasi
    Route::get('/request-donasi/search', [RequestDonasiController::class, 'search']); // Mencari data request donasi
});

// Routes untuk owner
Route::middleware(['auth:sanctum', 'checkRole:pegawai', 'checkJabatan:admin'])->group(function () {
    Route::get('/request-donasi', [RequestDonasiController::class, 'index']); // Menampilkan daftar request donasi
    Route::get('/donasi/history', [DonasiController::class, 'index']); // Menampilkan history donasi ke organisasi tertentu
    Route::post('/donasi', [DonasiController::class, 'store']); // Mengalokasikan barang ke organisasi
    Route::put('/donasi/{id}', [DonasiController::class, 'update']); // Mengupdate tanggal donasi, nama penerima donasi, dan status barang
});