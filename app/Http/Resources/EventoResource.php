<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'fecha' => $this->fecha->toIso8601String(),
            'lugar' => $this->lugar,
            'ciudad' => $this->ciudad,
            'precio' => (float) $this->precio,
            'genero' => $this->genero,
            'afiche_url' => $this->aficheUrl(),
            'artista_id' => $this->artista_id,
            'artista' => new ArtistaResource($this->whenLoaded('artista')),
            'creado' => $this->created_at?->toIso8601String(),
            'actualizado' => $this->updated_at?->toIso8601String(),
        ];
    }
}
