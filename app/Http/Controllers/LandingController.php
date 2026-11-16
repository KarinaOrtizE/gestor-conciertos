<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        // Se escapan % y _ para buscarlos como texto; '!' funciona como escape en SQLite, MySQL y PostgreSQL.
        $patron = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q).'%';

        $eventos = Evento::with('artista')
            ->where('fecha', '>=', now())
            ->when($q !== '', function ($query) use ($patron) {
                $query->where(function ($busqueda) use ($patron) {
                    $busqueda->whereRaw("titulo like ? escape '!'", [$patron])
                        ->orWhereHas('artista', fn ($a) => $a->whereRaw("nombre like ? escape '!'", [$patron]));
                });
            })
            ->orderBy('fecha')
            ->take(6)
            ->get();

        return view('landing', compact('eventos', 'q'));
    }
}
