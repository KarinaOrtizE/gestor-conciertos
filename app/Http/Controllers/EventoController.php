<?php

namespace App\Http\Controllers;

use App\Models\Artista;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventoController extends Controller
{
    public const GENEROS = ['Rock', 'Pop', 'Reggaetón', 'Salsa', 'Vallenato', 'Electrónica', 'Hip Hop', 'Jazz', 'Otro'];

    private function reglas(): array
    {
        return [
            'titulo'     => 'required|string|max:255',
            'fecha'      => 'required|date',
            'lugar'      => 'required|string|max:255',
            'ciudad'     => 'required|string|max:255',
            'precio'     => 'required|numeric|min:0',
            'genero'     => 'required|string|max:100',
            'artista_id' => 'required|exists:artistas,id',
            'afiche'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function index()
    {
        $eventos = Evento::with('artista')->orderBy('fecha')->get();
        return view('evento.listado', compact('eventos'));
    }

    public function create()
    {
        $artistas = Artista::orderBy('nombre')->get();
        $generos = self::GENEROS;
        return view('evento.formCrear', compact('artistas', 'generos'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas());

        if ($request->hasFile('afiche')) {
            $datos['afiche'] = $request->file('afiche')->store('afiches', 'public');
        }

        Evento::create($datos);

        return redirect()->route('eventos.index')->with('ok', 'Evento creado correctamente.');
    }

    public function show(Evento $evento)
    {
        $evento->load('artista');
        return view('evento.detalle', compact('evento'));
    }

    public function edit(Evento $evento)
    {
        $artistas = Artista::orderBy('nombre')->get();
        $generos = self::GENEROS;
        return view('evento.formEditar', compact('evento', 'artistas', 'generos'));
    }

    public function update(Request $request, Evento $evento)
    {
        $datos = $request->validate($this->reglas());

        if ($request->hasFile('afiche')) {
            if ($evento->afiche) {
                Storage::disk('public')->delete($evento->afiche);
            }
            $datos['afiche'] = $request->file('afiche')->store('afiches', 'public');
        } else {
            unset($datos['afiche']);
        }

        $evento->update($datos);

        return redirect()->route('eventos.index')->with('ok', 'Evento actualizado correctamente.');
    }

    public function destroy(Evento $evento)
    {
        if ($evento->afiche) {
            Storage::disk('public')->delete($evento->afiche);
        }
        $evento->delete();

        return redirect()->route('eventos.index')->with('ok', 'Evento eliminado.');
    }
}