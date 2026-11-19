<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventoRequest;
use App\Http\Resources\EventoResource;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EventoController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'genero' => ['nullable', 'string'],
            'ciudad' => ['nullable', 'string'],
            'artista_id' => ['nullable', 'integer'],
            'proximos' => ['nullable', 'boolean'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $eventos = Evento::with('artista')
            ->when($filtros['q'] ?? null, fn ($query, $q) => $query->whereContiene('titulo', $q))
            ->when($filtros['genero'] ?? null, fn ($query, $genero) => $query->where('genero', $genero))
            ->when($filtros['ciudad'] ?? null, fn ($query, $ciudad) => $query->where('ciudad', $ciudad))
            ->when($filtros['artista_id'] ?? null, fn ($query, $id) => $query->where('artista_id', $id))
            ->when($request->boolean('proximos'), fn ($query) => $query->where('fecha', '>=', now()))
            ->orderBy('fecha')
            ->paginate($filtros['por_pagina'] ?? 15)
            ->withQueryString();

        return EventoResource::collection($eventos);
    }

    public function store(EventoRequest $request)
    {
        $evento = Evento::create($request->datos())->load('artista');

        return (new EventoResource($evento))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Evento $evento)
    {
        return new EventoResource($evento->load('artista'));
    }

    public function update(EventoRequest $request, Evento $evento)
    {
        $evento->update($request->datos());

        return new EventoResource($evento->load('artista'));
    }

    public function destroy(Evento $evento)
    {
        $evento->delete();

        return response()->noContent();
    }
}
