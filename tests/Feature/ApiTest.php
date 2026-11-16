<?php

namespace Tests\Feature;

use App\Models\Artista;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private Artista $artista;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->artista = Artista::create(['nombre' => 'Aterciopelados', 'generos' => 'Rock']);
    }

    private function evento(array $extra = []): Evento
    {
        return Evento::create(array_merge([
            'titulo' => 'Gira', 'fecha' => now()->addMonth(), 'lugar' => 'Coliseo', 'ciudad' => 'Bogotá',
            'precio' => 120000, 'genero' => 'Rock', 'artista_id' => $this->artista->id,
        ], $extra));
    }

    private function datosEvento(array $extra = []): array
    {
        return array_merge([
            'titulo' => 'Nuevo', 'fecha' => '2027-02-01 20:00', 'lugar' => 'Arena', 'ciudad' => 'Cali',
            'precio' => 90000, 'genero' => 'Salsa', 'artista_id' => $this->artista->id,
        ], $extra);
    }

    public function test_lista_eventos_paginados_con_su_artista_sin_token(): void
    {
        $this->evento(['titulo' => 'Primero', 'fecha' => now()->addDay()]);
        $this->evento(['titulo' => 'Segundo', 'fecha' => now()->addDays(2)]);

        $this->getJson('/api/eventos?por_pagina=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Primero')
            ->assertJsonPath('data.0.precio', 120000)
            ->assertJsonPath('data.0.artista.nombre', 'Aterciopelados')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonStructure(['data' => [['id', 'titulo', 'fecha', 'lugar', 'ciudad', 'precio', 'genero', 'afiche_url', 'artista']], 'links', 'meta']);
    }

    public function test_filtra_eventos(): void
    {
        $this->evento(['titulo' => 'Rock en Bogotá']);
        $this->evento(['titulo' => 'Salsa en Cali', 'genero' => 'Salsa', 'ciudad' => 'Cali']);
        $this->evento(['titulo' => 'Rock pasado', 'fecha' => now()->subMonth()]);
        $this->evento(['titulo' => 'Promo 50%']);

        $titulos = fn (string $url) => collect($this->getJson($url)->assertOk()->json('data'))->pluck('titulo')->all();

        $this->assertSame(['Salsa en Cali'], $titulos('/api/eventos?genero=Salsa'));
        $this->assertSame(['Salsa en Cali'], $titulos('/api/eventos?ciudad=Cali'));
        $this->assertNotContains('Rock pasado', $titulos('/api/eventos?proximos=1'));
        $this->assertSame(['Promo 50%'], $titulos('/api/eventos?q=%25'));
    }

    public function test_detalle_de_artista_incluye_sus_eventos(): void
    {
        $this->evento(['titulo' => 'Gira']);

        $this->getJson("/api/artistas/{$this->artista->id}")
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Aterciopelados')
            ->assertJsonPath('data.eventos.0.titulo', 'Gira');

        $this->getJson('/api/artistas')->assertOk()->assertJsonPath('data.0.eventos_count', 1);
    }

    public function test_recurso_inexistente_responde_404_en_json(): void
    {
        $this->get('/api/eventos/999')->assertNotFound()->assertJsonStructure(['message']);
    }

    public function test_login_entrega_token_y_rechaza_credenciales_malas(): void
    {
        User::factory()->admin()->create(['email' => 'admin@example.com']);

        $this->postJson('/api/login', ['email' => 'admin@example.com', 'password' => 'mala'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Estas credenciales no coinciden con nuestros registros.');

        $token = $this->postJson('/api/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('usuario.rol', 'admin')
            ->json('token');

        $this->withToken($token)->postJson('/api/eventos', $this->datosEvento())->assertCreated();
    }

    public function test_logout_revoca_el_token(): void
    {
        $user = User::factory()->admin()->create();
        $token = $user->createToken('prueba')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_gestion_requiere_token_y_rol_admin(): void
    {
        $evento = $this->evento();

        $this->postJson('/api/eventos', $this->datosEvento())->assertUnauthorized();
        $this->deleteJson("/api/eventos/{$evento->id}")->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/eventos', $this->datosEvento())->assertForbidden();
        $this->deleteJson("/api/artistas/{$this->artista->id}")->assertForbidden();

        $this->assertSame(1, Evento::count());
    }

    public function test_admin_gestiona_eventos_con_afiche(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $id = $this->post('/api/eventos', $this->datosEvento(['afiche' => UploadedFile::fake()->image('a.png')]))
            ->assertCreated()
            ->assertJsonPath('data.artista.nombre', 'Aterciopelados')
            ->json('data.id');
        $afiche = Evento::find($id)->afiche;
        Storage::disk('public')->assertExists($afiche);

        $this->putJson("/api/eventos/{$id}", $this->datosEvento(['titulo' => 'Editado']))
            ->assertOk()->assertJsonPath('data.titulo', 'Editado');
        $this->assertSame($afiche, Evento::find($id)->afiche);

        $this->deleteJson("/api/eventos/{$id}")->assertNoContent();
        Storage::disk('public')->assertMissing($afiche);
    }

    public function test_admin_gestiona_artistas(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $id = $this->postJson('/api/artistas', ['nombre' => 'Grupo Niche'])->assertCreated()->json('data.id');
        $this->patchJson("/api/artistas/{$id}", ['nombre' => 'Grupo Niche (live)'])->assertOk();
        $this->assertSame('Grupo Niche (live)', Artista::find($id)->nombre);
        $this->deleteJson("/api/artistas/{$id}")->assertNoContent();
        $this->assertNull(Artista::find($id));
    }

    public function test_validacion_responde_422_en_espanol(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/eventos', $this->datosEvento(['genero' => 'Tango', 'titulo' => '']))
            ->assertUnprocessable()
            ->assertJsonPath('errors.titulo.0', 'El campo título es obligatorio.')
            ->assertJsonPath('errors.genero.0', 'El valor seleccionado en género no es válido.');
    }
}
