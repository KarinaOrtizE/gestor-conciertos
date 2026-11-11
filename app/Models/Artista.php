<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artista extends Model
{
protected $fillable = ['nombre', 'spotify_id', 'imagen_url', 'generos'];

public function eventos()
{
    return $this->hasMany(Evento::class);
}
}
