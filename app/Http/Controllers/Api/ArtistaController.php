<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArtistaRequest;
use App\Http\Resources\ArtistaResource;
use App\Models\Artista;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ArtistaController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $artistas = Artista::withCount('eventos')
            ->when($filtros['q'] ?? null, fn ($query, $q) => $query->whereContiene('nombre', $q))
            ->orderBy('nombre')
            ->paginate($filtros['por_pagina'] ?? 15)
            ->withQueryString();

        return ArtistaResource::collection($artistas);
    }

    public function store(ArtistaRequest $request)
    {
        return (new ArtistaResource(Artista::create($request->validated())))
            ->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Artista $artista)
    {
        return new ArtistaResource($artista->load(['eventos' => fn ($q) => $q->orderBy('fecha')]));
    }

    public function update(ArtistaRequest $request, Artista $artista)
    {
        $artista->update($request->validated());

        return new ArtistaResource($artista);
    }

    public function destroy(Artista $artista)
    {
        $artista->delete();

        return response()->noContent();
    }
}
