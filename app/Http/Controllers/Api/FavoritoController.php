<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventoResource;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FavoritoController extends Controller
{
    public function index(Request $request)
    {
        return EventoResource::collection(
            $request->user()->eventosFavoritos()->with('artista')->orderBy('fecha')->get()
        );
    }

    public function update(Request $request, Evento $evento): Response
    {
        $request->user()->eventosFavoritos()->syncWithoutDetaching([$evento->id]);

        return response()->noContent();
    }

    public function destroy(Request $request, Evento $evento): Response
    {
        $request->user()->eventosFavoritos()->detach($evento);

        return response()->noContent();
    }
}
