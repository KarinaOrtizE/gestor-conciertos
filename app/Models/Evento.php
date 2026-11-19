<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Evento extends Model
{
    public const GENEROS = ['Rock', 'Pop', 'Reggaetón', 'Salsa', 'Vallenato', 'Electrónica', 'Hip Hop', 'Jazz', 'Otro'];

    protected $fillable = ['titulo', 'fecha', 'lugar', 'ciudad', 'precio', 'genero', 'afiche', 'artista_id'];

    protected $casts = ['fecha' => 'datetime'];

    protected static function booted(): void
    {
        // El archivo del afiche sigue al registro: se borra al reemplazarlo o al eliminar el evento.
        static::updated(function (Evento $evento) {
            if ($evento->wasChanged('afiche') && $evento->getOriginal('afiche')) {
                Storage::disk('public')->delete($evento->getOriginal('afiche'));
            }
        });

        static::deleted(function (Evento $evento) {
            if ($evento->afiche) {
                Storage::disk('public')->delete($evento->afiche);
            }
        });
    }

    public function artista()
    {
        return $this->belongsTo(Artista::class);
    }

    public function usuariosFavoritos()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
