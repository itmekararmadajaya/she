<?php

use App\Http\Controllers\Apar\InspeksiController;
use App\Http\Controllers\Apar\LaporanController;
use App\Http\Controllers\Apar\RiwayatInspeksiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GedungController;
use App\Http\Controllers\ItemCheckController;
use App\Http\Controllers\AparController as LaporanAparController;
use App\Http\Controllers\MasterAparController;
use App\Http\Controllers\SendMailController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AparController;
use App\Http\Controllers\RouteTrackerController;
use App\Http\Controllers\PublicAparController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\HargaKebutuhanController;
use App\Http\Controllers\KebutuhanController;
use Illuminate\Http\Request;
use App\Models\Kebutuhan;
use App\Models\HargaKebutuhan;

Route::redirect('/', '/login')->name('redirect-login');
Route::get('/test-log', function () {
    Log::debug('Test log berhasil di ' . now());
    return 'Log berhasil ditulis, cek storage/logs/laravel.log';
});

// HALAMAN MENU
// Route::get('/', function(){
//     return view('pages.menu');
// })->name('main-menu')->middleware('auth');

// API
Route::get('/route-tracker', [RouteTrackerController::class, 'showRoutes'])->name('route-tracker');
Route::get('/api/kebutuhan-by-vendor/{vendorId}', [MasterAparController::class, 'getKebutuhanByVendor']);

Route::get('/apar/qrcode/{id}', [MasterAparController::class, 'showQrCode'])->name('apar.qrcode.show');

Route::get('/master-apar/kebutuhan-by-vendor/{vendorId}', [MasterAparController::class, 'getKebutuhanByVendor']);

// KLIK BELUM INSPEKSI
Route::get('/apar/uninspected', [AparController::class, 'uninspected'])->name('apar.uninspected');

// INSPEKSI BULANAN
Route::get('/apar-inspections/monthly', [AparController::class, 'getMonthlyInspections'])->name('inspections.monthly');
Route::get('/apar/uninspected', [AparController::class, 'getUninspectedApars'])->name('apar.uninspected');

// DOWNLOAD QR Code
Route::get('/apar/download-qr/{id}', [MasterAparController::class, 'downloadQr'])->name('apar.download.qr');
Route::get('/apar/qr/{kode}', [MasterAparController::class, 'handleQrScan'])->name('apar.handle.qr');
Route::get('/inspeksi/form/{apar_id}', [InspeksiController::class, 'showForm'])->name('inspeksi.form');
Route::get('/public/apar/{kode}/history', [PublicAparController::class, 'showPublicHistory'])->name('public.apar.history');

// DOWNLOAD EXCEL
Route::get('riwayat-inspeksi/export', [RiwayatInspeksiController::class, 'export'])->name('riwayat-inspeksi.export');

Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'weblogin'])->name('weblogin');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// KEUANGAN DAN VENDORS
Route::get('/transaksi/get-biaya', [App\Http\Controllers\TransaksiController::class, 'getBiaya'])->name('transaksi.getBiaya');
Route::resource('vendor', VendorController::class);
Route::resource('transaksi', TransaksiController::class); // Menggunakan TransaksiController dan URI 'transaksi'
Route::get('/transaksi/export/excel', [TransaksiController::class, 'exportExcel'])->name('transaksi.export.excel');
Route::resource('kebutuhan', KebutuhanController::class);
Route::resource('harga_kebutuhan', HargaKebutuhanController::class);
Route::get('/get-kebutuhans-by-vendor/{vendor_id}', function ($vendor_id) {
    $kebutuhans = HargaKebutuhan::where('vendor_id', $vendor_id)
                                ->with('kebutuhan') // Nama relasi harus `kebutuhan`
                                ->get();
    
    return response()->json($kebutuhans);
});
Route::get('/get-kebutuhans-by-vendor/{vendor_id}', function ($vendor_id) {
    $kebutuhans = HargaKebutuhan::where('vendor_id', $vendor_id)
                                ->with('kebutuhan')
                                ->get();
    
    return response()->json($kebutuhans);
});

// // Rute ini akan menangani permintaan dari aplikasi Flutter
// Route::get('/apar/search', [AparController::class, 'searchApar']);
// Route::post('/apar/inspeksi', [AparController::class, 'storeInspeksi']);

/*
 * Route for Admin Dashboard
 */
Route::group(['middleware' => [AdminMiddleware::class]], function(){
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('/users', UserController::class);

    Route::resource('/gedung', GedungController::class);

    Route::post('/item-check/restore/{itemCheck}', [ItemCheckController::class, 'restore'])->name('item-check.restore');
    Route::resource('/item-check', ItemCheckController::class);
    Route::post('/master-apar/restore/{apar}', [MasterAparController::class, 'restore'])->name('master-apar.restore');
    Route::get('/generate-qr/{apar}', [MasterAparController::class, 'generateQr'])->name('master-apar.generate-qr');
    Route::resource('/master-apar', MasterAparController::class);

    Route::get('/riwayat-inspeksi', [RiwayatInspeksiController::class, 'index'])->name('riwayat-inspeksi')->middleware('auth');
    Route::post('/riwayat-inspeksi/recycle', [RiwayatInspeksiController::class, 'recycle'])->name('riwayat-inspeksi.recycle')->middleware('auth');
    Route::put('/riwayat-inspeksi/{detail}', [RiwayatInspeksiController::class, 'updateItemStatus'])->name('riwayat-inspeksi.repair')->middleware('auth');
    Route::get('/get-ukuran/{kode}', [MasterAparController::class, 'getUkuran'])->name('get-ukuran');
});


Route::group(['prefix' => 'laporan', 'middleware' => [AdminMiddleware::class]], function() {
    Route::group(['prefix' => 'apar'], function(){
        
        // Perbaiki rute ini agar menunjuk ke MasterAparController
        Route::get('/refill', [MasterAparController::class, 'refillReport'])->name('laporan.apar.refill-index');
        Route::post('/export-refill', [LaporanAparController::class, 'exportExcelRefill'])->name('laporan.apar.refill-export');

        Route::get('/yearly', [LaporanAparController::class, 'yearlyIndex'])->name('laporan.apar.yearly-index');
    });
});

/**
 * Route for User
 */
// Route::group(['prefix' => 'apar', 'middleware' => 'auth'], function() {
    Route::get('inspeksi', [InspeksiController::class, 'index'])->name('apar.index');
//     Route::post('inspeksi', [InspeksiController::class, 'inspeksi'])->name('apar.inspeksi');
// });

/**
 * Route for quick menu
 */
Route::group(['prefix' => 'user/laporan'], function() {
    Route::get('yearly', [LaporanController::class, 'yearlyIndex'])->name('apar.user.laporan.yearly');
});

/**
 * Send mail notification
 */
Route::group(['prefix' => 'notification'], function(){
    Route::get('apar-refill', [SendMailController::class, 'aparRefill'])->name('notification.apar-refill');
});
