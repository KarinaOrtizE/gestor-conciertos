<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $eventos = Evento::with('artista')
            ->where('fecha', '>=', now())
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($busqueda) use ($q) {
                    $busqueda->whereContiene('titulo', $q)
                        ->orWhereHas('artista', fn ($a) => $a->whereContiene('nombre', $q));
                });
            })
            ->orderBy('fecha')
            ->take(6)
            ->get();

        return view('landing', compact('eventos', 'q'));
    }
}
