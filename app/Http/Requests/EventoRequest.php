<?php

namespace App\Http\Requests;

use App\Models\Evento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'fecha' => ['required', 'date'],
            'lugar' => ['required', 'string', 'max:255'],
            'ciudad' => ['required', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0'],
            'genero' => ['required', Rule::in(Evento::GENEROS)],
            'artista_id' => ['required', 'exists:artistas,id'],
            'afiche' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /** Datos listos para guardar: si llegó un afiche nuevo, se sube y se guarda su ruta. */
    public function datos(): array
    {
        $datos = $this->safe()->except('afiche');

        if ($this->hasFile('afiche')) {
            $datos['afiche'] = $this->file('afiche')->store('afiches', 'public');
        }

        return $datos;
    }
}
