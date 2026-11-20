<?php

namespace App\Http\Controllers;

use App\Models\Artista;
use App\Models\Evento;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $genero = in_array($request->query('genero'), Evento::GENEROS, true) ? $request->query('genero') : null;
        $proximos = fn () => Evento::where('fecha', '>=', now());

        $eventos = $proximos()->with('artista')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($busqueda) use ($q) {
                    $busqueda->whereContiene('titulo', $q)
                        ->orWhereHas('artista', fn ($a) => $a->whereContiene('nombre', $q));
                });
            })
            ->when($genero, fn ($query) => $query->where('genero', $genero))
            ->orderBy('fecha')
            ->take(6)
            ->get();

        $generos = $proximos()->distinct()->orderBy('genero')->pluck('genero');

        $cifras = [
            'conciertos' => $proximos()->count(),
            'artistas' => Artista::whereHas('eventos', fn ($e) => $e->where('fecha', '>=', now()))->count(),
            'ciudades' => $proximos()->distinct()->count('ciudad'),
        ];

        // Artistas con más conciertos próximos.
        $artistas = Artista::whereHas('eventos', fn ($e) => $e->where('fecha', '>=', now()))
            ->withCount(['eventos as proximos_count' => fn ($e) => $e->where('fecha', '>=', now())])
            ->orderByDesc('proximos_count')
            ->orderBy('nombre')
            ->take(6)
            ->get();

        $favoritos = $request->user()?->eventosFavoritos()->pluck('eventos.id') ?? collect();

        return view('landing', compact('eventos', 'q', 'genero', 'generos', 'cifras', 'artistas', 'favoritos'));
    }
}
