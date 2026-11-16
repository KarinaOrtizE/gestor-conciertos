<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventoRequest;
use App\Models\Artista;
use App\Models\Evento;
use App\Services\ClimaService;

class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::with('artista')->orderBy('fecha')->get();

        return view('evento.listado', compact('eventos'));
    }

    public function create()
    {
        $artistas = Artista::orderBy('nombre')->get();
        $generos = Evento::GENEROS;

        return view('evento.formCrear', compact('artistas', 'generos'));
    }

    public function store(EventoRequest $request)
    {
        Evento::create($request->datos());

        return redirect()->route('eventos.index')->with('ok', 'Evento creado correctamente.');
    }

    public function show(Evento $evento, ClimaService $clima)
    {
        $evento->load('artista');
        $clima = $clima->pronostico($evento);

        return view('evento.detalle', compact('evento', 'clima'));
    }

    public function edit(Evento $evento)
    {
        $artistas = Artista::orderBy('nombre')->get();
        $generos = Evento::GENEROS;

        return view('evento.formEditar', compact('evento', 'artistas', 'generos'));
    }

    public function update(EventoRequest $request, Evento $evento)
    {
        $evento->update($request->datos());

        return redirect()->route('eventos.index')->with('ok', 'Evento actualizado correctamente.');
    }

    public function destroy(Evento $evento)
    {
        $evento->delete();

        return redirect()->route('eventos.index')->with('ok', 'Evento eliminado.');
    }
}
