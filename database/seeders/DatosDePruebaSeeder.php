<?php

namespace Database\Seeders;

use App\Models\Artista;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

/**
 * Artistas y eventos de ejemplo para ver la aplicación con contenido.
 * Se quitan con `php artisan datos-prueba:borrar` (ver README).
 */
class DatosDePruebaSeeder extends Seeder
{
    public const ARTISTAS = [
        ['nombre' => 'canario', 'generos' => null, 'spotify_id' => null, 'imagen_url' => null],
        ['nombre' => 'Aterciopelados', 'generos' => 'Rock', 'spotify_id' => '3MqjsWDLhq8SyY6N3PE8yW', 'imagen_url' => 'https://i.scdn.co/image/ab6761610000e5eb38be4b4a83a69726b9e38b05'],
        ['nombre' => 'Grupo Niche', 'generos' => 'Salsa', 'spotify_id' => '1zng9JZpblpk48IPceRWs8', 'imagen_url' => 'https://i.scdn.co/image/ab6761610000e5eb74a0064f0d1d4c849d0e249b'],
        ['nombre' => 'Bomba Estéreo', 'generos' => 'Electrónica', 'spotify_id' => '5n9bMYfz9qss2VOW89EVs2', 'imagen_url' => 'https://i.scdn.co/image/ab6761610000e5ebb6138f2e113c0f569b2c3f5a'],
        ['nombre' => 'Silvestre Dangond', 'generos' => 'Vallenato', 'spotify_id' => '3OcvS8PzSGYMBvLdzY6g3e', 'imagen_url' => 'https://i.scdn.co/image/ab6761610000e5eb4defd9099c89b37bd67c74b7'],
    ];

    // Las fechas se cuentan en días desde hoy para que los eventos sigan siendo próximos al sembrarlos.
    private const EVENTOS = [
        ['titulo' => 'Concierto Clima Demo', 'artista' => 'canario', 'dias' => 3, 'hora' => '20:00', 'lugar' => 'Movistar Arena', 'ciudad' => 'Bogotá', 'genero' => 'Rock', 'precio' => 80000],
        ['titulo' => 'Ciudad Rara Demo', 'artista' => 'canario', 'dias' => 4, 'hora' => '19:00', 'lugar' => 'Plaza', 'ciudad' => 'Xyzqwerty', 'genero' => 'Otro', 'precio' => 1000],
        ['titulo' => 'Bomba en vivo', 'artista' => 'Bomba Estéreo', 'dias' => 10, 'hora' => '20:00', 'lugar' => 'Estadio', 'ciudad' => 'Medellín', 'genero' => 'Electrónica', 'precio' => 90000],
        ['titulo' => 'Gira Florecita Rockera', 'artista' => 'Aterciopelados', 'dias' => 20, 'hora' => '20:00', 'lugar' => 'Movistar Arena', 'ciudad' => 'Bogotá', 'genero' => 'Rock', 'precio' => 150000, 'afiche' => 'afiche-florecita-rockera.png'],
        ['titulo' => 'Tour Vallenato', 'artista' => 'Silvestre Dangond', 'dias' => 30, 'hora' => '20:00', 'lugar' => 'Estadio', 'ciudad' => 'Barranquilla', 'genero' => 'Vallenato', 'precio' => 90000],
        ['titulo' => 'Salsa al Parque', 'artista' => 'Grupo Niche', 'dias' => 45, 'hora' => '20:00', 'lugar' => 'Movistar Arena', 'ciudad' => 'Bogotá', 'genero' => 'Salsa', 'precio' => 150000],
        ['titulo' => 'Vallenato Fest', 'artista' => 'Silvestre Dangond', 'dias' => 60, 'hora' => '20:00', 'lugar' => 'Estadio', 'ciudad' => 'Valledupar', 'genero' => 'Vallenato', 'precio' => 90000],
        ['titulo' => 'Noche de Rock', 'artista' => 'Aterciopelados', 'dias' => 80, 'hora' => '20:00', 'lugar' => 'Movistar Arena', 'ciudad' => 'Bogotá', 'genero' => 'Rock', 'precio' => 150000],
        ['titulo' => 'Electro Night', 'artista' => 'Bomba Estéreo', 'dias' => 95, 'hora' => '20:00', 'lugar' => 'Estadio', 'ciudad' => 'Cali', 'genero' => 'Electrónica', 'precio' => 90000],
        ['titulo' => 'Rock Sinfónico', 'artista' => 'Bomba Estéreo', 'dias' => 120, 'hora' => '20:00', 'lugar' => 'Estadio', 'ciudad' => 'Bogotá', 'genero' => 'Rock', 'precio' => 90000],
        ['titulo' => 'Parranda', 'artista' => 'Silvestre Dangond', 'dias' => 150, 'hora' => '20:00', 'lugar' => 'Estadio', 'ciudad' => 'Santa Marta', 'genero' => 'Vallenato', 'precio' => 90000],
    ];

    private const FAVORITOS_DE_TEST = ['Concierto Clima Demo', 'Bomba en vivo', 'Gira Florecita Rockera', 'Salsa al Parque'];

    public function run(): void
    {
        $artistas = collect(self::ARTISTAS)->mapWithKeys(fn (array $datos) => [
            $datos['nombre'] => Artista::updateOrCreate(['nombre' => $datos['nombre']], $datos),
        ]);

        foreach (self::EVENTOS as $datos) {
            [$hora, $minuto] = explode(':', $datos['hora']);
            $evento = Evento::firstOrNew(['titulo' => $datos['titulo'], 'artista_id' => $artistas[$datos['artista']]->id]);
            $evento->fill([
                'fecha' => now()->addDays($datos['dias'])->setTime((int) $hora, (int) $minuto),
                'lugar' => $datos['lugar'],
                'ciudad' => $datos['ciudad'],
                'genero' => $datos['genero'],
                'precio' => $datos['precio'],
            ]);

            if (isset($datos['afiche']) && ! $evento->afiche) {
                $evento->afiche = self::copiarAfiche($datos['afiche']);
            }

            $evento->save();
        }

        $test = User::where('email', 'test@example.com')->first();
        $test?->eventosFavoritos()->syncWithoutDetaching(
            Evento::whereIn('titulo', self::FAVORITOS_DE_TEST)->pluck('id')
        );
    }

    /** Vuelve a copiar los afiches de ejemplo que sigan en la base pero ya no estén en el disco. */
    public static function restaurarArchivos(): void
    {
        foreach (array_filter(array_column(self::EVENTOS, 'afiche')) as $archivo) {
            $ruta = 'afiches/datos-prueba-'.$archivo;

            if (Evento::where('afiche', $ruta)->exists() && ! Storage::disk('public')->exists($ruta)) {
                self::copiarAfiche($archivo);
            }
        }
    }

    private static function copiarAfiche(string $archivo): string
    {
        return Storage::disk('public')->putFileAs('afiches', new File(__DIR__.'/datos-prueba/'.$archivo), 'datos-prueba-'.$archivo);
    }
}
