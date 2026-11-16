<?php

namespace Tests\Feature;

use App\Models\Artista;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SpotifyTest extends TestCase
{
    use RefreshDatabase;

    private function configurar(): void
    {
        config(['services.spotify.client_id' => 'id-prueba', 'services.spotify.client_secret' => 'secreto-prueba']);
    }

    private function fingirSpotify(): void
    {
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'token-prueba', 'expires_in' => 3600]),
            'api.spotify.com/v1/search*' => Http::response(['artists' => ['items' => [[
                'id' => '0FZK97MXMm5mUQ8mtudjuK',
                'name' => 'Aterciopelados',
                'genres' => ['rock en español', 'latin alternative'],
                'images' => [['url' => 'https://i.scdn.co/image/grande', 'width' => 640], ['url' => 'https://i.scdn.co/image/chica', 'width' => 160]],
                'external_urls' => ['spotify' => 'https://open.spotify.com/artist/0FZK97MXMm5mUQ8mtudjuK'],
            ]]]]),
        ]);
    }

    public function test_admin_busca_artistas_y_recibe_los_datos_del_formulario(): void
    {
        $this->configurar();
        $this->fingirSpotify();

        $this->actingAs(User::factory()->admin()->create())
            ->getJson('/artistas/spotify/buscar?q=aterciopelados')
            ->assertOk()
            ->assertExactJson(['artistas' => [[
                'spotify_id' => '0FZK97MXMm5mUQ8mtudjuK',
                'nombre' => 'Aterciopelados',
                'imagen_url' => 'https://i.scdn.co/image/grande',
                'generos' => 'Rock en español, Latin alternative',
                'url' => 'https://open.spotify.com/artist/0FZK97MXMm5mUQ8mtudjuK',
            ]]]);

        Http::assertSent(fn ($r) => str_contains($r->url(), 'api.spotify.com') && $r->hasHeader('Authorization', 'Bearer token-prueba'));
    }

    public function test_el_token_se_reutiliza_entre_busquedas(): void
    {
        $this->configurar();
        $this->fingirSpotify();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->getJson('/artistas/spotify/buscar?q=uno')->assertOk();
        $this->actingAs($admin)->getJson('/artistas/spotify/buscar?q=dos')->assertOk();

        Http::assertSentCount(3);
        $this->assertSame('token-prueba', Cache::get('spotify.token'));
    }

    public function test_solo_el_admin_puede_buscar(): void
    {
        $this->configurar();
        $this->fingirSpotify();

        $this->getJson('/artistas/spotify/buscar?q=x')->assertUnauthorized();
        $this->actingAs(User::factory()->create())->getJson('/artistas/spotify/buscar?q=xx')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_sin_credenciales_responde_503_y_el_formulario_no_muestra_el_buscador(): void
    {
        config(['services.spotify.client_id' => null, 'services.spotify.client_secret' => null]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->getJson('/artistas/spotify/buscar?q=xx')
            ->assertStatus(503)->assertJsonPath('mensaje', 'Spotify no está configurado.');
        $this->actingAs($admin)->get('/artistas/create')->assertOk()->assertDontSee('Buscar en Spotify');
    }

    public function test_con_credenciales_el_formulario_muestra_el_buscador(): void
    {
        $this->configurar();

        $this->actingAs(User::factory()->admin()->create())->get('/artistas/create')->assertSee('Buscar en Spotify');
    }

    public function test_credenciales_invalidas_responden_503_con_mensaje(): void
    {
        $this->configurar();
        Http::fake(['accounts.spotify.com/*' => Http::response(['error' => 'invalid_client'], 400)]);

        $this->actingAs(User::factory()->admin()->create())->getJson('/artistas/spotify/buscar?q=xx')
            ->assertStatus(503)
            ->assertJsonPath('mensaje', 'No se pudo autenticar con Spotify. Revisa las credenciales.');
    }

    public function test_spotify_id_debe_tener_el_formato_de_spotify_y_el_detalle_enlaza_al_artista(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/artistas', ['nombre' => 'X', 'spotify_id' => 'no-valido'])->assertSessionHasErrors('spotify_id');

        $this->actingAs($admin)->post('/artistas', ['nombre' => 'Aterciopelados', 'spotify_id' => '0FZK97MXMm5mUQ8mtudjuK'])
            ->assertSessionHasNoErrors();
        $artista = Artista::firstOrFail();
        $this->actingAs($admin)->get("/artistas/{$artista->id}")
            ->assertSee('https://open.spotify.com/artist/0FZK97MXMm5mUQ8mtudjuK');
    }
}
