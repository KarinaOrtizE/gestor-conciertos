<?php

use App\Http\Controllers\ArtistaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SpotifyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Perfil (viene de Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // CRUD del proyecto: cualquier usuario consulta, solo el admin modifica
    Route::middleware('can:administrar')->group(function () {
        Route::get('/artistas/spotify/buscar', [SpotifyController::class, 'buscar'])->name('artistas.spotify');
        Route::resource('artistas', ArtistaController::class)->except(['index', 'show']);
        Route::resource('eventos', EventoController::class)->except(['index', 'show']);
    });
    Route::resource('artistas', ArtistaController::class)->only(['index', 'show']);
    Route::resource('eventos', EventoController::class)->only(['index', 'show']);

    // Favoritos de cada usuario
    Route::get('/favoritos', [FavoritoController::class, 'index'])->name('favoritos.index');
    Route::post('/eventos/{evento}/favorito', [FavoritoController::class, 'alternar'])->name('eventos.favorito');

    // Reportes en PDF
    Route::get('/reportes/eventos', [ReporteController::class, 'eventos'])->name('reportes.eventos');
    Route::get('/eventos/{evento}/pdf', [ReporteController::class, 'evento'])->name('eventos.pdf');
});

require __DIR__.'/auth.php';
