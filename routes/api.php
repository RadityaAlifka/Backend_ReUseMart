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

Route::prefix('public')->group(function () {
    Route::get('/barang', [BarangController::class, 'index']); 
    Route::get('/barang/{id}', [BarangController::class, 'show']); 
    Route::get('/barang/{id}/garansi', [BarangController::class, 'checkGaransi']);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'checkRole:pegawai', 'checkJabatan:admin'])->group(function () {
    Route::post('/pegawai', [PegawaiController::class, 'store']);
    Route::put('/pegawai/{id}', [PegawaiController::class, 'update']);
    Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy']);
    Route::get('/pegawai', [PegawaiController::class, 'index']);
    Route::get('/pegawai/search', [PegawaiController::class, 'search']);
    Route::put('/organisasi/{id}', [OrganisasiController::class, 'update']);
    Route::delete('/organisasi/{id}', [OrganisasiController::class, 'destroy']);
    Route::get('/organisasi', [OrganisasiController::class, 'index']);
    Route::get('/organisasi/search', [OrganisasiController::class, 'search']);
});

Route::middleware(['auth:sanctum', 'checkRole:pegawai', 'checkJabatan:cs'])->group(function () {
    Route::post('/penitip', [PenitipController::class, 'store']);
    Route::put('/penitip/{id}', [PenitipController::class, 'update']);
    Route::delete('/penitip/{id}', [PenitipController::class, 'destroy']);
    Route::get('/penitip', [PenitipController::class, 'index']);
    Route::get('/penitip/search', [PenitipController::class, 'search']);
    Route::post('/diskusi', [DiskusiController::class, 'store']);
    Route::get('/diskusi', [DiskusiController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'checkRole:pembeli'])->group(function () {
    Route::get('/profil', [PembeliController::class, 'show']);
    Route::get('/transaksi', [PembeliController::class, 'history']);
    Route::post('/alamat', [AlamatController::class, 'store']);
    Route::put('/alamat/{id}', [AlamatController::class, 'update']);
    Route::delete('/alamat/{id}', [AlamatController::class, 'destroy']);
    Route::get('/alamat', [AlamatController::class, 'index']);
    Route::get('/alamat/search', [AlamatController::class, 'search']);
    Route::post('/diskusi', [DiskusiController::class, 'store']);
    Route::get('/diskusi', [DiskusiController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'checkRole:organisasi'])->group(function () {
    Route::post('/request-donasi', [RequestDonasiController::class, 'store']);
    Route::put('/request-donasi/{id}', [RequestDonasiController::class, 'update']);
    Route::delete('/request-donasi/{id}', [RequestDonasiController::class, 'destroy']);
    Route::get('/request-donasi', [RequestDonasiController::class, 'index']);
    Route::get('/request-donasi/search', [RequestDonasiController::class, 'search']);
});

Route::middleware(['auth:sanctum', 'checkRole:owner'])->group(function () {
    Route::get('/request-donasi', [RequestDonasiController::class, 'index']);
    Route::get('/donasi/history', [DonasiController::class, 'index']);
    Route::post('/donasi', [DonasiController::class, 'store']);
    Route::put('/donasi/{id}', [DonasiController::class, 'update']);
});
