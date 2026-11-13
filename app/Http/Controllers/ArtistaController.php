<?php

namespace App\Http\Controllers;

use App\Models\Artista;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre'     => 'required|string|max:255',
            'spotify_id' => 'nullable|string|max:255|unique:artistas,spotify_id',
            'imagen_url' => 'nullable|url|max:500',
            'generos'    => 'nullable|string|max:255',
        ]);

        Artista::create($datos);

        return redirect()->route('artistas.index')->with('ok', 'Artista creado correctamente.');
    }

    public function show(Artista $artista)
    {
        $artista->load('eventos');
        return view('artista.detalle', compact('artista'));
    }

    public function edit(Artista $artista)
    {
        return view('artista.formEditar', compact('artista'));
    }

    public function update(Request $request, Artista $artista)
    {
        $datos = $request->validate([
            'nombre'     => 'required|string|max:255',
            'spotify_id' => 'nullable|string|max:255|unique:artistas,spotify_id,' . $artista->id,
            'imagen_url' => 'nullable|url|max:500',
            'generos'    => 'nullable|string|max:255',
        ]);

        $artista->update($datos);

        return redirect()->route('artistas.index')->with('ok', 'Artista actualizado correctamente.');
    }

    public function destroy(Artista $artista)
    {
        $artista->delete();

        return redirect()->route('artistas.index')->with('ok', 'Artista eliminado.');
    }
}