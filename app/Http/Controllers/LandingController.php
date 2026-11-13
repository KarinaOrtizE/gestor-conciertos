<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');

        $eventos = Evento::with('artista')
            ->when($q, function ($query) use ($q) {
                $query->where('titulo', 'like', "%{$q}%")
                      ->orWhereHas('artista', fn ($a) => $a->where('nombre', 'like', "%{$q}%"));
            })
            ->orderBy('fecha')
            ->take(6)
            ->get();

        return view('landing', compact('eventos', 'q'));
    }
}