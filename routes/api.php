<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LokasiController;

Route::post('login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/showLokasi', [LokasiController::class, 'index']);
    Route::post('/lokasi', [LokasiController::class, 'store']);
    Route::get('/lokasi/{id}/show', [LokasiController::class, 'show']);
    Route::put('/lokasi/{id}/update', [LokasiController::class, 'update']);
    Route::delete('/lokasi/{id}/destroy', [LokasiController::class, 'destroy']);
});
