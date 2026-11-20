<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArtistaRequest;
use App\Models\Artista;

class ArtistaController extends Controller
{
    public function index()
    {
        $artistas = Artista::withCount('eventos')->orderBy('nombre')->get();

        return view('artista.listado', compact('artistas'));
    }

    public function create()
    {
        return view('artista.formCrear');
    }

    public function store(ArtistaRequest $request)
    {
        Artista::create($request->validated());

        return redirect()->route('artistas.index')->with('ok', 'Artista creado correctamente.');
    }

    public function show(Artista $artista)
    {
        $artista->load(['eventos' => fn ($eventos) => $eventos->orderBy('fecha')]);

        return view('artista.detalle', compact('artista'));
    }

    public function edit(Artista $artista)
    {
        return view('artista.formEditar', compact('artista'));
    }

    public function update(ArtistaRequest $request, Artista $artista)
    {
        $artista->update($request->validated());

        return redirect()->route('artistas.index')->with('ok', 'Artista actualizado correctamente.');
    }

    public function destroy(Artista $artista)
    {
        $artista->delete();

        return redirect()->route('artistas.index')->with('ok', 'Artista eliminado.');
    }
}
