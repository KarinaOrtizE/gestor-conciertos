<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'generos' => $this->generos,
            'imagen_url' => $this->imagen_url,
            'spotify_id' => $this->spotify_id,
            'eventos_count' => $this->whenCounted('eventos'),
            'eventos' => EventoResource::collection($this->whenLoaded('eventos')),
            'creado' => $this->created_at?->toIso8601String(),
            'actualizado' => $this->updated_at?->toIso8601String(),
        ];
    }
}
