<?php

use App\Http\Controllers\LaporanBillingController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RincianRawatInapControllers;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Lindungi route /home dengan middleware
Route::middleware(['auth.session'])->group(function () {
    Route::get('/home', [RincianRawatInapControllers::class, 'RincianRawatInap'])->name('rincian.rawat.inap');
});

// Lindungi route /home dengan middleware
Route::middleware(['auth.session'])->group(function () {
    Route::get('/admin', [RincianRawatInapControllers::class, 'RincianRawatInapAdmin'])->name('rincian.rawat.inap.admin');
});

// Route::get('/home', [RincianRawatInapControllers::class, 'RincianRawatInapPrint'])->name('rincian.rawat.inap');
Route::get('/rincian-rawat-inap/print', [RincianRawatInapControllers::class, 'print'])->name('rincian.rawatinap.print');
Route::get('/rincian-rawat-inap/export', [RincianRawatInapControllers::class, 'exportExcel'])->name('rincian.rawatinap.export');

Route::get('/LaporanBilling', [LaporanBillingController::class, 'Billing']);

