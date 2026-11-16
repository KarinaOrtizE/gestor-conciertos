<?php

use App\Http\Controllers\Api\ArtistaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventoController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('api.login');

// Consulta pública
Route::apiResource('eventos', EventoController::class)->only(['index', 'show'])->names('api.eventos');
Route::apiResource('artistas', ArtistaController::class)->only(['index', 'show'])->names('api.artistas');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Gestión: solo con token de un admin
    Route::middleware('can:administrar')->group(function () {
        Route::apiResource('eventos', EventoController::class)->except(['index', 'show'])->names('api.eventos');
        Route::apiResource('artistas', ArtistaController::class)->except(['index', 'show'])->names('api.artistas');
    });
});
