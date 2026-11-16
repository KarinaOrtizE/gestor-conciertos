<?php

namespace Tests\Feature;

use App\Models\Artista;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReproductorTest extends TestCase
{
    use RefreshDatabase;

    private const EMBED = 'https://open.spotify.com/embed/artist/';

    protected function setUp(): void
    {
        parent::setUp();
        // El detalle del evento consulta el clima; aquí no interesa.
        Http::fake(['*' => Http::response([], 500)]);
    }

    private function evento(Artista $artista, string $titulo = 'Gira', int $dias = 30): Evento
    {
        return Evento::create([
            'titulo' => $titulo, 'fecha' => now()->addDays($dias), 'lugar' => 'Coliseo', 'ciudad' => 'Bogotá',
            'precio' => 100000, 'genero' => 'Rock', 'artista_id' => $artista->id,
        ]);
    }

    public function test_el_detalle_del_artista_muestra_el_reproductor_solo_si_tiene_spotify_id(): void
    {
        $usuario = User::factory()->create();
        $conSpotify = Artista::create(['nombre' => 'Aterciopelados', 'spotify_id' => '3MqjsWDLhq8SyY6N3PE8yW']);
        $sinSpotify = Artista::create(['nombre' => 'Banda local']);

        $this->actingAs($usuario)->get("/artistas/{$conSpotify->id}")
            ->assertSee(self::EMBED.'3MqjsWDLhq8SyY6N3PE8yW', false)
            ->assertSee('Canciones de Aterciopelados en Spotify');
        $this->actingAs($usuario)->get("/artistas/{$sinSpotify->id}")->assertDontSee(self::EMBED, false);
    }

    public function test_el_detalle_del_evento_muestra_el_reproductor_de_su_artista(): void
    {
        $usuario = User::factory()->create();
        $evento = $this->evento(Artista::create(['nombre' => 'Grupo Niche', 'spotify_id' => '1zng9JZpblpk48IPceRWs8']));
        $sinSpotify = $this->evento(Artista::create(['nombre' => 'Banda local']), 'Otro');

        $this->actingAs($usuario)->get("/eventos/{$evento->id}")
            ->assertSee('Escucha a Grupo Niche')
            ->assertSee(self::EMBED.'1zng9JZpblpk48IPceRWs8', false);
        $this->actingAs($usuario)->get("/eventos/{$sinSpotify->id}")->assertDontSee(self::EMBED, false);
    }

    public function test_favoritos_tiene_un_reproductor_con_los_artistas_sin_repetir(): void
    {
        $usuario = User::factory()->create();
        $niche = Artista::create(['nombre' => 'Grupo Niche', 'spotify_id' => '1zng9JZpblpk48IPceRWs8']);
        $bomba = Artista::create(['nombre' => 'Bomba Estéreo', 'spotify_id' => '5n9bMYfz9qss2VOW89EVs2']);
        $local = Artista::create(['nombre' => 'Banda local']);
        $usuario->eventosFavoritos()->attach([
            $this->evento($bomba, 'Bomba en vivo', 60)->id,
            $this->evento($niche, 'Salsa uno', 10)->id,
            $this->evento($niche, 'Salsa dos', 20)->id,
            $this->evento($local, 'Sin Spotify', 5)->id,
        ]);

        $html = $this->actingAs($usuario)->get('/favoritos')
            ->assertSee('Escucha a los artistas de tus favoritos')
            ->assertSeeInOrder(['Grupo Niche', 'Bomba Estéreo'])
            ->getContent();

        // El reproductor arranca con el artista del evento más cercano.
        $this->assertStringContainsString('src="'.self::EMBED.'1zng9JZpblpk48IPceRWs8"', $html);
        $this->assertSame(1, substr_count($html, "actual = '1zng9JZpblpk48IPceRWs8'"));
    }

    public function test_sin_artistas_con_spotify_no_aparece_el_reproductor(): void
    {
        $usuario = User::factory()->create();
        $usuario->eventosFavoritos()->attach($this->evento(Artista::create(['nombre' => 'Banda local'])));

        $this->actingAs($usuario)->get('/favoritos')
            ->assertSee('Banda local')
            ->assertDontSee('Escucha a los artistas de tus favoritos')
            ->assertDontSee(self::EMBED, false);
    }
}
