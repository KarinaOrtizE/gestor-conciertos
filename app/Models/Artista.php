<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Artista extends Model
{
    protected $fillable = ['nombre', 'spotify_id', 'imagen_url', 'generos'];

    protected static function booted(): void
    {
        // La base borra los eventos en cascada sin pasar por el modelo, así que sus afiches se quitan aquí.
        static::deleting(function (Artista $artista) {
            Storage::disk('public')->delete(
                $artista->eventos()->whereNotNull('afiche')->pluck('afiche')->all()
            );
        });
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }
}
