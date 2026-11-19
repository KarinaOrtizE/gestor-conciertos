<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArtistaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'spotify_id' => ['nullable', 'string', 'regex:/^[A-Za-z0-9]{22}$/', Rule::unique('artistas', 'spotify_id')->ignore($this->route('artista'))],
            'imagen_url' => ['nullable', 'url', 'max:500'],
            'generos' => ['nullable', 'string', 'max:255'],
        ];
    }
}
