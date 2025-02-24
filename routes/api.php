<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\LokasiController;

Route::post('login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/lokasi/index', [LokasiController::class, 'index']);
    Route::post('/lokasi', [LokasiController::class, 'store']);
    Route::get('/lokasi/{id}/show', [LokasiController::class, 'show']);
    Route::put('/lokasi/{id}/update', [LokasiController::class, 'update']);
    Route::delete('/lokasi/{id}/destroy', [LokasiController::class, 'destroy']);
});

use App\Http\Controllers\KodeBarangController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/kode-barang/index', [KodeBarangController::class, 'index']);
    Route::get('/kode-barang/{id}/show', [KodeBarangController::class, 'show']);
    Route::post('/kode-barang', [KodeBarangController::class, 'store']);
    Route::put('/kode-barang/{id}/update', [KodeBarangController::class, 'update']);
    Route::delete('/kode-barang/{id}/destroy', [KodeBarangController::class, 'destroy']);
    Route::post('/kode-barang/import', [KodeBarangController::class, 'importFile']);
});
