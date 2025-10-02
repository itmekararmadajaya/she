<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AparController;
use App\Http\Controllers\AuthController;
// use App\Http\Controllers\Api\ReparasiController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\KebutuhanController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// POST FOTO
Route::post('apar/inspeksi/{id}/upload-photo', [AparController::class, 'uploadPhoto']);

Route::get('/apar/inspection-years', [AparController::class, 'getInspectionYears']);
// LOGIN
Route::post('/login', [AuthController::class, 'apiLogin']);
// DAFTAR APAR
Route::get('/apar/all', [AparController::class, 'getAllApars']);

Route::get('/apar/inspeksi/get_by_code', [AparController::class, 'getByCode']);

Route::get('/apar/uninspected', [AparController::class, 'apiUninspected']);

// INSPEKSI PER AREA
Route::get('/apar/uninspected-by-area', [AparController::class, 'getUninspectedAparsByArea']);

// HITUNG JUMLAH YANG BELUM INSPEKSI PER AREA
Route::get('/apar/uninspected-areas', [AparController::class, 'getUninspectedAreaCounts']);

// Rute API khusus untuk aplikasi Flutter
Route::get('/apar/search', [AparController::class, 'searchApar']);
Route::post('/apar/inspeksi', [AparController::class, 'storeInspeksi']);
Route::middleware('auth:sanctum')->post('/apar/{id}/update', [AparController::class, 'update']);
Route::get('/apar/expiring', [AparController::class, 'getExpiringApars']);
Route::middleware('auth:sanctum')->post('/update-apar', [AparController::class, 'update']);

// API BARU UNTUK REPARASI
// Route::post('/submit-reparasi', [ReparasiController::class, 'submitReparasi']);
// Route::get('/apar-rusak/count', [ReparasiController::class, 'countAparRusak']);
// Route::get('/apar-rusak/{apar_id}', [ReparasiController::class, 'getDetailRusak']);
// Route::get('/apar-rusak', [ReparasiController::class, 'getAparRusak']); 

// API BARU UNTUK TRANSAKSI
Route::get('/transaksi/options', [TransaksiController::class, 'getAparOptions']);
Route::get('/transaksi/biaya', [TransaksiController::class, 'getBiaya']);
Route::post('/transaksis', [TransaksiController::class, 'storeApi']);

Route::middleware('auth:sanctum')->post('/apar/refill', [AparController::class, 'refill']);
Route::get('/apar/check-inspection', [AparController::class, 'checkInspection']);

Route::get('/vendor/harga-kebutuhan', [AparController::class, 'getVendorHargaKebutuhan']);
Route::get('/apar/harga-isi-ulang', [AparController::class, 'getHargaIsiUlang']);

// API BARU UNTUK MENGHITUNG STATUS APAR
Route::get('/apar-status-counts', [AparController::class, 'getAparStatusCounts']);

// VENDORS
Log::info('API vendors dipanggil');
Route::get('/vendors', [VendorController::class, 'index']);
Route::post('/vendors', [VendorController::class, 'store']);
Route::get('/vendors/{id}', [VendorController::class, 'apiShow']);
Route::put('/vendors/{id}', [VendorController::class, 'update']);
Route::delete('/vendors/{id}', [VendorController::class, 'destroy']);
Route::get('/vendors', [VendorController::class, 'apiIndex']);
Route::get('/vendors/{vendor}/kebutuhans', [HargaKebutuhanController::class, 'getKebutuhanByVendor']);

//RUSAK
Route::get('/apar/rusak', [AparController::class, 'getRusakApars']);

// KEBUTUHAN
Route::get('/kebutuhans', [KebutuhanController::class, 'apiIndex']);

// PENGGUNAAN
Route::post('/apar/penggunaan', [AparController::class, 'storePenggunaan']);

// Rute API baru untuk menghitung status APAR secara konsisten dengan dashboard
Route::get('/apar-status-counts-consistent', [AparController::class, 'getAparStatusCountsConsistent']);