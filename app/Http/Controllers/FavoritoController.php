<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoritoController extends Controller
{
    public function index(Request $request): View
    {
        $favoritos = $request->user()->eventosFavoritos()->with('artista')->orderBy('fecha')->get();
        [$proximos, $pasados] = $favoritos->partition(fn (Evento $evento) => $evento->fecha->isFuture());

        return view('favoritos.index', [
            'proximos' => $proximos,
            'pasados' => $pasados->reverse(),
        ]);
    }

    public function alternar(Request $request, Evento $evento)
    {
        $cambios = $request->user()->eventosFavoritos()->toggle($evento);
        $favorito = $cambios['attached'] !== [];

        // El botón usa fetch y espera JSON; sin JavaScript el formulario recarga la página.
        if ($request->expectsJson()) {
            return response()->json(['favorito' => $favorito]);
        }

        return back()->with('ok', $favorito ? 'Evento agregado a favoritos.' : 'Evento quitado de favoritos.');
    }
}
