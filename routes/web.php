<?php

use App\Http\Controllers\Apar\InspeksiController;
use App\Http\Controllers\Apar\LaporanController;
use App\Http\Controllers\Apar\RiwayatInspeksiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GedungController;
use App\Http\Controllers\ItemCheckController;
use App\Http\Controllers\Laporan\AparController as LaporanAparController;
use App\Http\Controllers\MasterAparController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function(){
    return view('pages.menu');
})->name('main-menu')->middleware('auth');

Route::get('/login', [AuthController::class, 'loginForm'])->name('loginForm');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/**
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
});


Route::group(['prefix' => 'laporan', 'middleware' => [AdminMiddleware::class]], function() {
    Route::group(['prefix' => 'apar'], function(){
        Route::get('/rusak', [LaporanAparController::class, 'rusakIndex'])->name('laporan.apar.rusak-index');
        Route::post('/export-rusak', [LaporanAparController::class, 'exportExcelRusak'])->name('laporan.apar.rusak-export');
        
        Route::get('/refill', [LaporanAparController::class, 'refillIndex'])->name('laporan.apar.refill-index');
        Route::post('/export-refill', [LaporanAparController::class, 'exportExcelRefill'])->name('laporan.apar.refill-export');

        Route::get('/yearly', [LaporanAparController::class, 'yearlyIndex'])->name('laporan.apar.yearly-index');
    });
});

/**
 * Route for User
 */
Route::group(['prefix' => 'apar', 'middleware' => 'auth'], function() {
    Route::get('inspeksi', [InspeksiController::class, 'index'])->name('apar.index');
    Route::post('inspeksi', [InspeksiController::class, 'inspeksi'])->name('apar.inspeksi');

    Route::group(['prefix' => 'user/laporan'], function() {
        Route::get('yearly', [LaporanController::class, 'yearlyIndex'])->name('apar.user.laporan.yearly');
    });
});
