<?php

use App\Http\Controllers\ArtistaController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Perfil (viene de Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // CRUD del proyecto: cualquier usuario consulta, solo el admin modifica
    Route::middleware('can:administrar')->group(function () {
        Route::resource('artistas', ArtistaController::class)->except(['index', 'show']);
        Route::resource('eventos', EventoController::class)->except(['index', 'show']);
    });
    Route::resource('artistas', ArtistaController::class)->only(['index', 'show']);
    Route::resource('eventos', EventoController::class)->only(['index', 'show']);
});

require __DIR__.'/auth.php';