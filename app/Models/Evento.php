<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
protected $fillable = ['titulo', 'fecha', 'lugar', 'ciudad', 'precio', 'genero', 'afiche', 'artista_id'];

protected $casts = ['fecha' => 'datetime'];

public function artista()
{
    return $this->belongsTo(Artista::class);
}

public function usuariosFavoritos()
{
    return $this->belongsToMany(User::class);
}
}
