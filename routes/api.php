<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

use App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'getUserInfo']);
    Route::put('/user/change-name', [UserController::class, 'changeName']);
    Route::put('/user/change-password', [UserController::class, 'changePassword']);
});

use App\Http\Controllers\LokasiController;


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/lokasi/index', [LokasiController::class, 'index']);
    Route::post('/lokasi', [LokasiController::class, 'store']);
    Route::get('/lokasi/{id}/show', [LokasiController::class, 'show']);
    Route::put('/lokasi/{id}', [LokasiController::class, 'update']);
    Route::delete('/lokasi/{id}', [LokasiController::class, 'destroy']);
    Route::get('/lokasi/total', [LokasiController::class, 'totalLokasi']);
});

use App\Http\Controllers\KodeBarangController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/kode-barang/index', [KodeBarangController::class, 'index']);
    Route::get('/kode-barang/{id}/show', [KodeBarangController::class, 'show']);
    Route::post('/kode-barang', [KodeBarangController::class, 'store']);
    Route::put('/kode-barang/{id}', [KodeBarangController::class, 'update']);
    Route::delete('/kode-barang/{id}', [KodeBarangController::class, 'destroy']);
    Route::post('/kode-barang/import', [KodeBarangController::class, 'importFile']);
    Route::get('/kode-barang/total', [KodeBarangController::class, 'totalKodeBarang']);
});

use App\Http\Controllers\AssetItemController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/aset/index', [AssetItemController::class, 'index']);
    Route::get('/aset/{id}/show', [AssetItemController::class, 'show']);
    Route::post('/aset', [AssetItemController::class, 'store']);
    Route::put('/aset/{id}', [AssetItemController::class, 'update']);
    Route::delete('/aset/{id}', [AssetItemController::class, 'destroy']);
    Route::get('/aset/total', [AssetItemController::class, 'totalAssets']);
    Route::get('/aset/total-harga/current', [AssetItemController::class, 'totalHargaCurrentMonthYear']);
    Route::get('/aset/total-harga/{year}', [AssetItemController::class, 'totalHargaByYear']);
});

use App\Http\Controllers\BalanceController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/balance', [BalanceController::class, 'show']);
    Route::post('/balance/update', [BalanceController::class, 'update']);
    Route::post('/balance/add', action: [BalanceController::class, 'add']);
});

use App\Http\Controllers\KodeRekeningController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/kode-rekening/import', [KodeRekeningController::class, 'import']);
    Route::get('/kode-rekening/index', [KodeRekeningController::class, 'index']);
    Route::put('/kode-rekening/{id}', [KodeRekeningController::class, 'update']);
    Route::delete('/kode-rekening/{id}', [KodeRekeningController::class, 'destroy']);
});

use App\Http\Controllers\BhpItemController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/bhp/import', [BhpItemController::class, 'import']);
    Route::get('/bhp/index', [BhpItemController::class, 'index']);
    Route::post('/bhp/remove/{id}', [BhpItemController::class, 'remove']);
    Route::post('/bhp/undo-remove/{id}', [BhpItemController::class, 'undoRemoval']);
    Route::get('/bhp/riwayat', [BhpItemController::class, 'getRemovalLogs']);
});